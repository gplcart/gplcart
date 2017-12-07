<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains methods for setting order data
 */
trait ItemOrder
{

    abstract public function render($file, $data = array(), $merge = true, $default = '');

    /**
     * Adds "shipping_name" key
     * @param array $item
     * @param \gplcart\core\models\Shipping $shipping_model
     */
    public function setItemOrderShippingName(&$item, $shipping_model)
    {
        if (isset($item['shipping'])) {
            $data = $shipping_model->get($item['shipping']);
            $item['shipping_name'] = empty($data['title']) ? 'Unknown' : $data['title'];
        }
    }

    /**
     * Adds "payment_name" key
     * @param array $item
     * @param \gplcart\core\models\Payment $payment_model
     */
    public function setItemOrderPaymentName(&$item, $payment_model)
    {
        if (isset($item['payment'])) {
            $data = $payment_model->get($item['payment']);
            $item['payment_name'] = empty($data['title']) ? 'Unknown' : $data['title'];
        }
    }

    /**
     * Adds an address information for the order item
     * @param array $order
     * @param \gplcart\core\models\Address $address_model
     */
    public function setItemOrderAddress(&$order, $address_model)
    {
        $order['address'] = array();
        foreach (array('shipping', 'payment') as $type) {
            $address = $address_model->get($order["{$type}_address"]);
            if (!empty($address)) {
                $order['address'][$type] = $address;
                $order['address_translated'][$type] = $address_model->getTranslated($order['address'][$type], true);
            }
        }
    }

    /**
     * Adds "status_name" key
     * @param array $item
     * @param \gplcart\core\models\Order $order_model
     */
    public function setItemOrderStatusName(&$item, $order_model)
    {
        if (isset($item['status'])) {
            $data = $order_model->getStatusName($item['status']);
            $item['status_name'] = empty($data) ? 'Unknown' : $data;
        }
    }

    /**
     * Adds "is_new" key
     * @param array $item
     * @param \gplcart\core\models\Order $order_model
     */
    public function setItemOrderNew(&$item, $order_model)
    {
        $item['is_new'] = $order_model->isNew($item);
    }

    /**
     * Adds "store_name" key
     * @param array $item
     * @param \gplcart\core\models\Store $store_model
     */
    public function setItemOrderStoreName(&$item, $store_model)
    {
        if (isset($item['store_id'])) {
            $data = $store_model->get($item['store_id']);
            $item['store_name'] = empty($data['name']) ? 'Unknown' : $data['name'];
        }
    }

    /**
     * Adds a cart component information for the order item
     * @param array $item
     * @param \gplcart\core\models\Price $price_model
     */
    public function setItemOrderCartComponent(&$item, $price_model)
    {
        if (empty($item['data']['components']['cart']['items'])) {
            return null;
        }

        foreach ($item['data']['components']['cart']['items'] as $sku => $component) {

            if (empty($item['cart'][$sku]['product_store_id'])) {
                continue;
            }

            if ($item['cart'][$sku]['product_store_id'] != $item['store_id']) {
                $item['cart'][$sku]['product_status'] = 0;
            }

            $item['cart'][$sku]['price_formatted'] = $price_model->format($component['price'], $item['currency']);
        }

        $html = $this->render('backend|sale/order/panes/components/cart', array('order' => $item));
        $item['data']['components']['cart']['rendered'] = $html;
    }

    /**
     * Adds a shipping component information for the order item
     * @param array $item
     * @param \gplcart\core\models\Price $pmodel
     * @param \gplcart\core\models\Shipping $shmodel
     * @param \gplcart\core\models\Order $omodel
     */
    public function setItemOrderShippingComponent(&$item, $pmodel, $shmodel, $omodel)
    {
        if (!isset($item['data']['components']['shipping']['price'])) {
            return null;
        }

        $method = $shmodel->get($item['shipping']);
        $value = $item['data']['components']['shipping']['price'];

        if (abs($value) == 0) {
            $value = 0;
        }

        $method['price_formatted'] = $pmodel->format($value, $item['currency']);

        $data = array(
            'method' => $method,
            'title' => $omodel->getComponentType('shipping')
        );

        $html = $this->render('backend|sale/order/panes/components/method', $data);
        $item['data']['components']['shipping']['rendered'] = $html;
    }

    /**
     * Adds a payment component information for the order item
     * @param array $item
     * @param \gplcart\core\models\Price $pmodel
     * @param \gplcart\core\models\Payment $pamodel
     * @param \gplcart\core\models\Order $omodel
     */
    public function setItemOrderPaymentComponent(&$item, $pmodel, $pamodel, $omodel)
    {
        if (!isset($item['data']['components']['payment']['price'])) {
            return null;
        }

        $method = $pamodel->get($item['payment']);
        $value = $item['data']['components']['payment']['price'];

        if (abs($value) == 0) {
            $value = 0;
        }

        $method['price_formatted'] = $pmodel->format($value, $item['currency']);

        $data = array(
            'method' => $method,
            'title' => $omodel->getComponentType('payment')
        );

        $html = $this->render('backend|sale/order/panes/components/method', $data);
        $item['data']['components']['payment']['rendered'] = $html;
    }

    /**
     * Adds a price rule component information for the order item
     * @param array $item
     * @param \gplcart\core\models\Price $pmodel
     * @param \gplcart\core\models\PriceRule $prmodel
     */
    public function setItemOrderPriceRuleComponent(&$item, $pmodel, $prmodel)
    {
        foreach ($item['data']['components'] as $price_rule_id => $component) {

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

            $html = $this->render('backend|sale/order/panes/components/rule', $data);
            $item['data']['components'][$price_rule_id]['rendered'] = $html;
        }
    }

}
