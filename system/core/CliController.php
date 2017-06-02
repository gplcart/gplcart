<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

/**
 * Base parent CLI controller
 */
class CliController
{

    /**
     * Cli helper class instance
     * @var \gplcart\core\helpers\Cli $cli
     */
    protected $cli;

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Validator model instance
     * @var \gplcart\core\models\Validator $validator
     */
    protected $validator;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Logger class instance
     * @var \gplcart\core\Logger $logger
     */
    protected $logger;

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
     * An array of the current CLI route data
     * @var array
     */
    protected $current_route = array();

    /**
     * The current CLI command
     * @var string
     */
    protected $command;

    /**
     * The current CLI command arguments
     * @var array
     */
    protected $arguments = array();

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
     * Constructor
     */
    public function __construct()
    {
        $this->setInstanceProperties();
        $this->setRouteProperties();
        $this->outputHelp();

        $this->hook->fire('construct.cli.controller', $this);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->hook->fire('destruct.cli.controller', $this);
    }

    /**
     * Handle calls to unexisting static methods
     * @param string $method
     * @param array $args
     */
    public static function __callStatic($method, $args)
    {
        if (strpos($method, 'composer') === 0 && defined('GC_VERSION')) {
            /* @var $hook \gplcart\core\Hook */
            $hook = Container::get('gplcart\\core\\Hook');
            $hook->fire('cli.composer', $method, $args, $this);
        }
    }

    /**
     * Sets class instance properties
     */
    protected function setInstanceProperties()
    {
        $this->hook = Container::get('gplcart\\core\\Hook');
        $this->config = Container::get('gplcart\\core\\Config');
        $this->logger = Container::get('gplcart\\core\\Logger');
        $this->route = Container::get('gplcart\\core\\CliRoute');
        $this->cli = Container::get('gplcart\\core\\helpers\Cli');
        $this->user = Container::get('gplcart\\core\\models\\User');
        $this->language = Container::get('gplcart\\core\\models\\Language');
        $this->validator = Container::get('gplcart\\core\\models\\Validator');
    }

    /**
     * Sets route properties
     */
    protected function setRouteProperties()
    {
        $this->current_route = $this->route->get();
        $this->command = $this->current_route['command'];
        $this->arguments = gplcart_array_trim($this->current_route['arguments'], true);
    }

    /**
     * Returns a property
     * @param string $name
     * @return object
     */
    public function getProperty($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new \InvalidArgumentException("Property $name does not exist");
    }

    /**
     * Returns a translated string
     * @param string $text
     * @param array $arguments
     * @return string
     */
    public function text($text, array $arguments = array())
    {
        return $this->language->text($text, $arguments);
    }

    /**
     * Sets an array of submitted mapped data
     * @param array $map
     * @param null|array $arguments
     * @param array $default
     * @return array
     */
    public function setSubmittedMapped(array $map, $arguments = null,
            $default = array())
    {
        $mapped = $this->mapArguments($map, $arguments);
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
            gplcart_array_set_value($this->submitted, $key, $data);
            return $this->submitted;
        }

        return $this->submitted = (array) $data;
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
            $value = gplcart_array_get_value($this->submitted, $key);
            return isset($value) ? $value : $default;
        }
        return $this->submitted;
    }

    /**
     * Returns an array of filtered arguments
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Returns a single argument value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getArgument($key, $default = null)
    {
        return isset($this->arguments[$key]) ? $this->arguments[$key] : $default;
    }

    /**
     * Returns the cli command
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Whether a error is set
     * @param null|string $key
     * @return boolean
     */
    public function isError($key = null)
    {
        $value = $this->getError($key);
        return !empty($value);
    }

    /**
     * Whether a submitted kay is not empty
     * @param string $key
     * @return boolean
     */
    public function isSubmitted($key)
    {
        return (bool) $this->getSubmitted($key);
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
            gplcart_array_set_value($this->errors, $key, $error);
            return $this->errors;
        }
        return $this->errors = (array) $error;
    }

    /**
     * Returns a single error or an array of all defined errors
     * @param null|string $key
     * @return string|array
     */
    public function getError($key = null)
    {
        if (isset($key)) {
            return gplcart_array_get_value($this->errors, $key);
        }
        return $this->errors;
    }

    /**
     * Outputs and clears all existing errors
     * @param null|string|array $errors
     * @param boolean $abort
     */
    public function outputErrors($errors = null, $abort = false)
    {
        if (isset($errors)) {
            $this->errors = (array) $errors;
        }

        if (!empty($this->errors)) {
            $this->error(implode("\n", gplcart_array_flatten($this->errors)));
            $this->errors = array();
            if ($abort) {
                $this->abort();
            }

            $this->line();
        }
    }

    /**
     * Output all to the user
     */
    public function output()
    {
        $this->setError('php_errors', $this->logger->getPhpErrors());
        $this->outputErrors(null, true);
    }

    /**
     * Displays --help message for the current command
     */
    public function outputHelp()
    {
        if (!empty($this->arguments['help'])) {
            $this->outputCommandHelpMessage();
            $this->abort();
        }
    }

    /**
     * Output a formatted help message
     */
    public function outputCommandHelpMessage()
    {
        $output = false;
        if (!empty($this->current_route['help']['description'])) {
            $output = true;
            $this->line($this->text($this->current_route['help']['description']));
        }

        if (!empty($this->current_route['help']['options'])) {
            $output = true;
            $this->line($this->text('Options'));
            foreach ($this->current_route['help']['options'] as $option => $description) {
                $vars = array('@option' => $option, '@description' => $this->text($description));
                $this->line($this->text('  @option - @description', $vars));
            }
        }

        if (!$output) {
            $this->line($this->text('No description available'));
        }
    }

    /**
     * Help command callback. Lists all available commands
     */
    public function help()
    {
        $this->line($this->text('List of available commands. To see help for a certain command use --help option'));

        foreach ($this->route->getList() as $command => $info) {
            $description = $this->text('No description available');
            if (!empty($info['help']['description'])) {
                $description = $this->text($info['help']['description']);
            }

            $vars = array('@command' => $command, '@description' => $description);
            $this->line($this->text('  @command - @description', $vars));
        }

        $this->output();
    }

    /**
     * Map command line options to an array of submitted data to be passed to validators
     * @param array $map An array of pairs "options-name" => "some.array.value", e.g 'db-name' => 'database.name'
     * which turns --db-name command option into nested array $submitted['database']['name']
     * @param null|array $arguments
     * @return array
     */
    public function mapArguments(array $map, $arguments = null)
    {
        if (!isset($arguments)) {
            $arguments = $this->arguments;
        }

        $mapped = array();
        foreach ($arguments as $key => $value) {
            if (isset($map[$key]) && is_string($map[$key])) {
                gplcart_array_set_value($mapped, $map[$key], $value);
            }
        }

        return $mapped;
    }

    /**
     * Validates a submitted set of data
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
     * Whether an input passed a field validation
     * @param string $field
     * @return boolean
     */
    public function isValidInput($input, $field, $handler_id)
    {
        $this->setSubmitted($field, $input);
        $result = $this->validateComponent($handler_id, array('field' => $field));
        return $result === true;
    }

    /**
     * Output a error message
     * @param string $text
     * @return $this
     */
    public function error($text)
    {
        $this->cli->error($text);
        return $this;
    }

    /**
     * Output a text
     * @param string $text
     * @return $this
     */
    public function out($text)
    {
        $this->cli->out($text);
        return $this;
    }

    /**
     * Output a line with an optional text
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
     * @param string $default
     * @param string $marker
     */
    public function prompt($question, $default = '', $marker = ': ')
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
     * @return string
     */
    public function in($format = '')
    {
        return $this->cli->in($format);
    }

}
