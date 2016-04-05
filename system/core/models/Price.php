<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use core\models\Currency as ModelsCurrency;

/**
 * Manages basic behaviors and data related to currencies
 */
class Price
{

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Constructor
     * @param ModelsCurrency $currency
     */
    public function __construct(ModelsCurrency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * Returns a formatted price
     * @param integer $amount
     * @param string $currency_code
     * @param boolean $convert If true, convert amount to decimal
     * @return string
     */
    public function get($amount, $currency_code, $convert = true)
    {
        $current_currency = $this->currency->get();

        if ($convert && ($currency_code != $current_currency)) {
            $amount = $this->currency->convert($amount, $currency_code, $current_currency);
            return $this->format($amount, $current_currency);
        }

        return $this->format($amount, $currency_code);
    }

    /**
     * Formats a price value
     * @param integer $amount
     * @param string $currency_code
     * @param boolean $convert
     * @return string
     */
    public function format($amount, $currency_code, $convert = true)
    {
        $currency = $this->currency->get($currency_code);

        if ($convert) {
            $amount = $this->decimal($amount, $currency_code);
        }

        $price = number_format($this->round(abs($amount), $currency), $currency['decimals'], $currency['decimal_separator'], $currency['thousands_separator']);

        $replacement = array(
            '%s%s%s%s%s%s%s%s%s',
            $currency['code_placement'] == 'before' ? $currency['code'] : '',
            $currency['code_spacer'],
            $amount < 0 ? '-' : '',
            $currency['symbol_placement'] == 'before' ? $currency['symbol'] : '',
            $price,
            $currency['symbol_spacer'],
            $currency['symbol_placement'] == 'after' ? $currency['symbol'] : '',
            $currency['code_spacer'],
            $currency['code_placement'] == 'after' ? $currency['code'] : '',
        );

        $string = call_user_func_array('sprintf', $replacement);
        return trim($string);
    }

    /**
     * Converts a price from minor to major units
     * @staticvar array $divisors
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
     * Rounds a price value
     * @param integer $amount
     * @param string $currency
     * @return integer
     */
    public function round($amount, $currency)
    {
        if (empty($currency['rounding_step'])) {
            return round($amount, $currency['decimals']);
        }

        $modifier = 1 / $currency['rounding_step'];
        return round($amount * $modifier) / $modifier;
    }

    /**
     * Converts a price from major to minor units
     * @staticvar array $factors
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
            $decimal = $this->round($decimal, $this->currency->get($currency_code));
            return (int) round($decimal * $factors[$currency_code]);
        }

        return $decimal * $factors[$currency_code];
    }

    /**
     * Converts currencies. Alias of \core\models\Currency::convert()
     * @param integer $amount
     * @param string $currency_code
     * @param string $target_currency_code
     * @return integer
     */
    public function convert($amount, $currency_code, $target_currency_code)
    {
        return $this->currency->convert($amount, $currency_code, $target_currency_code);
    }

}
