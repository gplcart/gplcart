<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\price_rule;

use gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Currency as CurrencyModel;

/**
 * Contains callback methods to modify prices depending on the price rule type
 */
class Type
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
        $this->price = $price;
        $this->currency = $currency;
    }

    /**
     * Adds a percent price rule value to the original amount
     * @param int $amount
     * @param array $components
     * @param array $price_rule
     * @return int
     */
    public function percent(&$amount, array &$components, array $price_rule)
    {
        $value = $amount * ((float) $price_rule['value'] / 100);
        $components[$price_rule['price_rule_id']] = array('rule' => $price_rule, 'price' => $value);
        return $amount += $value;
    }

    /**
     * Adds a fixed price rule value to the original amount
     * @param int $amount
     * @param array $components
     * @param array $price_rule
     * @param string $original_currency
     * @return int
     */
    public function fixed(&$amount, array &$components, array $price_rule, $original_currency)
    {
        $value = $this->convertValue($price_rule, $original_currency);
        $components[$price_rule['price_rule_id']] = array('rule' => $price_rule, 'price' => $value);
        return $amount += $value;
    }

    /**
     * Sets a final amount using the price rule value
     * @param int $amount
     * @param array $components
     * @param array $price_rule
     * @param string $original_currency
     * @return int
     */
    public function finalAmount(&$amount, array &$components, array $price_rule, $original_currency)
    {
        $value = $this->convertValue($price_rule, $original_currency);
        $components[$price_rule['price_rule_id']] = array('rule' => $price_rule, 'price' => $value);
        return $amount = $value;
    }

    /**
     * Converts a price rule value to the minor units considering the currency
     * @param array $price_rule
     * @param string $currency
     * @return int
     */
    protected function convertValue(array $price_rule, $currency)
    {
        $amount = $this->price->amount(abs($price_rule['value']), $price_rule['currency']);
        $converted = $this->currency->convert($amount, $price_rule['currency'], $currency);
        return $price_rule['value'] < 0 ? -$converted : $converted;
    }

}
