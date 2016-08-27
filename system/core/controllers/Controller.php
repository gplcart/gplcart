<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\Container;
use core\Controller as BaseController;

/**
 * Contents specific to the frontend methods
 */
class Controller extends BaseController
{
    
    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;
    
    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;
    
    /**
     * Cart model instance
     * @var \core\models\Cart $cart
     */
    protected $cart;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Wishlist model instance
     * @var \core\models\Wishlist $wishlist
     */
    protected $wishlist;

    /**
     * Category model instance
     * @var \core\models\Category $category
     */
    protected $category;

    /**
     * Alias model instance instance
     * @var \core\models\Alias $alias
     */
    protected $alias;
    
    protected $cart_user_id;
    
    protected $cart_content = array();
    
    protected $wishlist_content = array();
    
    protected $compare_content = array();
    
    protected $category_tree = array();
    

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        /* @var $price \core\models\Price */
        $this->price = Container::instance('core\\models\\Price');
        
        /* @var $image \core\models\Image */
        $this->image = Container::instance('core\\models\\Image');
        
        /* @var $cart \core\models\Cart */
        $this->cart = Container::instance('core\\models\\Cart');
        
        /* @var $alias \core\models\Alias */
        $this->alias = Container::instance('core\\models\\Alias');
        
        /* @var $product \core\models\Product */
        $this->product = Container::instance('core\\models\\Product');
        
        /* @var $wishlist \core\models\Wishlist */
        $this->wishlist = Container::instance('core\\models\\Wishlist');
        
        /* @var $category \core\models\Category */
        $this->category = Container::instance('core\\models\\Category');
        
        if (!$this->url->isInstall()) {
            $this->cart_user_id = $this->cart->uid();
            $this->cart_content = $this->cart->getByUser($this->cart_user_id, false);
            $this->wishlist_content = $this->wishlist->getList(array('user_id' => $this->cart_user_id));
            $this->compare_content = $this->product->getCompared();
            $this->category_tree = $this->getCategoryTree($this->current_store);
        }
        
        
        $this->hook->fire('init.frontend', $this);
        
    }
    
    
    
    protected function getCategoryTree($store)
    {
        $tree = $this->category->getTree(array('store_id' => $store['store_id'], 'type' => 'catalog', 'status' => 1));

        $category_aliases = $this->alias->getMultiple('category_id', array_keys($tree));

        foreach ($tree as &$item) {
            $path = "category/{$item['category_id']}";

            if (!empty($category_aliases[$item['category_id']])) {
                $path = $category_aliases[$item['category_id']];
            }

            $item['url'] = $this->url->get($path);

            if ($this->url->path() === $path) {
                $item['active'] = true;
            }
        }

        return $tree;
    }
    
    public function getHoneypot(){
        return $this->render('common/honeypot');
    }

}
