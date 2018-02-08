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
     * @param \gplcart\core\models\OrderHistory $order_history_model
     */
    public function setItemOrderNew(&$item, $order_history_model)
    {
        $item['is_new'] = $order_history_model->isNew($item);
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

}
