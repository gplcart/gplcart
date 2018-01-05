<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\Sku as SkuModel,
    gplcart\core\models\Cart as CartModel,
    gplcart\core\models\Order as OrderModel,
    gplcart\core\models\Product as ProductModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate cart data
 */
class Cart extends ComponentValidator
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
     * @param CartModel $cart
     * @param ProductModel $product
     * @param SkuModel $sku
     * @param OrderModel $order
     */
    public function __construct(CartModel $cart, ProductModel $product, SkuModel $sku,
            OrderModel $order)
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
        $this->validateOptionsCart();
        $this->validateLimitCart();

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
            $this->setErrorUnavailable('update', $this->translation->text('Cart'));
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
        $field = 'product_id';

        if (isset($this->options['field']) && $this->options['field'] !== $field) {
            return null;
        }

        $label = $this->translation->text('Product');
        $product_id = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($product_id)) {
            return null;
        }

        if (empty($product_id)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($product_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            $this->setErrorUnavailable($field, $label);
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
        if ($this->isError('store_id')) {
            return null;
        }

        $field = 'sku';
        $sku = $this->getSubmitted($field);
        $label = $this->translation->text('SKU');
        $store_id = $this->getSubmitted('store_id');

        if (!isset($sku)) {
            return null;
        }

        if (empty($sku)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $product = $this->product->getBySku($sku, $store_id);

        if (empty($product['product_id'])) {
            $this->setErrorUnavailable($field, $label);
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
        $field = 'order_id';
        $order_id = $this->getSubmitted($field);
        $label = $this->translation->text('Order');

        if (empty($order_id)) {
            return null;
        }

        if (!is_numeric($order_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $order = $this->order->get($order_id);

        if (empty($order['order_id'])) {
            $this->setErrorUnavailable($field, $label);
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
        $field = 'quantity';

        if (isset($this->options['field']) && $this->options['field'] !== $field) {
            return null;
        }

        $quantity = $this->getSubmitted($field);
        $label = $this->translation->text('Quantity');

        if ($this->isUpdating() && !isset($quantity)) {
            return null;
        }

        if (empty($quantity)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($quantity)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if (strlen($quantity) > 2) {
            $this->setErrorLengthRange($field, $label, 1, 2);
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
        $increment = $this->getSubmitted('increment');

        if (!empty($admin) || empty($product) || $this->isError()) {
            return null;
        }

        if (!isset($increment)) {
            $increment = true;
        }

        $sku = $this->getSubmitted('sku');
        $stock = $this->getSubmitted('stock');
        $user_id = $this->getSubmitted('user_id');
        $store_id = $this->getSubmitted('store_id');
        $quantity = $this->getSubmitted('quantity');

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
            $error = $this->translation->text('Too low stock level');
            $this->setError('quantity', $error);
            return false;
        }

        $limit_sku = $this->cart->getLimits('sku');
        $limit_item = $this->cart->getLimits('item');

        if (!empty($limit_item) && !isset($existing_quantity['sku'][$sku])//
                && (count($existing_quantity['sku']) >= $limit_item)) {

            $error = $this->translation->text('Please no more than @num items', array('@num' => $limit_item));
            $this->setError('quantity', $error);
            return false;
        }

        if (!empty($limit_sku) && $expected_quantity_sku > $limit_sku) {
            $error = $this->translation->text('Please no more than @num items per SKU', array('@num' => $limit_sku));
            $this->setError('quantity', $error);
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

        $options = array_filter((array) $this->getSubmitted('options'));

        if (empty($options)) {
            $this->setSubmitted('options', array());
            $this->setSubmitted('sku', $product['sku']);
            $this->setSubmitted('stock', $product['stock']);
            return true;
        }

        $combination_id = $this->sku->getCombinationId($options, $product['product_id']);

        if (empty($product['combination'][$combination_id]['status'])) {
            $error = $this->translation->text('Invalid combination');
            $this->setError('options', $error);
            return false;
        }

        $this->setSubmitted('options', $options);
        $this->setSubmitted('combination_id', $combination_id);
        $this->setSubmitted('sku', $product['combination'][$combination_id]['sku']);
        $this->setSubmitted('stock', $product['combination'][$combination_id]['stock']);
        return true;
    }

}
