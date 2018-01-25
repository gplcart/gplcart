<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component as ComponentValidator;
use gplcart\core\models\Category as CategoryModel;
use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\ProductClass as ProductClassModel;
use gplcart\core\models\Sku as SkuModel;

/**
 * Provides methods to validate a product data
 */
class Product extends ComponentValidator
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
     * @param ProductModel $product
     * @param ProductClassModel $product_class
     * @param SkuModel $sku
     * @param CurrencyModel $currency
     * @param CategoryModel $category
     */
    public function __construct(ProductModel $product, ProductClassModel $product_class,
                                SkuModel $sku, CurrencyModel $currency, CategoryModel $category)
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
        $this->validateBool('status');
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
        $this->validateUserId(false);
        $this->validateDimensionProduct();
        $this->validateRelatedProduct();
        $this->validateClassProduct();
        $this->validateSkuProduct();
        $this->validateAttributeProduct();
        $this->validateCombinationProduct();
        $this->validateAlias();
        $this->validateUploadImages('product');

        $this->unsetSubmitted('update');

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
            $this->setErrorUnavailable('update', $this->translation->text('Product'));
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
        $field = 'subtract';
        $subtract = $this->getSubmitted($field);

        if (isset($subtract)) {
            $this->setSubmitted($field, filter_var($subtract, FILTER_VALIDATE_BOOLEAN));
        }

        return true;
    }

    /**
     * Validates currency code
     * @return boolean|null
     */
    protected function validateCurrencyProduct()
    {
        $field = 'currency';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Currency');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $currency = $this->currency->get($value);

        if (empty($currency)) {
            $this->setErrorUnavailable($field, $label);
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
            'category_id' => $this->translation->text('Category'),
            'brand_category_id' => $this->translation->text('Brand'),
        );

        $errors = 0;
        foreach ($fields as $field => $label) {

            $value = $this->getSubmitted($field);

            if (!isset($value)) {
                continue;
            }

            if (!is_numeric($value)) {
                $errors++;
                $this->setErrorNumeric($field, $label);
                continue;
            }

            if (empty($value)) {
                continue;
            }

            $category = $this->category->get($value);

            if (empty($category['category_id'])) {
                $errors++;
                $this->setErrorUnavailable($field, $label);
            }
        }

        return empty($errors);
    }

    /**
     * Validates measurement units
     * @return boolean
     */
    protected function validateUnitProduct()
    {
        $allowed = array(
            'size_unit' => $this->product->getSizeUnits(),
            'weight_unit' => $this->product->getWeightUnits()
        );

        $fields = array(
            'size_unit' => $this->translation->text('Size unit'),
            'weight_unit' => $this->translation->text('Weight unit')
        );

        $errors = 0;
        foreach ($fields as $field => $label) {
            $value = $this->getSubmitted($field);
            if (isset($value) && !isset($allowed[$field][$value])) {
                $errors++;
                $this->setErrorUnavailable($field, $label);
            }
        }

        return empty($errors);
    }

    /**
     * Validates a product price
     * @return boolean|null
     */
    protected function validatePriceProduct()
    {
        $field = 'price';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            return null;
        }

        $label = $this->translation->text('Price');

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if (strlen($value) > 8) { // Major units
            $this->setErrorLengthRange($field, $label, 0, 8);
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
        $field = 'stock';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Stock');

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if (strlen($value) > 10) {
            $this->setErrorLengthRange($field, $label, 0, 10);
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
            'width' => $this->translation->text('Width'),
            'height' => $this->translation->text('Height'),
            'length' => $this->translation->text('Length'),
            'weight' => $this->translation->text('Weight')
        );

        $errors = 0;
        foreach ($fields as $field => $label) {

            $value = $this->getSubmitted($field);

            if (!isset($value)) {
                continue;
            }

            if (!is_numeric($value)) {
                $errors++;
                $this->setErrorNumeric($field, $label);
            }

            if (strlen($value) > 10) {
                $errors++;
                $this->setErrorLengthRange($field, $label, 0, 10);
            }
        }

        return empty($errors);
    }

    /**
     * Validates related products
     * @return boolean|null
     */
    protected function validateRelatedProduct()
    {
        $field = 'related';

        if ($this->isExcluded($field) || $this->isError('store_id')) {
            return null;
        }

        $value = $this->getSubmitted($field);
        $store_id = $this->getSubmitted('store_id');

        if (empty($value)) {
            $this->setSubmitted($field, array());
            return null;
        }

        $product_ids = array();
        foreach (array_unique($value) as $product_id) {

            $product = $this->product->get($product_id);

            if (empty($product['store_id'])) {
                $this->setError($field, $this->translation->text('Some of related products are invalid'));
                return false;
            }

            if ($product['store_id'] != $store_id) {
                $this->setError($field, $this->translation->text('All related product must belong to the same store'));
                return false;
            }

            $product_ids[$product['product_id']] = $product['product_id'];
        }

        $updating = $this->getUpdating();

        if (isset($updating['product_id'])) {
            // Exclude the current product from the related products
            unset($product_ids[$updating['product_id']]);
        }

        $this->setSubmitted('related', $product_ids);
        return true;
    }

    /**
     * Validates a product SKU
     * @return boolean|null
     */
    protected function validateSkuProduct()
    {
        $field = 'sku';

        if ($this->isExcluded($field) || $this->isError()) {
            return null;
        }

        $value = $this->getSubmitted($field);
        $label = $this->translation->text('SKU');

        if ($this->isUpdating() && empty($value)) {
            $data = $this->getSubmitted();
            $value = $this->product->generateSku($data);
            $this->setSubmitted('sku', $value);
        }

        if (isset($value) && mb_strlen($value) > 255) {
            $this->setErrorLengthRange($field, $label, 0, 255);
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

        $existing = $this->sku->get(array('sku' => $value, 'store_id' => $store_id));

        if (isset($product_id) && isset($existing['product_id']) && $existing['product_id'] == $product_id) {
            return true;
        }

        if (!empty($existing)) {
            $this->setErrorExists($field, $label);
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
        $field = 'product_class_id';
        $value = $this->getSubmitted($field);

        if (empty($value)) {
            return null;
        }

        $label = $this->translation->text('Product class');

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $product_class = $this->product_class->get($value);

        if (empty($product_class)) {
            $this->setErrorUnavailable($field, $label);
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
        $attributes = $this->getSubmitted('field.attribute');
        $fields = $this->getSubmitted('product_fields.attribute');

        if (empty($fields)) {
            return null;
        }

        $errors = 0;
        foreach ($fields as $field_id => $field) {
            if (!empty($field['required']) && empty($attributes[$field_id])) {
                $this->setErrorRequired("attribute.$field_id", $field['title']);
                $errors++;
            }
        }

        return empty($errors);
    }

    /**
     * Validates an array of product combinations
     * @return boolean|null
     */
    protected function validateCombinationProduct()
    {
        $combinations = $this->getSubmitted('combination');

        if (empty($combinations)) {
            return null;
        }

        $index = 1;
        foreach ($combinations as &$combination) {

            $combination['status'] = !empty($combination['status']);

            $this->validateCombinationOptionsProduct($index, $combination);

            if ($this->isError("combination.$index")) {
                continue;
            }

            if (empty($combination['fields'])) {
                unset($combinations[$index]);
                continue;
            }

            $combination_id = $this->sku->getCombinationId($combination['fields']);

            if (isset($this->processed_combinations[$combination_id])) {
                $error = $this->translation->text('Combination already exists');
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
            $index++;
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
    protected function validateCombinationOptionsProduct($index, &$combination)
    {
        $options = $this->getSubmitted('product_fields.option');

        if (empty($options)) {
            return null;
        }

        $errors = 0;
        foreach ($options as $field_id => $field) {
            if (!empty($field['required']) && !isset($combination['fields'][$field_id])) {
                $this->setErrorRequired("combination.$index.fields.$field_id", $field['title']);
                $errors++;
            }
        }

        return empty($errors);
    }

    /**
     * Validates option combination SKUs
     * @param integer $index
     * @param array $combination
     * @return boolean|null
     */
    protected function validateCombinationSkuProduct($index, &$combination)
    {
        if (!isset($combination['sku'])) {
            return null;
        }

        if ($combination['sku'] === '') {
            return true;
        }

        $updating = $this->getUpdating();

        $product_id = null;
        if (isset($updating['product_id'])) {
            $product_id = $updating['product_id'];
        }

        $store_id = $this->getSubmitted('store_id');

        if (mb_strlen($combination['sku']) > 255) {
            $this->setErrorLengthRange("combination.$index.sku", $this->translation->text('SKU'), 0, 255);
            return false;
        }

        if (isset($this->processed_skus[$combination['sku']])) {
            $error = $this->translation->text('SKU must be unique per store');
            $this->setError("combination.$index.sku", $error);
            return false;
        }

        $existing = $this->sku->get(array('sku' => $combination['sku'], 'store_id' => $store_id));

        if (isset($product_id) && isset($existing['product_id']) && $existing['product_id'] == $product_id) {
            $this->processed_skus[$combination['sku']] = true;
            return true;
        }

        if (!empty($existing)) {
            $error = $this->translation->text('SKU must be unique per store');
            $this->setError("combination.$index.sku", $error);
            return false;
        }

        $this->processed_skus[$combination['sku']] = true;
        return true;
    }

    /**
     * Validates combination stock price
     * @param integer $index
     * @param array $combination
     * @return boolean
     */
    protected function validateCombinationPriceProduct($index, &$combination)
    {
        $price = $this->getSubmitted('price');

        if (empty($combination['price'])) {
            $combination['price'] = $price;
        }

        if (!is_numeric($combination['price']) || strlen($combination['price']) > 10) {
            $error = $this->translation->text('Only numeric values and no longer than @num characters', array('@num' => 10));
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
    protected function validateCombinationStockProduct($index, &$combination)
    {
        if (empty($combination['stock'])) {
            return null;
        }

        if (!is_numeric($combination['stock']) || strlen($combination['stock']) > 10) {
            $error = $this->translation->text('Only numeric values and no longer than @num characters', array('@num' => 10));
            $this->setError("combination.$index.stock", $error);
        }

        return !isset($error);
    }

}
