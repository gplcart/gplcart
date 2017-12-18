<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\models\Order as OrderModel;
use gplcart\core\helpers\Convertor as ConvertorHelper;

/**
 * Manages basic behaviors and data related to order dimensions
 */
class OrderDimension
{

    /**
     * Order model instance
     * @var \gplcart\core\models\Order $order
     */
    protected $order;

    /**
     * Convertor class instance
     * @var \gplcart\core\helpers\Convertor $convertor
     */
    protected $convertor;

    /**
     * @param OrderModel $order
     * @param ConvertorHelper $convertor
     */
    public function __construct(OrderModel $order, ConvertorHelper $convertor)
    {
        $this->order = $order;
        $this->convertor = $convertor;
    }

    /**
     * Returns a total volume of all products in the order
     * @param array|int $order
     * @param array $cart
     * @param integer $decimals
     * @return float
     */
    public function getTotalVolume($order, array $cart, $decimals = 2)
    {
        if (!is_array($order)) {
            $order = $this->order->get($order);
        }

        $total = 0;
        foreach ($cart['items'] as $item) {

            $product = $item['product'];
            if (empty($product['width']) || empty($product['height']) || empty($product['length'])) {
                return (float) 0;
            }

            $volume = $product['width'] * $product['height'] * $product['length'];
            if (empty($product['size_unit']) || $product['size_unit'] == $order['size_unit']) {
                $total += (float) ($volume * $item['quantity']);
                continue;
            }

            $order_cubic = $order['size_unit'] . '2';
            $product_cubic = $product['size_unit'] . '2';
            $converted = $this->convertor->convert($volume, $product_cubic, $order_cubic, $decimals);

            if (empty($converted)) {
                return (float) 0;
            }

            $total += (float) ($converted * $item['quantity']);
        }

        return round($total, $decimals);
    }

    /**
     * Returns a total weight of all products in the order
     * @param array|int $order
     * @param array $cart
     * @param integer $decimals
     * @return float
     */
    public function getTotalWeight($order, array $cart, $decimals = 2)
    {
        if (!is_array($order)) {
            $order = $this->order->get($order);
        }

        $total = 0;
        foreach ($cart['items'] as $item) {

            if (empty($item['product']['weight'])) {
                return (float) 0;
            }

            $product = $item['product'];
            if (empty($product['weight_unit']) || $product['weight_unit'] == $order['weight_unit']) {
                $total += (float) ($product['weight'] * $item['quantity']);
                continue;
            }

            $converted = $this->convertor->convert($product['weight'], $product['weight_unit'], $order['weight_unit'], $decimals);
            if (empty($converted)) {
                return (float) 0;
            }

            $total += (float) ($converted * $item['quantity']);
        }

        return round($total, $decimals);
    }

}
