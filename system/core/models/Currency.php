<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Hook;
use core\Config;
use core\classes\Tool;
use core\classes\Cache;
use core\classes\Request;

/**
 * Manages basic behaviors and data related to currencies
 */
class Currency
{

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Request class instance
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * PDO instance
     * @var \core\classes\Database
     */
    protected $db;

    /**
     * Constructor
     * @param Hook $hook
     * @param Request $request
     * @param Config $config
     */
    public function __construct(Hook $hook, Request $request, Config $config)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->request = $request;
        $this->db = $this->config->getDb();
    }

    /**
     * Adds a currency
     * @param array $data
     * @return boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('add.currency.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'code' => $data['code'],
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'status' => !empty($data['status']),
            'default' => !empty($data['default']),
            'decimals' => isset($data['decimals']) ? (int) $data['decimals'] : 2,
            'major_unit' => $data['major_unit'],
            'minor_unit' => $data['minor_unit'],
            'code_spacer' => isset($data['code_spacer']) ? $data['code_spacer'] : ' ',
            'numeric_code' => (int) $data['numeric_code'],
            'symbol_spacer' => isset($data['symbol_spacer']) ? $data['symbol_spacer'] : ' ',
            'rounding_step' => isset($data['rounding_step']) ? (int) $data['rounding_step'] : 0,
            'code_placement' => isset($data['code_placement']) ? $data['code_placement'] : 'after',
            'convertion_rate' => isset($data['convertion_rate']) ? $data['convertion_rate'] : 1,
            'symbol_placement' => isset($data['symbol_placement']) ? $data['symbol_placement'] : 'before',
            'decimal_separator' => isset($data['decimal_separator']) ? $data['decimal_separator'] : '.',
            'thousands_separator' => isset($data['thousands_separator']) ? $data['thousands_separator'] : ',',
        );

        $currencies = $this->getList();
        $currencies[$data['code']] = $values;
        $this->config->set('currencies', $currencies);

        if (!empty($values['default'])) {
            $this->config->set('currency', $data['code']);
        }

        $this->hook->fire('add.currency.after', $data);
        return true;
    }

    /**
     * Returns an array of currensies
     * @param boolean $enabled
     * @return type
     */
    public function getList($enabled = false)
    {
        $currencies = &Cache::memory("currencies.$enabled");

        if (isset($currencies)) {
            return $currencies;
        }

        $default = $this->defaultCurrency();
        $saved = $this->config->get('currencies', array());
        $currencies = Tool::merge($default, $saved);

        $this->hook->fire('currencies', $currencies);

        if ($enabled) {
            $currencies = array_filter($currencies, function ($currency) {
                return !empty($currency['status']);
            });
        }

        return $currencies;
    }

    /**
     * Updates a currency
     * @param string $code
     * @param array $data
     * @return boolean
     */
    public function update($code, array $data)
    {
        $this->hook->fire('update.currency.before', $code, $data);

        $currencies = $this->getList();

        if (empty($currencies[$code])) {
            return false;
        }

        if (!empty($data['default'])) {
            $this->config->set('currency', $code);
        }

        $currencies[$code] = $data + $currencies[$code];
        $this->config->set('currencies', $currencies);

        $this->hook->fire('update.currency.after', $data);
        return true;
    }

    /**
     * Deletes a currency
     * @param string $code
     * @return boolean
     */
    public function delete($code)
    {
        $this->hook->fire('delete.currency.before', $code);

        $currencies = $this->getList();

        if (empty($currencies[$code])) {
            return false;
        }

        if (!$this->canDelete($code)) {
            return false;
        }

        unset($currencies[$code]);
        $this->config->set('currencies', $currencies);

        $this->hook->fire('delete.currency.after', $code);
        return true;
    }

    /**
     * Returns true if the currency can be deleted
     * @param type $code
     * @return boolean
     */
    public function canDelete($code)
    {
        if ($code == $this->config->get('currency', 'USD')) {
            return false;
        }

        $sql = '
            SELECT NOT EXISTS (SELECT currency FROM orders WHERE currency=:currency) AND
            NOT EXISTS (SELECT currency FROM price_rule WHERE currency=:currency) AND
            NOT EXISTS (SELECT currency FROM product WHERE currency=:currency)';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':currency' => $code));

        return (bool) $sth->fetchColumn();
    }

    /**
     * Converts currencies
     * @param integer $amount
     * @param string $currency_code
     * @param string $target_currency_code
     * @return integer
     */
    public function convert($amount, $currency_code, $target_currency_code)
    {
        if ($currency_code === $target_currency_code) {
            return $amount; // Nothing to convert
        }

        $currency = $this->get($currency_code);
        $target_currency = $this->get($target_currency_code);

        $exponent = $target_currency['decimals'] - $currency['decimals'];
        $amount *= pow(10, $exponent);

        return $amount * ($currency['conversion_rate'] / $target_currency['conversion_rate']);
    }

    /**
     * Loads a currency from the database
     * @param string $currency_code
     * @return array
     */
    public function get($currency_code = null)
    {
        $currency = &Cache::memory("currency.$currency_code");

        if (isset($currency)) {
            return $currency;
        }

        $list = $this->getList();

        if ($currency_code) {
            $currency = isset($list[$currency_code]) ? $list[$currency_code] : array();
            return $currency;
        }

        $url = $this->request->get('currency');

        if ($url) {
            $currency_code = $url;
            $query = true;
        } else {
            $cookie = $this->request->cookie('currency');
            if ($cookie) {
                $currency_code = $cookie;
            }
        }

        if (isset($currency_code) && isset($list[$currency_code])) {
            if (isset($query)) {
                Tool::setCookie('currency', $currency_code, $this->config->get('currency_cookie_lifespan', 31536000));
            }
            $currency = $currency_code;
            return $currency_code;
        }

        $currency = $this->getDefault();
        return $currency;
    }

    /**
     * Returns a default currency
     * @param boolean $load
     * @return string
     */
    public function getDefault($load = false)
    {
        $default_currency = $this->config->get('currency', 'USD');

        if (!$load) {
            return $default_currency;
        }

        $currencies = $this->getList();
        return isset($currencies[$default_currency]) ? $currencies[$default_currency] : array();
    }

    /**
     * Returns an array of default currencies
     * @return array
     */
    protected function defaultCurrency()
    {
        return array(
            'USD' => array(
                'code' => 'USD',
                'name' => 'United States Dollars',
                'symbol' => '$',
                'status' => 1,
                'default' => 1,
                'decimals' => 2,
                'major_unit' => 'Dollar',
                'minor_unit' => 'Cent',
                'code_spacer' => ' ',
                'numeric_code' => 840,
                'symbol_spacer' => ' ',
                'rounding_step' => 0,
                'code_placement' => 'after',
                'convertion_rate' => 1,
                'symbol_placement' => 'before',
                'decimal_separator' => '.',
                'thousands_separator' => ','
            )
        );
    }
}
