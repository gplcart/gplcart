<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Sku as SkuModel;
use gplcart\core\models\Cart as CartModel;
use gplcart\core\models\Order as OrderModel;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate cart data
 */
class Cart extends BaseValidator
{

    /**
     * Cart model instance
     * @var \gplcart\core\models\Cart $cart
     */
    protected $cart;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Sku model instance
     * @var \gplcart\core\models\Sku $sku
     */
    protected $sku;

    /**
     * Order model instance
     * @var \gplcart\core\models\Order $order
     */
    protected $order;

    /**
     * Constructor
     * @param CartModel $cart
     * @param ProductModel $product
     * @param SkuModel $sku
     * @param OrderModel $order
     */
    public function __construct(CartModel $cart, ProductModel $product,
            SkuModel $sku, OrderModel $order)
    {
        parent::__construct();

        $this->sku = $sku;
        $this->cart = $cart;
        $this->order = $order;
        $this->product = $product;
    }

    /**
     * Performs full cart data validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function cart(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateCart();
        $this->validateStoreId();
        $this->validateProductCart();
        $this->validateSkuCart();
        $this->validateUserCartId();
        $this->validateOrderCart();
        $this->validateQuantityCart();
        $this->validateLimitCart();
        $this->validateOptionsCart();

        return $this->getResult();
    }

    /**
     * Validates a cart item to by updated
     * @return boolean
     */
    protected function validateCart()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->cart->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Cart'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a product ID
     * @return boolean|null
     */
    protected function validateProductCart()
    {
        $product_id = $this->getSubmitted('product_id', $this->options);

        if ($this->isUpdating() && !isset($product_id)) {
            return null;
        }

        if (empty($product_id)) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('product_id', $error, $this->options);
            return false;
        }

        if (!is_numeric($product_id)) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('product_id', $error, $this->options);
            return false;
        }

        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            $vars = array('@name' => $this->language->text('Product'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('product_id', $error, $this->options);
            return false;
        }

        $this->setSubmitted('product', $product);
        return true;
    }

    /**
     * Validates a cart item SKU
     * @return boolean|null
     */
    protected function validateSkuCart()
    {
        if ($this->isError('store_id', $this->options)) {
            return null;
        }

        $sku = $this->getSubmitted('sku', $this->options);

        if (!isset($sku)) {
            return null;
        }

        if (empty($sku)) {
            $vars = array('@field' => $this->language->text('SKU'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('sku', $error, $this->options);
            return false;
        }

        $store_id = $this->getSubmitted('store_id', $this->options);
        $product = $this->product->getBySku($sku, $store_id);

        if (empty($product['product_id'])) {
            $vars = array('@name' => $this->language->text('Product'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('sku', $error, $this->options);
            return false;
        }

        $this->setSubmitted('product', $product);
        return true;
    }

    /**
     * Validates an order ID
     * @return boolean|null
     */
    protected function validateOrderCart()
    {
        $order_id = $this->getSubmitted('order_id', $this->options);

        if (empty($order_id)) {
            return null;
        }

        if (!is_numeric($order_id)) {
            $vars = array('@field' => $this->language->text('Order'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('order_id', $error, $this->options);
            return false;
        }

        $order = $this->order->get($order_id);

        if (empty($order['order_id'])) {
            $vars = array('@name' => $this->language->text('Order'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('order_id', $error, $this->options);
            return false;
        }

        return true;
    }

    /**
     * Validates cart item quantity
     * @return boolean|null
     */
    protected function validateQuantityCart()
    {
        $quantity = $this->getSubmitted('quantity', $this->options);

        if ($this->isUpdating() && !isset($quantity)) {
            return null;
        }

        if (empty($quantity)) {
            $vars = array('@field' => $this->language->text('Quantity'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('quantity', $error, $this->options);
            return false;
        }

        if (!is_numeric($quantity)) {
            $vars = array('@field' => $this->language->text('Quantity'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('quantity', $error, $this->options);
            return false;
        }

        if (strlen($quantity) > 2) {
            $vars = array('@max' => 2, '@field' => $this->language->text('Quantity'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('quantity', $error, $this->options);
            return false;
        }

        return true;
    }

    /**
     * Validates cart limits
     * @return boolean|null
     */
    protected function validateLimitCart()
    {
        $admin = $this->getSubmitted('admin');
        $product = $this->getSubmitted('product');

        if (!empty($admin) || empty($product) || $this->isError()) {
            return null;
        }

        $increment = $this->getSubmitted('increment');

        if (!isset($increment)) {
            $increment = true;
        }

        $sku = $this->getSubmitted('sku', $this->options);
        $stock = $this->getSubmitted('stock', $this->options);
        $user_id = $this->getSubmitted('user_id', $this->options);
        $store_id = $this->getSubmitted('store_id', $this->options);
        $quantity = $this->getSubmitted('quantity', $this->options);

        if (!isset($stock)) {
            $stock = $product['stock'];
        }

        $conditions = array('user_id' => $user_id, 'store_id' => $store_id);
        $existing_quantity = $this->cart->getQuantity($conditions);

        $expected_quantity_sku = $quantity;
        if (!empty($increment) && isset($existing_quantity['sku'][$sku])) {
            $expected_quantity_sku += $existing_quantity['sku'][$sku];
        }

        if ($product['subtract'] && $expected_quantity_sku > $stock) {
            $error = $this->language->text('Too low stock level');
            $this->setError('quantity', $error, $this->options);
            return false;
        }

        $limit_sku = $this->cart->getLimits('sku');
        $limit_item = $this->cart->getLimits('item');

        if (!empty($limit_item) && !isset($existing_quantity['sku'][$sku])//
                && (count($existing_quantity['sku']) >= $limit_item)) {

            $vars = array('%num' => $limit_item);
            $error = $this->language->text('Sorry, you cannot have more than %num items in your cart', $vars);
            $this->setError('quantity', $error, $this->options);
            return false;
        }

        if (!empty($limit_sku) && $expected_quantity_sku > $limit_sku) {
            $vars = array('%num' => $limit_sku);
            $error = $this->language->text('Sorry, you cannot have more than %num items per SKU in your cart', $vars);
            $this->setError('quantity', $error, $this->options);
            return false;
        }

        return true;
    }

    /**
     * Validates products cart options
     * @return boolean|null
     */
    protected function validateOptionsCart()
    {
        $product = $this->getSubmitted('product');

        if (empty($product) || $this->isError()) {
            return null;
        }

        $ops = $this->getSubmitted('options', $this->options);

        if (empty($ops)) {
            $this->setSubmitted('sku', $product['sku'], $this->options);
            $this->setSubmitted('stock', $product['stock'], $this->options);
            return true;
        }

        $combination_id = $this->sku->getCombinationId($ops, $product['product_id']);

        if (empty($product['combination'][$combination_id]['sku'])) {
            $error = $this->language->text('Invalid option combination');
            $this->setError('options', $error, $this->options);
            return false;
        }

        $this->setSubmitted('combination_id', $combination_id, $this->options);
        $this->setSubmitted('sku', $product['combination'][$combination_id]['sku'], $this->options);
        $this->setSubmitted('stock', $product['combination'][$combination_id]['stock'], $this->options);
        return true;
    }

}
