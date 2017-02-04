<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\Container;
use gplcart\core\Controller as BaseController;

/**
 * Contents specific to the frontend methods
 */
class Controller extends BaseController
{

    /**
     * Trigger model instance
     * @var \gplcart\core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Image model instance
     * @var \gplcart\core\models\Image $image
     */
    protected $image;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Compare model instance
     * @var \gplcart\core\models\Compare $compare
     */
    protected $compare;

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
     * Cart model instance
     * @var \gplcart\core\models\Cart $cart
     */
    protected $cart;

    /**
     * Collection item model instance
     * @var \gplcart\core\models\CollectionItem $collection_item
     */
    protected $collection_item;

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
     * Current user cart ID
     * @var integer|string
     */
    protected $cart_uid;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setFrontendInstancies();
        $this->setFrontendProperties();

        $this->submitCart();
        $this->submitCompare();
        $this->submitWishlist();

        $this->hook->fire('init.frontend', $this);

        $this->controlHttpStatus();
    }

    /**
     * Sets model instancies
     */
    protected function setFrontendInstancies()
    {
        $this->cart = Container::get('gplcart\\core\\models\\Cart');
        $this->price = Container::get('gplcart\\core\\models\\Price');
        $this->image = Container::get('gplcart\\core\\models\\Image');
        $this->trigger = Container::get('gplcart\\core\\models\\Trigger');
        $this->product = Container::get('gplcart\\core\\models\\Product');
        $this->compare = Container::get('gplcart\\core\\models\\Compare');
        $this->wishlist = Container::get('gplcart\\core\\models\\Wishlist');
        $this->category = Container::get('gplcart\\core\\models\\Category');
        $this->collection_item = Container::get('gplcart\\core\\models\\CollectionItem');
    }

    /**
     * Sets controller's properties
     */
    protected function setFrontendProperties()
    {
        if (!$this->url->isInstall()) {
            $this->triggered = $this->getFiredTriggers();
            $this->data_categories = $this->getCategories();
            $this->cart_uid = $this->cart->uid();
        }
    }

    /**
     * Returns an array of fired triggers for the current context
     * @return array
     */
    protected function getFiredTriggers()
    {
        $conditions = array('status' => 1, 'store_id' => $this->store_id);
        return $this->trigger->getFired($conditions);
    }

    /**
     * Returns the current cart data
     * @param null|string $key
     * @return mixed
     */
    public function cart($key = null)
    {
        $conditions = array('user_id' => $this->cart_uid, 'store_id' => $this->store_id);

        if (!isset($key)) {
            return $this->cart->getContent($conditions);
        }
        if ($key == 'count_total') {
            return (int) $this->cart->getQuantity($conditions, 'total');
        }
        if ($key == 'user_id') {
            return $this->cart_uid;
        }
    }

    /**
     * Returns an array of recently viewed products
     * @return array
     */
    public function viewed()
    {
        $limit = $this->config('product_recent_limit', 12);
        return $this->product->getViewed($limit);
    }

    /**
     * Returns an array of product IDs to compare
     * @return array|integer
     */
    public function compare($key = null)
    {
        $items = $this->compare->getList();

        if ($key == 'count') {
            return count($items);
        }
        return $items;
    }

    /**
     * Returns a wishlist data
     * @return array|integer
     */
    public function wishlist($key = null)
    {
        $options = array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );

        $result = (array) $this->wishlist->getList($options);

        if ($key == 'count') {
            return count($result);
        }
        return $result;
    }

    /**
     * Returns rendered main menu
     * @param array $options
     * @return string
     */
    public function menu(array $options = array())
    {
        $options += array('items' => $this->data_categories);
        return $this->renderMenu($options);
    }

    /**
     * Handles "Add to cart" event
     */
    protected function submitCart()
    {
        if ($this->isPosted('add_to_cart')) {
            $this->validateAddToCart();
            $this->addToCart();
        } else if ($this->isPosted('remove_from_cart')) {
            $this->setSubmitted('cart');
            $this->deleteFromCart();
        }
    }

    /**
     * Performs "Add to cart" action
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
            $result['message'] = implode('<br>', gplcart_array_flatten($errors));
        }

        if ($this->request->isAjax()) {
            if ($result['severity'] === 'success') {
                $result += array('modal' => $this->renderCartPreview());
            }
            $this->response->json($result);
        }
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Validates "Add to cart" action
     */
    protected function validateAddToCart()
    {
        $this->setSubmitted('product');

        $this->setSubmitted('user_id', $this->cart_uid);
        $this->setSubmitted('store_id', $this->store_id);
        $this->setSubmitted('quantity', $this->getSubmitted('quantity', 1));

        $this->validate('cart');
    }

    /**
     * Deletes a cart item
     */
    protected function deleteFromCart()
    {
        $conditions = array('cart_id' => $this->getSubmitted('cart_id'));

        if (!$this->cart->delete($conditions)) {
            return array('redirect' => '', 'severity' => 'success');
        }

        $result = array(
            'redirect' => '',
            'severity' => 'success',
            'quantity' => $this->cart('count_total'),
            'message' => $this->text('Cart item has been deleted')
        );

        $preview = $this->renderCartPreview();

        if (empty($preview)) {
            $result['message'] = '';
            $result['quantity'] = 0;
        }

        if ($this->request->isAjax()) {
            $result['modal'] = $preview;
            $this->response->json($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Returns an array of prepared categories
     * @param array $conditions
     * @param array $options
     * @return array
     */
    protected function getCategories($conditions = array(), $options = array())
    {
        $conditions += array(
            'status' => 1,
            'type' => 'catalog',
            'store_id' => $this->store_id
        );

        $options += array(
            'entity' => 'category',
            'imagestyle' => $this->settings('image_style_category_child', 3));

        $categories = $this->category->getTree($conditions);
        return $this->prepareEntityItems($categories, $options);
    }

    /**
     * Returns rendered cart preview
     * @return string
     */
    protected function renderCartPreview()
    {
        $cart = $this->cart();

        if (empty($cart['items'])) {
            return '';
        }

        $options = array(
            'cart' => $this->prepareCart($cart),
            'limit' => $this->config('cart_preview_limit', 5)
        );

        return $this->render('cart/preview', $options);
    }

    /**
     * Prepares an array of cart items
     * @param array $cart
     * @return array
     */
    public function prepareCart(array $cart)
    {
        foreach ($cart['items'] as &$item) {
            $item['currency'] = $cart['currency'];
            $this->attachItemThumbCart($item);
            $this->attachItemPriceFormatted($item);
            $this->attachItemTotalFormatted($item);
        }
        $this->attachItemTotalFormatted($cart);
        return $cart;
    }

    /**
     * Adds/removes a product from comparison
     */
    protected function submitCompare()
    {
        $this->setSubmitted('product');

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
            $result = $this->compare->addProduct($submitted['product'], $submitted);
        } else {
            $result['message'] = implode('<br>', gplcart_array_flatten($errors));
        }

        if ($this->request->isAjax()) {
            $this->response->json($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Deletes a submitted product from the comparison
     */
    protected function deleteFromCompare()
    {
        $result = $this->compare->deleteProduct($this->getSubmitted('product_id'));
        if ($this->request->isAjax()) {
            $this->response->json($result);
        }
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Validates "Add to compare" action
     */
    protected function validateAddToCompare()
    {
        $this->validate('compare');
    }

    /**
     * Adds/removes a product from the wishlist
     */
    protected function submitWishlist()
    {
        $this->setSubmitted('product');

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
        $this->validate('wishlist');
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
            $submitted = $this->getSubmitted();
            $result = $this->wishlist->addProduct($submitted);
        } else {
            $result['message'] = implode('<br>', gplcart_array_flatten($errors));
        }

        if ($this->request->isAjax()) {
            $this->response->json($result);
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

        if ($this->request->isAjax()) {
            $this->response->json($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Loads products from an array of product IDs
     * @param array $conditions
     * @param array $options
     * @return array
     */
    protected function getProducts($conditions = array(), $options = array())
    {
        $options += array('entity' => 'product');
        $conditions += array('status' => 1, 'store_id' => $this->store_id);

        if (isset($conditions['product_id']) && empty($conditions['product_id'])) {
            return array();
        }

        $products = (array) $this->product->getList($conditions);
        return $this->prepareEntityItems($products, $options);
    }

    /**
     * Returns an array of collection items
     * @param array $conditions
     * @return array
     */
    protected function getCollectionItems($conditions = array(),
            $options = array())
    {
        $conditions += array(
            'status' => 1,
            'store_id' => $this->store_id
        );

        $items = $this->collection_item->getItems($conditions);

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
     * 
     * @param array $conditions
     * @return string
     */
    protected function renderCollection(array $conditions)
    {
        $items = $this->getCollectionItems($conditions);

        if (empty($items)) {
            return '';
        }

        $item = reset($items);

        $data = array(
            'items' => $items,
            'title' => $item['collection_item']['collection_title']
        );

        return $this->render($item['collection_handler']['template']['list'], $data);
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
            'ids' => array_keys($items),
            'id_key' => "{$options['entity']}_id",
            'template_item' => "{$options['entity']}/item/{$options['view']}",
            'imagestyle' => $this->settings("image_style_{$options['entity']}_{$options['view']}", 3)
        );

        foreach ($items as &$item) {

            $this->attachItemUrl($item, $options);
            $this->attachItemUrlActive($item);
            $this->attachItemIndentation($item);
            $this->attachItemThumb($item, $options);

            if ($options['entity'] == 'product') {
                $this->attachItemPriceCalculated($item);
                $this->attachItemPriceFormatted($item);
                $this->attachItemInWishlist($item);
                $this->attachItemInComparison($item);
                $this->attachItemRenderedProduct($item, $options);
            } else {
                $this->attachItemRendered($item, array($options['entity'] => $item), $options);
            }
        }

        return $items;
    }

    /**
     * Adds "In comparison" boolean flag
     * @param array $item
     */
    protected function attachItemInComparison(array &$item)
    {
        $item['in_comparison'] = $this->compare->exists($item['product_id']);
    }

    /**
     * Adds "In wishlist" boolean flag
     * @param array $item
     */
    protected function attachItemInWishlist(&$item)
    {
        $conditions = array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id,
            'product_id' => $item['product_id']
        );

        $item['in_wishlist'] = $this->wishlist->exists($conditions);
    }

    /**
     * Add formatted total amount
     * @param array $item
     */
    protected function attachItemTotalFormatted(array &$item)
    {
        $item['total_formatted'] = $this->price->format($item['total'], $item['currency']);
    }

    /**
     * Add thumb URL
     * @param array $data
     * @param array $options
     * @return array
     */
    protected function attachItemThumb(&$data, $options = array())
    {
        if (empty($options['imagestyle'])) {
            return $data;
        }

        if (!empty($options['path'])) {
            $data['thumb'] = $this->image->url($options['imagestyle'], $options['path']);
            return $data;
        }

        if (empty($data['images'])) {
            $data['thumb'] = $this->image->getThumb($data, $options);
            return $data; // Processing single item, exit 
        }

        foreach ($data['images'] as &$image) {
            $image['url'] = $this->image->urlFromPath($image['path']);
            $image['thumb'] = $this->image->url($options['imagestyle'], $image['path']);
        }

        return $data;
    }

    /**
     * Add thumb URLs to cart items
     * @param array $item
     */
    protected function attachItemThumbCart(array &$item)
    {
        $options = array(
            'path' => '',
            'imagestyle' => $this->settings('image_style_cart', 3)
        );

        if (empty($item['product']['combination_id']) && !empty($item['product']['images'])) {
            $imagefile = reset($item['product']['images']);
            $options['path'] = $imagefile['path'];
        }

        if (!empty($item['product']['file_id'])//
                && !empty($item['product']['images'][$item['product']['file_id']]['path'])) {
            $options['path'] = $item['product']['images'][$item['product']['file_id']]['path'];
        }

        $this->attachItemThumb($item, $options);
    }

    /**
     * Add alias URL to an entity
     * @param array $data
     * @param array $options
     */
    protected function attachItemUrl(array &$data, array $options)
    {
        if (isset($options['id_key'])) {
            $id = $data[$options['id_key']];
            $entity = preg_replace('/_id$/', '', $options['id_key']);
            $data['url'] = empty($data['alias']) ? $this->url("$entity/$id") : $this->url($data['alias']);
        }
    }

    /**
     * Adds rendered product item
     * @param array $product
     * @param array $options
     */
    protected function attachItemRenderedProduct(&$product, $options)
    {
        $options += array(
            'buttons' => array(
                'cart_add', 'wishlist_add', 'compare_add'));

        $data = array(
            'product' => $product,
            'buttons' => $options['buttons']
        );

        $this->attachItemRendered($product, $data, $options);
    }

    /**
     * Add rendered item
     * @param array $item
     * @param array $data
     * @param array $options
     */
    protected function attachItemRendered(&$item, $data, $options)
    {
        if (isset($options['template_item'])) {
            $item['rendered'] = $this->render($options['template_item'], $data);
        }
    }

    /**
     * Add formatted price
     * @param array $item
     */
    protected function attachItemPriceFormatted(array &$item)
    {
        $item['price_formatted'] = $this->price->format($item['price'], $item['currency']);

        if (isset($item['original_price'])) {
            $item['original_price_formatted'] = $this->price->format($item['original_price'], $item['currency']);
        }
    }

    /**
     * Add calculated product price
     * @param array $product
     */
    protected function attachItemPriceCalculated(array &$product)
    {
        $calculated = $this->product->calculate($product);

        if (empty($calculated)) {
            return null;
        }

        if ($product['price'] != $calculated['total']) {
            $product['original_price'] = $product['price'];
        }

        $product['price'] = $calculated['total'];
        $product['price_rule_components'] = $calculated['components'];
    }

    /**
     * Whether the item URL mathes the current URL
     * @param array $item
     */
    protected function attachItemUrlActive(array &$item)
    {
        if (isset($item['url'])) {
            $item['active'] = ($this->base . (string) $this->path($item['url'])) !== '';
        }
    }

    /**
     * Add indentation string indicating item depth (only for categories)
     * @param array $item
     */
    protected function attachItemIndentation(array &$item)
    {
        if (isset($item['depth'])) {
            $item['indentation'] = str_repeat('<span class="indentation"></span>', $item['depth']);
        }
    }

    /**
     * Returns rendered honeypot input
     * @return string
     */
    public function renderHoneyPot()
    {
        return $this->render('common/honeypot');
    }

    /**
     * Returns rendered "Share this" widget
     * @param array $options
     * @return string
     */
    public function renderShareWidget(array $options = array())
    {
        $options += array(
            'title' => $this->ptitle,
            'url' => $this->url('', array(), true)
        );

        return $this->render('common/share', $options);
    }

}
