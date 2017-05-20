<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Cache,
    gplcart\core\Handler;

/**
 * Manages basic behaviors and data related to sending e-mails
 */
class Mail extends Model
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an array of email handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &Cache::memory(__METHOD__);

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = require GC_CONFIG_MAIL;
        $this->hook->fire('mail.handlers', $handlers);
        return $handlers;
    }

    /**
     * Sends an e-mail
     * @param string|array $to
     * @param string $subject
     * @param string $message
     * @param array $options
     * @return mixed
     */
    public function send($to, $subject, $message, array $options)
    {
        settype($to, 'array');
        $this->hook->fire('mail.send.before', $to, $subject, $message, $options);

        $result = null;
        $this->hook->fire('mail.send', $to, $subject, $message, $options, $result);

        if (isset($result)) {
            return $result;
        }

        return $this->mail($to, $subject, $message, $options);
    }

    /**
     * Sends E-mail with predefined parameters using a handler ID
     * @param string $handler_id
     * @param array $arguments
     * @return boolean
     */
    public function set($handler_id, array $arguments)
    {
        $handlers = $this->getHandlers();
        $data = Handler::call($handlers, $handler_id, 'data', $arguments);
        return call_user_func_array(array($this, 'send'), $data);
    }

    /**
     * Send E-mail using PHP mail() function
     * @param array $receivers
     * @param string $subject
     * @param string $message
     * @param array $options
     * @return boolean
     */
    public function mail(array $receivers, $subject, $message, array $options)
    {
        $subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
        $from = "=?UTF-8?B?" . base64_encode($options['from']) . "?=";

        $headers = array();
        $headers[] = "From: $from <$from>";
        $headers[] = "MIME-Version: 1.0";

        if (!empty($options['html'])) {
            $headers[] = "Content-type: text/html; charset=UTF-8";
        }

        $header = implode("\r\n", $headers);

        $to = array();
        foreach ($receivers as $address) {
            if (is_array($address)) {
                $to[] = "{$address[0]} <{$address[1]}>";
            } else {
                $to[] = $address;
            }
        }

        return mail(implode(',', $to), $subject, $message, $header);
    }

}
