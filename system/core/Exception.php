<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\Container;
use Exception as Base;

/**
 * Custom base exception class
 */
class Exception extends Base
{

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Constructor
     * @param mixed $message
     * @param numeric $code
     * @param Base $previous
     */
    public function __construct($message = null, $code = 0,
            Base $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->logger = Container::instance('core\\Logger');
    }

    /**
     * Formatted string for display
     * @return string
     */
    public function __toString()
    {
        return parent::__toString();
    }

    /**
     * Saves an exception to the database
     * @param string $message
     */
    public function log($message = '')
    {
        $error = $this->getMessageArray($message);
        $this->logger->log('php_exception', $error, 'danger');
    }

    /**
     * Returns a formatted message
     * @param string $message
     * @return string
     */
    public function getFormattedMessage($message = '')
    {
        $error = $this->getMessageArray($message);
        return $this->logger->errorMessage($error, 'PHP Exception');
    }

    /**
     * Returns an array of exception data to be rendered
     * @param string $message
     * @return array
     */
    protected function getMessageArray($message = '')
    {
        if ($message === '') {
            $message = $this->message;
        }

        $data = array(
            'message' => $this->message,
            'code' => $this->code,
            'file' => $this->file,
            'line' => $this->line
        );

        return $data;
    }
}
