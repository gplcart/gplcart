<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\handlers\condition\Base as BaseHandler;

/**
 * Provides methods to check cart conditions
 */
class Cart extends BaseHandler
{

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * @param PriceModel $price
     * @param CurrencyModel $currency
     */
    public function __construct(PriceModel $price, CurrencyModel $currency)
    {
        parent::__construct();

        $this->price = $price;
        $this->currency = $currency;
    }

    /**
     * Whether the cart total condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function total(array $condition, array $data)
    {
        if (!isset($data['cart']['total']) || empty($data['cart']['currency'])) {
            return false;
        }

        $condition_value = explode('|', reset($condition['value']), 2);
        $condition_currency = $data['cart']['currency'];

        if (!empty($condition_value[1])) {
            $condition_currency = $condition_value[1];
        }

        $condition_value[0] = $this->price->amount($condition_value[0], $condition_currency);
        $value = $this->currency->convert($condition_value[0], $condition_currency, $data['cart']['currency']);

        return $this->compare($data['cart']['total'], $value, $condition['operator']);
    }

    /**
     * Whether the cart product ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function productId(array $condition, array $data)
    {
        if (empty($data['cart']['items']) || !in_array($condition['operator'], array('=', '!='))) {
            return false;
        }

        $ids = array();
        foreach ($data['cart']['items'] as $item) {
            $ids[] = $item['product_id'];
        }

        return $this->compare($ids, $condition['value'], $condition['operator']);
    }

    /**
     * Whether the cart product SKU condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function sku(array $condition, array $data)
    {
        if (empty($data['cart']['items']) || !in_array($condition['operator'], array('=', '!='))) {
            return false;
        }

        return $this->compare(array_keys($data['cart']['items']), $condition['value'], $condition['operator']);
    }

}
