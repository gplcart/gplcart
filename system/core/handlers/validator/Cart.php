<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

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
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsCart $cart
     * @param ModelsProduct $product
     */
    public function __construct(ModelsLanguage $language, ModelsCart $cart,
            ModelsProduct $product)
    {

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
                    'sku' => $product['sku'],
                    'stock' => $product['stock']
            ));
        }

        $combination_id = $this->product->getCombinationId($options, $product['product_id']);

        if (empty($product['combination'][$combination_id]['sku'])) {
            return $this->language->text('Invalid option combination');
        }

        return array(
            'result' => array(
                'combination_id' => $combination_id,
                'sku' => $product['combination'][$combination_id]['sku'],
                'stock' => $product['combination'][$combination_id]['stock']
        ));
    }

    /**
     * 
     * @param type $value
     * @param array $options
     * @return type
     */
    public function limits($value, array $options = array())
    {
        $product = $options['data'];
        $user_id = $options['submitted']['user_id'];
        $stock = (int) $options['submitted']['stock'];
        $quantity = (int) $options['submitted']['quantity'];

        $skus = 1;
        $total = $quantity;
        $items = $this->cart->getList(array('user_id' => $user_id));

        foreach ($items as $item) {
            $total += (int) $item['quantity'];
            $skus++;
        }

        if ($product['subtract'] && $quantity > $stock) {
            return $this->language->text('Too low stock level');
        }

        $limit_sku = $this->cart->getLimits('sku');
        $limit_total = $this->cart->getLimits('total');

        if (!empty($limit_sku) && ($skus > $limit_sku)) {
            return $this->language->text('Sorry, you cannot have more than %num items per SKU in your cart', array(
                        '%num' => $limit_sku));
        }

        if (!empty($limit_total) && ($total > $limit_total)) {
            return $this->language->text('Sorry, you cannot have more than %num items in your cart', array(
                        '%num' => $limit_total));
        }

        return true;
    }

}
