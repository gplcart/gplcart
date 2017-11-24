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
trait Order
{

    /**
     * Prepare shipping method
     * @param array $order
     * @param \gplcart\core\models\Shipping $shipping_model
     */
    protected function prepareOrderShippingTrait(&$order, $shipping_model)
    {
        $data = $shipping_model->get($order['shipping']);
        $order['shipping_name'] = empty($data['title']) ? $this->text('Unknown') : $data['title'];
    }

    /**
     * Prepare order payment method
     * @param array $order
     * @param \gplcart\core\models\Payment $payment_model
     */
    protected function prepareOrderPaymentTrait(&$order, $payment_model)
    {
        $data = $payment_model->get($order['payment']);
        $order['payment_name'] = empty($data['title']) ? $this->text('Unknown') : $data['title'];
    }

    /**
     * Prepare order store
     * @param array $order
     * @param \gplcart\core\models\Store $store_model
     */
    protected function prepareOrderStoreTrait(&$order, $store_model)
    {
        $data = $store_model->get($order['store_id']);
        $order['store_name'] = empty($data['name']) ? $this->text('Unknown') : $data['name'];
    }

    /**
     * Prepare order status
     * @param array $order
     * @param \gplcart\core\models\Order $order_model
     */
    protected function prepareOrderStatusTrait(&$order, $order_model)
    {
        $data = $order_model->getStatusName($order['status']);
        $order['status_name'] = empty($data) ? $this->text('Unknown') : $data;
    }

    /**
     * Prepare order total
     * @param array $order
     * @param \gplcart\core\models\Price $price_model
     */
    protected function prepareOrderTotalTrait(&$order, $price_model)
    {
        $order['total_formatted'] = $price_model->format($order['total'], $order['currency']);
    }

    /**
     * Mark the order is new or not
     * @param array $order
     * @param \gplcart\core\models\Order $order_model
     */
    protected function prepareOrderNewTrait(&$order, $order_model)
    {
        $order['is_new'] = $order_model->isNew($order);
    }

    /**
     * Prepare order addresses
     * @param array $order
     * @param \gplcart\core\models\Address $address_model
     */
    protected function prepareOrderAddressTrait(&$order, $address_model)
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

}
