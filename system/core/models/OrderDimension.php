<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use Exception;
use gplcart\core\Hook;
use gplcart\core\models\Convertor as ConvertorModel;

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
     * Convertor model class instance
     * @var \gplcart\core\models\Convertor $convertor
     */
    protected $convertor;

    /**
     * @param Hook $hook
     * @param Convertor $convertor
     */
    public function __construct(Hook $hook, ConvertorModel $convertor)
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
    public function getVolume(array $order, array &$cart)
    {
        $result = null;
        $this->hook->attach('order.volume.get.before', $order, $cart, $result, $this);

        if (isset($result)) {
            return (float) $result;
        }

        $total = 0.0;

        foreach ($cart['items'] as &$item) {
            $this->sumTotalVolume($total, $item, $order);
        }

        $result = round($total, 2);
        $this->hook->attach('order.volume.get.after', $order, $cart, $result, $this);
        return (float) $result;
    }

    /**
     * Returns a total weight of all products in the order
     * @param array $order
     * @param array $cart
     * @return float|null
     */
    public function getWeight(array $order, array &$cart)
    {
        $result = null;
        $this->hook->attach('order.weight.get.before', $order, $cart, $result, $this);

        if (isset($result)) {
            return (float) $result;
        }

        $total = 0.0;
        foreach ($cart['items'] as &$item) {
            $this->sumTotalWeight($total, $item, $order);
        }

        $result = round($total, 2);
        $this->hook->attach('order.weight.get.after', $order, $cart, $result, $this);
        return (float) $result;
    }

    /**
     * Sum volume totals
     * @param float $total
     * @param array $item
     * @param array $order
     * @return null
     */
    protected function sumTotalVolume(&$total, array &$item, array $order)
    {
        $product = &$item['product'];

        if (empty($product['width']) || empty($product['height']) || empty($product['length'])) {
            return null;
        }

        if ($product['size_unit'] !== $order['size_unit']) {

            try {
                $product['width'] = $this->convertor->convert($product['width'], $product['size_unit'], $order['size_unit']);
                $product['height'] = $this->convertor->convert($product['height'], $product['size_unit'], $order['size_unit']);
                $product['length'] = $this->convertor->convert($product['length'], $product['size_unit'], $order['size_unit']);

            } catch (Exception $ex) {
                return null;
            }

            $product['size_unit'] = $order['size_unit'];
        }

        $volume = (float) ($product['width'] * $product['height'] * $product['length']);
        $total += (float) ($volume * $item['quantity']);
        return null;
    }

    /**
     * Sum weight totals
     * @param float $total
     * @param array $item
     * @param array $order
     * @return null
     */
    protected function sumTotalWeight(&$total, array &$item, array $order)
    {
        $product = &$item['product'];

        if ($product['weight_unit'] !== $order['weight_unit']) {

            try {
                $product['weight'] = $this->convertor->convert($product['weight'], $product['weight_unit'], $order['weight_unit']);
            } catch (Exception $ex) {
                return null;
            }

            $product['weight_unit'] = $order['weight_unit'];
        }

        $total += (float) ($product['weight'] * $item['quantity']);
        return null;
    }

}
