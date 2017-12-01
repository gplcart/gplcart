<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains methods for setting price values
 */
trait ItemPrice
{

    /**
     * Adds "total_formatted" key
     * @param array $item
     * @param \gplcart\core\models\Price $price_model
     */
    public function setItemTotalFormatted(array &$item, $price_model)
    {
        $item['total_formatted'] = $price_model->format($item['total'], $item['currency']);
    }

    /**
     * Adds "total_formatted_number" key
     * @param array $item
     * @param \gplcart\core\models\Price $price_model
     */
    public function setItemTotalFormattedNumber(array &$item, $price_model)
    {
        $item['total_formatted_number'] = $price_model->format($item['total'], $item['currency'], true, false);
    }

    /**
     * Add keys with formatted prices
     * @param array $item
     * @param \gplcart\core\models\Price $price_model
     * @param string|null $currency
     */
    public function setItemPriceFormatted(array &$item, $price_model, $currency = null)
    {
        if (!isset($currency)) {
            $currency = $item['currency'];
        }

        $price = $price_model->convert($item['price'], $item['currency'], $currency);
        $item['price_formatted'] = $price_model->format($price, $currency);

        if (isset($item['original_price'])) {
            $price = $price_model->convert($item['original_price'], $item['currency'], $currency);
            $item['original_price_formatted'] = $price_model->format($price, $currency);
        }
    }

    /**
     * Adjust an original price according to applied price rules
     * @param array $item
     * @param \gplcart\core\models\Product $product_model
     */
    public function setItemPriceCalculated(array &$item, $product_model)
    {
        $calculated = $product_model->calculate($item);

        if ($item['price'] != $calculated) {
            $item['original_price'] = $item['price'];
        }

        $item['price'] = $calculated;
    }

}
