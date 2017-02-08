<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains controller order methods
 */
trait ControllerOrder
{

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
     */
    protected function prepareOrderComponentsCartTrait($controller, &$order,
            $type, $dir)
    {
        if ($type === 'cart') {
            $data = array('order' => $order);
            $html = $controller->render("$dir/cart", $data);
            $order['data']['components']['cart']['rendered'] = $html;
        }
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
