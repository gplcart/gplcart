<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\helpers\String;
use core\models\Sku as SkuModel;
use core\models\Product as ProductModel;
use core\models\Currency as CurrencyModel;
use core\models\Category as CategoryModel;
use core\models\ProductClass as ProductClassModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate a product data
 */
class Product extends BaseValidator
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
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Category model instance
     * @var \core\models\Category $category
     */
    protected $category;

    /**
     * Product class model instance
     * @var \core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * Sku model instance
     * @var \core\models\Sku $sku
     */
    protected $sku;

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Constructor
     * @param ProductModel $product
     * @param ProductClassModel $product_class
     * @param SkuModel $sku
     * @param CurrencyModel $currency
     * @param CategoryModel $category
     */
    public function __construct(ProductModel $product,
            ProductClassModel $product_class, SkuModel $sku,
            CurrencyModel $currency, CategoryModel $category)
    {
        parent::__construct();

        $this->sku = $sku;
        $this->product = $product;
        $this->currency = $currency;
        $this->category = $category;
        $this->product_class = $product_class;
    }

    /**
     * Performs full product data validation
     * @param array $submitted
     * @param array $options
     */
    public function product(array &$submitted, array $options = array())
    {
        $this->validateProduct($submitted);
        $this->validateSubtractProduct($submitted);
        $this->validateStatus($submitted);
        $this->validateCurrencyProduct($submitted);
        $this->validateCategoryProduct($submitted);
        $this->validateUnitProduct($submitted);
        $this->validatePriceProduct($submitted);
        $this->validateStockProduct($submitted);
        $this->validateTitle($submitted);
        $this->validateDescription($submitted);
        $this->validateMetaTitle($submitted);
        $this->validateMetaDescription($submitted);
        $this->validateTranslation($submitted);
        $this->validateImages($submitted);
        $this->validateStoreId($submitted);
        $this->validateUserId($submitted);
        $this->validateDimensionProduct($submitted);
        $this->validateRelatedProduct($submitted);
        $this->validateClassProduct($submitted);
        $this->validateSkuProduct($submitted);
        $this->validateAttributeProduct($submitted);
        $this->validateCombinationProduct($submitted);
        $this->validateAliasProduct($submitted);

        return $this->getResult();
    }

    /**
     * Validates a product data
     * @param array $submitted
     * @return boolean
     */
    protected function validateProduct(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {
            $data = $this->product->get($submitted['update']);
            if (empty($data)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Product')));
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

    /**
     * Validates "Subtract" bool value
     * @param array $submitted
     * @return boolean
     */
    protected function validateSubtractProduct(array &$submitted)
    {
        if (isset($submitted['subtract'])) {
            $submitted['subtract'] = String::toBool($submitted['subtract']);
        }

        return true;
    }

    /**
     * Validates currency code
     * @param array $submitted
     * @return boolean
     */
    protected function validateCurrencyProduct(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['currency'])) {
            return null;
        }

        if (empty($submitted['currency'])) {
            $this->errors['currency'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Currency')
            ));
            return false;
        }

        $currency = $this->currency->get($submitted['currency']);

        if (empty($currency)) {
            $this->errors['currency'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Currency')));
            return false;
        }

        return true;
    }

    /**
     * Validates product categories
     * @param array $submitted
     * @return boolean
     */
    protected function validateCategoryProduct(array &$submitted)
    {
        $fields = array('category_id', 'brand_category_id');

        $error = false;
        foreach ($fields as $field) {

            if (!isset($submitted[$field])) {
                continue;
            }

            $name = ucfirst(strtok($field, '_'));

            if (!is_numeric($submitted[$field])) {
                $error = true;
                $options = array('@field' => $this->language->text($name));
                $this->errors[$field] = $this->language->text('@field must be numeric', $options);
                continue;
            }

            if (empty($submitted[$field])) {
                continue;
            }

            $category = $this->category->get($submitted[$field]);

            if (empty($category)) {
                $error = true;
                $this->errors[$field] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text($name)));
            }
        }

        return !$error;
    }

    /**
     * Validates measurement units
     * @param array $submitted
     * @return boolean
     */
    protected function validateUnitProduct(array &$submitted)
    {
        $fields = array(
            'volume_unit' => array('mm', 'in', 'cm'),
            'weight_unit' => array('g', 'kg', 'lb', 'oz')
        );

        $error = false;
        foreach ($fields as $field => $units) {

            if (!isset($submitted[$field])) {
                continue;
            }

            $name = ucfirst(strtok($field, '_'));

            if (!in_array($submitted[$field], $units)) {
                $error = true;
                $this->errors[$field] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text($name)));
            }
        }

        return !$error;
    }

    /**
     * Validates a product price
     * @param array $submitted
     * @return boolean
     */
    protected function validatePriceProduct(array $submitted)
    {
        if (!isset($submitted['price'])) {
            return null;
        }

        if (!is_numeric($submitted['price'])) {
            $options = array('@field' => $this->language->text('Price'));
            $this->errors['price'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        if (strlen($submitted['price']) > 8) { // Major units
            $options = array('@max' => 8, '@field' => $this->language->text('Price'));
            $this->errors['price'] = $this->language->text('@field must not be longer than @max characters', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a product price
     * @param array $submitted
     * @return boolean
     */
    protected function validateStockProduct(array $submitted)
    {
        if (!isset($submitted['stock'])) {
            return null;
        }

        if (!is_numeric($submitted['stock'])) {
            $options = array('@field' => $this->language->text('Stock'));
            $this->errors['stock'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        if (strlen($submitted['stock']) > 10) {
            $options = array('@max' => 10, '@field' => $this->language->text('Stock'));
            $this->errors['stock'] = $this->language->text('@field must not be longer than @max characters', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates product dimensions
     * @param array $submitted
     * @return boolean
     */
    protected function validateDimensionProduct(array $submitted)
    {
        $fields = array('width', 'height', 'length', 'weight');

        $error = false;
        foreach ($fields as $field) {

            if (!isset($submitted[$field])) {
                continue;
            }

            if (!is_numeric($submitted[$field])) {
                $error = true;
                $options = array('@field' => $this->language->text(ucfirst($field)));
                $this->errors[$field] = $this->language->text('@field must be numeric', $options);
            }

            if (strlen($submitted[$field]) > 10) {
                $error = true;
                $options = array('@max' => 10, '@field' => $this->language->text(ucfirst($field)));
                $this->errors[$field] = $this->language->text('@field must not be longer than @max characters', $options);
            }
        }

        return !$error;
    }

    /**
     * Validates / creates an alias
     * @param array $submitted
     * @return boolean
     */
    protected function validateAliasProduct(array &$submitted)
    {
        if (!empty($this->errors)) {
            return null;
        }

        if (isset($submitted['alias'])//
                && isset($submitted['update']['alias'])//
                && ($submitted['update']['alias'] === $submitted['alias'])) {
            return true; // Do not check own alias on update
        }

        if (empty($submitted['alias']) && !empty($submitted['update'])) {
            $submitted['alias'] = $this->product->createAlias($submitted);
            return true;
        }

        return $this->validateAlias($submitted);
    }

    /**
     * Validates related products
     * @param array $submitted
     * @return boolean
     */
    protected function validateRelatedProduct(array &$submitted)
    {
        if (empty($submitted['related'])) {
            $submitted['related'] = array();
            return null;
        }

        // Remove duplicates
        $modified = array_flip($submitted['related']);

        if (isset($submitted['update']['product_id'])) {
            // Exclude the current product from related products
            unset($modified[$submitted['update']['product_id']]);
        }

        $submitted['related'] = array_flip($modified);
        return true;
    }

    /**
     * Validates a product SKU
     * @param array $submitted
     * @return boolean
     */
    protected function validateSkuProduct(array &$submitted)
    {
        if (!empty($this->errors)) {
            return null;
        }

        if (!empty($submitted['update']) && empty($submitted['sku'])) {
            $submitted['sku'] = $this->product->createSku($submitted);
        }

        if (isset($submitted['sku']) && mb_strlen($submitted['sku']) > 255) {
            $options = array('@max' => 255, '@field' => $this->language->text('SKU'));
            $this->errors['sku'] = $this->language->text('@field must not be longer than @max characters', $options);
            return false;
        }

        if (!empty($submitted['sku'])) {
            $this->processed_skus[$submitted['sku']] = true;
        }

        if (isset($submitted['update']['sku'])//
                && ($submitted['update']['sku'] === $submitted['sku'])) {
            return true;
        }

        $product_id = null;
        if (isset($submitted['update']['product_id'])) {
            $product_id = $submitted['update']['product_id'];
        }

        $store_id = null;
        if (isset($submitted['store_id'])) {
            $store_id = $submitted['store_id'];
        } else if (isset($submitted['update']['store_id'])) {
            $store_id = $submitted['update']['store_id'];
        }

        $existing = $this->sku->get($submitted['sku'], $store_id, $product_id);

        if (empty($existing)) {
            return true;
        }

        $this->errors['sku'] = $this->language->text('@object already exists', array(
            '@object' => $this->language->text('SKU')));
        return false;
    }

    /**
     * Validates a product class
     * @param array $submitted
     * @return boolean
     */
    protected function validateClassProduct(array &$submitted)
    {
        if (!isset($submitted['product_class_id'])) {
            return null;
        }

        if (!is_numeric($submitted['product_class_id'])) {
            $options = array('@field' => $this->language->text('Product class'));
            $this->errors['product_class_id'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        $product_class = $this->product_class->get($submitted['product_class_id']);

        if (empty($product_class)) {
            $this->errors['product_class_id'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Product class')));
            return false;
        }

        $submitted['product_fields'] = $this->product_class->getFieldData($submitted['product_class_id']);
        return true;
    }

    /**
     * Validates an array of product attributes
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateAttributeProduct(array &$submitted)
    {
        if (!empty($this->errors) || empty($submitted['product_fields']['attribute'])) {
            return null;
        }

        foreach ($submitted['product_fields']['attribute'] as $field_id => $field) {

            if (isset($submitted['field']['attribute'][$field_id])) {
                $value = $submitted['field']['attribute'][$field_id];
            }

            if (!empty($field['required']) && empty($value)) {
                $this->errors['attribute'][$field_id] = $this->language->text('Required field');
            }
        }

        return empty($this->errors['attribute']);
    }

    /**
     * Validates an array of product combinations
     * @param array $submitted
     * @return boolean
     */
    protected function validateCombinationProduct(array &$submitted)
    {
        if (!empty($this->errors) || empty($submitted['product_fields'])) {
            return null;
        }

        if (empty($submitted['combination'])) {
            return null;
        }

        foreach ($submitted['combination'] as $index => &$combination) {

            if (empty($combination['fields'])) {
                unset($submitted['combination'][$index]);
                continue;
            }

            $this->validateCombinationOptionsProduct($index, $combination, $submitted);

            if (isset($this->errors['combination'][$index])) {
                continue;
            }

            $combination_id = $this->sku->getCombinationId($combination['fields']);

            if (isset($this->processed_combinations[$combination_id])) {
                $error = $this->language->text('Option combination already defined');
                $this->errors['combination'][$index]['exists'] = $error;
            }

            $this->validateCombinationSkuProduct($index, $combination, $submitted);
            $this->validateCombinationPriceProduct($index, $combination, $submitted);
            $this->validateCombinationStockProduct($index, $combination, $submitted);

            foreach ($combination['fields'] as $field_value_id) {
                if (!isset($this->stock_amount[$field_value_id])) {
                    $this->stock_amount[$field_value_id] = (int) $combination['stock'];
                }
            }

            $this->processed_combinations[$combination_id] = true;
        }

        if (empty($this->errors)) {
            $submitted['stock'] = array_sum($this->stock_amount);
            $submitted['combination'] = $submitted['combination'];
            return true;
        }

        return false;
    }

    /**
     * Validates option combination fields
     * @param integer $index
     * @param array $combination
     * @param array $submitted
     * @return boolean
     */
    protected function validateCombinationOptionsProduct($index,
            array $combination, array $submitted)
    {
        if (empty($submitted['product_fields']['option'])) {
            return null;
        }

        $error = false;
        foreach ($submitted['product_fields']['option'] as $field_id => $field) {
            if (!empty($field['required']) && !isset($combination['fields'][$field_id])) {
                $this->errors['combination'][$index]['fields'][$field_id] = $this->language->text('Required field');
                $error = true;
            }
        }

        return !$error;
    }

    /**
     * Validates option combination SKUs
     * @param insteger $index
     * @param array $combination
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateCombinationSkuProduct($index,
            array &$combination, array $submitted)
    {
        if (!isset($combination['sku'])) {
            return null;
        }

        $product_id = null;
        if (isset($submitted['update']['product_id'])) {
            $product_id = $submitted['update']['product_id'];
        }

        if ($combination['sku'] !== '') {

            if (mb_strlen($combination['sku']) > 255) {
                $options = array('@max' => 255, '@field' => $this->language->text('SKU'));
                $error = $this->language->text('@field must not be longer than @max characters', $options);
                $this->errors['combination'][$index]['sku'] = $error;
                return false;
            }

            if (isset($this->processed_skus[$combination['sku']])) {
                $error = $this->language->text('SKU must be unique per store');
                $this->errors['combination'][$index]['sku'] = $error;
                return false;
            }

            if ($this->sku->get($combination['sku'], $submitted['store_id'], $product_id)) {
                $error = $this->language->text('SKU must be unique per store');
                $this->errors['combination'][$index]['sku'] = $error;
                return false;
            }

            $this->processed_skus[$combination['sku']] = true;
            return true;
        }

        if (!empty($product_id)) {
            $pattern = $submitted['sku'] . '-' . crc32(uniqid('', true));
            $options = array('store_id' => $submitted['store_id']);
            $combination['sku'] = $this->sku->generate($pattern, array(), $options);
            $this->processed_skus[$combination['sku']] = true;
        }

        return true;
    }

    /**
     * Validates combination stock price
     * @param integer $index
     * @param array $combination
     * @param array $submitted
     * @return boolean
     */
    protected function validateCombinationPriceProduct($index,
            array &$combination, array $submitted)
    {
        if (empty($combination['price'])) {
            $combination['price'] = $submitted['price'];
        }

        $error = false;
        if (!is_numeric($combination['price']) || strlen($combination['price']) > 10) {
            $message = $this->language->text('Only numeric values and no longer than @num characters', array('@num' => 10));
            $this->errors['combination'][$index]['price'] = $message;
            $error = true;
        }

        return !$error;
    }

    /**
     * Validates combination stock level
     * @param integer $index
     * @param array $combination
     * @param array $submitted
     * @return null|boolean
     */
    protected function validateCombinationStockProduct($index,
            array &$combination, array $submitted)
    {
        if (empty($combination['stock'])) {
            return null;
        }

        $error = false;
        if (!is_numeric($combination['stock']) || strlen($combination['stock']) > 10) {
            $message = $this->language->text('Only numeric values and no longer than %s chars', array('%s' => 10));
            $this->errors['combination'][$index]['stock'] = $message;
            $error = true;
        }

        return !$error;
    }

}
