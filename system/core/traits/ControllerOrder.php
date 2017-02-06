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

    abstract protected function getInstanceTrait($name);

    /**
     * Returns an array of prepared order components
     * @param array $order
     * @param string $dir
     * @return array
     */
    protected function prepareOrderComponentsTrait(array &$order, $dir)
    {
        if (empty($order['data']['components'])) {
            return array();
        }

        foreach (array_keys($order['data']['components']) as $type) {
            $this->prepareOrderComponentsCartTrait($order, $type, $dir);
            $this->prepareOrderComponentsMethodTrait($order, $type, $dir);
            $this->prepareOrderComponentsPriceRuleTrait($order, $type, $dir);
        }

        ksort($order['data']['components']);
        return $order['data']['components'];
    }

    /**
     * Sets rendered component "Cart"
     * @param array $order
     * @param string $type
     * @param string $dir
     */
    protected function prepareOrderComponentsCartTrait(&$order, $type, $dir)
    {
        if ($type === 'cart') {
            $data = array('order' => $order);
            $html = $this->render("$dir/cart", $data);
            $order['data']['components']['cart']['rendered'] = $html;
        }
    }

    /**
     * Sets rendered shipping/payment component
     * @param array $order
     * @param string $type
     * @param string $dir
     * @return null
     */
    protected function prepareOrderComponentsMethodTrait(&$order, $type, $dir)
    {
        /* @var $shipping_model \gplcart\core\models\Shipping */
        $shipping_model = $this->getInstanceTrait('shipping');

        /* @var $payment_model \gplcart\core\models\Payment */
        $payment_model = $this->getInstanceTrait('payment');

        /* @var $price_model \gplcart\core\models\Price */
        $price_model = $this->getInstanceTrait('price');

        if ($type == 'shipping') {
            $method = $shipping_model->get();
        } else if ($type == 'payment') {
            $method = $payment_model->get();
        } else {
            return null;
        }

        if (empty($method['name'])) {
            $method['name'] = $this->text('Unknown');
        }

        $price = $order['data']['components'][$type];

        if (abs($price) == 0) {
            $price = 0;
        }

        $method['price_formatted'] = $price_model->format($price, $order['currency']);

        $html = $this->render("$dir/method", array('method' => $method));
        $order['data']['components'][$type]['rendered'] = $html;
    }

    /**
     * Sets rendered rule component
     * @param array $order
     * @param string $type
     * @param string $dir
     * @return null
     */
    protected function prepareOrderComponentsPriceRuleTrait(&$order, $type, $dir)
    {
        if (!is_numeric($type)) {
            return null; // Numeric type = price rule ID
        }

        /* @var $price_model \gplcart\core\models\Price */
        $price_model = $this->getInstanceTrait('price');

        /* @var $pricerule_model \gplcart\core\models\PriceRule */
        $pricerule_model = $this->getInstanceTrait('pricerule');

        $rule = $pricerule_model->get($type);
        $price = $order['data']['components'][$type];

        if (abs($price) == 0) {
            $price = 0;
        }

        $data = array(
            'rule' => $rule,
            'price' => $price_model->format($price, $rule['currency'])
        );

        $html = $this->render("$dir/rule", $data);

        $order['data']['components'][$type] = array(
            'rendered' => $html,
            'price' => $order['data']['components'][$type]);
    }

}
