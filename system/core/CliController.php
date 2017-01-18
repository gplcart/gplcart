<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

/**
 * Basic CLI controller
 */
class CliController
{

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
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * An array of the current CLI route data
     * @var array
     */
    protected $current_route = array();

    /**
     * The current command
     * @var string
     */
    protected $command;

    /**
     * An array of mapped data ready for validation
     * @var array
     */
    protected $submitted = array();

    /**
     * An array of messages to output to the user
     * @var array
     */
    protected $messages = array();

    /**
     * An array of errors to output to the user
     * @var array
     */
    protected $errors = array();

    /**
     * Whether in dialog mode
     * @var bool
     */
    protected $dialog = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (GC_CLI_EMULATE) {
            ini_set('memory_limit', '-1');
            ini_set('max_execution_time', 0);
        }

        $this->setInstanceProperties();
        $this->setRouteProperties();
        $this->controlAccess();
        $this->outputHelp();
    }

    /**
     * Sets route properties
     */
    protected function setRouteProperties()
    {
        $this->current_route = $this->route->get();
        $this->command = $this->current_route['command'];
    }

    /**
     * Sets class instance properties
     */
    protected function setInstanceProperties()
    {
        $map = array(
            'user' => 'models\\User',
            'validator' => 'models\\Validator',
            'language' => 'models\\Language',
            'request' => 'helpers\Request',
            'config' => 'Config',
            'logger' => 'Logger',
            'route' => 'CliRoute'
        );

        foreach ($map as $property => $class) {
            $this->{$property} = Container::get("gplcart\\core\\$class");
        }
    }

    /**
     * Returns a translated string
     * @param string $text
     * @param array $arguments
     * @return string
     */
    protected function text($text, array $arguments = array())
    {
        return $this->language->text($text, $arguments);
    }

    /**
     * Controls global access
     */
    protected function controlAccess()
    {
        if (!GC_CLI_EMULATE) {
            return null;
        }

        if (!$this->config->tokenValid($this->request->post('cli_token'))) {
            $this->setError($this->text('Invalid CLI token'));
            $this->output();
        }

        if (!$this->user->access('cli')) {
            $this->setError($this->text('No access'));
            $this->output();
        }
    }

    /**
     * Sets an array of submitted mapped data
     * @param array $map
     * @param array $default
     * @param boolean $filter
     * @return array
     */
    protected function setSubmittedMapped($map, $default = array(),
            $filter = true)
    {
        $arguments = $this->getArguments($filter);
        $mapped = $this->mapArguments($arguments, $map);
        $data = gplcart_array_merge($default, $mapped);

        return $this->setSubmitted($data);
    }

    /**
     * Sets an array of submitted data
     * @param array $data
     */
    protected function setSubmitted(array $data)
    {
        $this->submitted = $data;
        return $this->submitted;
    }

    /**
     * Returns a submitted value
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     */
    protected function getSubmitted($key = null, $default = null)
    {
        if (isset($key)) {
            return array_key_exists($key, $this->submitted) ? $this->submitted[$key] : $default;
        }

        return $this->submitted;
    }

    /**
     * Returns an array of cleaned arguments
     * @param bool $filter
     * @return array
     */
    protected function getArguments($filter = true)
    {
        return gplcart_array_trim($this->current_route['arguments'], $filter);
    }

    /**
     * Sets a single message
     * @param string $message
     * @return \gplcart\core\CliController
     */
    protected function setMessage($message)
    {
        $this->messages[$message] = $message;
        return $this;
    }

    /**
     * Sets php errors recorded by the logger
     */
    protected function setPhpErrors()
    {
        $errors = $this->logger->getErrors();

        foreach ($errors as $messages) {
            foreach ($messages as $message) {
                $this->setMessage($message);
            }
        }
    }

    /**
     * Whether a error is set
     * @param null|string $key
     * @return boolean
     */
    protected function isError($key = null)
    {
        if (isset($key)) {
            return isset($this->errors[$key]);
        }

        return !empty($this->errors);
    }

    /**
     * Whether a submitted kay is not empty
     * @param string $key
     * @return boolean
     */
    protected function isSubmitted($key)
    {
        return (bool) $this->getSubmitted($key);
    }

    /**
     * Sets an error
     * @param string $error
     * @param string $key
     * @return array
     */
    protected function setError($error, $key = null)
    {
        if (isset($key)) {
            $this->errors[$key] = $error;
            return $this->errors;
        }

        $this->errors[] = $error;
        return $this->errors;
    }

    /**
     * Outputs all defined messages
     * @param bool $exit
     */
    protected function outputMessages($exit = true)
    {
        foreach ($this->messages as $key => $message) {
            $this->printMessage((string) $message);
            unset($this->messages[$key]);
        }

        if ($exit) {
            exit;
        }
    }

    /**
     * Outputs a error message(s)
     * @param null|string $key
     * @param bool $exit
     * @return null
     */
    protected function outputError($key, $exit = true)
    {
        if (isset($this->errors[$key])) {
            $this->printError($this->errors[$key]);
            unset($this->errors[$key]);
        }

        if ($exit) {
            exit(1);
        }
    }

    /**
     * Outputs all defined errors
     * @param bool $exit
     */
    protected function outputErrors($exit = true)
    {
        $errors = gplcart_array_flatten($this->errors);

        foreach ($errors as $error) {
            $this->printError($error);
        }

        $this->errors = array();

        if ($exit) {
            exit(1);
        }
    }

    /**
     * Prints an error message
     * @param string $error
     */
    protected function printError($error)
    {
        if (GC_CLI_EMULATE) {
            echo $this->prepare($error);
        } else {
            fwrite(STDERR, $error);
        }
    }

    /**
     * Prints a simple message
     * @param string $message
     */
    protected function printMessage($message)
    {
        if (GC_CLI_EMULATE) {
            echo $this->prepare($message);
        } else {
            fwrite(STDOUT, $message);
        }
    }

    /**
     * Prepares and filters a string before output
     * @param string $string
     * @return string
     */
    protected function prepare($string)
    {
        $filtered = filter_var($string, FILTER_SANITIZE_STRING);
        return nl2br(str_replace(' ', '&nbsp;', $filtered));
    }

    /**
     * Displays --help message for the current command
     */
    protected function outputHelp()
    {
        $arguments = $this->getArguments();

        if (!empty($arguments['help'])) {
            $message = $this->getHelpMessage();
            $this->setMessage($message)->outputMessages(true);
        }
    }

    /**
     * Returns a formatted help message
     * @return string
     */
    protected function getHelpMessage()
    {
        $message = '';

        if (!empty($this->current_route['help']['description'])) {
            $message .= $this->current_route['help']['description'] . PHP_EOL;
        }

        if (!empty($this->current_route['help']['options'])) {
            $list = array();
            foreach ($this->current_route['help']['options'] as $option => $description) {
                $list[] = "    $option - $description";
            }

            $message .= $this->text("Options:\n@list", array('@list' => implode(PHP_EOL, $list)));
        }

        if (empty($message)) {
            $message = $this->text('No description available') . PHP_EOL;
        }

        return $message;
    }

    /**
     * Outputs errors and messages to the user
     */
    protected function output()
    {
        $this->setPhpErrors();

        if ($this->isError()) {
            $this->outputErrors();
        }

        $this->outputMessages();
    }

    /**
     * Returns an array of mapped data
     * @param array $arguments
     * @param array $map
     * @return array
     */
    protected function mapArguments(array $arguments, array $map)
    {
        if (empty($map) || empty($arguments)) {
            return array();
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
     * Help command callback. Lists all available commands
     */
    public function help()
    {
        $list = $this->route->getList();

        $message = $this->text('List of available commands. To see command options use \'--help\' option:') . PHP_EOL;

        foreach ($list as $command => $info) {
            $description = $this->text('No description available');
            if (!empty($info['help']['description'])) {
                $description = $info['help']['description'];
            }

            $message .= "    $command - $description" . PHP_EOL;
        }

        $this->setMessage($message)->output();
    }

    /**
     * Validates a submitted data
     * @param string $handler_id
     * @param array $options
     * @return array
     */
    protected function validate($handler_id, array $options = array())
    {
        $result = $this->validator->run($handler_id, $this->submitted, $options);

        if ($result === true) {
            return array();
        }

        $this->errors = (array) $result;
        return $this->errors;
    }

}
