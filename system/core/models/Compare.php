<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\classes\Tool;
use core\classes\Cache;
use core\classes\Request;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to product comparison
 */
class Compare extends Model
{

    /**
     * Request class instance
     * @var \core\classes\Request $request
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
        $items = &Cache::memory('get.compared');

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
        return Tool::setCookie('comparison', implode('|', (array) $product_ids), $lifespan);
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
