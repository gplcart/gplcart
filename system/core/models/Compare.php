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
use gplcart\core\helpers\Request as RequestHelper;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to product comparison
 */
class Compare extends Model
{

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param LanguageModel $language
     * @param RequestHelper $request
     */
    public function __construct(LanguageModel $language, RequestHelper $request)
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
        $this->hook->fire('compare.add.before', $product_id);

        if (empty($product_id)) {
            return false;
        }

        $product_ids = $this->getList();

        if (in_array($product_id, $product_ids)) {
            return false;
        }

        array_unshift($product_ids, $product_id);

        $this->controlLimit($product_ids);

        $result = $this->set($product_ids);

        $this->hook->fire('compare.add.after', $product_id, $result);
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
        $this->hook->fire('compare.add.product.before', $product, $data);

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->language->text('Unable to add this product')
        );

        if (empty($product)) {
            return $result;
        }

        $added = $this->add($product['product_id']);

        if (!empty($added)) {

            $existing = count($this->getList());
            $existing ++;

            $href = $this->request->base() . 'compare';

            $result = array(
                'redirect' => '',
                'severity' => 'success',
                'quantity' => $existing,
                'message' => $this->language->text('Product has been added to <a href="!href">comparison</a>', array('!href' => $href))
            );
        }

        $this->hook->fire('compare.add.product.after', $product, $data, $result);
        return $result;
    }

    /**
     * Removes a product from comparison and returns an array of result data
     * @param integer $product_id
     * @return array
     */
    public function deleteProduct($product_id)
    {
        $this->hook->fire('compare.delete.product.before', $product_id);

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->language->text('Unable to remove this product from comparison')
        );

        if (empty($product_id)) {
            return $result;
        }

        if ($this->delete($product_id)) {

            $existing = count($this->getList());

            $result = array(
                'redirect' => '',
                'severity' => 'success',
                'quantity' => $existing,
                'message' => $this->language->text('Product has been removed from comparison')
            );
        }

        $this->hook->fire('compare.delete.product.after', $product_id, $result);
        return $result;
    }

    /**
     * Reduces a number of items to save
     * @param array $product_ids
     */
    protected function controlLimit(array &$product_ids)
    {
        $limit = (int) $this->config->get('comparison_limit', 10);

        if (!empty($limit)) {
            $product_ids = array_slice($product_ids, 0, $limit);
        }
    }

    /**
     * Returns an array of products to be compared
     * @return array
     */
    public function getList()
    {
        $items = &Cache::memory(__METHOD__);

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

        $this->hook->fire('compare.list', $items);
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
     * Sets compared products to the cookie
     * @param array $product_ids
     * @return boolean
     */
    public function set(array $product_ids)
    {
        $lifespan = $this->config->get('comparison_cookie_lifespan', 604800);
        $result = $this->request->setCookie('comparison', implode('|', (array) $product_ids), $lifespan);

        Cache::clearMemory('comparison');
        return $result;
    }

    /**
     * Removes a products from comparison
     * @param integer $product_id
     * @return array|boolean
     */
    public function delete($product_id)
    {
        $this->hook->fire('compare.delete.before', $product_id);

        if (empty($product_id)) {
            return false;
        }

        $compared = $this->getList();

        if (empty($compared)) {
            return false;
        }

        $product_ids = array_flip($compared);

        unset($product_ids[$product_id]);

        $result = $this->set(array_keys($product_ids));
        $this->hook->fire('compare.delete.after', $product_id, $result);

        return $result;
    }

}
