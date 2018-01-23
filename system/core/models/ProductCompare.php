<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config;
use gplcart\core\helpers\Request as RequestHelper;
use gplcart\core\Hook;

/**
 * Manages basic behaviors and data related to product comparison
 */
class ProductCompare
{

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
    }

    /**
     * Returns an array of product ID to be compared
     * @return array
     */
    public function getList()
    {
        $result = &gplcart_static('product.compare.list');

        if (isset($result)) {
            return (array) $result;
        }

        $this->hook->attach('product.compare.list.before', $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        $cookie = $this->request->cookie('product_compare', '', 'string');
        $result = array_filter(array_map('trim', explode('|', urldecode($cookie))), 'ctype_digit');
        $this->hook->attach('product.compare.list.after', $result, $this);
        return $result;
    }

    /**
     * Adds a product to comparison
     * @param integer $product_id
     * @return boolean
     */
    public function add($product_id)
    {
        $result = null;
        $this->hook->attach('product.compare.add.before', $product_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $product_ids = $this->getList();

        if (in_array($product_id, $product_ids)) {
            return false;
        }

        array_unshift($product_ids, $product_id);
        $this->controlLimit($product_ids);

        $result = $this->set($product_ids);
        $this->hook->attach('product.compare.add.after', $product_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Removes a products from comparison
     * @param integer $product_id
     * @return array|boolean
     */
    public function delete($product_id)
    {
        $result = null;
        $this->hook->attach('product.compare.delete.before', $product_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $compared = $this->getList();

        if (empty($compared)) {
            return false;
        }

        $product_ids = array_flip($compared);
        unset($product_ids[$product_id]);

        $result = $this->set(array_keys($product_ids));
        $this->hook->attach('product.compare.delete.after', $product_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Saves an array of product ID in cookie
     * @param array $product_ids
     * @return boolean
     */
    public function set(array $product_ids)
    {
        $lifespan = $this->getCookieLifespan();
        $result = $this->request->setCookie('product_compare', implode('|', (array) $product_ids), $lifespan);

        gplcart_static_clear();
        return $result;
    }

    /**
     * Returns a max number of items to compare
     * @return integer
     */
    public function getLimit()
    {
        return (int) $this->config->get('product_compare_limit', 10);
    }

    /**
     * Returns cookie lifespan (in seconds)
     * @return int
     */
    public function getCookieLifespan()
    {
        return (int) $this->config->get('product_compare_cookie_lifespan', 30 * 24 * 60 * 60);
    }

    /**
     * Whether a product is added to comparison
     * @param integer $product_id
     * @return boolean
     */
    public function exists($product_id)
    {
        return in_array($product_id, $this->getList());
    }

    /**
     * Reduces a number of items to save
     * @param array $product_ids
     */
    protected function controlLimit(array &$product_ids)
    {
        $limit = $this->getLimit();

        if (!empty($limit)) {
            $product_ids = array_slice($product_ids, 0, $limit);
        }
    }

}
