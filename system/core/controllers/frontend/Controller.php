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
 * Contents specific to the front-end methods
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
        $this->data['_menu'] = $this->getCategoryMenu();
        $this->data['_captcha'] = $this->renderCaptcha();
        $this->data['_comparison'] = $this->getComparison();
        $this->data['_currency'] = $currencies[$this->current_currency];
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
        $this->price = Container::get('gplcart\\core\\models\\Price');
        $this->trigger = Container::get('gplcart\\core\\models\\Trigger');
        $this->product = Container::get('gplcart\\core\\models\\Product');
        $this->wishlist = Container::get('gplcart\\core\\models\\Wishlist');
        $this->category = Container::get('gplcart\\core\\models\\Category');
        $this->currency = Container::get('gplcart\\core\\models\\Currency');
        $this->compare = Container::get('gplcart\\core\\models\\ProductCompare');
        $this->collection_item = Container::get('gplcart\\core\\models\\CollectionItem');
    }

    /**
     * Sets controller's properties
     */
    protected function setFrontendProperties()
    {
        if (!$this->isInstall()) {
            if (!$this->isInternalRoute()) {
                $this->triggered = $this->getFiredTriggers();
                $this->data_categories = $this->getCategories();
            }
            $this->current_currency = $this->currency->getCode();
        }
    }

    /**
     * Returns an array of fired triggers for the current context
     * @return array
     */
    public function getFiredTriggers()
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
     * Returns an array of product IDs to compare
     * @return array
     */
    public function getComparison()
    {
        return $this->compare->getList();
    }

    /**
     * Returns wishlist items for the current user
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
     * Returns rendered main menu
     * @return string
     */
    public function getCategoryMenu()
    {
        return $this->renderMenu(array('items' => $this->data_categories));
    }

    /**
     * Handles "Add to cart" event
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

        if ($this->isAjax()) {
            if ($result['severity'] === 'success') {
                $result += array('modal' => $this->renderCartPreview());
            }
            $this->response->outputJson($result);
        }
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Validates "Add to cart" action
     */
    protected function validateAddToCart()
    {
        $this->setSubmitted('user_id', $this->cart_uid);
        $this->setSubmitted('store_id', $this->store_id);
        $this->setSubmitted('quantity', $this->getSubmitted('quantity', 1));

        $this->validateComponent('cart');
    }

    /**
     * Deletes a cart item
     * @return null|array
     */
    protected function deleteFromCart()
    {
        if (!$this->cart->delete($this->getSubmitted('cart_id'))) {
            return array('redirect' => '', 'severity' => 'success');
        }

        $cart = $this->getCart();

        $result = array(
            'redirect' => '',
            'severity' => 'success',
            'message' => $this->text('Product has been deleted from cart'),
            'quantity' => empty($cart['quantity']) ? 0 : $cart['quantity']
        );

        $preview = $this->renderCartPreview();

        if (empty($preview)) {
            $result['message'] = '';
            $result['quantity'] = 0;
        }

        if ($this->isAjax()) {
            $result['modal'] = $preview;
            $this->response->outputJson($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
        return null;
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
     * Returns rendered cart preview
     * @return string
     */
    protected function renderCartPreview()
    {
        $cart = $this->getCart();

        if (empty($cart['items'])) {
            return '';
        }

        $options = array(
            'cart' => $this->prepareCart($cart),
            'limit' => $this->config('cart_preview_limit', 5)
        );

        return $this->render('cart/preview', $options, true);
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
            $this->setItemThumbCart($item);
            $this->setItemPriceFormatted($item);
            $this->setItemTotalFormatted($item);
        }

        $this->setItemTotalFormatted($cart);
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
            $result = $this->compare->addProduct($submitted['product'], $submitted);
        } else {
            $result['message'] = implode('<br>', gplcart_array_flatten($errors));
        }

        if ($this->isAjax()) {
            $this->response->outputJson($result);
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

        if ($this->isAjax()) {
            $this->response->outputJson($result);
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
     * Validates "Add to compare" action
     */
    protected function validateAddToCompare()
    {
        $this->validateComponent('compare');
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
            $result['message'] = implode('<br>', gplcart_array_flatten($errors));
        }

        if ($this->isAjax()) {
            $this->response->outputJson($result);
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
            $this->response->outputJson($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Loads products from an array of product IDs
     * @param array $conditions
     * @param array $options
     * @return array
     */
    public function getProducts($conditions = array(), $options = array())
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
    public function getCollectionItems(array $conditions, array $options)
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

        return $this->render($item['collection_handler']['template']['list'], $data, true);
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
            'imagestyle' => $this->configTheme("image_style_{$options['entity']}_{$options['view']}", 3)
        );

        foreach ($items as &$item) {

            $this->setItemIndentation($item);
            $this->setItemUrl($item, $options);
            $this->setItemUrlActive($item);
            $this->setItemThumb($item, $options);

            if ($options['entity'] == 'product') {
                $this->setItemInComparison($item);
                $this->setItemPriceCalculated($item);
                $this->setItemInWishlist($item);
                $this->setItemPriceFormatted($item);
                $this->setItemRenderedProduct($item, $options);
            } else {
                $this->setItemRendered($item, array('item' => $item), $options);
            }
        }

        return $items;
    }

    /**
     * Returns rendered "Share this" widget
     * @param array $options
     * @return string
     */
    public function renderShareWidget(array $options = array())
    {
        $options += array('url' => $this->url('', array(), true));
        return $this->render('common/share', $options);
    }

    /**
     * Adds the "In comparison" boolean flag
     * @param array $item
     * @return array
     */
    protected function setItemInComparison(array &$item)
    {
        $item['in_comparison'] = $this->compare->exists($item['product_id']);
        return $item;
    }

    /**
     * Adds the "In wishlist" boolean flag to the item
     * @param array $item
     * @return array
     */
    protected function setItemInWishlist(&$item)
    {
        $conditions = array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id,
            'product_id' => $item['product_id']
        );

        $item['in_wishlist'] = $this->wishlist->exists($conditions);
        return $item;
    }

    /**
     * Adds a full formatted total amount to the item
     * @param array $item
     * @return array
     */
    protected function setItemTotalFormatted(array &$item)
    {
        $item['total_formatted'] = $this->price->format($item['total'], $item['currency']);
        return $item;
    }

    /**
     * Add a formatted total amount without currency sign to the item
     * @param array $item
     * @return array
     */
    protected function setItemTotalFormattedNumber(array &$item)
    {
        $item['total_formatted_number'] = $this->price->format($item['total'], $item['currency'], true, false);
        return $item;
    }

    /**
     * Add a thumb URL to the item
     * @param array $item
     * @param array $options
     * @return array
     */
    protected function setItemThumb(array &$item, array $options = array())
    {
        if (empty($options['imagestyle'])) {
            return $item;
        }

        if (!empty($options['path'])) {
            $item['thumb'] = $this->image($options['path'], $options['imagestyle']);
        } else if (!empty($item['path'])) {
            $item['thumb'] = $this->image($item['path'], $options['imagestyle']);
        } else if (empty($item['images'])) {
            $item['thumb'] = $this->image->getThumb($item, $options);
        } else {
            foreach ($item['images'] as &$image) {
                $image['url'] = $this->image($image['path']);
                $image['thumb'] = $this->image($image['path'], $options['imagestyle']);
                $this->setItemIsThumbPlaceholder($image);
            }
        }

        $this->setItemIsThumbPlaceholder($item);
        return $item;
    }

    /**
     * Sets a boolean flag indicating that the thumb is an image placeholder
     * @param array $item
     * @return array
     */
    protected function setItemIsThumbPlaceholder(array &$item)
    {
        if (!empty($item['thumb'])) {
            $item['thumb_placeholder'] = $this->image->isPlaceholder($item['thumb']);
        }

        return $item;
    }

    /**
     * Add thumb URLs to the cart items
     * @param array $item
     * @return array
     */
    protected function setItemThumbCart(array &$item)
    {
        $options = array(
            'path' => '',
            'imagestyle' => $this->configTheme('image_style_cart', 3)
        );

        if (empty($item['product']['combination_id']) && !empty($item['product']['images'])) {
            $imagefile = reset($item['product']['images']);
            $options['path'] = $imagefile['path'];
        }

        if (!empty($item['product']['file_id']) && !empty($item['product']['images'][$item['product']['file_id']]['path'])) {
            $options['path'] = $item['product']['images'][$item['product']['file_id']]['path'];
        }

        if (empty($options['path'])) {
            $item['thumb'] = $this->image->getPlaceholder($options['imagestyle']);
        } else {
            $this->setItemThumb($item, $options);
        }

        return $item;
    }

    /**
     * Add alias URL to an entity
     * @param array $item
     * @param array $options
     * @return array
     */
    protected function setItemUrl(array &$item, array $options = array())
    {
        if (isset($options['id_key']) && empty($options['no_item_url'])) {

            $id = $item[$options['id_key']];
            $entity = preg_replace('/_id$/', '', $options['id_key']);
            $item['url'] = empty($item['alias']) ? $this->url("$entity/$id") : $this->url($item['alias']);

            // URL with preserved query to retain view, sort etc
            $item['url_query'] = empty($item['alias']) ? $this->url("$entity/$id", $this->query) : $this->url($item['alias'], $this->query);
        }

        return $item;
    }

    /**
     * Adds a rendered product to the item
     * @param array $item
     * @param array $options
     * @return array
     */
    protected function setItemRenderedProduct(array &$item, $options = array())
    {
        if (!empty($options['template_item'])) {

            $options += array(
                'buttons' => array(
                    'cart_add', 'wishlist_add', 'compare_add'));

            $data = array(
                'item' => $item,
                'buttons' => $options['buttons']
            );

            $this->setItemRendered($item, $data, $options);
        }

        return $item;
    }

    /**
     * Add a rendered content to the item
     * @param array $item
     * @param array $data
     * @param array $options
     * @return array
     */
    protected function setItemRendered(array &$item, $data, $options = array())
    {
        if (!empty($options['template_item'])) {
            $item['rendered'] = $this->render($options['template_item'], $data, true);
        }

        return $item;
    }

    /**
     * Add a formatted price to the item
     * @param array $item
     * @return array
     */
    protected function setItemPriceFormatted(array &$item)
    {
        $price = $this->currency->convert($item['price'], $item['currency'], $this->current_currency);
        $item['price_formatted'] = $this->price->format($price, $this->current_currency);

        if (isset($item['original_price'])) {
            $price = $this->currency->convert($item['original_price'], $item['currency'], $this->current_currency);
            $item['original_price_formatted'] = $this->price->format($price, $this->current_currency);
        }

        return $item;
    }

    /**
     * Add a calculated product price to the item
     * @param array $item
     * @return array
     */
    protected function setItemPriceCalculated(array &$item)
    {
        $calculated = $this->product->calculate($item);

        if (!empty($calculated)) {

            if ($item['price'] != $calculated['total']) {
                $item['original_price'] = $item['price'];
            }

            $item['price'] = $calculated['total'];
            $item['price_rule_components'] = $calculated['components'];
        }

        return $item;
    }

    /**
     * Sets boolean flag indicating that item's URL matches the current URL
     * @param array $item
     * @return array
     */
    protected function setItemUrlActive(array &$item)
    {
        if (isset($item['url'])) {
            $item['active'] = $this->path(substr($item['url'], strlen($this->base)));
        }

        return $item;
    }

    /**
     * Add indentation string indicating item's depth (only for categories)
     * @param array $item
     * @return array
     */
    protected function setItemIndentation(array &$item)
    {
        if (isset($item['depth'])) {
            $item['indentation'] = str_repeat('<span class="indentation"></span>', $item['depth']);
        }

        return $item;
    }

    /**
     * Sets a breadcrumb item that points to home page
     */
    protected function setBreadcrumbHome()
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumb($breadcrumb);
    }

}
