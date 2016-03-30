<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use core\Hook;
use core\Config;
use core\Logger;

class Mail
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
     * @var \libraries\phpmailer\class.phpmailer $mailer
     */
    protected $mailer;

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Constructor
     * @param Hook $hook
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(Hook $hook, Config $config, Logger $logger)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->logger = $logger;

        require GC_LIBRARY_DIR . '/phpmailer/PHPMailerAutoload.php';
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

        if (isset($options['email_method']) && $options['email_method'] == 'smtp') {
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
            $this->mailer->Debugoutput = function($str, $level) {
                $this->debug = $str;
            };
        }

        $options['status'] = $this->mailer->send();

        if (!$options['status'] || $this->mailer->ErrorInfo) {
            $this->logErrors(implode(',', $addresses), $this->mailer->ErrorInfo);
            $this->errors = $this->mailer->ErrorInfo;
        }

        if ($options['status'] && !$this->mailer->ErrorInfo) {
            $this->logSuccess(implode(',', $addresses));
        }

        $this->hook->fire('mail.after', $to, $message, $options);
        return (bool) $options['status'];
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
            'html' => false,
            'attachment' => array(),
            'reply' => '',
            'from' => '',
            'smtp_auth' => true,
            'smtp_port' => 25,
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_host' => array('localhost'),
            'smtp_secure' => 'tls',
        );
    }

    /**
     * Logs errors when unable to send an e-mail
     * @param string $address
     * @param string $error
     */
    protected function logErrors($address, $error)
    {
        $log = array(
            'message' => 'Failed to send E-mail to %address. Error: %error',
            'variables' => array(
                '%address' => $address,
                '%error' => $error
            )
        );

        $this->logger->log('email', $log, 'warning');
    }

    /**
     * Logs success sending e-mails
     * @param string $address
     */
    protected function logSuccess($address)
    {
        $log = array(
            'message' => 'E-mail to %address has been sent',
            'variables' => array('%address' => $address)
        );

        $this->logger->log('email', $log);
    }

    /**
     * Returns a string containing default e-mail signature
     * @param array $options Store settings
     * @return string
     */
    public function signatureText($options)
    {
        $signature[] = '-------------------------------------';

        if ($options['owner']) {
            $signature[] = "!owner";
        }

        if ($options['address']) {
            $signature[] = "!address";
        }

        if ($options['phone']) {
            $signature[] = "tel: !phone";
        }

        if ($options['fax']) {
            $signature[] = "fax: !fax";
        }

        if ($options['email']) {
            $signature[] = "e-mail: !store_email";
        }

        if ($options['map']) {
            $signature[] = "Find us on Google Maps: !map";
        }

        return count($signature > 1) ? implode("\n", $signature) : '';
    }

    /**
     * Returns an array of placeholders for the signature
     * @param array $options
     * @return array
     */
    public function signatureVariables(array $options)
    {
        return array(
            '!owner' => $options['owner'],
            '!phone' => implode(',', $options['phone']),
            '!store_email' => implode(',', $email),
            '!fax' => implode(',', $options['fax']),
            '!address' => $options['address'],
            '!map' => $options['map'] ? 'http://maps.google.com/?q=' . implode(',', $options['map']) : '',
        );
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

}
