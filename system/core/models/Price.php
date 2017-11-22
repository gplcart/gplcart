<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\models\Currency as CurrencyModel;

/**
 * Manages basic behaviors and data related to prices
 */
class Price
{

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * @param CurrencyModel $currency
     */
    public function __construct(CurrencyModel $currency)
    {
        $this->currency = $currency;
    }

    /**
     * Format a price
     * @param integer $amount
     * @param string $currency_code
     * @param bool $decimal
     * @param bool $full
     * @return string
     */
    public function format($amount, $currency_code, $decimal = true, $full = true)
    {
        $currency = $this->currency->get($currency_code);

        if (empty($currency)) {
            return 'n/a';
        }

        if ($decimal) {
            $amount = $this->decimal($amount, $currency_code);
        }

        // Pass the amount to the currency template as %price variable
        $currency['price'] = $this->formatNumber($amount, $currency);

        if ($full) {
            return $this->formatTemplate($amount, $currency);
        }

        return $amount < 0 ? '-' . $currency['price'] : $currency['price'];
    }

    /**
     * Format an amount using the currency template
     * @param int|float $amount
     * @param array $data
     * @return string
     */
    public function formatTemplate($amount, array $data)
    {
        $placeholders = array();
        foreach (array_keys($data) as $key) {
            $placeholders["%$key"] = $key;
        }

        $formatted = gplcart_string_replace($data['template'], $placeholders, $data);
        return $amount < 0 ? "-$formatted" : $formatted;
    }

    /**
     * Format an amount with grouped thousands
     * @param int|float $amount
     * @param array $currency
     * @return string
     */
    public function formatNumber($amount, array $currency)
    {
        $rounded = $this->round(abs($amount), $currency);
        return number_format($rounded, $currency['decimals'], $currency['decimal_separator'], $currency['thousands_separator']);
    }

    /**
     * Converts an amount from minor to major units
     * @param integer $amount
     * @param string $currency_code
     * @return float
     */
    public function decimal($amount, $currency_code)
    {
        static $divisors = array();

        if (empty($divisors[$currency_code])) {
            $currency = $this->currency->get($currency_code);
            $divisors[$currency_code] = pow(10, $currency['decimals']);
        }

        return $amount / $divisors[$currency_code];
    }

    /**
     * Rounds an amount
     * @param integer $amount
     * @param array $currency
     * @return integer
     */
    public function round($amount, array $currency)
    {
        if (empty($currency['rounding_step'])) {
            return round($amount, $currency['decimals']);
        }

        $modifier = 1 / $currency['rounding_step'];
        return round($amount * $modifier) / $modifier;
    }

    /**
     * Converts a price from major to minor units
     * @param float $decimal
     * @param string|null $currency_code
     * @param boolean $round
     * @return integer
     */
    public function amount($decimal, $currency_code = null, $round = true)
    {
        static $factors = array();

        if (empty($currency_code)) {
            $currency_code = $this->currency->getDefault();
        }

        if (empty($factors[$currency_code])) {
            $currency = $this->currency->get($currency_code);
            $factors[$currency_code] = pow(10, $currency['decimals']);
        }

        if ($round) {
            $currency = $this->currency->get($currency_code);
            $decimal = $this->round($decimal, $currency);
            return (int) round($decimal * $factors[$currency_code]);
        }

        return $decimal * $factors[$currency_code];
    }

    /**
     * Converts currencies
     * @param int $amount
     * @param string $from_currency
     * @param string $to_currency
     * @return int
     */
    public function convert($amount, $from_currency, $to_currency)
    {
        return $this->currency->convert($amount, $from_currency, $to_currency);
    }

}
