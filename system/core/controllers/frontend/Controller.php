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
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * The current currency code
     * @var string
     */
    protected $current_currency;

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

        $this->hook->fire('construct.controller.frontend', $this);

        $this->controlHttpStatus();
    }

    /**
     * Sets model instancies
     */
    protected function setFrontendInstancies()
    {
        $this->price = Container::get('gplcart\\core\\models\\Price');
        $this->image = Container::get('gplcart\\core\\models\\Image');
        $this->trigger = Container::get('gplcart\\core\\models\\Trigger');
        $this->product = Container::get('gplcart\\core\\models\\Product');
        $this->compare = Container::get('gplcart\\core\\models\\Compare');
        $this->wishlist = Container::get('gplcart\\core\\models\\Wishlist');
        $this->category = Container::get('gplcart\\core\\models\\Category');
        $this->currency = Container::get('gplcart\\core\\models\\Currency');
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
            $this->current_currency = (string) $this->currency->get();
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
     * @param null|string $key
     * @return mixed
     */
    public function cart($key = null)
    {
        if ($key === 'user_id') {
            return $this->cart_uid;
        }

        $conditions = array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );

        $cart = $this->cart->getContent($conditions);

        if ($key === 'count_total') {
            return empty($cart['quantity']) ? 0 : $cart['quantity'];
        }

        return $cart;
    }

    /**
     * Returns an array of recently viewed products
     * @return array
     */
    public function viewed()
    {
        $limit = $this->config('recent_limit', 4);
        return $this->product->getViewed($limit);
    }

    /**
     * Returns an array of product IDs to compare
     * @return array|integer
     */
    public function compare($key = null)
    {
        $items = $this->compare->getList();

        if ($key === 'count') {
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
            'product_status' => 1,
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );

        $result = (array) $this->wishlist->getList($options);

        if ($key === 'count') {
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

        $this->validateComponent('cart');
    }

    /**
     * Deletes a cart item
     */
    protected function deleteFromCart()
    {
        if (!$this->cart->delete($this->getSubmitted('cart_id'))) {
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
        $product_id = $this->getSubmitted('product_id');
        $result = $this->compare->deleteProduct($product_id);

        if ($this->request->isAjax()) {
            $this->response->json($result);
        } else {
            $this->controlDeleteFromCompare($result, $product_id);
        }
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Controls redirect after a product has been deleted from comparison
     * If the result redirect is empty and the current location is "compare/1,2,3"
     * It removes the deleted product ID (e.g 3) and sets redirect to "compare/1,2"
     * @param array $result
     * @param integer $product_id
     */
    protected function controlDeleteFromCompare(array &$result, $product_id)
    {
        if (empty($result['redirect'])) {
            $segments = $this->url->segments();
            if (isset($segments[0]) && $segments[0] === 'compare' && !empty($segments[1])) {
                $ids = array_filter(array_map('trim', explode(',', $segments[1])), 'ctype_digit');
                unset($ids[array_search($product_id, $ids)]);
                $result['redirect'] = $segments[0] . '/' . implode(',', $ids);
            }
        }
    }

    /**
     * Validates "Add to compare" action
     */
    protected function validateAddToCompare()
    {
        $this->validateComponent('compare');
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
     * @param array $options
     * @return array
     */
    protected function getCollectionItems(array $conditions, array $options)
    {
        $conditions += array('status' => 1, 'store_id' => $this->store_id);
        $items = $this->collection_item->getItems($conditions);

        if (empty($items)) {
            return array();
        }

        $item = reset($items);

        $options += array(
            'no_item_url' => true,
            'entity' => $item['collection_item']['type'],
            'template_item' => $item['collection_handler']['template']['item']
        );

        return $this->prepareEntityItems($items, $options);
    }

    /**
     * Returns a rendered collection
     * @param array $conditions
     * @param array $options
     * @return string
     */
    protected function renderCollection(array $conditions, $options = array())
    {
        $items = $this->getCollectionItems($conditions, $options);

        if (empty($items)) {
            return '';
        }

        $item = reset($items);

        $data = array(
            'items' => $items,
            'title' => $item['collection_item']['collection_title'],
            'collection_id' => $item['collection_item']['collection_id']
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
     * Add full formatted total amount
     * @param array $item
     */
    protected function attachItemTotalFormatted(array &$item)
    {
        $item['total_formatted'] = $this->price->format($item['total'], $item['currency']);
    }

    /**
     * Add formatted total amount without currency sign
     * @param array $item
     */
    protected function attachItemTotalFormattedNumber(array &$item)
    {
        $item['total_formatted_number'] = $this->price->format($item['total'], $item['currency'], true, false);
    }

    /**
     * Add decimat total
     * @param array $item
     */
    protected function attachItemTotalDecimal(array &$item)
    {
        $item['total_decimal'] = $this->price->decimal($item['total'], $item['currency']);
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

        if (!empty($data['path'])) {
            $data['thumb'] = $this->image->url($options['imagestyle'], $data['path']);
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
        if (isset($options['id_key']) && empty($options['no_item_url'])) {
            $id = $data[$options['id_key']];
            $entity = preg_replace('/_id$/', '', $options['id_key']);
            $data['url'] = empty($data['alias']) ? $this->url("$entity/$id") : $this->url($data['alias']);
            // URL with preserved query to retain view, sort etc
            $data['url_query'] = empty($data['alias']) ? $this->url("$entity/$id", $this->query) : $this->url($data['alias'], $this->query);
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
        $price = $this->currency->convert($item['price'], $item['currency'], $this->current_currency);
        $item['price_formatted'] = $this->price->format($price, $this->current_currency);

        if (isset($item['original_price'])) {
            $price = $this->currency->convert($item['original_price'], $item['currency'], $this->current_currency);
            $item['original_price_formatted'] = $this->price->format($price, $this->current_currency);
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
            $path = substr($item['url'], strlen($this->base));
            $item['active'] = $this->path($path);
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
