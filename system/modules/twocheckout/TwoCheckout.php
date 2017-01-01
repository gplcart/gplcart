<?php

/**
 * @package 2Checkout payment module
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\twocheckout;

use gplcart\core\Config;
use gplcart\core\models\Language as LanguageModel;

/**
 * Main module class
 */
class TwoCheckout
{

    /**
     * Config instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param LanguageModel $language
     * @param Config $config
     */
    public function __construct(LanguageModel $language, Config $config)
    {
        $this->config = $config;
        $this->language = $language;
    }

    /**
     * Returns module info
     * @return array
     */
    public function info()
    {
        return array(
            'core' => '1.0',
            'name' => '2 Checkout',
            'author' => 'Iurii Makukh',
            'settings' => $this->getDefaultSettings(),
            'configure' => 'admin/module/settings/2checkout',
            'description' => '<a target="_blank" href="http://2checkout.com">2checkout</a> payment method'
        );
    }

    /**
     * Adds a new route for settings page
     * @param array $routes
     */
    public function hookRoute(&$routes)
    {
        $routes['admin/module/settings/2checkout'] = array(
            'access' => 'module_edit',
            'handlers' => array(
                'controller' => array('modules\\twocheckout\\controllers\\Settings', 'editSettings')
            )
        );
    }

    /**
     * Implements hook payment.method
     * @param array $methods
     */
    public function hookPaymentMethod(array &$methods)
    {
        $methods['2checkout'] = array(
            'weight' => 0,
            'status' => true,
            'title' => '2Checkout',
            'module' => 'twocheckout',
            'template' => array('complete' => 'submit'),
            'description' => 'Pay via 2checkout.com. Accepts credit, debit cards, PayPal',
            'image' => 'system/modules/twocheckout/image/icon.png'
        );
    }
    
    /**
     * Implements hook remote.transaction
     * @param array $order
     * @param array $request
     * @param array $result
     * @return boolean
     */
    public function hookRemoteTransaction($order, $request, &$result)
    {
        if (!$this->requestIsValid($request)) {
            return false;
        }

        if ($this->setDemoResult($order, $request, $result)) {
            return true;
        }

        $this->setTransactionResult($order, $request, $result);
        return true;
    }

    /**
     * Validates a data sent by the payment service and sets a result
     * @param array $order
     * @param array $request
     * @param array $result
     * @return boolean
     */
    protected function setTransactionResult($order, $request, &$result)
    {
        if ($request['credit_card_processed'] !== 'Y') {
            return false;
        }

        $settings = $this->config->module('twocheckout');

        $string = $settings['secret']
                . $settings['account']
                . $request['order_number']
                . $request['total'];

        $hash = strtoupper(md5($string));

        if ($hash !== $request['key']) {
            return false;
        }

        $result = array(
            'redirect' => '/',
            'severity' => 'success',
            'remote_transaction_id' => $request['order_number'],
            'message' => $this->language->text('Thank you! Order #@order_id has been paid', array(
                '@order_id' => $order['order_id'])),
        );
        
        return true;
    }

    /**
     * Sets transaction result if demo mode is ON
     * @param array $order
     * @param array $request
     * @param array $result
     * @return boolean
     */
    protected function setDemoResult($order, $request, &$result)
    {
        if (empty($request['demo']) || $request['demo'] !== 'Y') {
            return false;
        }

        $result = array(
            'redirect' => '/',
            'severity' => 'success',
            'remote_transaction_id' => $request['order_number'],
            'message' => $this->language->text('Order #@order_id has been paid in demo mode. Your card will not be charged', array(
                '@order_id' => $order['order_id'])),
        );

        return true;
    }

    /**
     * Returs TRUE if the payment service sent all needed data
     * @param array $request
     * @return bool
     */
    protected function requestIsValid(array $request)
    {
        return (isset($request['key']) && isset($request['total'])
                && isset($request['order_number'])
                && isset($request['li_0_product_id'])
                && isset($request['credit_card_processed']));
    }

    /**
     * Returns an array of default module settings
     * @return array
     */
    protected function getDefaultSettings()
    {
        $settings = array(
            'secret' => '',
            'account' => '',
            'demo' => 1
        );

        return $settings;
    }

}
