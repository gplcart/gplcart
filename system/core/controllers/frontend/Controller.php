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
    gplcart\core\traits\Widget as WidgetTrait;

/**
 * Contents specific to the front-end methods
 */
class Controller extends BaseController
{

    use WidgetTrait,
        ItemTrait;

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
     * @var \gplcart\core\models\ProductCompare $compare
     */
    protected $productcompare;

    /**
     * Wishlist model instance
     * @var \gplcart\core\models\Wishlist $wishlist
     */
    protected $wishlist;

    /**
     * Category model instance
     * @var \gplcart\core\models\Category $category
     */
    protected $category;

    /**
     * Collection item model instance
     * @var \gplcart\core\models\CollectionItem $collection_item
     */
    protected $collectionitem;

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

            $this->submitCart();
            $this->submitCompare();
            $this->submitWishlist();
        }

        $this->hook->attach('construct.controller.frontend', $this);
        $this->controlHttpStatus();
    }

    /**
     * Sets default data for front-end templates
     */
    protected function setDefaultDataFrontend()
    {
        $currencies = $this->currency->getList(true);

        $this->data['_cart'] = $this->getCart();
        $this->data['_currencies'] = $currencies;
        $this->data['_wishlist'] = $this->getWishlist();
        $this->data['_comparison'] = $this->getComparison();
        $this->data['_captcha'] = $this->getWidgetCaptcha($this);
        $this->data['_currency'] = $currencies[$this->current_currency];
        $this->data['_menu'] = $this->getWidgetCategoryMenu($this, $this->data_categories);
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
        $classes = array('price', 'trigger', 'product', 'wishlist', 'category',
            'currency', 'productcompare', 'collectionitem');

        foreach ($classes as $class) {
            $this->{$class} = $this->getInstance("gplcart\\core\\models\\$class");
        }
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
     * @return array
     */
    public function getTriggered()
    {
        $options = array(
            'status' => 1,
            'store_id' => $this->store_id
        );

        return $this->trigger->getTriggered(array(), $options);
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
     * @param array $options
     * @return array
     */
    public function getCart(array $options = array())
    {
        $options += array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );

        $cart = $this->cart->getContent($options);

        if (empty($cart['items'])) {
            return array();
        }

        return $this->prepareCart($cart);
    }

    /**
     * Returns an array of product IDs to compare
     * @return array
     */
    public function getComparison()
    {
        return $this->productcompare->getList();
    }

    /**
     * Returns an array of wishlist items for the current user
     * @return array
     */
    public function getWishlist()
    {
        $options = array(
            'product_status' => 1,
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );

        return (array) $this->wishlist->getList($options);
    }

    /**
     * Handles submitted cart products
     */
    protected function submitCart()
    {
        $this->setSubmitted('product');
        $this->filterSubmitted(array('product_id'));

        if ($this->isPosted('add_to_cart')) {
            $this->validateAddToCart();
            $this->addToCart();
        } else if ($this->isPosted('remove_from_cart')) {
            $this->setSubmitted('cart');
            $this->deleteFromCart();
        }
    }

    /**
     * Adds a product to the cart
     */
    protected function addToCart()
    {
        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->text('An error occurred')
        );

        $errors = $this->error();

        if (empty($errors)) {
            $submitted = $this->getSubmitted();
            $result = $this->cart->addProduct($submitted['product'], $submitted);
        } else {
            $result['message'] = $this->format($errors);
        }

        if ($this->isAjax()) {
            $result['modal'] = $this->getWidgetCartPreview($this, $this->getCart());
            $this->outputJson($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Validates adding a product to the cart
     */
    protected function validateAddToCart()
    {
        $this->setSubmitted('user_id', $this->cart_uid);
        $this->setSubmitted('store_id', $this->store_id);
        $this->setSubmitted('quantity', $this->getSubmitted('quantity', 1));

        $this->validateComponent('cart');
    }

    /**
     * Deletes a submitted cart item
     */
    protected function deleteFromCart()
    {
        $result = $this->cart->submitDelete($this->getSubmitted('cart_id'));

        if (empty($result['quantity'])) {
            $result['message'] = '';
        }

        if ($this->isAjax()) {
            $result['modal'] = $this->getWidgetCartPreview($this, $this->getCart());
            $this->outputJson($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
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

        $categories = $this->category->getTree($conditions);
        return $this->prepareEntityItems($categories, $options);
    }

    /**
     * Prepares an array of cart items
     * @param array $cart
     * @return array
     */
    protected function prepareCart(array $cart)
    {
        foreach ($cart['items'] as &$item) {
            $item['currency'] = $cart['currency'];
            $this->setItemCartThumb($item, $this->image);
            $this->setItemPriceFormatted($item, $this->price, $this->current_currency);
            $this->setItemTotalFormatted($item, $this->price);
            $this->setItemProductBundle($item['product'], $this->product, $this->image);
        }

        $this->setItemTotalFormatted($cart, $this->price);
        return $cart;
    }

    /**
     * Adds/removes a product from comparison
     */
    protected function submitCompare()
    {
        $this->setSubmitted('product');
        $this->filterSubmitted(array('product_id'));

        if ($this->isPosted('remove_from_compare')) {
            $this->deleteFromCompare();
        } else if ($this->isPosted('add_to_compare')) {
            $this->validateAddToCompare();
            $this->addToCompare();
        }
    }

    /**
     * Adds a product to comparison
     */
    protected function addToCompare()
    {
        $errors = $this->error();

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->text('An error occurred')
        );

        if (empty($errors)) {
            $submitted = $this->getSubmitted();
            $result = $this->productcompare->addProduct($submitted['product'], $submitted);
        } else {
            $result['message'] = $this->format($errors);
        }

        if ($this->isAjax()) {
            $this->outputJson($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Deletes a submitted product from the comparison
     */
    protected function deleteFromCompare()
    {
        $product_id = $this->getSubmitted('product_id');
        $result = $this->productcompare->deleteProduct($product_id);

        if ($this->isAjax()) {
            $this->outputJson($result);
        } else {
            $this->controlDeleteFromCompare($result, $product_id);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Controls redirect after a product has been deleted from comparison
     * @param array $result
     * @param integer $product_id
     */
    protected function controlDeleteFromCompare(array &$result, $product_id)
    {
        if (empty($result['redirect'])) {
            $segments = $this->url->getSegments();
            if (isset($segments[0]) && $segments[0] === 'compare' && !empty($segments[1])) {
                $ids = array_filter(array_map('trim', explode(',', $segments[1])), 'ctype_digit');
                unset($ids[array_search($product_id, $ids)]);
                $result['redirect'] = $segments[0] . '/' . implode(',', $ids);
            }
        }
    }

    /**
     * Adds/removes a product from the wishlist
     */
    protected function submitWishlist()
    {
        $this->setSubmitted('product');
        $this->filterSubmitted(array('product_id'));

        if ($this->isPosted('remove_from_wishlist')) {
            $this->deleteFromWishlist();
        } else if ($this->isPosted('add_to_wishlist')) {
            $this->validateAddToWishlist();
            $this->addToWishlist();
        }
    }

    /**
     * Validates "Add to wishlist" action
     */
    protected function validateAddToWishlist()
    {
        $this->setSubmitted('user_id', $this->cart_uid);
        $this->setSubmitted('store_id', $this->store_id);

        $this->validateComponent('wishlist');
    }

    /**
     * Add a product to the wishlist
     */
    protected function addToWishlist()
    {
        $errors = $this->error();

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->text('An error occurred')
        );

        if (empty($errors)) {
            $result = $this->wishlist->addProduct($this->getSubmitted());
        } else {
            $result['message'] = $this->format($errors);
        }

        if ($this->isAjax()) {
            $this->outputJson($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Deletes a submitted product from the wishlist
     */
    protected function deleteFromWishlist()
    {
        $condititons = array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id,
            'product_id' => $this->getSubmitted('product_id')
        );

        $result = $this->wishlist->deleteProduct($condititons);

        if ($this->isAjax()) {
            $this->outputJson($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Validates "Add to compare" action
     */
    protected function validateAddToCompare()
    {
        $this->validateComponent('compare');
    }

    /**
     * Loads products from an array of product IDs
     * @param array $conditions
     * @param array $options
     * @return array
     */
    public function getProducts($conditions = array(), $options = array())
    {
        $options += array(
            'entity' => 'product'
        );

        $conditions += array(
            'status' => 1,
            'store_id' => $this->store_id
        );

        if (isset($conditions['product_id']) && empty($conditions['product_id'])) {
            return array();
        }

        $list = (array) $this->product->getList($conditions);
        return $this->prepareEntityItems($list, $options);
    }

    /**
     * Returns an array of collection items
     * @param array $conditions
     * @param array $options
     * @return array
     */
    public function getCollectionItems(array $conditions, array $options)
    {
        $conditions += array(
            'status' => 1,
            'store_id' => $this->store_id
        );

        $items = $this->collectionitem->getItems($conditions);

        if (empty($items)) {
            return array();
        }

        $item = reset($items);

        $options += array(
            'entity' => $item['collection_item']['type'],
            'template_item' => $item['collection_handler']['template']['item']
        );

        return $this->prepareEntityItems($items, $options);
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
            'entity_id' => array_keys($items),
            'entity' => $options['entity'],
            'template_item' => "{$options['entity']}/item/{$options['view']}",
            'imagestyle' => $this->configTheme("image_style_{$options['entity']}_{$options['view']}", 3)
        );

        foreach ($items as &$item) {

            $this->setItemThumb($item, $this->image, $options);

            if ($options['entity'] === 'product') {
                $this->setItemProductInComparison($item, $this->productcompare);
                $this->setItemPriceCalculated($item, $this->product);
                $this->setItemProductInWishlist($item, $this->cart_uid, $this->store_id, $this->wishlist);
                $this->setItemPriceFormatted($item, $this->price, $this->current_currency);
                $this->setItemProductBundle($item, $this->product, $this->image);
                $this->setItemProductRendered($item, $options);
            } else {
                $this->setItemIndentation($item);
                $this->setItemUrl($item, $options);
                $this->setItemUrlActive($item, $this->base, $this->path);
                $this->setItemRendered($item, array('item' => $item), $options);
            }
        }

        return $items;
    }

}
