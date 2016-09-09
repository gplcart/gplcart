<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Sku as ModelsSku;
use core\models\Product as ModelsProduct;
use core\models\Language as ModelsLanguage;

/**
 * Provides methods to validate various product data
 */
class Product
{

    /**
     * An array of combination stock levels to be summed up
     * @var array
     */
    protected $stock_amount = array();

    /**
     * An array of processed option combinations
     * @var array
     */
    protected $processed_combinations = array();

    /**
     * An array of processed SKUs
     * @var array
     */
    protected $processed_skus = array();

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

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
     * @param ModelsProduct $product
     * @param ModelsSku $sku
     */
    public function __construct(ModelsLanguage $language,
            ModelsProduct $product, ModelsSku $sku)
    {
        $this->sku = $sku;
        $this->product = $product;
        $this->language = $language;
    }

    /**
     * Checks if a product in the database
     * @param string $value
     * @param array $options
     * @return boolean|string
     */
    public function exists($value, array $options = array())
    {
        if (empty($value) && empty($options['required'])) {
            return true;
        }

        $product = $this->product->get($value);

        if (empty($product)) {
            return $this->language->text('Product does not exist');
        }

        return array('result' => $product);
    }

    public function skuUnique($value, array $options = array())
    {
        if (!empty($value)) {
            $this->processed_skus[$value] = true;
        }

        if (empty($value) && empty($options['required'])) {
            return true;
        }

        if (isset($options['data']['sku']) && ($options['data']['sku'] === $value)) {
            return true;
        }

        $product_id = null;
        if (isset($options['data']['product_id'])) {
            $product_id = $options['data']['product_id'];
        }

        $store_id = $options['submitted']['store_id'];
        $existing = $this->sku->get($value, $store_id, $product_id);

        if (empty($existing)) {
            return true;
        }

        return $this->language->text('SKU must be unique per store');
    }

    /**
     * Validates product attributes
     * @param null|string $value
     * @param array $options
     * @return boolean|array
     */
    public function attributes($value, array $options = array())
    {
        if (empty($options['fields'])) {
            return true;
        }

        $product_fields = $options['fields'];

        if (empty($product_fields['attribute'])) {
            return true;
        }

        $errors = array();
        foreach ($product_fields['attribute'] as $field_id => $field) {

            if (isset($options['submitted']['field']['attribute'][$field_id])) {
                $value = $options['submitted']['field']['attribute'][$field_id];
            }

            if (!empty($field['required']) && empty($value)) {
                $errors['attribute'][$field_id] = $this->language->text('Required field');
            }
        }

        if (empty($errors)) {
            return true;
        }

        return $errors;
    }

    /**
     * Validates submitted option combinations
     * @param null|array $combinations
     * @param array $options
     * @return boolean
     */
    public function combinations($combinations, array $options = array())
    {
        if (empty($combinations)) {
            return true;
        }

        $errors = array();

        foreach ($combinations as $index => &$combination) {

            if (empty($combination['fields'])) {
                unset($combinations[$index]);
                continue;
            }

            $this->validateCombinationOptions($index, $combination, $errors, $options);

            if (isset($errors['combination'][$index])) {
                continue;
            }

            $combination_id = $this->product->getCombinationId($combination['fields']);
            
            if (isset($this->processed_combinations[$combination_id])) {
                $error = $this->language->text('Option combination already defined');
                $errors[$index]['exists'] = $error;
            }

            $this->validateCombinationSku($index, $combination, $errors, $options);
            $this->validateCombinationPrice($index, $combination, $errors, $options);
            $this->validateCombinationStock($index, $combination, $errors, $options);

            foreach ($combination['fields'] as $field_value_id) {
                if (!isset($this->stock_amount[$field_value_id])) {
                    $this->stock_amount[$field_value_id] = (int) $combination['stock'];
                }
            }

            $this->processed_combinations[$combination_id] = true;
        }

        $stock = array_sum($this->stock_amount);

        if (empty($errors)) {
            return array('result' => array(
                    'stock' => $stock, 'combination' => $combinations));
        }
        
        return $errors;
    }

    /**
     * Validates option combination fields
     * @param integer $index
     * @param array $combination
     * @param array $errors
     * @param array $options
     */
    protected function validateCombinationOptions($index, array $combination,
            array &$errors, array $options)
    {
        if (!empty($options['fields']['option'])) {
            foreach ($options['fields']['option'] as $field_id => $field) {
                if (!empty($field['required']) && !isset($combination['fields'][$field_id])) {
                    $errors[$index]['fields'][$field_id] = $this->language->text('Required field');
                }
            }
        }
    }

    /**
     * Validates option combination SKUs
     * @param integer $index
     * @param array $combination
     * @param array $errors
     * @param array $options
     * @return null
     */
    protected function validateCombinationSku($index, array &$combination,
            array &$errors, array $options)
    {
        if (!isset($combination['sku'])) {
            return;
        }

        $store_id = $options['submitted']['store_id'];
        $product_id = isset($options['data']['product_id']) ? $options['data']['product_id'] : null;

        if ($combination['sku'] !== '') {

            if (mb_strlen($combination['sku']) > 255) {
                $error = $this->language->text('Content must not exceed %s characters', array('%s' => 255));
                $errors[$index]['sku'] = $error;
                return;
            }

            if (isset($this->processed_skus[$combination['sku']])) {
                $error = $this->language->text('SKU must be unique per store');
                $errors[$index]['sku'] = $error;
                return;
            }

            if ($this->sku->get($combination['sku'], $store_id, $product_id)) {
                $error = $this->language->text('SKU must be unique per store');
                $errors[$index]['sku'] = $error;
                return;
            }

            $this->processed_skus[$combination['sku']] = true;
            return;
        }

        if (!empty($product_id)) {
            $pattern = $options['submitted']['sku'] . '-' . crc32(uniqid('', true));
            $combination['sku'] = $this->sku->generate($pattern, array(), array('store_id' => $store_id));
            $this->processed_skus[$combination['sku']] = true;
        }
    }

    /**
     * Validates combination stock price
     * @param integer $index
     * @param array $combination
     * @param array $errors
     * @param array $options
     * @return null
     */
    protected function validateCombinationPrice($index, array &$combination,
            array &$errors, array $options)
    {
        if (empty($combination['price'])) {
            $combination['price'] = $options['submitted']['price'];
        }

        if (!is_numeric($combination['price']) || strlen($combination['price']) > 10) {
            $message = $this->language->text('Only numeric values and no longer than %s chars', array('%s' => 10));
            $errors[$index]['price'] = $message;
        }
    }

    /**
     * Validates combination stock level
     * @param integer $index
     * @param array $combination
     * @param array $errors
     * @param array $options
     * @return null
     */
    protected function validateCombinationStock($index, array &$combination,
            array &$errors, array $options)
    {
        if (empty($combination['stock'])) {
            return;
        }

        if (!is_numeric($combination['stock']) || strlen($combination['stock']) > 10) {
            $message = $this->language->text('Only numeric values and no longer than %s chars', array('%s' => 10));
            $errors[$index]['stock'] = $message;
        }
    }

}
