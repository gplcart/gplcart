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
use core\models\Language as ModelsLanguage;

/**
 * Provides methods to validate cart data
 */
class Cart
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

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
     * @param ModelsLanguage $language
     * @param ModelsCart $cart
     * @param ModelsProduct $product
     * @param ModelsSku $sku
     */
    public function __construct(ModelsLanguage $language, ModelsCart $cart,
            ModelsProduct $product, ModelsSku $sku)
    {
        $this->sku = $sku;
        $this->cart = $cart;
        $this->product = $product;
        $this->language = $language;
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

    /**
     * 
     * @param type $value
     * @param array $options
     * @return type
     */
    public function limits($value, array $options = array())
    {
        if (empty($value)) {
            return false;
        }
        
        $product = $options['data'];
        
        if(empty($product)){
            return false;
        }

        $options += array('increment' => true);

        $sku = $value['sku'];
        $user_id = $value['user_id'];
        $store_id = $value['store_id'];

        $stock = $product['stock'];
        if (isset($value['stock'])) {
            $stock = $value['stock'];
        }

        $quantity = (int) $value['quantity'];
        $conditions = array('user_id' => $user_id, 'store_id' => $store_id);
        $existing_quantity = $this->cart->getQuantity($conditions);

        $expected_quantity_sku = $quantity;
        if (!empty($options['increment']) && isset($existing_quantity['sku'][$sku])) {
            $expected_quantity_sku += $existing_quantity['sku'][$sku];
        }

        if ($product['subtract'] && $expected_quantity_sku > (int) $stock) {
            return $this->language->text('Too low stock level');
        }

        $limit_sku = $this->cart->getLimits('sku');
        $limit_item = $this->cart->getLimits('item');

        if (!empty($limit_item) && !isset($existing_quantity['sku'][$sku]) && (count($existing_quantity['sku']) >= $limit_item)) {
            return $this->language->text('Sorry, you cannot have more than %num items in your cart', array(
                        '%num' => $limit_item));
        }

        if (!empty($limit_sku) && ($expected_quantity_sku > $limit_sku)) {
            return $this->language->text('Sorry, you cannot have more than %num items per SKU in your cart', array(
                        '%num' => $limit_sku));
        }

        return true;
    }

}
