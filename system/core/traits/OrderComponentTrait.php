<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains controller methods for order components
 */
trait OrderComponentTrait
{

    /**
     * Prepare cart component
     * @param array $order
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Price $price
     */
    protected function prepareOrderComponentCartTrait(&$order,
            \gplcart\core\Controller $controller,
            \gplcart\core\models\Price $price)
    {
        if (empty($order['data']['components']['cart'])) {
            return null;
        }

        foreach ($order['data']['components']['cart'] as $sku => $value) {
            if ($order['cart'][$sku]['product_store_id'] != $order['store_id']) {
                $order['cart'][$sku]['product_status'] = 0;
            }
            $order['cart'][$sku]['price_formatted'] = $price->format($value, $order['currency']);
        }

        $html = $controller->render('backend|sale/order/panes/components/cart', array('order' => $order));
        $order['data']['components']['cart']['rendered'] = $html;
    }

    /**
     * Prepare shipping method component
     * @param array $order
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Price $price
     * @param \gplcart\core\models\Shipping $shipping
     */
    protected function prepareOrderComponentShippingMethodTrait(&$order,
            \gplcart\core\Controller $controller,
            \gplcart\core\models\Price $price,
            \gplcart\core\models\Shipping $shipping)
    {
        if (!isset($order['data']['components']['shipping'])) {
            return null;
        }

        $method = $shipping->get($order['shipping']);

        if (empty($method['name'])) {
            $method['name'] = $controller->text('Unknown');
        }

        $value = $order['data']['components']['shipping'];

        if (abs($value) == 0) {
            $value = 0;
        }

        $method['price_formatted'] = $price->format($value, $order['currency']);
        $html = $controller->render('backend|sale/order/panes/components/method', array('method' => $method));
        $order['data']['components']['shipping']['rendered'] = $html;
    }

    /**
     * Prepare payment method component
     * @param array $order
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Price $price
     * @param \gplcart\core\models\Payment $payment
     */
    protected function prepareOrderComponentPaymentMethodTrait(&$order,
            \gplcart\core\Controller $controller,
            \gplcart\core\models\Price $price,
            \gplcart\core\models\Payment $payment)
    {
        if (!isset($order['data']['components']['payment'])) {
            return null;
        }

        $method = $payment->get($order['payment']);

        if (empty($method['name'])) {
            $method['name'] = $controller->text('Unknown');
        }

        $value = $order['data']['components']['payment'];

        if (abs($value) == 0) {
            $value = 0;
        }

        $method['price_formatted'] = $price->format($value, $order['currency']);
        $html = $controller->render('backend|sale/order/panes/components/method', array('method' => $method));
        $order['data']['components']['payment']['rendered'] = $html;
    }

    /**
     * Prepare price rule component
     * @param array $order
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Price $price
     * @param \gplcart\core\models\PriceRule $pricerule
     */
    protected function prepareOrderComponentPriceRuleTrait(&$order,
            \gplcart\core\Controller $controller,
            \gplcart\core\models\Price $price,
            \gplcart\core\models\PriceRule $pricerule)
    {
        foreach (array_keys($order['data']['components']) as $type) {

            if (!is_numeric($type)) {
                continue;
            }

            $rule = $pricerule->get($type);
            $value = $order['data']['components'][$type];

            if (abs($value) == 0) {
                $value = 0;
            }

            $data = array('rule' => $rule, 'price' => $price->format($value, $rule['currency']));
            $html = $controller->render('backend|sale/order/panes/components/rule', $data);
            $order['data']['components'][$type] = array('rendered' => $html, 'price' => $value);
        }
    }

}
