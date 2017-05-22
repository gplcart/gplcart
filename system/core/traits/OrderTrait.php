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
trait OrderTrait
{

    /**
     * Prepare shipping method
     * @param array $order
     * @param \gplcart\core\models\Shipping $model
     * @param \gplcart\core\Controller $controller
     */
    protected function prepareOrderShippingTrait(&$order,
            \gplcart\core\models\Shipping $model,
            \gplcart\core\Controller $controller)
    {
        $data = $model->get($order['shipping']);
        $order['shipping_name'] = empty($data['title']) ? $controller->text('Unknown') : $data['title'];
    }

    /**
     * Prepare order payment method
     * @param array $order
     * @param \gplcart\core\models\Payment $model
     * @param \gplcart\core\Controller $controller
     */
    protected function prepareOrderPaymentTrait(&$order,
            \gplcart\core\models\Payment $model,
            \gplcart\core\Controller $controller)
    {
        $data = $model->get($order['payment']);
        $order['payment_name'] = empty($data['title']) ? $controller->text('Unknown') : $data['title'];
    }

    /**
     * Prepare order store
     * @param array $order
     * @param \gplcart\core\models\Store $model
     * @param \gplcart\core\Controller $controller
     */
    protected function prepareOrderStoreTrait(&$order,
            \gplcart\core\models\Store $model,
            \gplcart\core\Controller $controller)
    {
        $data = $model->get($order['store_id']);
        $order['store_name'] = empty($data['name']) ? $controller->text('Unknown') : $data['name'];
    }

    /**
     * Prepare order status
     * @param array $order
     * @param \gplcart\core\models\Order $model
     * @param \gplcart\core\Controller $controller
     */
    protected function prepareOrderStatusTrait(&$order,
            \gplcart\core\models\Order $model,
            \gplcart\core\Controller $controller)
    {
        $data = $model->getStatusName($order['status']);
        $order['status_name'] = empty($data) ? $controller->text('Unknown') : $data;
    }

    /**
     * Prepare order total
     * @param array $order
     * @param \gplcart\core\models\Price $model
     */
    protected function prepareOrderTotalTrait(&$order,
            \gplcart\core\models\Price $model)
    {
        $order['total_formatted'] = $model->format($order['total'], $order['currency']);
    }

    /**
     * Prepare order addresses
     * @param array $order
     * @param \gplcart\core\models\Address $model
     */
    protected function prepareOrderAddressTrait(&$order,
            \gplcart\core\models\Address $model)
    {
        $order['address'] = array();
        foreach (array('shipping', 'payment') as $type) {
            $address = $model->get($order["{$type}_address"]);
            if (!empty($address)) {
                $order['address'][$type] = $address;
                $order['address_translated'][$type] = $model->getTranslated($order['address'][$type], true);
            }
        }
    }

}
