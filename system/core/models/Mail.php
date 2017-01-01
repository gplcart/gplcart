<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\Cache;
use gplcart\core\Logger;
use gplcart\core\Library;
use gplcart\core\Handler;

/**
 * Manages basic behaviors and data related to sending e-mails
 */
class Mail extends Model
{

    /**
     * Debug info
     * @var string
     */
    protected $debug = '';

    /**
     * Errors
     * @var string
     */
    protected $errors = '';

    /**
     * PHPMailer instance
     * @var \PHPMailer $mailer
     */
    protected $mailer;

    /**
     * Library class instance
     * @var \gplcart\core\Library $library
     */
    protected $library;

    /**
     * Logger class instance
     * @var \gplcart\core\Logger $logger
     */
    protected $logger;

    /**
     * Constructor
     * @param Logger $logger
     */
    public function __construct(Logger $logger, Library $library)
    {
        parent::__construct();
        $this->logger = $logger;
        $this->library = $library;
        $this->library->load('phpmailer');
    }

    /**
     * Returns an array of email handlers
     * @return array
     */
    protected function getHandlers()
    {
        $handlers = &Cache::memory('mail.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = array();

        $handlers['order_created_admin'] = array(
            'access' => 'order',
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\mail\\Order', 'createdToAdmin')
            ),
        );

        $handlers['order_created_customer'] = array(
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\mail\\Order', 'createdToCustomer'),
            ),
        );

        $handlers['user_registered_admin'] = array(
            'access' => 'user',
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\mail\\Account', 'registeredToAdmin'),
            ),
        );

        $handlers['user_registered_customer'] = array(
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\mail\\Account', 'registeredToCustomer'),
            ),
        );

        $handlers['user_reset_password'] = array(
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\mail\\Account', 'resetPassword'),
            ),
        );

        $handlers['user_changed_password'] = array(
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\mail\\Account', 'changedPassword'),
            ),
        );

        $this->hook->fire('mail.handlers', $handlers);

        return $handlers;
    }

    /**
     * Sends an e-mail
     * @param string|array $to
     * @param array $message
     * @param array $options
     * @return boolean
     */
    public function send($to, array $message, array $options = array())
    {
        $options += $this->defaultOptions();

        $this->hook->fire('mail.before', $to, $message, $options);

        if (empty($options['from']) || empty($to) || empty($message)) {
            return false; // Allows modules to abort sending
        }

        // Get fresh instance for each sending
        $this->mailer = new \PHPMailer;

        if (isset($options['email_method']) && $options['email_method'] === 'smtp') {
            $this->mailer->isSMTP();
        }

        $this->mailer->Host = implode(';', (array) $options['smtp_host']);
        $this->mailer->SMTPAuth = !empty($options['smtp_auth']);
        $this->mailer->Username = $options['smtp_username'];
        $this->mailer->Password = $options['smtp_password'];
        $this->mailer->SMTPSecure = $options['smtp_secure'];
        $this->mailer->Port = $options['smtp_port'];

        $body = reset($message);

        $this->mailer->Subject = key($message);
        $this->mailer->Body = $body;

        if (is_array($body)) {
            $this->mailer->Body = reset($body);
            if (count($body) > 1) {
                $this->mailer->AltBody = end($body);
            }
        }

        call_user_func_array(array($this->mailer, 'setFrom'), (array) $options['from']);

        $addresses = array();
        foreach ((array) $to as $address) {
            $address = (array) $address;
            call_user_func_array(array($this->mailer, 'addAddress'), $address);
            $addresses[] = reset($address);
        }

        if (!empty($options['reply'])) {
            call_user_func_array(array($this->mailer, 'addReplyTo'), (array) $options['reply']);
        }

        if (!empty($options['cc'])) {
            $this->mailer->addCC($options['cc']);
        }

        if (!empty($options['bcc'])) {
            $this->mailer->addBCC($options['bcc']);
        }

        if (!empty($options['attachment'])) {
            foreach ($options['attachment'] as $attachment) {
                call_user_func_array(array($this->mailer, 'addAttachment'), (array) $attachment);
            }
        }

        if (!empty($options['html'])) {
            $this->mailer->isHTML(true);
        }

        if (isset($options['debug'])) {
            $this->mailer->SMTPDebug = (int) $options['debug'];
            $this->mailer->Debugoutput = function ($str, $level) {
                $this->debug = $str;
            };
        }

        $options['status'] = $this->mailer->send();
        $this->errors = $this->mailer->ErrorInfo;

        $this->log(implode(',', $addresses), $options);

        $this->hook->fire('mail.after', $to, $message, $options);
        return (bool) $options['status'];
    }

    /**
     * Returns the current instance of PHPMailer
     * @return object
     */
    public function getMailer()
    {
        return $this->mailer;
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

        if (empty($handlers[$handler_id])) {
            return false;
        }

        return Handler::call($handlers, $handler_id, 'process', $arguments);
    }

    /**
     * Returns a debug information
     * @return string
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Returns an array of errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Returns an array of default settings
     * @return array
     */
    protected function defaultOptions()
    {
        return array(
            'cc' => '',
            'bcc' => '',
            'from' => '',
            'reply' => '',
            'debug' => 2,
            'html' => false,
            'attachment' => array(),
            'smtp_auth' => $this->config->get('smtp_auth', 1),
            'smtp_port' => $this->config->get('smtp_port', 587),
            'smtp_secure' => $this->config->get('smtp_secure', 'tls'),
            'smtp_username' => $this->config->get('smtp_username', ''),
            'smtp_password' => $this->config->get('smtp_password', ''),
            'smtp_host' => $this->config->get('smtp_host', array('smtp.gmail.com')),
            'email_method' => $this->config->get('email_method', 'mail'),
        );
    }

    /**
     * Logs sending an email
     * @param string $address
     * @param array $options
     */
    protected function log($address, $options)
    {
        $severity = 'info';
        $message = 'E-mail to !address has been sent.';

        if (empty($options['status'])) {
            $severity = 'warning';
            $message = 'Failed to send E-mail to !address.';
        }

        $message .= '<br>Errors: <pre>!errors</pre>Debugging info:<pre>!debug</pre>';

        $log = array(
            'message' => $message,
            'variables' => array(
                '!address' => $address,
                '!debug' => $this->debug,
                '!errors' => $this->errors
            )
        );

        $this->logger->log('mail', $log, $severity);
    }

}
