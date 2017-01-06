<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\Cache;
use gplcart\core\Handler;
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
     * @param integer $value1
     * @param mixed $value2
     * @param string $operator
     * @return boolean
     */
    public function compareNumeric($value1, $value2, $operator)
    {
        if (!is_numeric($value1)) {
            return false;
        }

        if (!is_numeric($value2) && !is_array($value2)) {
            return false;
        }

        switch ($operator) {
            case '>=':
                return ($value1 >= $value2);
            case '<=':
                return ($value1 <= $value2);
            case '>':
                return ($value1 > $value2);
            case '<':
                return ($value1 < $value2);
            case '=':

                if (is_array($value2)) {
                    return in_array($value1, $value2);
                }

                return ($value1 == $value2);

            case '!=':
                if (is_array($value2)) {
                    return !in_array($value1, $value2);
                }

                return ($value1 != $value2);
        }

        return false;
    }

    /**
     * Compares string values
     * @param integer $value1
     * @param mixed $value2
     * @param string $operator
     * @return boolean
     */
    public function compareString($value1, $value2, $operator)
    {
        if (!is_string($value1)) {
            return false;
        }

        if (!is_string($value2) && !is_array($value2)) {
            return false;
        }

        switch ($operator) {
            case '=':

                if (is_array($value2)) {
                    return in_array($value1, $value2, true);
                }

                return (strcmp($value1, $value2) === 0);

            case '!=':
                if (is_array($value2)) {
                    return !in_array($value1, $value2, true);
                }

                return (strcmp($value1, $value2) !== 0);
        }

        return false;
    }

    /**
     * Returns true if all conditions are met
     * @param array $conditions
     * @param array $data
     * @return boolean
     */
    public function isMet(array $conditions, array $data)
    {
        $this->hook->fire('condition.met.before', $conditions, $data);

        if (empty($conditions)) {
            return true;
        }

        $handlers = $this->getHandlers();

        $met = 0;
        foreach ($conditions as $condition) {
            $result = Handler::call($handlers, $condition['id'], 'process', array($condition, $data));

            if ($result === true) {
                $met++;
            }
        }

        $result = (count($conditions) == $met);
        $this->hook->fire('condition.met.after', $conditions, $data, $result);
        return $result;
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
     * Returns a condition handler
     * @param string $condition_id
     * @param string $method
     * @return mixed
     */
    public function getHandler($condition_id, $method)
    {
        $handlers = $this->getHandlers();
        return Handler::get($handlers, $condition_id, $method);
    }

    /**
     * Returns an array of condition handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &Cache::memory('condition.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = array();

        $handlers['route'] = array(
            'title' => $this->language->text('Route (global)'),
            'description' => $this->language->text('Parameters: <a href="@url">system route pattern</a>. Only = and != operators allowed', array('@url' => $this->url->get('admin/report/route'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'route'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'route'),
            ),
        );

        $handlers['path'] = array(
            'title' => $this->language->text('Path (global)'),
            'description' => $this->language->text('Parameters: path with regexp pattern. Only = and != operators allowed'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'path'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'path'),
            ),
        );

        $handlers['user_id'] = array(
            'title' => $this->language->text('User ID (global)'),
            'description' => $this->language->text('Parameters: <a href="@url">list of numeric IDs</a>, separated by comma', array('@url' => $this->url->get('admin/user/list'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'userId'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'userId'),
            ),
        );

        $handlers['user_role_id'] = array(
            'title' => $this->language->text('User role ID (global)'),
            'description' => $this->language->text('Parameters: <a href="@url">list of numeric IDs</a>, separated by comma', array('@url' => $this->url->get('admin/user/role'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'userRole'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'userRole'),
            ),
        );

        $handlers['date'] = array(
            'title' => $this->language->text('Current date (global)'),
            'description' => $this->language->text('Parameters: One value in time format'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'date'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'date'),
            ),
        );

        $handlers['product_id'] = array(
            'title' => $this->language->text('Product ID (checkout)'),
            'description' => $this->language->text('Parameters: <a href="@url">list of numeric IDs</a>, separated by comma', array('@url' => $this->url->get('admin/content/product'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'productId'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'productId'),
            ),
        );

        $handlers['product_category_id'] = array(
            'title' => $this->language->text('Product category ID of "@type" category group (checkout)', array('@type' => 'catalog')),
            'description' => $this->language->text('Parameters: <a href="@url">list of numeric IDs</a>, separated by comma', array('@url' => $this->url->get('admin/content/category-group'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'categoryId'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'categoryId'),
            ),
        );

        $handlers['product_brand_category_id'] = array(
            'title' => $this->language->text('Product category ID of "@type" category group (checkout)', array('@type' => 'brand')),
            'description' => $this->language->text('Parameters: <a href="@url">list of numeric IDs</a>, separated by comma', array('@url' => $this->url->get('admin/content/category-group'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'brandCategoryId'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'categoryId'),
            ),
        );

        $handlers['used'] = array(
            'title' => $this->language->text('Number of times the coupon was used (checkout)'),
            'description' => $this->language->text('Parameters: One numeric value'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'used'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'used'),
            ),
        );

        $handlers['shipping'] = array(
            'title' => $this->language->text('Shipping method (checkout)'),
            'description' => $this->language->text('Parameters: Shipping service ID'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'shipping'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'shipping'),
            ),
        );

        $handlers['payment'] = array(
            'title' => $this->language->text('Payment method (checkout)'),
            'description' => $this->language->text('Parameters: Payment service ID'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'payment'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'payment'),
            ),
        );

        $handlers['shipping_address_id'] = array(
            'title' => $this->language->text('Shipping address ID (checkout)'),
            'description' => $this->language->text('Parameters: list of numeric IDs, separated by comma'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'shippingAddressId'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'shippingAddressId'),
            ),
        );

        $handlers['country'] = array(
            'title' => $this->language->text('Country code (checkout)'),
            'description' => $this->language->text('Parameters: <a href="@url">list of codes</a>, separated by comma', array('@url' => $this->url->get('admin/settings/country'))),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'country'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'country'),
            ),
        );

        $handlers['state'] = array(
            'title' => $this->language->text('Country state code (checkout)'),
            'description' => $this->language->text('Parameters: list of codes, separated by comma'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'state'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'state'),
            ),
        );
        
        $handlers['shipping_zone_id'] = array(
            'title' => $this->language->text('Shipping address zone ID (checkout)'),
            'description' => $this->language->text('Parameters: list of numeric IDs, separated by comma'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'shippingZone'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'shippingZone'),
            ),
        );

        $handlers['cart_total'] = array(
            'title' => $this->language->text('Cart total (checkout)'),
            'description' => $this->language->text('Parameters: numeric value'),
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\trigger\\Condition', 'cartTotal'),
                'validate' => array('gplcart\\core\\handlers\\validator\\Condition', 'price'),
            ),
        );

        $this->hook->fire('condition.handlers', $handlers);
        return $handlers;
    }

}
