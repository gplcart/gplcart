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
    public function __construct(ModelsCart $cart,ModelsProduct $product, ModelsSku $sku)
    {
        parent::__construct();

        $this->sku = $sku;
        $this->cart = $cart;
        $this->product = $product;
    }
    
    public function cart(array &$submitted, array $options = array()){
        
        $this->validateCart($submitted);
        $this->validateStoreId($submitted, $options);
        $this->validateSkuCart($submitted, $options);
        $this->validateUserCart($submitted, $options);
        $this->validateProductCart($submitted, $options);
        $this->validateOrderCart($submitted, $options);
        $this->validateQuantityCart($submitted, $options);
        $this->validateLimitCart($submitted, $options);
        
        unset($submitted['product']);
        
        return empty($this->errors) ? true : $this->errors;
        
    }
    
    /**
     * Validates a cart to by updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateCart(array &$submitted){
        
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {

            $data = $this->cart->get($submitted['update']);

            if (empty($data)) {
                $error = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Address')));
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
            $error = $this->language->text('@field is required', array(
                '@field' => $this->language->text('SKU')));
            $this->setError('sku', $error, $options);
            return false;
        }

        $product = $this->product->getBySku($sku, $store_id);

        if (empty($product)) {
            $error = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Product')));
            $this->setError('sku', $error, $options);
            return false;
        }
        
        $submitted['product'] = $product;
        return true;
    }

    /**
     * Validates a user ID
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateUserCart(array &$submitted, array $options)
    {
        $user_id = $this->getSubmitted('user_id', $submitted, $options);

        if (!empty($submitted['update']) && !isset($user_id)) {
            return null;
        }

        if (empty($user_id)) {
            $error = $this->language->text('@field is required', array(
                '@field' => $this->language->text('User')
            ));
            $this->setError('user_id', $error, $options);
            return false;
        }

        if (strlen($user_id) > 255) {
            $error = $this->language->text('@field must not be longer than @max characters', array(
                '@max' => 255,
                '@field' => $this->language->text('User')
            ));
            $this->setError('user_id', $error, $options);
            return false;
        }

        if (!is_numeric($user_id)) {
            return true; // Anonymous user
        }

        $user = $this->user->get($user_id);

        if (empty($user)) {
            $error = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('User')));
            $this->setError('user_id', $error, $options);
            return false;
        }

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
            $error = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Product')
            ));
            $this->setError('product_id', $error, $options);
            return false;
        }

        if (!is_numeric($product_id)) {
            $error = $this->language->text('@field must be numeric', array(
                '@field' => $this->language->text('Product')));
            $this->setError('product_id', $error, $options);
            return false;
        }

        $product = $this->product->get($product_id);

        if (empty($product)) {
            $error = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Product')));
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
            $error = $this->language->text('@field must be numeric', array(
                '@field' => $this->language->text('Order')));
            $this->setError('order_id', $error, $options);
            return false;
        }

        $order = $this->order->get($order_id);

        if (empty($order)) {
            $error = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Order')));
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
            $error = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Quantity')
            ));
            $this->setError('quantity', $error, $options);
            return false;
        }

        if (!is_numeric($quantity)) {
            $error = $this->language->text('@field must be numeric', array(
                '@field' => $this->language->text('Quantity')));
            $this->setError('quantity', $error, $options);
            return false;
        }

        if (strlen($quantity) > 2) {
            $error = $this->language->text('@field must not be longer than @max characters', array(
                '@max' => 2,
                '@field' => $this->language->text('Quantity')
            ));
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
        if(!empty($submitted['admin'])){
            return null;
        }
        
        if(empty($submitted['product']) || $this->isError()){
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

        if (!empty($limit_item) && !isset($existing_quantity['sku'][$sku]) && (count($existing_quantity['sku']) >= $limit_item)) {
            $error = $this->language->text('Sorry, you cannot have more than %num items in your cart', array(
                        '%num' => $limit_item));
            $this->setError('quantity', $error, $options);
            return false;
        }

        if (!empty($limit_sku) && ($expected_quantity_sku > $limit_sku)) {
            $error = $this->language->text('Sorry, you cannot have more than %num items per SKU in your cart', array(
                        '%num' => $limit_sku));
            $this->setError('quantity', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Checks product options
     * @param string $options
     * @param array $params
     * @return boolean|string
     */
    public function options($options, array $params = array())
    {
        $product = $params['data'];

        if (empty($product['product_id']) || empty($product['sku'])) {
            return $this->language->text('Invalid product');
        }

        if (empty($options)) {

            return array(
                'result' => array(
                    'cart' => array(
                        'sku' => $product['sku'],
                        'stock' => $product['stock']
                    ))
            );
        }

        $combination_id = $this->sku->getCombinationId($options, $product['product_id']);

        if (empty($product['combination'][$combination_id]['sku'])) {
            return $this->language->text('Invalid option combination');
        }

        return array(
            'result' => array(
                'cart' => array(
                    'combination_id' => $combination_id,
                    'sku' => $product['combination'][$combination_id]['sku'],
                    'stock' => $product['combination'][$combination_id]['stock']
                ))
        );
    }

}
