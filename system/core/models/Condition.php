<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Cache,
    gplcart\core\Container;
use gplcart\core\helpers\Url;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to trigger conditions
 */
class Condition extends Model
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Url helper instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Constructor
     * @param LanguageModel $language
     * @param Url $url
     */
    public function __construct(LanguageModel $language, Url $url)
    {
        parent::__construct();

        $this->url = $url;
        $this->language = $language;
    }

    /**
     * Compares numeric values
     * @param mixed $a
     * @param mixed $b
     * @param string $operator
     * @return boolean
     */
    public function compare($a, $b, $operator)
    {
        settype($a, 'array');
        settype($b, 'array');

        if (in_array($operator, array('>=', '<=', '>', '<'))) {
            $a = reset($a);
            $b = reset($b);
        }

        switch ($operator) {
            case '>=':
                return ($a >= $b);
            case '<=':
                return ($a <= $b);
            case '>':
                return ($a > $b);
            case '<':
                return ($a < $b);
            case '=':
                return count(array_intersect($a, $b)) > 0;
            case '!=':
                return count(array_intersect($a, $b)) == 0;
        }

        return false;
    }

    /**
     * Returns true if all conditions are met
     * @param array $trigger
     * @param array $data
     * @return boolean
     */
    public function isMet(array $trigger, array $data)
    {
        $this->hook->fire('condition.met.before', $trigger, $data);

        if (empty($trigger['data']['conditions'])) {
            return false;
        }

        $met = true;
        $context = array('processed' => array());
        $handlers = $this->getHandlers();

        foreach ($trigger['data']['conditions'] as $condition) {

            if (empty($handlers[$condition['id']]['handlers']['process'])) {
                continue;
            }

            $class = $handlers[$condition['id']]['handlers']['process'];
            $instance = Container::get($class);

            $result = call_user_func_array(array($instance, $class[1]), array($condition, $data, &$context));
            $context['processed'][$condition['id']] = $result;

            if ($result !== true) {
                $met = false;
                break;
            }
        }

        $this->hook->fire('condition.met.after', $trigger, $data, $met);
        return $met;
    }

    /**
     * Returns an array of condition operators
     * @return array
     */
    public function getOperators()
    {
        return array(
            "<" => $this->language->text('Less than'),
            ">" => $this->language->text('Greater than'),
            "=" => $this->language->text('Equal (is in list)'),
            "<=" => $this->language->text('Less than or equal to'),
            ">=" => $this->language->text('Greater than or equal to'),
            "!=" => $this->language->text('Not equal (is not in list)')
        );
    }

    /**
     * Returns an array of condition handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &Cache::memory(__METHOD__);

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = array();

        $handlers['url_route'] = array(
            'title' => $this->language->text('System URL route (global)'),
            'description' => $this->language->text('Parameters: <a href="@url">system route pattern</a>. Only = and != operators allowed', array('@url' => $this->url->get('admin/report/route'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Url', 'route'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Url', 'route'),
            ),
        );

        $handlers['url_path'] = array(
            'title' => $this->language->text('URL path (global)'),
            'description' => $this->language->text('Parameters: path with regexp pattern. Only = and != operators allowed. Do not use trailing slashes. Example: account/(\d+)'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Url', 'path'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Url', 'path'),
            ),
        );

        $handlers['user_id'] = array(
            'title' => $this->language->text('User ID (global)'),
            'description' => $this->language->text('Parameters: <a href="@url">list of numeric IDs</a>, separated by comma', array('@url' => $this->url->get('admin/user/list'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\User', 'id'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\User', 'id'),
            ),
        );

        $handlers['user_role_id'] = array(
            'title' => $this->language->text('User role ID (global)'),
            'description' => $this->language->text('Parameters: <a href="@url">list of numeric IDs</a>, separated by comma', array('@url' => $this->url->get('admin/user/role'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\User', 'roleId'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\User', 'roleId'),
            ),
        );

        $handlers['date'] = array(
            'title' => $this->language->text('Current date (global)'),
            'description' => $this->language->text('Parameters: One value in <a target="_blank" href="http://php.net/manual/en/datetime.formats.php">time format</a>'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Date', 'date'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Date', 'date'),
            ),
        );

        $handlers['pricerule_used'] = array(
            'title' => $this->language->text('Number of times a price rule code (coupon) has been used (checkout)'),
            'description' => $this->language->text('Parameters: One numeric value'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\PriceRule', 'used'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\PriceRule', 'used'),
            ),
        );

        $handlers['order_shipping_method'] = array(
            'title' => $this->language->text('Shipping method (checkout)'),
            'description' => $this->language->text('Parameters: <a href="@url">list of IDs</a>, separated by comma', array('@url' => $this->url->get('admin/report/shipping'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Order', 'shippingMethod'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Order', 'shippingMethod'),
            ),
        );

        $handlers['order_payment_method'] = array(
            'title' => $this->language->text('Payment method (checkout)'),
            'description' => $this->language->text('Parameters: <a href="@url">list of IDs</a>, separated by comma', array('@url' => $this->url->get('admin/report/payment'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Order', 'paymentMethod'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Order', 'paymentMethod'),
            ),
        );

        $handlers['shipping_country_code'] = array(
            'title' => $this->language->text('Shipping country code (checkout)'),
            'description' => $this->language->text('Parameters: <a href="@url">list of codes</a>, separated by comma', array('@url' => $this->url->get('admin/settings/country'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Shipping', 'countryCode'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Shipping', 'countryCode'),
            ),
        );

        $handlers['shipping_state_id'] = array(
            'title' => $this->language->text('Shipping state ID (checkout)'),
            'description' => $this->language->text('Parameters: list of numeric IDs, separated by comma'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Shipping', 'stateId'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Shipping', 'stateId'),
            ),
        );

        $handlers['shipping_zone_id'] = array(
            'title' => $this->language->text('Shipping address zone ID (checkout)'),
            'description' => $this->language->text('Parameters: list of numeric IDs, separated by comma'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Shipping', 'zoneId'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Shipping', 'zoneId'),
            ),
        );

        $handlers['payment_country_code'] = array(
            'title' => $this->language->text('Payment country code (checkout)'),
            'description' => $this->language->text('Parameters: <a href="@url">list of codes</a>, separated by comma', array('@url' => $this->url->get('admin/settings/country'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Payment', 'countryCode'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Payment', 'countryCode'),
            ),
        );

        $handlers['payment_state_id'] = array(
            'title' => $this->language->text('Payment state ID (checkout)'),
            'description' => $this->language->text('Parameters: list of numeric IDs, separated by comma'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Payment', 'stateId'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Payment', 'stateId'),
            ),
        );

        $handlers['payment_zone_id'] = array(
            'title' => $this->language->text('Payment address zone ID (checkout)'),
            'description' => $this->language->text('Parameters: list of numeric IDs, separated by comma'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Payment', 'zoneId'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Payment', 'zoneId'),
            ),
        );

        $handlers['cart_total'] = array(
            'title' => $this->language->text('Cart total (checkout)'),
            'description' => $this->language->text('Parameters: one value in format "price|currency". If only price specified, default currency will be used'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Cart', 'total'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Price', 'price'),
            ),
        );

        $handlers['cart_product_id'] = array(
            'title' => $this->language->text('Cart contains product ID (checkout)'),
            'description' => $this->language->text('Parameters: list of numeric IDs, separated by comma'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Cart', 'productId'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'id'),
            ),
        );

        $handlers['cart_sku'] = array(
            'title' => $this->language->text('Cart contains SKU (checkout)'),
            'description' => $this->language->text('Parameters: list of SKU, separated by comma'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Cart', 'sku'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'sku'),
            ),
        );

        $handlers['product_id'] = array(
            'title' => $this->language->text('Product ID'),
            'description' => $this->language->text('Parameters: <a href="@url">list of numeric IDs</a>, separated by comma', array('@url' => $this->url->get('admin/content/product'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Product', 'id'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'id'),
            ),
        );

        $handlers['product_category_id'] = array(
            'title' => $this->language->text('Product category ID of "@type" category group', array('@type' => 'catalog')),
            'description' => $this->language->text('Parameters: <a href="@url">list of numeric IDs</a>, separated by comma', array('@url' => $this->url->get('admin/content/category-group'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Product', 'categoryId'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'categoryId'),
            ),
        );

        $handlers['product_brand_category_id'] = array(
            'title' => $this->language->text('Product category ID of "@type" category group', array('@type' => 'brand')),
            'description' => $this->language->text('Parameters: <a href="@url">list of numeric IDs</a>, separated by comma', array('@url' => $this->url->get('admin/content/category-group'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\condition\\Product', 'brandCategoryId'),
                'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'categoryId'),
            ),
        );

        $this->hook->fire('condition.handlers', $handlers);
        return $handlers;
    }

}
