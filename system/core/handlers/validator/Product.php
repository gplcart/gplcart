<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Sku as SkuModel;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\models\Category as CategoryModel;
use gplcart\core\models\ProductClass as ProductClassModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

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
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Category model instance
     * @var \gplcart\core\models\Category $category
     */
    protected $category;

    /**
     * Product class model instance
     * @var \gplcart\core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * Sku model instance
     * @var \gplcart\core\models\Sku $sku
     */
    protected $sku;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
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
     * @return array|boolean
     */
    public function product(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateProduct($options);
        $this->validateSubtractProduct($options);
        $this->validateStatus($options);
        $this->validateCurrencyProduct($options);
        $this->validateCategoryProduct($options);
        $this->validateUnitProduct($options);
        $this->validatePriceProduct($options);
        $this->validateStockProduct($options);
        $this->validateTitle($options);
        $this->validateDescription($options);
        $this->validateMetaTitle($options);
        $this->validateMetaDescription($options);
        $this->validateTranslation($options);
        $this->validateImages($options);
        $this->validateStoreId($options);
        $this->validateUserId($options);
        $this->validateDimensionProduct($options);
        $this->validateRelatedProduct($options);
        $this->validateClassProduct($options);
        $this->validateSkuProduct($options);
        $this->validateAttributeProduct($options);
        $this->validateCombinationProduct($options);
        $this->validateAliasProduct($options);

        return $this->getResult();
    }

    /**
     * Validates a product data
     * @param array $options
     * @return boolean|null
     */
    protected function validateProduct(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->product->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Product'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates "Subtract" bool value
     * @param array $options
     * @return boolean
     */
    protected function validateSubtractProduct(array $options)
    {
        $subtract = $this->getSubmitted('subtract', $options);

        if (isset($subtract)) {
            $subtract = gplcart_string_bool($subtract);
            $this->setSubmitted('subtract', $subtract, $options);
        }

        return true;
    }

    /**
     * Validates currency code
     * @param array $options
     * @return boolean|null
     */
    protected function validateCurrencyProduct(array $options)
    {
        $value = $this->getSubmitted('currency', $options);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Currency'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('currency', $error, $options);
            return false;
        }

        $currency = $this->currency->get($value);

        if (empty($currency)) {
            $vars = array('@name' => $this->language->text('Currency'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('currency', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates product categories
     * @param array $options
     * @return boolean
     */
    protected function validateCategoryProduct(array $options)
    {
        $fields = array(
            'category_id' => $this->language->text('Category'),
            'brand_category_id' => $this->language->text('Brand'),
        );

        foreach ($fields as $field => $name) {

            $value = $this->getSubmitted($field, $options);

            if (!isset($value)) {
                continue;
            }

            if (!is_numeric($value)) {
                $vars = array('@field' => $name);
                $error = $this->language->text('@field must be numeric', $vars);
                $this->setError($field, $error, $options);
                continue;
            }

            if (empty($value)) {
                continue;
            }

            $category = $this->category->get($value);

            if (empty($category['category_id'])) {
                $vars = array('@name' => $name);
                $error = $this->language->text('@name is unavailable', $vars);
                $this->setError($field, $error, $options);
            }
        }

        return !isset($error);
    }

    /**
     * Validates measurement units
     * @param array $options
     * @return boolean
     */
    protected function validateUnitProduct(array $options)
    {
        $allowed = array(
            'volume_unit' => array('mm', 'in', 'cm'),
            'weight_unit' => array('g', 'kg', 'lb', 'oz')
        );

        $fields = array(
            'volume_unit' => $this->language->text('Volume unit'),
            'weight_unit' => $this->language->text('Weight unit')
        );

        foreach ($fields as $field => $name) {

            $value = $this->getSubmitted($field, $options);

            if (!isset($value)) {
                continue;
            }

            if (!in_array($value, $allowed[$field])) {
                $vars = array('@name' => $name);
                $error = $this->language->text('@name is unavailable', $vars);
                $this->setError($field, $error, $options);
            }
        }

        return !isset($error);
    }

    /**
     * Validates a product price
     * @param array $options
     * @return boolean|null
     */
    protected function validatePriceProduct(array $options)
    {
        $value = $this->getSubmitted('price', $options);

        if (!isset($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Price'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('price', $error, $options);
            return false;
        }

        if (strlen($value) > 8) { // Major units
            $vars = array('@max' => 8, '@field' => $this->language->text('Price'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('price', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a product price
     * @param array $options
     * @return boolean|null
     */
    protected function validateStockProduct(array $options)
    {
        $value = $this->getSubmitted('stock', $options);

        if (!isset($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Stock'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('stock', $error, $options);
            return false;
        }

        if (strlen($value) > 10) {
            $vars = array('@max' => 10, '@field' => $this->language->text('Stock'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('stock', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates product dimensions
     * @param array $options
     * @return boolean
     */
    protected function validateDimensionProduct(array $options)
    {
        $fields = array(
            'width' => $this->language->text('Width'),
            'height' => $this->language->text('Height'),
            'length' => $this->language->text('Length'),
            'weight' => $this->language->text('Weight')
        );

        foreach ($fields as $field => $name) {

            $value = $this->getSubmitted($field, $options);

            if (!isset($value)) {
                continue;
            }

            if (!is_numeric($value)) {
                $vars = array('@field' => $name);
                $error = $this->language->text('@field must be numeric', $vars);
                $this->setError($field, $error, $options);
            }

            if (strlen($value) > 10) {
                $vars = array('@max' => 10, '@field' => $name);
                $error = $this->language->text('@field must not be longer than @max characters', $vars);
                $this->setError($field, $error, $options);
            }
        }

        return !isset($error);
    }

    /**
     * Validates/creates an alias
     * @param array $options
     * @return boolean|null
     */
    protected function validateAliasProduct(array $options)
    {
        if ($this->isError()) {
            return null;
        }

        $updating = $this->getUpdating();
        $value = $this->getSubmitted('alias', $options);

        if (isset($value)//
                && isset($updating['alias'])//
                && ($updating['alias'] === $value)) {
            return true; // Do not check own alias on update
        }

        if (empty($value) && $this->isUpdating()) {
            $data = $this->getSubmitted();
            $value = $this->product->createAlias($data);
            $this->setSubmitted('alias', $value, $options);
            return true;
        }

        return $this->validateAlias($options);
    }

    /**
     * Validates related products
     * @param array $options
     * @return boolean|null
     */
    protected function validateRelatedProduct(array $options)
    {
        $value = $this->getSubmitted('related', $options);

        if (empty($value)) {
            $this->setSubmitted('related', array(), $options);
            return null;
        }

        // Remove duplicates
        $modified = array_flip($value);

        // Exclude the current product from related products
        $updating = $this->getUpdating();

        if (isset($updating['product_id'])) {
            unset($modified[$updating['product_id']]);
        }

        // Set filtered product IDs
        $this->setSubmitted('related', array_flip($modified), $options);
        return true;
    }

    /**
     * Validates a product SKU
     * @param array $options
     * @return boolean|null
     */
    protected function validateSkuProduct(array $options)
    {
        if ($this->isError()) {
            return null;
        }

        $value = $this->getSubmitted('sku', $options);

        if ($this->isUpdating() && empty($value)) {
            $data = $this->getSubmitted();
            $value = $this->product->createSku($data);
            $this->setSubmitted('sku', $value, $options);
        }

        if (isset($value) && mb_strlen($value) > 255) {
            $vars = array('@max' => 255, '@field' => $this->language->text('SKU'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('sku', $error, $options);
            return false;
        }

        if (!empty($value)) {
            $this->processed_skus[$value] = true;
        }

        $updating = $this->getUpdating();

        if (isset($updating['sku']) && ($updating['sku'] === $value)) {
            return true;
        }

        $product_id = null;
        if (isset($updating['product_id'])) {
            $product_id = $updating['product_id'];
        }

        $store_id = $this->getSubmitted('store_id', $options);

        if (isset($updating['store_id'])) {
            $store_id = $updating['store_id'];
        }

        $existing = $this->sku->get($value, $store_id, $product_id);

        if (!empty($existing)) {
            $vars = array('@object' => $this->language->text('SKU'));
            $error = $this->language->text('@object already exists', $vars);
            $this->setError('sku', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a product class
     * @param array $options
     * @return boolean|null
     */
    protected function validateClassProduct(array $options)
    {
        $value = $this->getSubmitted('product_class_id', $options);

        if (!isset($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Product class'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('product_class_id', $error, $options);
            return false;
        }

        $product_class = $this->product_class->get($value);

        if (empty($product_class)) {
            $vars = array('@name' => $this->language->text('Product class'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('product_class_id', $error, $options);
            return false;
        }

        $fields = $this->product_class->getFieldData($value);
        $this->setSubmitted('product_fields', $fields);
        return true;
    }

    /**
     * Validates an array of product attributes
     * @param array $options
     * @return boolean|null
     */
    protected function validateAttributeProduct(array $options)
    {
        $attributes = $this->getSubmitted('product_fields.attribute');

        if ($this->isError() || empty($attributes)) {
            return null;
        }

        foreach ($attributes as $field_id => $field) {

            if (isset($attributes[$field_id])) {
                $value = $attributes[$field_id];
            }

            if (!empty($field['required']) && empty($value)) {
                $vars = array('@field' => $this->language->text('Field'));
                $error = $this->language->text('@field is required', $vars);
                $this->setError("attribute.$field_id", $error, $options);
            }
        }

        return $this->isError('attribute', $options);
    }

    /**
     * Validates an array of product combinations
     * @param array $options
     * @return boolean|null
     */
    protected function validateCombinationProduct(array $options)
    {
        $fields = $this->getSubmitted('product_fields');

        if ($this->isError() || empty($fields)) {
            return null;
        }

        $combinations = $this->getSubmitted('combination', $options);

        if (empty($combinations)) {
            return null;
        }

        foreach ($combinations as $index => &$combination) {

            if (empty($combination['fields'])) {
                unset($combinations[$index]);
                continue;
            }

            $this->validateCombinationOptionsProduct($index, $combination, $options);

            if ($this->isError("combination.$index", $options)) {
                continue;
            }

            $combination_id = $this->sku->getCombinationId($combination['fields']);

            if (isset($this->processed_combinations[$combination_id])) {
                $error = $this->language->text('Option combination already defined');
                $this->setError("combination.$index.exists", $error, $options);
            }

            $this->validateCombinationSkuProduct($index, $combination, $options);
            $this->validateCombinationPriceProduct($index, $combination, $options);
            $this->validateCombinationStockProduct($index, $combination, $options);

            foreach ($combination['fields'] as $field_value_id) {
                if (!isset($this->stock_amount[$field_value_id])) {
                    $this->stock_amount[$field_value_id] = (int) $combination['stock'];
                }
            }

            $this->processed_combinations[$combination_id] = true;
        }

        if ($this->isError()) {
            return false;
        }

        $this->setSubmitted('combination', $combinations, $options);
        $this->setSubmitted('stock', array_sum($this->stock_amount), $options);
        return true;
    }

    /**
     * Validates option combination fields
     * @param integer $index
     * @param array $combination
     * @param array $options
     * @return boolean|null
     */
    protected function validateCombinationOptionsProduct($index,
            array &$combination, array $options)
    {
        $op = $this->getSubmitted('product_fields.option');

        if (empty($op)) {
            return null;
        }

        foreach ($op as $field_id => $field) {
            if (!empty($field['required']) && !isset($combination['fields'][$field_id])) {
                $vars = array('@field' => $this->language->text('Field'));
                $error = $this->language->text('@field is required', $vars);
                $this->setError("combination.$index.fields.$field_id", $error, $options);
            }
        }

        return !isset($error);
    }

    /**
     * Validates option combination SKUs
     * @param integer $index
     * @param array $combination
     * @param array $options
     * @return boolean|null
     */
    protected function validateCombinationSkuProduct($index,
            array &$combination, array $options)
    {
        if (!isset($combination['sku'])) {
            return null;
        }

        $updating = $this->getUpdating();

        $product_id = null;
        if (isset($updating['product_id'])) {
            $product_id = $updating['product_id'];
        }

        $sku = $this->getSubmitted('sku', $options);
        $store_id = $this->getSubmitted('store_id', $options);

        if ($combination['sku'] !== '') {

            if (mb_strlen($combination['sku']) > 255) {
                $options = array('@max' => 255, '@field' => $this->language->text('SKU'));
                $error = $this->language->text('@field must not be longer than @max characters', $options);
                $this->setError("combination.$index.sku", $error, $options);
                return false;
            }

            if (isset($this->processed_skus[$combination['sku']])) {
                $error = $this->language->text('SKU must be unique per store');
                $this->setError("combination.$index.sku", $error, $options);
                return false;
            }

            if ($this->sku->get($combination['sku'], $store_id, $product_id)) {
                $error = $this->language->text('SKU must be unique per store');
                $this->setError("combination.$index.sku", $error, $options);
                return false;
            }

            $this->processed_skus[$combination['sku']] = true;
            return true;
        }

        if (!empty($product_id)) {
            $pattern = "$sku-" . crc32(uniqid('', true));
            $options = array('store_id' => $store_id);
            $combination['sku'] = $this->sku->generate($pattern, array(), $options);
            $this->processed_skus[$combination['sku']] = true;
        }

        return true;
    }

    /**
     * Validates combination stock price
     * @param integer $index
     * @param array $combination
     * @param array $options
     * @return boolean
     */
    protected function validateCombinationPriceProduct($index,
            array &$combination, array $options)
    {
        $price = $this->getSubmitted('price', $options);

        if (empty($combination['price'])) {
            $combination['price'] = $price;
        }

        if (!is_numeric($combination['price']) || strlen($combination['price']) > 10) {
            $error = $this->language->text('Only numeric values and no longer than @num characters', array('@num' => 10));
            $this->setError("combination.$index.price", $error, $options);
        }

        return !isset($error);
    }

    /**
     * Validates combination stock level
     * @param integer $index
     * @param array $combination
     * @param array $options
     * @return null|boolean
     */
    protected function validateCombinationStockProduct($index,
            array &$combination, array $options)
    {
        if (empty($combination['stock'])) {
            return null;
        }

        if (!is_numeric($combination['stock']) || strlen($combination['stock']) > 10) {
            $error = $this->language->text('Only numeric values and no longer than %s chars', array('%s' => 10));
            $this->setError("combination.$index.stock", $error, $options);
        }

        return !isset($error);
    }

}
