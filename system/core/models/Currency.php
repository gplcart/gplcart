<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\helpers\Request as RequestHelper;

/**
 * Manages basic behaviors and data related to currencies
 */
class Currency extends Model
{

    /**
     * URL GET key
     */
    const URL_KEY = 'currency';

    /**
     * Cookie key
     */
    const COOKIE_KEY = 'currency';

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * @param RequestHelper $request
     */
    public function __construct(RequestHelper $request)
    {
        parent::__construct();

        $this->request = $request;
    }

    /**
     * Adds a currency
     * @param array $data
     * @return boolean
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('currency.get.before', $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $data['modified'] = GC_TIME;

        $default = $this->getDefaultData();
        $data += $default;

        if (!empty($data['default'])) {
            $data['status'] = 1;
            $this->config->set('currency', $data['code']);
        }

        $currencies = $this->getList(false, false);
        $currencies[$data['code']] = array_intersect_key($data, $default);
        $this->config->set('currencies', $currencies);

        $result = true;
        $this->hook->attach('currency.get.after', $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of currencies
     * @param bool $enabled
     * @param bool $cache
     * @return array
     */
    public function getList($enabled = false, $cache = true)
    {
        $currencies = &gplcart_static(__METHOD__ . $enabled);

        if ($cache && isset($currencies)) {
            return $currencies;
        }

        $default = $this->getDefaultList();

        if ($cache) {
            $saved = $this->config->get('currencies', array());
        } else {
            $saved = $this->config->select('currencies', array());
        }

        $currencies = array_merge($default, $saved);
        $this->hook->attach('currency.list', $currencies, $this);

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
        $result = null;
        $this->hook->attach('currency.update.before', $code, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $currencies = $this->getList(false, false);

        if (empty($currencies[$code])) {
            return false;
        }

        $data['modified'] = GC_TIME;

        if (!empty($data['default'])) {
            $data['status'] = 1;
            $this->config->set('currency', $code);
        }

        $data += $currencies[$code];
        $default = $this->getDefaultData();
        $currencies[$code] = array_intersect_key($data, $default);

        $this->config->set('currencies', $currencies);

        $result = true;
        $this->hook->attach('currency.update.after', $code, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes a currency
     * @param string $code
     * @return boolean
     */
    public function delete($code)
    {
        $result = null;
        $this->hook->attach('currency.delete.before', $code, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!$this->canDelete($code)) {
            return false;
        }

        $currencies = $this->getList(false, false);

        unset($currencies[$code]);

        $this->config->set('currencies', $currencies);

        $result = true;
        $this->hook->attach('currency.delete.after', $code, $result, $this);
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

        $sql = 'SELECT NOT EXISTS (SELECT currency FROM orders WHERE currency=:code)'
                . ' AND NOT EXISTS (SELECT currency FROM price_rule WHERE currency=:code)'
                . ' AND NOT EXISTS (SELECT currency FROM product WHERE currency=:code)';

        return (bool) $this->db->fetchColumn($sql, array('code' => $code));
    }

    /**
     * Converts currencies
     * @param integer $amount
     * @param string $code
     * @param string $target_code
     * @return integer
     */
    public function convert($amount, $code, $target_code)
    {
        if ($code === $target_code) {
            return $amount; // Nothing to convert
        }

        $currency = $this->get($code);
        $target_currency = $this->get($target_code);

        $exponent = $target_currency['decimals'] - $currency['decimals'];
        $amount *= pow(10, $exponent);

        return $amount * ($currency['conversion_rate'] / $target_currency['conversion_rate']);
    }

    /**
     * Loads a currency from the database
     * @param null|string $code
     * @return array|string
     */
    public function get($code = null)
    {
        $currency = &gplcart_static(__METHOD__ . $code);

        if (isset($currency)) {
            return $currency;
        }

        $list = $this->getList();

        if (!empty($code)) {
            $currency = empty($list[$code]) ? array() : $list[$code];
            return $currency;
        }

        $code = $this->getFromUrl();

        if (empty($code)) {
            $code = $this->getFromCookie();
        }

        if (empty($list[$code]['status'])) {
            $code = $this->getDefault();
        }

        $this->setCookie($code);
        return $currency = $code;
    }

    /**
     * Saves a currency code in cookie
     * @param string $code
     */
    public function setCookie($code)
    {
        $lifespan = $this->config->get('currency_cookie_lifespan', 365*24*60*60);
        $this->request->setCookie(self::COOKIE_KEY, $code, $lifespan);
    }

    /**
     * Returns a currency code from cookie
     * @return string
     */
    public function getFromCookie()
    {
        return $this->request->cookie(self::COOKIE_KEY, '', 'string');
    }

    /**
     * Returns a currency code from the current GET query
     * @return string
     */
    public function getFromUrl()
    {
        return $this->request->get(self::URL_KEY, '', 'string');
    }

    /**
     * Returns a currency by a numeric code
     * @param integer $code
     * @return array
     */
    public function getByNumericCode($code)
    {
        $list = $this->getList();

        foreach ($list as $currency) {
            if ($currency['numeric_code'] == $code) {
                return $currency;
            }
        }

        return array();
    }

    /**
     * Returns a default currency
     * @param boolean $load
     * @return string
     */
    public function getDefault($load = false)
    {
        $currency = $this->config->get('currency', 'USD');

        if ($load) {
            $currencies = $this->getList();
            return empty($currencies[$currency]) ? array() : $currencies[$currency];
        }

        return $currency;
    }

    /**
     * Returns an array of default currencies
     * @return array
     */
    protected function getDefaultList()
    {
        $list = (array) $this->getIso();
        $default = $this->getDefaultData();

        foreach ($list as $code => &$currency) {
            $currency['code'] = $code;
            $currency += $default;
        }

        $list['USD']['status'] = 1;
        $list['USD']['default'] = 1;

        return $list;
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

    /**
     * Returns an array of currencies or a single currency data if $code is set
     * @param null|string $code
     * @return array|string
     */
    public function getIso($code = null)
    {
        static $data = null;

        if (!isset($data)) {
            $data = require GC_CONFIG_CURRENCY;
        }

        if (isset($code)) {
            return isset($data[$code]) ? $data[$code] + array('code' => $code) : array();
        }

        return $data;
    }

}
