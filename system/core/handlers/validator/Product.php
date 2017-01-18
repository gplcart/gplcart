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
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateProduct();
        $this->validateSubtractProduct();
        $this->validateStatus();
        $this->validateCurrencyProduct();
        $this->validateCategoryProduct();
        $this->validateUnitProduct();
        $this->validatePriceProduct();
        $this->validateStockProduct();
        $this->validateTitle();
        $this->validateDescription();
        $this->validateMetaTitle();
        $this->validateMetaDescription();
        $this->validateTranslation();
        $this->validateImages();
        $this->validateStoreId();
        $this->validateUserId();
        $this->validateDimensionProduct();
        $this->validateRelatedProduct();
        $this->validateClassProduct();
        $this->validateSkuProduct();
        $this->validateAttributeProduct();
        $this->validateCombinationProduct();
        $this->validateAliasProduct();

        return $this->getResult();
    }

    /**
     * Validates a product data
     * @return boolean|null
     */
    protected function validateProduct()
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
     * @return boolean
     */
    protected function validateSubtractProduct()
    {
        $subtract = $this->getSubmitted('subtract');

        if (isset($subtract)) {
            $subtract = gplcart_string_bool($subtract);
            $this->setSubmitted('subtract', $subtract);
        }

        return true;
    }

    /**
     * Validates currency code
     * @return boolean|null
     */
    protected function validateCurrencyProduct()
    {
        $value = $this->getSubmitted('currency');

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Currency'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('currency', $error);
            return false;
        }

        $currency = $this->currency->get($value);

        if (empty($currency)) {
            $vars = array('@name' => $this->language->text('Currency'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('currency', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates product categories
     * @return boolean
     */
    protected function validateCategoryProduct()
    {
        $fields = array(
            'category_id' => $this->language->text('Category'),
            'brand_category_id' => $this->language->text('Brand'),
        );

        foreach ($fields as $field => $name) {

            $value = $this->getSubmitted($field);

            if (!isset($value)) {
                continue;
            }

            if (!is_numeric($value)) {
                $vars = array('@field' => $name);
                $error = $this->language->text('@field must be numeric', $vars);
                $this->setError($field, $error);
                continue;
            }

            if (empty($value)) {
                continue;
            }

            $category = $this->category->get($value);

            if (empty($category['category_id'])) {
                $vars = array('@name' => $name);
                $error = $this->language->text('@name is unavailable', $vars);
                $this->setError($field, $error);
            }
        }

        return !isset($error);
    }

    /**
     * Validates measurement units
     * @return boolean
     */
    protected function validateUnitProduct()
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

            $value = $this->getSubmitted($field);

            if (!isset($value)) {
                continue;
            }

            if (!in_array($value, $allowed[$field])) {
                $vars = array('@name' => $name);
                $error = $this->language->text('@name is unavailable', $vars);
                $this->setError($field, $error);
            }
        }

        return !isset($error);
    }

    /**
     * Validates a product price
     * @return boolean|null
     */
    protected function validatePriceProduct()
    {
        $value = $this->getSubmitted('price');

        if (!isset($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Price'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('price', $error);
            return false;
        }

        if (strlen($value) > 8) { // Major units
            $vars = array('@max' => 8, '@field' => $this->language->text('Price'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('price', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a product price
     * @return boolean|null
     */
    protected function validateStockProduct()
    {
        $value = $this->getSubmitted('stock');

        if (!isset($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Stock'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('stock', $error);
            return false;
        }

        if (strlen($value) > 10) {
            $vars = array('@max' => 10, '@field' => $this->language->text('Stock'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('stock', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates product dimensions
     * @return boolean
     */
    protected function validateDimensionProduct()
    {
        $fields = array(
            'width' => $this->language->text('Width'),
            'height' => $this->language->text('Height'),
            'length' => $this->language->text('Length'),
            'weight' => $this->language->text('Weight')
        );

        foreach ($fields as $field => $name) {

            $value = $this->getSubmitted($field);

            if (!isset($value)) {
                continue;
            }

            if (!is_numeric($value)) {
                $vars = array('@field' => $name);
                $error = $this->language->text('@field must be numeric', $vars);
                $this->setError($field, $error);
            }

            if (strlen($value) > 10) {
                $vars = array('@max' => 10, '@field' => $name);
                $error = $this->language->text('@field must not be longer than @max characters', $vars);
                $this->setError($field, $error);
            }
        }

        return !isset($error);
    }

    /**
     * Validates/creates an alias
     * @return boolean|null
     */
    protected function validateAliasProduct()
    {
        if ($this->isError()) {
            return null;
        }

        $updating = $this->getUpdating();
        $value = $this->getSubmitted('alias');

        if (isset($value)//
                && isset($updating['alias'])//
                && ($updating['alias'] === $value)) {
            return true; // Do not check own alias on update
        }

        if (empty($value) && $this->isUpdating()) {
            $data = $this->getSubmitted();
            $value = $this->product->createAlias($this->alias, $data, 'product');
            $this->setSubmitted('alias', $value);
            return true;
        }

        return $this->validateAlias();
    }

    /**
     * Validates related products
     * @return boolean|null
     */
    protected function validateRelatedProduct()
    {
        $value = $this->getSubmitted('related');

        if (empty($value)) {
            $this->setSubmitted('related', array());
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
        $this->setSubmitted('related', array_flip($modified));
        return true;
    }

    /**
     * Validates a product SKU
     * @return boolean|null
     */
    protected function validateSkuProduct()
    {
        if ($this->isError()) {
            return null;
        }

        $value = $this->getSubmitted('sku');

        if ($this->isUpdating() && empty($value)) {
            $data = $this->getSubmitted();
            $value = $this->product->createSku($data);
            $this->setSubmitted('sku', $value);
        }

        if (isset($value) && mb_strlen($value) > 255) {
            $vars = array('@max' => 255, '@field' => $this->language->text('SKU'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('sku', $error);
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

        $store_id = $this->getSubmitted('store_id');

        if (isset($updating['store_id'])) {
            $store_id = $updating['store_id'];
        }

        $existing = $this->sku->get($value, $store_id, $product_id);

        if (!empty($existing)) {
            $vars = array('@object' => $this->language->text('SKU'));
            $error = $this->language->text('@object already exists', $vars);
            $this->setError('sku', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a product class
     * @return boolean|null
     */
    protected function validateClassProduct()
    {
        $value = $this->getSubmitted('product_class_id');

        if (empty($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Product class'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('product_class_id', $error);
            return false;
        }

        $product_class = $this->product_class->get($value);

        if (empty($product_class)) {
            $vars = array('@name' => $this->language->text('Product class'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('product_class_id', $error);
            return false;
        }

        $fields = $this->product_class->getFieldData($value);
        $this->setSubmitted('product_fields', $fields);
        return true;
    }

    /**
     * Validates an array of product attributes
     * @return boolean|null
     */
    protected function validateAttributeProduct()
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
                $this->setError("attribute.$field_id", $error);
            }
        }

        return $this->isError('attribute');
    }

    /**
     * Validates an array of product combinations
     * @return boolean|null
     */
    protected function validateCombinationProduct()
    {
        $fields = $this->getSubmitted('product_fields');

        if ($this->isError() || empty($fields)) {
            return null;
        }

        $combinations = $this->getSubmitted('combination');

        if (empty($combinations)) {
            return null;
        }

        foreach ($combinations as $index => &$combination) {

            if (empty($combination['fields'])) {
                unset($combinations[$index]);
                continue;
            }

            $this->validateCombinationOptionsProduct($index, $combination);

            if ($this->isError("combination.$index")) {
                continue;
            }

            $combination_id = $this->sku->getCombinationId($combination['fields']);

            if (isset($this->processed_combinations[$combination_id])) {
                $error = $this->language->text('Combination already exists');
                $this->setError("combination.$index.exists", $error);
            }

            $this->validateCombinationSkuProduct($index, $combination);
            $this->validateCombinationPriceProduct($index, $combination);
            $this->validateCombinationStockProduct($index, $combination);

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

        $this->setSubmitted('combination', $combinations);
        $this->setSubmitted('stock', array_sum($this->stock_amount));
        return true;
    }

    /**
     * Validates option combination fields
     * @param integer $index
     * @param array $combination
     * @return boolean|null
     */
    protected function validateCombinationOptionsProduct($index,
            array &$combination)
    {
        $op = $this->getSubmitted('product_fields.option');

        if (empty($op)) {
            return null;
        }

        foreach ($op as $field_id => $field) {
            if (!empty($field['required']) && !isset($combination['fields'][$field_id])) {
                $vars = array('@field' => $this->language->text('Field'));
                $error = $this->language->text('@field is required', $vars);
                $this->setError("combination.$index.fields.$field_id", $error);
            }
        }

        return !isset($error);
    }

    /**
     * Validates option combination SKUs
     * @param integer $index
     * @param array $combination
     * @return boolean|null
     */
    protected function validateCombinationSkuProduct($index, array &$combination)
    {
        if (!isset($combination['sku'])) {
            return null;
        }

        $updating = $this->getUpdating();

        $product_id = null;
        if (isset($updating['product_id'])) {
            $product_id = $updating['product_id'];
        }

        $sku = $this->getSubmitted('sku');
        $store_id = $this->getSubmitted('store_id');

        if ($combination['sku'] !== '') {

            if (mb_strlen($combination['sku']) > 255) {
                $vars = array('@max' => 255, '@field' => $this->language->text('SKU'));
                $error = $this->language->text('@field must not be longer than @max characters', $vars);
                $this->setError("combination.$index.sku", $error);
                return false;
            }

            if (isset($this->processed_skus[$combination['sku']])) {
                $error = $this->language->text('SKU must be unique per store');
                $this->setError("combination.$index.sku", $error);
                return false;
            }

            if ($this->sku->get($combination['sku'], $store_id, $product_id)) {
                $error = $this->language->text('SKU must be unique per store');
                $this->setError("combination.$index.sku", $error);
                return false;
            }

            $this->processed_skus[$combination['sku']] = true;
            return true;
        }

        if (!empty($product_id)) {
            $pattern = "$sku-" . crc32(uniqid('', true));
            $combination['sku'] = $this->sku->generate($pattern, array(), array('store_id' => $store_id));
            $this->processed_skus[$combination['sku']] = true;
        }

        return true;
    }

    /**
     * Validates combination stock price
     * @param integer $index
     * @param array $combination
     * @return boolean
     */
    protected function validateCombinationPriceProduct($index,
            array &$combination)
    {
        $price = $this->getSubmitted('price');

        if (empty($combination['price'])) {
            $combination['price'] = $price;
        }

        if (!is_numeric($combination['price']) || strlen($combination['price']) > 10) {
            $error = $this->language->text('Only numeric values and no longer than @num characters', array('@num' => 10));
            $this->setError("combination.$index.price", $error);
        }

        return !isset($error);
    }

    /**
     * Validates combination stock level
     * @param integer $index
     * @param array $combination
     * @return null|boolean
     */
    protected function validateCombinationStockProduct($index,
            array &$combination)
    {
        if (empty($combination['stock'])) {
            return null;
        }

        if (!is_numeric($combination['stock']) || strlen($combination['stock']) > 10) {
            $error = $this->language->text('Only numeric values and no longer than %s chars', array('%s' => 10));
            $this->setError("combination.$index.stock", $error);
        }

        return !isset($error);
    }

}
