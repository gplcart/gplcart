<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\models\Price as PriceModel;
use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\models\Condition as ConditionModel;

/**
 * Provides methods to check cart conditions
 */
class Cart
{

    /**
     * Condition model instance
     * @var \gplcart\core\models\Condition $condition
     */
    protected $condition;

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
     * Constructor
     * @param ConditionModel $condition
     * @param PriceModel $price
     * @param CurrencyModel $currency
     */
    public function __construct(ConditionModel $condition, PriceModel $price,
            CurrencyModel $currency)
    {
        $this->price = $price;
        $this->currency = $currency;
        $this->condition = $condition;
    }

    /**
     * Returns true if a cart total condition is met
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

        $cart_currency = $data['cart']['currency'];
        $condition_currency = $cart_currency;

        if (!empty($condition_value[1])) {
            $condition_currency = $condition_value[1];
        }

        $condition_value[0] = (int) $this->price->amount($condition_value[0], $condition_currency);
        $value = $this->currency->convert((int) $condition_value[0], $condition_currency, $cart_currency);

        return $this->condition->compareNumeric((int) $data['cart']['total'], $value, $condition['operator']);
    }

}
