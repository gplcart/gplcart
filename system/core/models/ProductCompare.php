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
use gplcart\core\models\Translation as TranslationModel;

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
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param Translation $translation
     * @param RequestHelper $request
     */
    public function __construct(Hook $hook, Config $config, TranslationModel $translation,
            RequestHelper $request)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->request = $request;
        $this->translation = $translation;
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
     * Adds a product to comparison and returns an array of results
     * @param array $product
     * @param array $data
     * @return array
     */
    public function addProduct(array $product, array $data)
    {
        $result = array();
        $this->hook->attach('product.compare.add.product.before', $product, $data, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->translation->text('Unable to add product')
        );

        if ($this->add($product['product_id'])) {

            $quantity = count($this->getList());

            if ($quantity < $this->getLimit()) {
                $quantity ++;
            }

            $href = $this->request->base() . 'compare';

            $result = array(
                'redirect' => '',
                'severity' => 'success',
                'quantity' => $quantity,
                'message' => $this->translation->text('Product has been added to <a href="@url">comparison</a>', array('@url' => $href))
            );
        }

        $this->hook->attach('product.compare.add.product.after', $product, $data, $result, $this);
        return (array) $result;
    }

    /**
     * Removes a product from comparison and returns an array of result data
     * @param integer $product_id
     * @return array
     */
    public function deleteProduct($product_id)
    {
        $result = null;
        $this->hook->attach('product.compare.delete.product.before', $product_id, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $result = array(
            'redirect' => '',
            'severity' => '',
            'message' => ''
        );

        if ($this->delete($product_id)) {
            $existing = count($this->getList());
            $result = array(
                'redirect' => '',
                'severity' => 'success',
                'quantity' => $existing,
                'message' => $this->translation->text('Product has been removed from comparison')
            );
        }

        $this->hook->attach('product.compare.delete.product.after', $product_id, $result, $this);
        return (array) $result;
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
     * Returns an array of product ID to be compared
     * @return array
     */
    public function getList()
    {
        $items = &gplcart_static('product.compare.list');

        if (isset($items)) {
            return (array) $items;
        }

        $items = array();
        $cookie = $this->request->cookie('product_compare', '', 'string');

        if (empty($cookie)) {
            return $items;
        }

        $array = explode('|', urldecode($cookie));
        $items = array_filter(array_map('trim', $array), 'ctype_digit');

        $this->hook->attach('product.compare.list', $items, $this);
        return $items;
    }

    /**
     * Whether a product is added to comparison
     * @param integer $product_id
     * @return boolean
     */
    public function exists($product_id)
    {
        $compared = $this->getList();
        return in_array($product_id, $compared);
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

}
