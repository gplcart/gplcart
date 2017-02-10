<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains order controller methods
 */
trait ControllerOrder
{

    /**
     * Adds extra data to the order
     * @param \gplcart\core\Controller $controller
     * @param array $order
     * @return array
     */
    protected function prepareOrderTrait($controller, array &$order)
    {
        if (!$controller instanceof \gplcart\core\Controller) {
            throw new \RuntimeException("Object is not instance of \gplcart\core\Controller");
        }

        /* @var $shipping_model \gplcart\core\models\Shipping */
        $shipping_model = $controller->prop('shipping');
        /* @var $payment_model \gplcart\core\models\Payment */
        $payment_model = $controller->prop('payment');
        /* @var $store_model \gplcart\core\models\Store */
        $store_model = $controller->prop('store');
        /* @var $order_model \gplcart\core\models\Order */
        $order_model = $controller->prop('order');
        /* @var $price_model \gplcart\core\models\Price */
        $price_model = $controller->prop('price');
        /* @var $address_model \gplcart\core\models\Address */
        $address_model = $controller->prop('address');

        $store = $store_model->get($order['store_id']);
        $payment = $payment_model->get($order['payment']);
        $shipping = $shipping_model->get($order['shipping']);
        $status = $order_model->getStatusName($order['status']);

        foreach (array('shipping', 'payment') as $type) {
            $order['address'][$type] = $address_model->get($order["{$type}_address"]);
            if (!empty($order['address'][$type])) {
                $order['address_translated'][$type] = $address_model->getTranslated($order['address'][$type], true);
            }
        }

        $order['status_name'] = empty($status) ? $this->text('Unknown') : $status;
        $order['store_name'] = empty($store['name']) ? $this->text('Unknown') : $store['name'];
        $order['payment_name'] = empty($payment['title']) ? $this->text('Unknown') : $payment['title'];
        $order['shipping_name'] = empty($shipping['title']) ? $this->text('Unknown') : $shipping['title'];

        $order['total_formatted'] = $price_model->format($order['total'], $order['currency']);
        return $order;
    }

    /**
     * Returns an array of prepared order components
     * @param \gplcart\core\Controller $controller
     * @param array $order
     * @param string $dir
     * @return array
     */
    protected function prepareOrderComponentsTrait($controller, &$order, $dir)
    {
        if (!$controller instanceof \gplcart\core\Controller) {
            throw new \RuntimeException("Object is not instance of \gplcart\core\Controller");
        }

        if (empty($order['data']['components'])) {
            return array();
        }

        foreach (array_keys($order['data']['components']) as $type) {
            $this->prepareOrderComponentsCartTrait($controller, $order, $type, $dir);
            $this->prepareOrderComponentsMethodTrait($controller, $order, $type, $dir);
            $this->prepareOrderComponentsPriceRuleTrait($controller, $order, $type, $dir);
        }

        ksort($order['data']['components']);
        return $order['data']['components'];
    }

    /**
     * Sets rendered component "Cart"
     * @param \gplcart\core\Controller $controller
     * @param array $order
     * @param string $type
     * @param string $dir
     * @return null
     */
    protected function prepareOrderComponentsCartTrait($controller, &$order,
            $type, $dir)
    {
        if ($type !== 'cart') {
            return null;
        }

        /* @var $price_model \gplcart\core\models\Price */
        $price_model = $controller->prop('price');

        foreach ($order['data']['components']['cart'] as $sku => $price) {
            if ($order['cart'][$sku]['product_store_id'] != $order['store_id']) {
                $order['cart'][$sku]['product_status'] = 0;
            }
            $order['cart'][$sku]['price_formatted'] = $price_model->format($price, $order['currency']);
        }

        $data = array('order' => $order);
        $html = $controller->render("$dir/cart", $data);
        $order['data']['components']['cart']['rendered'] = $html;
    }

    /**
     * Sets rendered shipping/payment component
     * @param \gplcart\core\Controller $controller
     * @param array $order
     * @param string $type
     * @param string $dir
     * @return null
     */
    protected function prepareOrderComponentsMethodTrait($controller, &$order,
            $type, $dir)
    {
        /* @var $shipping_model \gplcart\core\models\Shipping */
        $shipping_model = $controller->prop('shipping');
        /* @var $payment_model \gplcart\core\models\Payment */
        $payment_model = $controller->prop('payment');
        /* @var $price_model \gplcart\core\models\Price */
        $price_model = $controller->prop('price');

        if ($type == 'shipping') {
            $method = $shipping_model->get($order['shipping']);
        } else if ($type == 'payment') {
            $method = $payment_model->get($order['payment']);
        } else {
            return null;
        }

        if (empty($method['name'])) {
            $method['name'] = $controller->text('Unknown');
        }

        $price = $order['data']['components'][$type];

        if (abs($price) == 0) {
            $price = 0;
        }

        $method['price_formatted'] = $price_model->format($price, $order['currency']);

        $html = $controller->render("$dir/method", array('method' => $method));
        $order['data']['components'][$type]['rendered'] = $html;
    }

    /**
     * Sets rendered rule component
     * @param \gplcart\core\Controller $controller
     * @param array $order
     * @param string $type
     * @param string $dir
     * @return null
     */
    protected function prepareOrderComponentsPriceRuleTrait($controller,
            &$order, $type, $dir)
    {
        if (!is_numeric($type)) {
            return null; // Numeric type = price rule ID
        }

        /* @var $price_model \gplcart\core\models\Price */
        $price_model = $controller->prop('price');
        /* @var $pricerule_model \gplcart\core\models\PriceRule */
        $pricerule_model = $controller->prop('pricerule');

        $rule = $pricerule_model->get($type);
        $price = $order['data']['components'][$type];

        if (abs($price) == 0) {
            $price = 0;
        }

        $data = array(
            'rule' => $rule,
            'price' => $price_model->format($price, $rule['currency'])
        );

        $html = $controller->render("$dir/rule", $data);

        $order['data']['components'][$type] = array(
            'rendered' => $html,
            'price' => $order['data']['components'][$type]);
    }

}
