<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use InvalidArgumentException;

/**
 * Base parent CLI controller
 */
class CliController
{

    /**
     * CLI helper class instance
     * @var \gplcart\core\helpers\Cli $cli
     */
    protected $cli;

    /**
     * Validator model instance
     * @var \gplcart\core\models\Validator $validator
     */
    protected $validator;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * CLI router class instance
     * @var \gplcart\core\CliRoute $route
     */
    protected $route;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * The current language code
     * @var string
     */
    protected $langcode = '';

    /**
     * The current CLI command
     * @var string
     */
    protected $command;

    /**
     * The current CLI command parameters
     * @var array
     */
    protected $params = array();

    /**
     * An array of mapped data ready for validation
     * @var array
     */
    protected $submitted = array();

    /**
     * An array of errors to output to the user
     * @var array
     */
    protected $errors = array();

    /**
     * An array of the current CLI route data
     * @var array
     */
    protected $current_route = array();

    /**
     * The current user
     * @var array
     */
    protected $current_user;

    /**
     * The current user ID
     * @var null|int
     */
    protected $uid = null;

    /**
     * Whether the controller is initialized
     * @var bool
     */
    private $initialized = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initialized = true;

        $this->setInstanceProperties();
        $this->setRouteProperties();
        $this->setLanguage();
        $this->setUser();

        $this->controlRouteAccess();
        $this->controlOptions();
        $this->controlArguments();

        $this->hook->attach('construct.cli.controller', $this);
        $this->outputHelp();
    }

    /**
     * Whether the controller is initialized
     * @return bool
     */
    final public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->hook->attach('destruct.cli.controller', $this);
    }

    /**
     * Sets class instance properties
     */
    protected function setInstanceProperties()
    {
        $this->hook = $this->getInstance('gplcart\\core\\Hook');
        $this->config = $this->getInstance('gplcart\\core\\Config');
        $this->route = $this->getInstance('gplcart\\core\\CliRoute');
        $this->cli = $this->getInstance('gplcart\\core\\helpers\Cli');
        $this->user = $this->getInstance('gplcart\\core\\models\\User');
        $this->language = $this->getInstance('gplcart\\core\\models\\Language');
        $this->validator = $this->getInstance('gplcart\\core\\models\\Validator');
        $this->translation = $this->getInstance('gplcart\\core\\models\\Translation');
    }

    /**
     * Sets the current language
     * @param null|string $code
     * @return $this
     */
    public function setLanguage($code = null)
    {
        if (!isset($code)) {
            $code = $this->config->get('cli_langcode', '');
        }

        $this->langcode = $code;
        $this->translation->set($code, null);

        return $this;
    }

    /**
     * Sets the current user from the command using -u option
     */
    protected function setUser()
    {
        $this->uid = $this->getParam('u', null);

        if (isset($this->uid)) {

            $this->current_user = $this->user->get($this->uid);

            if (empty($this->current_user['status'])) {
                $this->errorAndExit($this->text('No access'));
            }
        }
    }

    /**
     * Sets route properties
     */
    protected function setRouteProperties()
    {
        $this->current_route = $this->route->get();
        $this->command = $this->current_route['command'];
        $this->params = gplcart_array_trim($this->current_route['params'], true);
    }

    /**
     * Control access to the route
     * @param string $permission
     */
    protected function controlAccess($permission)
    {
        if (isset($this->uid) && !$this->user->access($permission, $this->uid)) {
            $this->errorAndExit($this->text('No access'));
        }
    }

    /**
     * Controls supported command options
     */
    protected function controlOptions()
    {
        $allowed = array();

        if (!empty($this->current_route['options'])) {
            foreach (array_keys($this->current_route['options']) as $options) {
                foreach (explode(',', $options) as $option) {
                    $allowed[trim(trim($option, '-'))] = true;
                }
            }
        }

        $submitted = $this->getOptions();
        if (!empty($submitted) && !array_intersect_key($submitted, $allowed)) {
            $this->errorAndExit($this->text('Unsupported command options'));
        }
    }

    /**
     * Controls supported command arguments
     */
    protected function controlArguments()
    {
        if (!empty($this->current_route['arguments'])) {
            $submitted = $this->getArguments();
            if (!empty($submitted) && !array_intersect_key($submitted, $this->current_route['arguments'])) {
                $this->errorAndExit($this->text('Unsupported command arguments'));
            }
        }
    }

    /**
     * Controls access to the current route
     */
    protected function controlRouteAccess()
    {
        if (!$this->config->get('cli_status', true)) {
            $this->errorAndExit($this->text('No access'));
        }

        $this->controlAccess('cli');

        if (isset($this->current_route['access'])) {
            $this->controlAccess($this->current_route['access']);
        }
    }

    /**
     * Returns the current user ID
     * @return array
     */
    public function getUser()
    {
        return $this->current_user;
    }

    /**
     * Returns a property
     * @param string $name
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getProperty($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new InvalidArgumentException("Property $name does not exist");
    }

    /**
     * Set a property
     * @param string $property
     * @param object $value
     * @return $this
     */
    public function setProperty($property, $value)
    {
        $this->{$property} = $value;
        return $this;
    }

    /**
     * Returns an object instance
     * @param string $class
     * @return object
     */
    public function getInstance($class)
    {
        return Container::get($class);
    }

    /**
     * Returns a translated string
     * @param string $text
     * @param array $arguments
     * @return string
     */
    public function text($text, array $arguments = array())
    {
        return $this->translation->text($text, $arguments);
    }

    /**
     * Returns a truncated string
     * @param string $string
     * @param integer $length
     * @param string $trimmarker
     * @return string
     */
    public function truncate($string, $length = 100, $trimmarker = '...')
    {
        return mb_strimwidth($string, 0, $length, $trimmarker, 'UTF-8');
    }

    /**
     * Sets an array of submitted mapped data
     * @param array $map
     * @param null|array $params
     * @param array $default
     * @return array
     */
    public function setSubmittedMapped(array $map, $params = null, array $default = array())
    {
        $mapped = $this->mapParams($map, $params);
        $merged = gplcart_array_merge($default, $mapped);

        return $this->setSubmitted(null, $merged);
    }

    /**
     * Sets a submitted data
     * @param null|string $key
     * @param mixed $data
     * @return array
     */
    public function setSubmitted($key, $data)
    {
        if (isset($key)) {
            gplcart_array_set($this->submitted, $key, $data);
            return $this->submitted;
        }

        return $this->submitted = (array) $data;
    }

    /**
     * Removes a value(s) from an array of submitted data
     * @param string|array $key
     * @return array
     */
    public function unsetSubmitted($key)
    {
        gplcart_array_unset($this->submitted, $key);
        return $this->submitted;
    }

    /**
     * Returns a submitted value
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     */
    public function getSubmitted($key = null, $default = null)
    {
        if (isset($key)) {
            $value = gplcart_array_get($this->submitted, $key);
            return isset($value) ? $value : $default;
        }

        return $this->submitted;
    }

    /**
     * Returns a single parameter value
     * @param string|array|null $key
     * @param mixed $default
     * @return mixed
     */
    public function getParam($key = null, $default = null)
    {
        if (!isset($key)) {
            return $this->params;
        }

        foreach ((array) $key as $k) {
            if (isset($this->params[$k])) {
                return $this->params[$k];
            }
        }

        return $default;
    }

    /**
     * Returns an array of command positional arguments
     * @return array
     */
    public function getArguments()
    {
        return array_filter($this->params, function ($key) {
            return is_int($key);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Returns an array of command options like --op or -o
     * @return array
     */
    public function getOptions()
    {
        return array_filter($this->params, function ($key) {
            return !is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Returns the current CLI command
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Whether a error exists
     * @param null|string $key
     * @return boolean
     */
    public function isError($key = null)
    {
        $value = $this->getError($key);
        return is_array($value) ? !empty($value) : isset($value);
    }

    /**
     * Whether a submitted key is not empty
     * @param string $key
     * @return boolean
     */
    public function isSubmitted($key)
    {
        return (bool) $this->getSubmitted($key);
    }

    /**
     * Formats a local time/date
     * @param integer $timestamp
     * @param bool $full
     * @return string
     */
    public function date($timestamp, $full = true)
    {
        if ($full) {
            $format = $this->config->get('date_full_format', 'd.m.Y H:i');
        } else {
            $format = $this->config->get('date_short_format', 'd.m.y');
        }

        return date($format, $timestamp);
    }

    /**
     * Sets an error
     * @param null|string $key
     * @param mixed $error
     * @return array
     */
    public function setError($key, $error)
    {
        if (isset($key)) {
            gplcart_array_set($this->errors, $key, $error);
            return $this->errors;
        }

        return $this->errors = (array) $error;
    }

    /**
     * Returns a single error or an array of all defined errors
     * @param null|string $key
     * @return mixed
     */
    public function getError($key = null)
    {
        if (isset($key)) {
            return gplcart_array_get($this->errors, $key);
        }

        return $this->errors;
    }

    /**
     * Output an error message and stop the script execution
     * @param string $text
     */
    public function errorAndExit($text)
    {
        $this->error($text)->abort(1);
    }

    /**
     * Print and clear up all existing errors
     * @param boolean $exit_on_error
     */
    public function errors($exit_on_error = false)
    {
        if (!empty($this->errors)) {

            foreach (gplcart_array_flatten($this->errors) as $error) {
                $this->error($error);
            }

            $this->errors = array();

            if ($exit_on_error) {
                $this->abort(1);
            }
        }
    }

    /**
     * Output all to the user and stop the script execution
     */
    public function output()
    {
        $this->errors(true);
        $this->abort();
    }

    /**
     * Map the command line parameters to an array of submitted data to be passed to validators
     * @param array $map
     * @param null|array $params
     * @return array
     */
    public function mapParams(array $map, $params = null)
    {
        if (!isset($params)) {
            $params = $this->params;
        }

        $mapped = array();

        foreach ($params as $key => $value) {
            if (isset($map[$key]) && is_string($map[$key])) {
                gplcart_array_set($mapped, $map[$key], $value);
            }
        }

        return $mapped;
    }

    /**
     * Validates a submitted data
     * @param string $handler_id
     * @param array $options
     * @return mixed
     */
    public function validateComponent($handler_id, array $options = array())
    {
        $result = $this->validator->run($handler_id, $this->submitted, $options);

        if ($result === true) {
            return true;
        }

        $this->setError(null, $result);
        return $result;
    }

    /**
     * Whether the user input passed the field validation
     * @param mixed $input
     * @param string $field
     * @param string $handler_id
     * @return bool
     */
    public function isValidInput($input, $field, $handler_id)
    {
        $this->setSubmitted($field, $input);
        return $this->validateComponent($handler_id, array('field' => $field)) === true;
    }

    /**
     * Validates a user input from prompt
     * @param string $field
     * @param string $label
     * @param string $validator
     * @param string|null $default
     */
    protected function validatePrompt($field, $label, $validator, $default = null)
    {
        $input = $this->prompt($label, $default);

        if (!$this->isValidInput($input, $field, $validator)) {
            $this->errors();
            // Prompt until correct input
            $this->validatePrompt($field, $label, $validator, $default);
        }
    }

    /**
     * Validates a chosen menu option
     * @param string $field
     * @param string $label
     * @param string $validator
     * @param array $options
     * @param null|string $default
     */
    protected function validateMenu($field, $label, $validator, array $options, $default = null)
    {
        $input = $this->menu($options, $default, $label);

        if (!$this->isValidInput($input, $field, $validator)) {
            $this->errors();
            // Show menu until correct choose
            $this->validateMenu($field, $label, $validator, $options, $default);
        }
    }

    /**
     * Output help for a certain command or the current command if a help option is specified
     * @param string|null $command
     */
    public function outputHelp($command = null)
    {
        $help_options = $this->config->get('cli_help_option', 'h');

        if (isset($command) || $this->getParam($help_options)) {

            if (!isset($command)) {
                $command = $this->command;
            }

            $aliases = $this->route->getAliases();

            if (isset($aliases[$command])) {
                $command = $aliases[$command];
            }

            $routes = $this->route->getList();

            if (empty($routes[$command])) {
                $this->errorAndExit($this->text('Unknown command'));
            }

            $this->printHelpText($routes[$command]);
            $this->output();
        }
    }

    /**
     * Print command help text
     * @param array $command
     */
    protected function printHelpText(array $command)
    {
        $shown = false;

        if (!empty($command['description'])) {
            $shown = true;
            $this->line($this->text($command['description']));
        }

        if (!empty($command['usage'])) {
            $shown = true;
            $this->line()->line($this->text('Usage:'));
            foreach ($command['usage'] as $usage) {
                $this->line($usage);
            }
        }

        if (!empty($command['options'])) {
            $shown = true;
            $this->line()->line($this->text('Options:'));
            foreach ($command['options'] as $name => $description) {
                $vars = array('@option' => $name, '@description' => $this->text($description));
                $this->line($this->text('  @option  @description', $vars));
            }
        }

        if (!$shown) {
            $this->line($this->text('No help found for the command'));
        }
    }

    /**
     * Shows language selector and validates user's input
     * @param null|string $langcode
     * @return string
     */
    public function selectLanguage($langcode = null)
    {
        $languages = array();

        foreach ($this->language->getList() as $code => $language) {
            if ($code === 'en' || is_file($this->translation->getFile($code))) {
                $languages[$code] = $language['name'];
            }
        }

        if (count($languages) < 2 && !isset($langcode)) {
            return null;
        }

        if (isset($langcode)) {
            $selected = $langcode;
        } else {
            $selected = $this->menu($languages, 'en', $this->text('Language (enter a number)'));
        }

        if (empty($languages[$selected])) {
            $this->error($this->text('Invalid language'));
            $this->selectLanguage();
        } else {
            $this->langcode = (string) $selected;
            $this->translation->set($this->langcode, null);
            $this->config->set('cli_langcode', $this->langcode);
        }

        return $this->langcode;
    }

    /**
     * Output an error message
     * @param string $text
     * @return $this
     */
    public function error($text)
    {
        $this->cli->error($text . PHP_EOL);
        return $this;
    }

    /**
     * Output inline text
     * @param string $text
     * @return $this
     */
    public function out($text)
    {
        $this->cli->out($text);
        return $this;
    }

    /**
     * Output a text line
     * @param string $text
     * @return $this
     */
    public function line($text = '')
    {
        $this->cli->line($text);
        return $this;
    }

    /**
     * Output an input prompt
     * @param string $question
     * @param string|null $default
     * @param string $marker
     * @return mixed
     */
    public function prompt($question, $default = null, $marker = ': ')
    {
        return $this->cli->prompt($question, $default, $marker);
    }

    /**
     * Presents a user with a multiple choice questions
     * @param string $question
     * @param string $choice
     * @param string $default
     * @return string
     */
    public function choose($question, $choice = 'yn', $default = 'n')
    {
        return $this->cli->choose($question, $choice, $default);
    }

    /**
     * Displays a menu where a user can enter a number to choose an option
     * @param array $items
     * @param mixed $default
     * @param string $title
     * @return mixed
     */
    public function menu(array $items, $default = null, $title = '')
    {
        return $this->cli->menu($items, $default, $title);
    }

    /**
     * Terminate the current script with an optional code or message
     * @param integer|string $code
     */
    public function abort($code = 0)
    {
        exit($code);
    }

    /**
     * Read the user input
     * @param string $format
     * @return string
     */
    public function in($format = '')
    {
        return $this->cli->in($format);
    }

    /**
     * Output simple table
     * @param array $data
     * @return $this
     */
    public function table(array $data)
    {
        $this->cli->table($data);
        return $this;
    }

}
