<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook,
    gplcart\core\Config;
use gplcart\core\helpers\Request as RequestHelper;

/**
 * Manages basic behaviors and data related to currencies
 */
class Currency
{

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param RequestHelper $request
     */
    public function __construct(Hook $hook, Config $config, RequestHelper $request)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->request = $request;
        $this->db = $config->getDb();
    }

    /**
     * Loads a currency from the database
     * @param string $code
     * @return array
     */
    public function get($code)
    {
        $result = null;
        $this->hook->attach('currency.get.before', $code, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        $list = $this->getList();
        $result = empty($list[$code]) ? array() : $list[$code];
        $this->hook->attach('currency.get.after', $code, $result, $this);
        return (array) $result;
    }

    /**
     * Returns an array of currencies
     * @param array $options
     * @return array
     */
    public function getList(array $options = array())
    {
        $options += array(
            'enabled' => false,
            'in_database' => false
        );

        $currencies = &gplcart_static(gplcart_array_hash(array('currency.list' => $options)));

        if (isset($currencies)) {
            return $currencies;
        }

        $this->hook->attach('currency.list.before', $options, $currencies, $this);

        if (isset($currencies)) {
            return $currencies;
        }

        $iso = (array) $this->getIso();
        $default = $this->getDefaultData();
        $saved = $this->config->get('currencies', array());
        $currencies = array_replace_recursive($iso, $saved);

        $this->hook->attach('currency.list.after', $options, $currencies, $this);

        foreach ($currencies as $code => &$currency) {

            $currency['code'] = $code;
            $currency += $default;
            $currency['in_database'] = isset($saved[$code]);

            if ($code === 'USD') {
                $currency['status'] = $currency['default'] = 1;
            }

            if ($options['enabled'] && empty($currency['status'])) {
                unset($currencies[$code]);
                continue;
            }

            if ($options['in_database'] && empty($currency['in_database'])) {
                unset($currencies[$code]);
            }
        }

        return $currencies;
    }

    /**
     * Adds a currency
     * @param array $data
     * @return boolean
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('currency.add.before', $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $data['modified'] = GC_TIME;

        $default = $this->getDefaultData();
        $data += $default;

        $currencies = $this->config->select('currencies', array());
        $currencies[$data['code']] = array_intersect_key($data, $default);
        $this->config->set('currencies', $currencies);

        if (!empty($data['default'])) {
            $data['status'] = 1;
            $this->config->set('currency', $data['code']);
        }

        $result = true;
        $this->hook->attach('currency.add.after', $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Updates a currency
     * @param string $code
     * @param array $data
     * @return boolean
     */
    public function update($code, array $data)
    {
        $result = null;
        $this->hook->attach('currency.update.before', $code, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $data['modified'] = GC_TIME;

        if (!empty($data['default'])) {
            $data['status'] = 1;
            $this->config->set('currency', $code);
        }

        $default = $this->getDefaultData();
        $data += $default;

        $currencies = $this->config->select('currencies', array());
        $currencies[$code] = array_intersect_key($data, $default);
        $this->config->set('currencies', $currencies);

        $result = true;
        $this->hook->attach('currency.update.after', $code, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes a currency
     * @param string $code
     * @param bool $check
     * @return boolean
     */
    public function delete($code, $check = true)
    {
        $result = null;
        $this->hook->attach('currency.delete.before', $code, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($code)) {
            return false;
        }

        $currencies = $this->config->select('currencies', array());
        unset($currencies[$code]);
        $this->config->set('currencies', $currencies);

        $result = true;
        $this->hook->attach('currency.delete.after', $code, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether the currency can be deleted
     * @param string $code
     * @return boolean
     */
    public function canDelete($code)
    {
        if ($code == $this->getDefault()) {
            return false;
        }

        $currencies = $this->config->select('currencies', array());

        if (!isset($currencies[$code])) {
            return false;
        }

        $sql = 'SELECT NOT EXISTS (SELECT currency FROM orders WHERE currency=:code)'
                . ' AND NOT EXISTS (SELECT currency FROM price_rule WHERE currency=:code)'
                . ' AND NOT EXISTS (SELECT currency FROM product WHERE currency=:code)';

        return (bool) $this->db->fetchColumn($sql, array('code' => $code));
    }

    /**
     * Converts currencies
     * @param integer $amount
     * @param string $from_currency
     * @param string $to_currency
     * @return integer
     */
    public function convert($amount, $from_currency, $to_currency)
    {
        if ($from_currency === $to_currency) {
            return $amount; // Nothing to convert
        }

        $currency = $this->get($from_currency);
        $target_currency = $this->get($to_currency);

        $exponent = $target_currency['decimals'] - $currency['decimals'];
        $amount *= pow(10, $exponent);

        return $amount * ($currency['conversion_rate'] / $target_currency['conversion_rate']);
    }

    /**
     * Returns the current currency code
     * @param bool $set
     * @return string
     */
    public function getCode($set = true)
    {
        $list = $this->getList();
        $code = $this->getFromUrl();

        if (empty($code)) {
            $code = $this->getFromCookie();
        }

        if (empty($list[$code]['status'])) {
            $code = $this->getDefault();
        }

        if ($set) {
            $this->setCookie($code);
        }

        return $code;
    }

    /**
     * Saves a currency code in cookie
     * @param string $code
     */
    public function setCookie($code)
    {
        $lifespan = $this->config->get('currency_cookie_lifespan', 365 * 24 * 60 * 60);
        $this->request->setCookie('currency', $code, $lifespan);
    }

    /**
     * Returns a currency code from cookie
     * @return string
     */
    public function getFromCookie()
    {
        return $this->request->cookie('currency', '', 'string');
    }

    /**
     * Returns a currency code from the current GET query
     * @return string
     */
    public function getFromUrl()
    {
        return $this->request->get('currency', '', 'string');
    }

    /**
     * Returns a default currency
     * @return string
     */
    public function getDefault()
    {
        return $this->config->get('currency', 'USD');
    }

    /**
     * Returns an array of currencies or a single currency data if $code is set
     * @param null|string $code
     * @return array|string
     */
    public function getIso($code = null)
    {
        $data = (array) gplcart_config_get(GC_FILE_CONFIG_CURRENCY);

        if (isset($code)) {
            return isset($data[$code]) ? $data[$code] + array('code' => $code) : array();
        }

        return $data;
    }

    /**
     * Returns an array of default currency data
     * @return array
     */
    protected function getDefaultData()
    {
        return array(
            'code' => '',
            'name' => '',
            'symbol' => '',
            'status' => 0,
            'default' => 0,
            'modified' => 0,
            'decimals' => 2,
            'major_unit' => '',
            'minor_unit' => '',
            'numeric_code' => '',
            'rounding_step' => 0,
            'conversion_rate' => 1,
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'template' => '%symbol%price'
        );
    }

}
