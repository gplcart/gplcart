<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\Controller as BaseController;
use gplcart\core\traits\Item as ItemTrait,
    gplcart\core\traits\Cart as CartTrait,
    gplcart\core\traits\Widget as WidgetTrait,
    gplcart\core\traits\Wishlist as WishlistTrait,
    gplcart\core\traits\ItemPrice as ItemPriceTrait,
    gplcart\core\traits\ProductCompare as ProductCompareTrait;

/**
 * Parent controller that contents front-end specific methods
 */
class Controller extends BaseController
{

    use ItemTrait,
        CartTrait,
        WidgetTrait,
        WishlistTrait,
        ItemPriceTrait,
        ProductCompareTrait;

    /**
     * Trigger model instance
     * @var \gplcart\core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Compare model instance
     * @var \gplcart\core\models\ProductCompare $product_compare
     */
    protected $product_compare;

    /**
     * Compare model instance
     * @var \gplcart\core\models\ProductCompareAction $product_compare_action
     */
    protected $product_compare_action;

    /**
     * Wishlist model instance
     * @var \gplcart\core\models\Wishlist $wishlist
     */
    protected $wishlist;

    /**
     * Wishlist action model instance
     * @var \gplcart\core\models\WishlistAction $wishlist_action
     */
    protected $wishlist_action;

    /**
     * Category model instance
     * @var \gplcart\core\models\Category $category
     */
    protected $category;

    /**
     * Cart action model instance
     * @var \gplcart\core\models\CartAction $cart_action
     */
    protected $cart_action;

    /**
     * The current currency code
     * @var string
     */
    protected $current_currency;

    /**
     * An array of fired triggers for the current context
     * @var array
     */
    protected $triggered = array();

    /**
     * Catalog category tree for the current store
     * @var array
     */
    protected $data_categories = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setFrontendInstancies();
        $this->setFrontendProperties();

        if (!$this->isInternalRoute()) {

            $this->setDefaultDataFrontend();
            $this->setDefaultJsStoreFrontend();

            $this->submitCart($this->cart_action);
            $this->submitWishlist($this->wishlist_action);
            $this->submitProductCompare($this->product_compare_action);
        }

        $this->hook->attach('construct.controller.frontend', $this);
        $this->controlHttpStatus();
    }

    /**
     * Returns a number of cart items for the current user
     */
    public function getCartQuantity(array $options = array())
    {
        $options += array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );

        return $this->cart->getQuantity($options);
    }

    /**
     * Sets default data for templates
     */
    protected function setDefaultDataFrontend()
    {
        $currencies = $this->currency->getList(true);

        $this->data['_currencies'] = $currencies;
        $this->data['_cart'] = $this->getCartQuantity();
        $this->data['_wishlist'] = $this->getWishlist();
        $this->data['_captcha'] = $this->getWidgetCaptcha();
        $this->data['_comparison'] = $this->getProductComparison();
        $this->data['_currency'] = $currencies[$this->current_currency];
        $this->data['_menu'] = $this->getWidgetMenu(array('items' => $this->data_categories));
    }

    /**
     * Set per-store JS (Google Analytics etc)
     */
    protected function setDefaultJsStoreFrontend()
    {
        if (!empty($this->current_store['data']['js'])) {
            $this->setJs($this->current_store['data']['js'], array('position' => 'bottom', 'aggregate' => false));
        }
    }

    /**
     * Sets model instances
     */
    protected function setFrontendInstancies()
    {
        $this->price = $this->getInstance('gplcart\\core\\models\\Price');
        $this->trigger = $this->getInstance('gplcart\\core\\models\\Trigger');
        $this->product = $this->getInstance('gplcart\\core\\models\\Product');
        $this->wishlist = $this->getInstance('gplcart\\core\\models\\Wishlist');
        $this->category = $this->getInstance('gplcart\\core\\models\\Category');
        $this->currency = $this->getInstance('gplcart\\core\\models\\Currency');
        $this->cart_action = $this->getInstance('gplcart\\core\\models\\CartAction');
        $this->product_compare = $this->getInstance('gplcart\\core\\models\\ProductCompare');
        $this->wishlist_action = $this->getInstance('gplcart\\core\\models\\WishlistAction');
        $this->product_compare_action = $this->getInstance('gplcart\\core\\models\\ProductCompareAction');
    }

    /**
     * Sets controller's properties
     */
    protected function setFrontendProperties()
    {
        if (!$this->isInstall()) {
            if (!$this->isInternalRoute()) {
                $this->triggered = $this->getTriggered();
                $this->data_categories = $this->getCategories();
            }
            $this->current_currency = $this->currency->getCode();
        }
    }

    /**
     * Returns an array of fired triggers
     * @param array $conditions
     * @return array
     */
    public function getTriggered(array $conditions = array())
    {
        $conditions += array(
            'status' => 1,
            'store_id' => $this->store_id
        );

        return $this->trigger->getTriggered(array(), $conditions);
    }

    /**
     * Whether a given trigger ID has been triggered
     * @param integer $trigger_id
     * @return bool
     */
    public function isTriggered($trigger_id)
    {
        return in_array($trigger_id, $this->triggered);
    }

    /**
     * Returns the current cart data
     * @param array $conditions
     * @return array
     */
    public function getCart(array $conditions = array())
    {
        $conditions += array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );

        return $this->prepareCart($this->cart->getContent($conditions));
    }

    /**
     * Prepares an array of cart items
     * @param array $cart
     * @return array
     */
    protected function prepareCart(array $cart)
    {
        if (empty($cart['items'])) {
            return array();
        }

        foreach ($cart['items'] as &$item) {
            $item['currency'] = $cart['currency'];
            $this->prepareCartItem($item);
        }

        $this->setItemTotalFormatted($cart, $this->price);
        return $cart;
    }

    /**
     * Returns an array of product IDs to compare
     * @return array
     */
    public function getProductComparison()
    {
        return $this->product_compare->getList();
    }

    /**
     * Returns an array of wishlist items for the current user
     * @return array
     */
    public function getWishlist()
    {
        $conditions = array(
            'product_status' => 1,
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );

        return (array) $this->wishlist->getList($conditions);
    }

    /**
     * Returns rendered cart preview
     * @return string
     */
    public function getCartPreview()
    {
        return $this->getWidgetCartPreview($this->getCart());
    }

    /**
     * Returns an array of prepared categories
     * @param array $conditions
     * @param array $options
     * @return array
     */
    public function getCategories($conditions = array(), $options = array())
    {
        $conditions += array(
            'status' => 1,
            'type' => 'catalog',
            'store_id' => $this->store_id
        );

        $options += array(
            'entity' => 'category',
            'imagestyle' => $this->configTheme('image_style_category_child', 3));

        return $this->prepareEntityItems($this->category->getTree($conditions), $options);
    }

    /**
     * Loads products from an array of product IDs
     * @param array $conditions
     * @param array $options
     * @return array
     */
    public function getProducts($conditions = array(), $options = array())
    {
        $conditions += array(
            'status' => 1,
            'store_id' => $this->store_id
        );

        if (isset($conditions['product_id']) && empty($conditions['product_id'])) {
            return array();
        }

        $options += array('entity' => 'product');
        return $this->prepareEntityItems((array) $this->product->getList($conditions), $options);
    }

    /**
     * Prepare an array of entity items like pages, products etc
     * @param array $items
     * @param array $options
     * @return array
     */
    protected function prepareEntityItems($items, $options = array())
    {
        if (empty($items) || empty($options['entity'])) {
            return array();
        }

        if (!isset($options['view']) || !in_array($options['view'], array('list', 'grid'))) {
            $options['view'] = 'grid';
        }

        $options += array(
            'entity' => $options['entity'],
            'entity_id' => array_keys($items),
            'template_item' => "{$options['entity']}/item/{$options['view']}",
            'imagestyle' => $this->configTheme("image_style_{$options['entity']}_{$options['view']}", 3)
        );

        foreach ($items as &$item) {
            $this->prepareEntityItem($item, $options);
        }

        return $items;
    }

    /**
     * Prepare an entity item
     * @param array $item
     * @param array $options
     */
    protected function prepareEntityItem(array &$item, array $options)
    {
        $this->setItemUrl($item, $options);
        $this->setItemThumb($item, $this->image, $options);

        if ($options['entity'] === 'product') {
            $this->prepareProductItem($item, $options);
        } else {
            $this->setItemIndentation($item);
            $this->setItemUrlActive($item);
            $this->setItemRendered($item, array('item' => $item), $options);
        }
    }

    /**
     * Prepare a product item
     * @param array $product
     * @param array $options
     */
    protected function prepareProductItem(array &$product, array $options)
    {
        $this->setItemProductInComparison($product, $this->product_compare);
        $this->setItemPriceCalculated($product, $this->product);
        $this->setItemProductInWishlist($product, $this->wishlist);
        $this->setItemPriceFormatted($product, $this->price, $this->current_currency);
        $this->setItemProductBundle($product, $this->product, $this->image);
        $this->setItemRenderedProduct($product, $options);
    }

    /**
     * Prepare a cart item
     * Prepare cart item
     * @param array $item
     */
    protected function prepareCartItem(array &$item)
    {
        $this->setItemImages($item['product'], 'product', $this->image);
        $this->setItemThumbCart($item, $this->image);
        $this->setItemPriceFormatted($item, $this->price, $this->current_currency);
        $this->setItemTotalFormatted($item, $this->price);
        $this->setItemProductBundle($item['product'], $this->product, $this->image);
    }

}
