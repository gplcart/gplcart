<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Sku as ModelsSku;
use core\models\Cart as ModelsCart;
use core\models\Product as ModelsProduct;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate cart data
 */
class Cart extends BaseValidator
{

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
     * Sku model instance
     * @var \core\models\Sku $sku
     */
    protected $sku;

    /**
     * Constructor
     * @param ModelsCart $cart
     * @param ModelsProduct $product
     * @param ModelsSku $sku
     */
    public function __construct(ModelsCart $cart, ModelsProduct $product,
            ModelsSku $sku)
    {
        parent::__construct();

        $this->sku = $sku;
        $this->cart = $cart;
        $this->product = $product;
    }

    public function cart(array &$submitted, array $options = array())
    {

        $this->validateCart($submitted);
        $this->validateStoreId($submitted, $options);
        $this->validateSkuCart($submitted, $options);
        $this->validateUserCartId($submitted, $options);
        $this->validateProductCart($submitted, $options);
        $this->validateOrderCart($submitted, $options);
        $this->validateQuantityCart($submitted, $options);
        $this->validateLimitCart($submitted, $options);
        $this->validateOptionsCart($submitted, $options);

        unset($submitted['product']);

        return $this->getResult();
    }

    /**
     * Validates a cart item to by updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateCart(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {

            $data = $this->cart->get($submitted['update']);

            if (empty($data)) {
                $vars = array('@name' => $this->language->text('Cart'));
                $error = $this->language->text('Object @name does not exist', $vars);
                $this->setError('update', $error);
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

    /**
     * Validates a cart item SKU
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateSkuCart(array &$submitted, array $options)
    {
        if ($this->isError('store_id', $options)) {
            return null;
        }

        $sku = $this->getSubmitted('sku', $submitted, $options);
        $store_id = $this->getSubmitted('store_id', $submitted, $options);

        if (!empty($submitted['update']) && !isset($sku)) {
            return null;
        }

        if (empty($sku)) {
            $vars = array('@field' => $this->language->text('SKU'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('sku', $error, $options);
            return false;
        }

        $product = $this->product->getBySku($sku, $store_id);

        if (empty($product)) {
            $vars = array('@name' => $this->language->text('Product'));
            $error = $this->language->text('Object @name does not exist', $vars);
            $this->setError('sku', $error, $options);
            return false;
        }

        $submitted['product'] = $product;
        return true;
    }

    /**
     * Validates a product ID
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateProductCart(array &$submitted, array $options)
    {
        $product_id = $this->getSubmitted('product_id', $submitted, $options);

        if (!empty($submitted['update']) && !isset($product_id)) {
            return null;
        }

        if (empty($product_id)) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        if (!is_numeric($product_id)) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            $vars = array('@name' => $this->language->text('Product'));
            $error = $this->language->text('Object @name does not exist', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        if (isset($submitted['product']['product_id'])//
                && $submitted['product']['product_id'] != $product_id) {

            $error = $this->language->text('Invalid products');
            $this->setError('product_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates an order ID
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateOrderCart(array &$submitted, array $options)
    {
        $order_id = $this->getSubmitted('order_id', $submitted, $options);

        if (empty($order_id)) {
            return null;
        }

        if (!is_numeric($order_id)) {
            $vars = array('@field' => $this->language->text('Order'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('order_id', $error, $options);
            return false;
        }

        $order = $this->order->get($order_id);

        if (empty($order)) {
            $vars = array('@name' => $this->language->text('Order'));
            $error = $this->language->text('Object @name does not exist', $vars);
            $this->setError('order_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates cart item quantity
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateQuantityCart(array &$submitted, array $options)
    {
        $quantity = $this->getSubmitted('quantity', $submitted, $options);

        if (!empty($submitted['update']) && !isset($quantity)) {
            return null;
        }

        if (empty($quantity)) {
            $vars = array('@field' => $this->language->text('Quantity'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('quantity', $error, $options);
            return false;
        }

        if (!is_numeric($quantity)) {
            $vars = array('@field' => $this->language->text('Quantity'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('quantity', $error, $options);
            return false;
        }

        if (strlen($quantity) > 2) {
            $vars = array('@max' => 2, '@field' => $this->language->text('Quantity'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('quantity', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates cart limits
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateLimitCart(array &$submitted, array $options)
    {
        if (!empty($submitted['admin'])) {
            return null;
        }

        if (empty($submitted['product']) || $this->isError()) {
            return null;
        }

        $submitted += array('increment' => true);

        $sku = $this->getSubmitted('sku', $submitted, $options);
        $stock = $this->getSubmitted('stock', $submitted, $options);
        $user_id = $this->getSubmitted('user_id', $submitted, $options);
        $store_id = $this->getSubmitted('store_id', $submitted, $options);
        $quantity = $this->getSubmitted('quantity', $submitted, $options);

        if (!isset($stock)) {
            $stock = $submitted['product']['stock'];
        }

        $conditions = array('user_id' => $user_id, 'store_id' => $store_id);
        $existing_quantity = $this->cart->getQuantity($conditions);

        $expected_quantity_sku = $quantity;
        if (!empty($submitted['increment']) && isset($existing_quantity['sku'][$sku])) {
            $expected_quantity_sku += $existing_quantity['sku'][$sku];
        }

        if ($submitted['product']['subtract'] && $expected_quantity_sku > $stock) {
            $error = $this->language->text('Too low stock level');
            $this->setError('quantity', $error, $options);
            return false;
        }

        $limit_sku = $this->cart->getLimits('sku');
        $limit_item = $this->cart->getLimits('item');

        if (!empty($limit_item) && !isset($existing_quantity['sku'][$sku])//
                && (count($existing_quantity['sku']) >= $limit_item)) {

            $vars = array('%num' => $limit_item);
            $error = $this->language->text('Sorry, you cannot have more than %num items in your cart', $vars);
            $this->setError('quantity', $error, $options);
            return false;
        }

        if (!empty($limit_sku) && $expected_quantity_sku > $limit_sku) {
            $vars = array('%num' => $limit_sku);
            $error = $this->language->text('Sorry, you cannot have more than %num items per SKU in your cart', $vars);
            $this->setError('quantity', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates products cart options
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateOptionsCart(array &$submitted, array $options)
    {
        if (empty($submitted['product']) || $this->isError()) {
            return null;
        }

        $product = $submitted['product'];
        $ops = $this->getSubmitted('options', $submitted, $options);

        if (empty($ops)) {
            $this->setSubmitted('sku', $product['sku'], $submitted, $options);
            $this->setSubmitted('stock', $product['stock'], $submitted, $options);
            return true;
        }

        $combination_id = $this->sku->getCombinationId($ops, $product['product_id']);

        if (empty($product['combination'][$combination_id]['sku'])) {
            $error = $this->language->text('Invalid option combination');
            $this->setError('options', $error, $options);
            return false;
        }

        $this->setSubmitted('combination_id', $combination_id, $submitted, $options);
        $this->setSubmitted('sku', $product['combination'][$combination_id]['sku'], $submitted, $options);
        $this->setSubmitted('stock', $product['combination'][$combination_id]['stock'], $submitted, $options);
        return true;
    }

}
