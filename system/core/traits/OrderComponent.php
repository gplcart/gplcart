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
     * @param \gplcart\core\models\Price $pmodel
     */
    protected function prepareOrderComponentCartTrait(&$order, $controller, $pmodel)
    {
        if (!empty($order['data']['components']['cart']['items'])) {

            foreach ($order['data']['components']['cart']['items'] as $sku => $component) {
                if ($order['cart'][$sku]['product_store_id'] != $order['store_id']) {
                    $order['cart'][$sku]['product_status'] = 0;
                }

                $order['cart'][$sku]['price_formatted'] = $pmodel->format($component['price'], $order['currency']);
            }

            $html = $controller->render('backend|sale/order/panes/components/cart', array('order' => $order));
            $order['data']['components']['cart']['rendered'] = $html;
        }
    }

    /**
     * Prepare shipping method component
     * @param array $order
     * @param \gplcart\core\Controller $cont
     * @param \gplcart\core\models\Price $pmodel
     * @param \gplcart\core\models\Shipping $shmodel
     * @param \gplcart\core\models\Order $omodel
     */
    protected function prepareOrderComponentShippingTrait(&$order, $cont, $pmodel, $shmodel, $omodel)
    {
        if (isset($order['data']['components']['shipping']['price'])) {

            $method = $shmodel->get($order['shipping']);
            $value = $order['data']['components']['shipping']['price'];

            if (abs($value) == 0) {
                $value = 0;
            }

            $component_types = $omodel->getComponentTypes();
            $method['price_formatted'] = $pmodel->format($value, $order['currency']);
            $data = array('method' => $method, 'title' => $component_types['shipping']);

            $html = $cont->render('backend|sale/order/panes/components/method', $data);
            $order['data']['components']['shipping']['rendered'] = $html;
        }
    }

    /**
     * Prepare payment method component
     * @param array $order
     * @param \gplcart\core\Controller $cont
     * @param \gplcart\core\models\Price $pmodel
     * @param \gplcart\core\models\Payment $pamodel
     * @param \gplcart\core\models\Order $omodel
     */
    protected function prepareOrderComponentPaymentTrait(&$order, $cont, $pmodel, $pamodel, $omodel)
    {
        if (isset($order['data']['components']['payment']['price'])) {

            $method = $pamodel->get($order['payment']);
            $value = $order['data']['components']['payment']['price'];

            if (abs($value) == 0) {
                $value = 0;
            }

            $component_types = $omodel->getComponentTypes();
            $method['price_formatted'] = $pmodel->format($value, $order['currency']);
            $data = array('method' => $method, 'title' => $component_types['payment']);

            $html = $cont->render('backend|sale/order/panes/components/method', $data);
            $order['data']['components']['payment']['rendered'] = $html;
        }
    }

    /**
     * Prepare price rule component
     * @param array $order
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Price $pmodel
     * @param \gplcart\core\models\PriceRule $prmodel
     */
    protected function prepareOrderComponentPriceRuleTrait(&$order, $controller, $pmodel, $prmodel)
    {
        foreach ($order['data']['components'] as $price_rule_id => $component) {

            if (!is_numeric($price_rule_id)) {
                continue;
            }

            $price_rule = $prmodel->get($price_rule_id);

            if (abs($component['price']) == 0) {
                $component['price'] = 0;
            }

            $data = array(
                'rule' => $price_rule,
                'price' => $pmodel->format($component['price'], $price_rule['currency']));

            $html = $controller->render('backend|sale/order/panes/components/rule', $data);
            $order['data']['components'][$price_rule_id]['rendered'] = $html;
        }
    }

}
