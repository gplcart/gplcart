<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook;
use gplcart\core\models\Mail as MailModel,
    gplcart\core\models\Cart as CartModel,
    gplcart\core\models\Order as OrderModel,
    gplcart\core\models\PriceRule as PriceRuleModel,
    gplcart\core\models\Translation as TranslationModel;

/**
 * Manages basic behaviors and data related to order actions
 */
class OrderAction
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Cart model instance
     * @var \gplcart\core\models\Cart $cart
     */
    protected $cart;

    /**
     * Order model instance
     * @var \gplcart\core\models\Order $order
     */
    protected $order;

    /**
     * Mail model instance
     * @var \gplcart\core\models\Mail $mail
     */
    protected $mail;

    /**
     * Price rule model instance
     * @var \gplcart\core\models\PriceRule $price_rule
     */
    protected $price_rule;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * @param Hook $hook
     * @param CartModel $cart
     * @param OrderModel $order
     * @param PriceRuleModel $price_rule
     * @param TranslationModel $translation
     * @param MailModel $mail
     */
    public function __construct(Hook $hook, CartModel $cart, OrderModel $order,
            PriceRuleModel $price_rule, TranslationModel $translation, MailModel $mail)
    {
        $this->hook = $hook;
        $this->mail = $mail;
        $this->cart = $cart;
        $this->order = $order;
        $this->price_rule = $price_rule;
        $this->translation = $translation;
    }

    /**
     * Notify when an order has been created
     * @param array $order
     * @return boolean
     */
    public function notifyCreated(array $order)
    {
        $this->mail->set('order_created_admin', array($order));

        if (is_numeric($order['user_id']) && !empty($order['user_email'])) {
            return $this->mail->set('order_created_customer', array($order));
        }

        return false;
    }

    /**
     * Notify when an order has been updated
     * @param array $order
     * @return boolean
     */
    public function notifyUpdated(array $order)
    {
        if (is_numeric($order['user_id']) && !empty($order['user_email'])) {
            return $this->mail->set('order_updated_customer', array($order));
        }

        return false;
    }

    /**
     * Adds an order
     * @param array $data
     * @param array $options
     * @return array
     */
    public function add(array $data, array $options = array())
    {
        $result = array();
        $this->hook->attach('order.submit.before', $data, $options, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $this->prepareComponents($data);
        $data['order_id'] = $this->order->add($data);

        if (empty($data['order_id'])) {
            return $this->getResultError();
        }

        $order = $this->order->get($data['order_id']);
        $this->setBundledProducts($order, $data);

        if (empty($options['admin'])) {
            $this->setPriceRules($order);
            $this->updateCart($order, $data['cart']);
            $this->notifyCreated($order);
            $result = $this->getResultAdded($order);
        } else {
            $this->cloneCart($order, $data['cart']);
            $result = $this->getResultAddedAdmin($order);
        }

        $this->hook->attach('order.submit.after', $data, $options, $result, $this);
        return (array) $result;
    }

    /**
     * Returns an array of resulting data when an order has not been added
     * @return array
     */
    protected function getResultError()
    {
        return array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->translation->text('An error occurred')
        );
    }

    /**
     * Returns an array of resulting data when an order has been added by an admin
     * @param array $order
     * @return array
     */
    protected function getResultAddedAdmin(array $order)
    {
        return array(
            'order' => $order,
            'severity' => 'success',
            'redirect' => "admin/sale/order/{$order['order_id']}",
            'message' => $this->translation->text('Order has been created')
        );
    }

    /**
     * Returns an array of resulting data when an order has been added by a customer
     * @param array $order
     * @return array
     */
    protected function getResultAdded(array $order)
    {
        return array(
            'message' => '',
            'order' => $order,
            'severity' => 'success',
            'redirect' => "checkout/complete/{$order['order_id']}"
        );
    }

    /**
     * Adds bundled products
     * @param array $order
     * @param array $data
     */
    protected function setBundledProducts(array $order, array $data)
    {
        $update = false;
        foreach ($data['cart']['items'] as $item) {

            if (empty($item['product']['bundled_products'])) {
                continue;
            }

            foreach ($item['product']['bundled_products'] as $product) {

                $cart = array(
                    'sku' => $product['sku'],
                    'user_id' => $data['user_id'],
                    'quantity' => $item['quantity'],
                    'store_id' => $data['store_id'],
                    'order_id' => $order['order_id'],
                    'product_id' => $product['product_id'],
                );

                $this->cart->add($cart);

                $update = true;
                $order['data']['components']['cart']['items'][$product['sku']]['price'] = 0;
            }
        }

        if ($update) {
            $this->order->update($order['order_id'], array('data' => $order['data']));
        }
    }

    /**
     * Clone an order
     * @param array $order
     * @param array $cart
     */
    protected function cloneCart(array $order, array $cart)
    {
        foreach ($cart['items'] as $item) {
            $cart_id = $item['cart_id'];
            unset($item['cart_id']);
            $item['user_id'] = $order['user_id'];
            $item['order_id'] = $order['order_id'];
            $this->cart->add($item);
            $this->cart->delete($cart_id);
        }
    }

    /**
     * Update cart items after order was created
     * @param array $order
     * @param array $cart
     */
    protected function updateCart(array $order, array $cart)
    {
        foreach ($cart['items'] as $item) {

            $data = array(
                'user_id' => $order['user_id'],
                'order_id' => $order['order_id']
            );

            $this->cart->update($item['cart_id'], $data);
        }
    }

    /**
     * Sets price rules after the order was created
     * @param array $order
     */
    protected function setPriceRules(array $order)
    {
        foreach (array_keys($order['data']['components']) as $component_id) {
            if (is_numeric($component_id)) {
                $rule = $this->price_rule->get($component_id);
                if (isset($rule['code']) && $rule['code'] !== '') {
                    $this->price_rule->setUsed($rule['price_rule_id']);
                }
            }
        }
    }

    /**
     * Prepares order components
     * @param array $order
     */
    protected function prepareComponents(array &$order)
    {
        if (!empty($order['cart']['items'])) {
            foreach ($order['cart']['items'] as $sku => $item) {
                $order['data']['components']['cart']['items'][$sku]['price'] = $item['total'];
            }
        }
    }

}
