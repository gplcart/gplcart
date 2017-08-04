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
trait OrderComponent
{

    /**
     * Prepare cart component
     * @param array $order
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Price $price_model
     */
    protected function prepareOrderComponentCartTrait(&$order,
            \gplcart\core\Controller $controller,
            \gplcart\core\models\Price $price_model)
    {
        if (empty($order['data']['components']['cart']['items'])) {
            return null;
        }

        foreach ($order['data']['components']['cart']['items'] as $sku => $component) {
            if ($order['cart'][$sku]['product_store_id'] != $order['store_id']) {
                $order['cart'][$sku]['product_status'] = 0;
            }

            $order['cart'][$sku]['price_formatted'] = $price_model->format($component['price'], $order['currency']);
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
     * @param \gplcart\core\models\Order $order_model
     */
    protected function prepareOrderComponentShippingMethodTrait(&$order,
            \gplcart\core\Controller $controller,
            \gplcart\core\models\Price $price,
            \gplcart\core\models\Shipping $shipping,
            \gplcart\core\models\Order $order_model)
    {
        if (!isset($order['data']['components']['shipping']['price'])) {
            return null;
        }

        $method = $shipping->get($order['shipping']);
        $value = $order['data']['components']['shipping']['price'];

        if (abs($value) == 0) {
            $value = 0;
        }

        $component_types = $order_model->getComponentTypes();

        $method['price_formatted'] = $price->format($value, $order['currency']);
        $data = array('method' => $method, 'title' => $component_types['shipping']);

        $html = $controller->render('backend|sale/order/panes/components/method', $data);
        $order['data']['components']['shipping']['rendered'] = $html;
    }

    /**
     * Prepare payment method component
     * @param array $order
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Price $price_model
     * @param \gplcart\core\models\Payment $payment_model
     * @param \gplcart\core\models\Order $order_model
     */
    protected function prepareOrderComponentPaymentMethodTrait(&$order,
            \gplcart\core\Controller $controller,
            \gplcart\core\models\Price $price_model,
            \gplcart\core\models\Payment $payment_model,
            \gplcart\core\models\Order $order_model)
    {
        if (!isset($order['data']['components']['payment']['price'])) {
            return null;
        }

        $method = $payment_model->get($order['payment']);
        $value = $order['data']['components']['payment']['price'];

        if (abs($value) == 0) {
            $value = 0;
        }

        $component_types = $order_model->getComponentTypes();

        $method['price_formatted'] = $price_model->format($value, $order['currency']);
        $data = array('method' => $method, 'title' => $component_types['payment']);

        $html = $controller->render('backend|sale/order/panes/components/method', $data);
        $order['data']['components']['payment']['rendered'] = $html;
    }

    /**
     * Prepare price rule component
     * @param array $order
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Price $price_model
     * @param \gplcart\core\models\PriceRule $pricerule_model
     */
    protected function prepareOrderComponentPriceRuleTrait(&$order,
            \gplcart\core\Controller $controller,
            \gplcart\core\models\Price $price_model,
            \gplcart\core\models\PriceRule $pricerule_model)
    {

        foreach ($order['data']['components'] as $price_rule_id => $component) {

            if (!is_numeric($price_rule_id)) {
                continue;
            }

            $rule = $pricerule_model->get($price_rule_id);

            if (abs($component['price']) == 0) {
                $component['price'] = 0;
            }

            $data = array(
                'rule' => $rule,
                'price' => $price_model->format($component['price'], $rule['currency']));

            $html = $controller->render('backend|sale/order/panes/components/rule', $data);
            $order['data']['components'][$price_rule_id]['rendered'] = $html;
        }
    }

}
