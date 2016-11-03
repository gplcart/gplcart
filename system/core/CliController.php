<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\classes\Tool;

/**
 * Basic CLI controller
 */
class CliController
{

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * CLI router class instance
     * @var \core\CliRoute $route
     */
    protected $route;

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
        /* @var $config \core\Config */
        $this->config = Container::instance('core\\Config');

        /* @var $logger \core\Logger */
        $this->logger = Container::instance('core\\Logger');

        /* @var $route \core\CliRoute */
        $this->route = Container::instance('core\\CliRoute');

        $this->current_route = $this->route->get();
        $this->command = $this->current_route['command'];

        $this->outputHelp();
    }

    /**
     * Sets an array of submitted mapped data
     * @param array $map
     * @param bool $filter
     * @return array
     */
    protected function setSubmitted(array $map, $filter = true)
    {
        $arguments = $this->getArguments($filter);
        $this->submitted = $this->mapArguments($arguments, $map);
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
            $result = Tool::getArrayValue($this->submitted, $key);
            return isset($result) ? $result : $default;
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
        return Tool::trimArray($this->current_route['arguments'], $filter);
    }

    /**
     * Sets a single message
     * @param string $message
     */
    protected function setMessage($message, $severity = '')
    {
        $this->messages[$message] = array($message, $severity);
    }

    /**
     * Sets php errors recorded by the logger
     */
    protected function setPhpErrors()
    {
        $errors = $this->logger->getErrors();
        foreach ($errors as $severity => $messages) {
            foreach ($messages as $message) {
                $this->setMessage($message, $severity);
            }
        }
    }

    /**
     * Whether a error is set
     * @param null|string $key
     * @return type
     */
    protected function isError($key = null)
    {
        if (isset($key)) {
            return isset($this->errors[$key]);
        }

        return !empty($this->errors);
    }

    /**
     * Returns a colored string
     * @param string $text
     * @param string $sevetity
     * @return string
     */
    protected function getColored($text, $sevetity)
    {
        $default = array(
            'info' => "\e[0;37;44m%s\e[0m\n",
            'warning' => "\e[0;30;43m%s\e[0m\n",
            'danger' => "\e[1;37;41m%s\e[0m\n"
        );

        $map = $this->config->get('cli_colors', $default);
        return isset($map[$sevetity]) ? sprintf($map[$sevetity], $text) : $text;
    }

    /**
     * Sets an error
     * @param string $error
     * @param string $key
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
     * Returns a user input
     * @return string
     */
    protected function getInput()
    {
        return fgets(STDIN);
    }

    /**
     * Outputs all defined messages
     * @param bool $exit
     */
    protected function outputMessages($exit = true)
    {
        foreach ($this->messages as $key => $message) {

            if (is_array($message)) {
                list($text, $sevetity) = $message;
                $message = $this->getColored($text, $sevetity);
            }

            fwrite(STDOUT, (string) $message);
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
            fwrite(STDERR, $this->getColored($this->errors[$key], 'danger'));
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
        foreach ($this->errors as $key => $error) {
            fwrite(STDERR, $this->getColored($error, 'danger'));
            unset($this->errors[$key]);
        }

        if ($exit) {
            exit(1);
        }
    }

    /**
     * Displays --help message for the curren command
     * @return null
     */
    protected function outputHelp()
    {
        $arguments = $this->getArguments();

        if (empty($arguments['help'])) {
            return null;
        }

        $message = '';
        if (!empty($this->current_route['help']['description'])) {
            $message .= 'Description: ' . $this->current_route['help']['description'] . "\n";
        }

        if (!empty($this->current_route['help']['options'])) {
            $message .= "Options:\n";
            foreach ($this->current_route['help']['options'] as $option => $description) {
                $message .= "    $option - $description\n";
            }
        }

        if (empty($message)) {
            $message = "Sorry. Developers were to lazy to describe this command\n";
        }

        $this->setMessage($message);
        $this->outputMessages(true);
        return null;
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
            if (isset($map[$key])) {
                Tool::setArrayValue($mapped, $map[$key], $value);
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

        $message = "List of available commands. To see command options use '--help' option:\n";
        foreach ($list as $command => $info) {
            $description = 'No description available';
            if (!empty($info['help']['description'])) {
                $description = $info['help']['description'];
            }

            $message .= "    $command - $description\n";
        }

        $this->setMessage($message);
        $this->output();
    }

}
