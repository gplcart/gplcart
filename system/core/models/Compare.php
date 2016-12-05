<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\helpers\Cache;
use core\helpers\Request;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to product comparison
 */
class Compare extends Model
{

    /**
     * Request class instance
     * @var \core\helpers\Request $request
     */
    protected $request;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param Request $request
     */
    public function __construct(ModelsLanguage $language, Request $request)
    {
        parent::__construct();

        $this->request = $request;
        $this->language = $language;
    }

    /**
     * Adds a product to comparison
     * @param integer $product_id
     * @return boolean
     */
    public function add($product_id)
    {
        $this->hook->fire('add.compare.before', $product_id);

        if (empty($product_id)) {
            return false;
        }

        $product_ids = $this->get();

        if (in_array($product_id, $product_ids)) {
            return false;
        }

        array_unshift($product_ids, $product_id);

        $this->controlLimit($product_ids);

        $result = $this->set($product_ids);

        $this->hook->fire('add.compare.after', $product_id, $result);
        return $result;
    }

    /**
     * Adds a product to comparison and returns
     * an array of results
     * @param array $product
     * @param array $data
     * @return array
     */
    public function addProduct(array $product, array $data)
    {
        $this->hook->fire('add.product.compare.before', $product, $data);

        if (empty($product)) {
            return array();
        }

        $result = array(
            'severity' => 'warning',
            'message' => $this->language->text('Product has not been added to comparison')
        );

        $added = $this->add($product['product_id']);

        if (!empty($added)) {

            // Since the cookie isn't available until the next request
            // we add one more here
            $existing = count($this->get());
            $existing ++;
            
            $href = $this->request->base() . 'compare';

            $result = array(
                'severity' => 'success',
                'quantity' => $existing,
                'message' => $this->language->text('Product has been added to <a href="!href">comparison</a>', array('!href' => $href))
            );
        }

        $this->hook->fire('add.product.compare.after', $product, $data, $result);
        return $result;
    }
    
    /**
     * Removes a product from comparison and returns
     * an array of result data
     * @param integer $product_id
     * @return array
     */
    public function deleteProduct($product_id)
    {
        $this->hook->fire('delete.product.compare.before', $product_id);

        if (empty($product_id)) {
            return array();
        }

        $result = array(
            'severity' => 'warning',
            'message' => $this->language->text('Product has not been removed from comparison')
        );

        $deleted = (bool) $this->delete($product_id);

        if ($deleted) {

            $existing = count($this->get());

            $result = array(
                'severity' => 'success',
                'quantity' => $existing,
                'message' => $this->language->text('Product has been deleted from comparison')
            );
        }

        $this->hook->fire('delete.product.compare.after', $product_id, $result);
        return $result;
    }

    /**
     * Reduces a number of items to save
     * @param array $product_ids
     */
    protected function controlLimit(array &$product_ids)
    {
        $limit = (int) $this->config->get('product_comparison_limit', 10);

        if (!empty($limit)) {
            $product_ids = array_slice($product_ids, 0, $limit);
        }
    }

    /**
     * Returns an array of products to be compared
     * @return array
     */
    public function get()
    {
        $items = &Cache::memory('comparison');

        if (isset($items)) {
            return (array) $items;
        }

        $items = array();
        $cookie = (string) $this->request->cookie('comparison');

        if (empty($cookie)) {
            return $items;
        }

        $array = explode('|', urldecode($cookie));
        $items = array_filter(array_map('trim', $array), 'is_numeric');

        $this->hook->fire('get.compare', $items);
        return $items;
    }

    /**
     * Whether a product is added to comparison
     * @param integer $product_id
     * @return boolean
     */
    public function exists($product_id)
    {
        $compared = $this->get();
        return in_array($product_id, $compared);
    }

    /**
     * Sets compared products to the cookie
     * @param array $product_ids
     * @return boolean
     */
    public function set(array $product_ids)
    {
        $lifespan = $this->config->get('product_comparison_cookie_lifespan', 604800);
        $result = $this->request->setCookie('comparison', implode('|', (array) $product_ids), $lifespan);

        Cache::clearMemory('comparison');
        return $result;
    }

    /**
     * Removes a products from comparison
     * @param integer $product_id
     * @return array
     */
    public function delete($product_id)
    {
        $this->hook->fire('delete.compare.before', $product_id);

        if (empty($product_id)) {
            return false;
        }

        $compared = $this->get();

        if (empty($compared)) {
            return false;
        }

        $product_ids = array_flip($compared);

        unset($product_ids[$product_id]);

        $rest = array_keys($product_ids);
        $this->set($rest);

        $this->hook->fire('delete.compare.after', $product_id, $rest);
        return $rest;
    }

}
