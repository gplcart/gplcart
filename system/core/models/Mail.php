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
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to sending e-mails
 */
class Mail extends Model
{

    /**
     * Language model class instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();

        $this->language = $language;
    }

    /**
     * Returns an array of mailers
     * @return array
     */
    public function getMailers()
    {
        $handlers = &Cache::memory(__METHOD__);

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = $this->getDefaultMailers();
        $this->hook->fire('mail.handlers', $handlers);
        return $handlers;
    }

    /**
     * Returns an array of default mailers
     * @return array
     */
    protected function getDefaultMailers()
    {
        return array(
            'php' => array(
                'name' => 'PHP',
                'handlers' => array(
                    'send' => array(__CLASS__, 'mail')
                ),
            )
        );
    }

    /**
     * Returns an array of email data handlers
     * @return array
     */
    public function getDataHandlers()
    {
        $handlers = &Cache::memory(__METHOD__);

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = $this->getDefaultDataHandlers();
        $this->hook->fire('mail.data.handlers', $handlers);
        return $handlers;
    }

    /**
     * Returns an array of default data handlers
     * @return array
     */
    protected function getDefaultDataHandlers()
    {
        $handlers = array();

        $handlers['order_created_admin'] = array(
            'name' => $this->language->text('E-mail to an admin after an order has been created'),
            'handlers' => array(
                'data' => array('gplcart\\core\\handlers\\mail\\data\\Order', 'createdToAdmin')
            ),
        );

        $handlers['order_created_customer'] = array(
            'name' => $this->language->text('E-mail to a customer after its order has been created'),
            'handlers' => array(
                'data' => array('gplcart\\core\\handlers\\mail\\data\\Order', 'createdToCustomer'),
            ),
        );

        $handlers['order_updated_customer'] = array(
            'name' => $this->language->text('E-mail to a customer after its order has been updated'),
            'handlers' => array(
                'data' => array('gplcart\\core\\handlers\\mail\\data\\Order', 'updatedToCustomer'),
            ),
        );

        $handlers['user_registered_admin'] = array(
            'name' => $this->language->text('E-mail to an admin after a user account has been created'),
            'handlers' => array(
                'data' => array('gplcart\\core\\handlers\\mail\\data\\Account', 'registeredToAdmin'),
            ),
        );

        $handlers['user_registered_customer'] = array(
            'name' => $this->language->text('E-mail to a user after its account has been created'),
            'handlers' => array(
                'data' => array('gplcart\\core\\handlers\\mail\\data\\Account', 'registeredToCustomer'),
            ),
        );

        $handlers['user_reset_password'] = array(
            'name' => $this->language->text('E-mail to a user after he/she requested a new password'),
            'handlers' => array(
                'data' => array('gplcart\\core\\handlers\\mail\\data\\Account', 'resetPassword'),
            ),
        );

        $handlers['user_changed_password'] = array(
            'name' => $this->language->text('E-mail to a user after its account password has been changed'),
            'handlers' => array(
                'data' => array('gplcart\\core\\handlers\\mail\\data\\Account', 'changedPassword'),
            ),
        );

        return $handlers;
    }

    /**
     * Sends an e-mail
     * @param string|array $to
     * @param string $subject
     * @param string $message
     * @param array $options
     * @return integer
     */
    public function send($to, $subject, $message, array $options = array())
    {
        $mailers = $this->getMailers();
        $options['mailer'] = $this->config->get('mailer', 'php');

        $this->hook->fire('mail.send.before', $to, $subject, $message, $options);

        if (empty($options['mailer']) || empty($to)) {
            return false;
        }

        $result = Handler::call($mailers, $options['mailer'], 'send', array($to, $subject, $message, $options));
        $this->hook->fire('mail.send.after', $to, $subject, $message, $options, $result);
        return $result;
    }

    /**
     * Sends E-mail with predefined parameters using a handler ID
     * @param string $handler_id
     * @param array $arguments
     * @return integer
     */
    public function set($handler_id, array $arguments)
    {
        $data = $this->getData($handler_id, $arguments);
        return call_user_func_array(array($this, 'send'), $data);
    }

    /**
     * Returns an array of data for a given handler used to send E-mails
     * @param string $handler_id
     * @param array $arguments
     * @return array
     */
    public function getData($handler_id, array $arguments)
    {
        $handlers = $this->getDataHandlers();

        if (empty($handlers[$handler_id])) {
            return array();
        }

        $data = Handler::call($handlers, $handler_id, 'data', array($arguments));
        $this->hook->fire('mail.data', $handler_id, $arguments, $data);
        return $data;
    }

    /**
     * Send E-mail using PHP mail() function
     * @param array|string $to
     * @param string $subject
     * @param string $message
     * @param array $options
     * @return integer
     */
    public function mail($to, $subject, $message, array $options)
    {
        settype($to, 'array');

        $subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
        $from = "=?UTF-8?B?" . base64_encode($options['from']) . "?=";

        $headers = array();
        $headers[] = "From: $from <$from>";
        $headers[] = "MIME-Version: 1.0";

        if (!empty($options['html'])) {
            $headers[] = "Content-type: text/html; charset=UTF-8";
        }

        $header = implode("\r\n", $headers);

        $sent = 0;
        foreach ($to as $address) {
            $sent += (int) mail($address, $subject, $message, $header);
        }

        return $sent;
    }

}
