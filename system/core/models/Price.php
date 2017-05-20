<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\models\Currency as CurrencyModel;

/**
 * Manages basic behaviors and data related to prices
 */
class Price extends Model
{

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Constructor
     * @param CurrencyModel $currency
     */
    public function __construct(CurrencyModel $currency)
    {
        parent::__construct();

        $this->currency = $currency;
    }

    /**
     * Format a price
     * @param integer $amount
     * @param string $currency
     * @param bool $decimal
     * @param bool $full
     * @return string
     */
    public function format($amount, $currency, $decimal = true, $full = true)
    {
        $data = (array) $this->currency->get($currency);

        if ($decimal) {
            $amount = $this->decimal($amount, $currency);
        }

        $rounded = $this->round(abs($amount), $data);
        $data['price'] = number_format($rounded, $data['decimals'], $data['decimal_separator'], $data['thousands_separator']);

        if (!$full) {
            return $amount < 0 ? "-{$data['price']}" : $data['price'];
        }

        $placeholders = array();
        foreach (array_keys($data) as $key) {
            $placeholders["%$key"] = $key;
        }

        $formatted = gplcart_string_replace($data['template'], $placeholders, $data);
        return $amount < 0 ? "-$formatted" : $formatted;
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
            $currency = (array) $this->currency->get($currency_code);
            $factors[$currency_code] = pow(10, $currency['decimals']);
        }

        if ($round) {
            $currency = (array) $this->currency->get($currency_code);
            $decimal = $this->round($decimal, $currency);
            return (int) round($decimal * $factors[$currency_code]);
        }

        return $decimal * $factors[$currency_code];
    }

}
