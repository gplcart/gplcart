<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook;
use gplcart\core\helpers\Convertor as ConvertorHelper;

/**
 * Manages basic behaviors and data related to order dimensions
 */
class OrderDimension
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Convertor class instance
     * @var \gplcart\core\helpers\Convertor $convertor
     */
    protected $convertor;

    /**
     * @param Hook $hook
     * @param ConvertorHelper $convertor
     */
    public function __construct(Hook $hook, ConvertorHelper $convertor)
    {
        $this->hook = $hook;
        $this->convertor = $convertor;
    }

    /**
     * Returns a total volume of all products in the order
     * @param array $order
     * @param array $cart
     * @return float|null
     */
    public function getVolume(array $order, array $cart)
    {
        $result = null;
        $this->hook->attach('order.get.volume.before', $order, $cart, $result, $this);

        if (isset($result)) {
            return (float) $result;
        }

        $total = 0.0;
        foreach ($cart['items'] as $item) {

            try {
                $this->sumTotalVolume($total, $item, $order);
            } catch (\Exception $ex) {
                trigger_error($ex->getMessage());
                return null;
            }
        }

        $result = round($total, 2);
        $this->hook->attach('order.get.volume.after', $order, $cart, $result, $this);
        return (float) $result;
    }

    /**
     * Returns a total weight of all products in the order
     * @param array $order
     * @param array $cart
     * @return float|null
     */
    public function getWeight(array $order, array $cart)
    {
        $result = null;
        $this->hook->attach('order.get.weight.before', $order, $cart, $result, $this);

        if (isset($result)) {
            return (float) $result;
        }

        $total = 0.0;
        foreach ($cart['items'] as $item) {

            try {
                $this->sumTotalWeight($total, $item, $order);
            } catch (\Exception $ex) {
                trigger_error($ex->getMessage());
                return null;
            }
        }

        $result = round($total, 2);
        $this->hook->attach('order.get.weight.after', $order, $cart, $result, $this);
        return (float) $result;
    }

    /**
     * Returns an array of packages for the order
     * @param array $order
     * @param array $cart
     * @return array
     */
    public function getPackages(array $order, array $cart)
    {
        $result = null;
        $this->hook->attach('order.get.packages.before', $order, $cart, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        $result = array();
        $this->hook->attach('order.get.packages.after', $order, $cart, $result, $this);
        return (array) $result;
    }

    /**
     * Sum volume totals
     * @param float $total
     * @param array $item
     * @param array $order
     * @return null
     * @throws \InvalidArgumentException
     */
    protected function sumTotalVolume(&$total, array $item, array $order)
    {
        $product = $item['product'];
        if (empty($product['width']) || empty($product['height']) || empty($product['length'])) {
            return null;
        }

        $volume = (float) ($product['width'] * $product['height'] * $product['length']);

        if (empty($product['size_unit']) || $product['size_unit'] == $order['size_unit']) {
            $total += (float) ($volume * $item['quantity']);
            return null;
        }

        $order_cubic = $order['size_unit'] . '3';
        $product_cubic = $product['size_unit'] . '3';

        $converted = $this->convertor->convert($volume, $product_cubic, $order_cubic);
        $total += (float) ($converted * $item['quantity']);
        return null;
    }

    /**
     * Sum weight totals
     * @param float $total
     * @param array $item
     * @param array $order
     * @return null
     */
    protected function sumTotalWeight(&$total, array $item, array $order)
    {
        $product = $item['product'];
        if (empty($product['weight_unit']) || $product['weight_unit'] == $order['weight_unit']) {
            $total += (float) ($product['weight'] * $item['quantity']);
            return null;
        }

        $converted = $this->convertor->convert($product['weight'], $product['weight_unit'], $order['weight_unit']);
        $total += (float) ($converted * $item['quantity']);
        return null;
    }

}
