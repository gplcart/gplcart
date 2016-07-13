<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\job\import;

use core\classes\Csv;
use core\models\Sku as ModelsSku;
use core\models\User as ModelsUser;
use core\models\Store as ModelsStore;
use core\models\Alias as ModelsAlias;
use core\models\Import as ModelsImport;
use core\models\Product as ModelsProduct;
use core\models\Currency as ModelsCurrency;
use core\models\Language as ModelsLanguage;
use core\models\Category as ModelsCategory;
use core\models\ProductClass as ModelsProductClass;

/**
 * Imports products from CSV file
 */
class Product
{

    /**
     * Import model instance
     * @var \core\models\Import $import
     */
    protected $import;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * CSV class instance
     * @var \core\classes\Csv $csv
     */
    protected $csv;

    /**
     * Category model instance
     * @var \core\models\Category $category
     */
    protected $category;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Product class model instance
     * @var \core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * Store model instance
     * @var \core\models\Store $store
     */
    protected $store;

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
     * Url model instance
     * @var \core\models\Alias $alias
     */
    protected $alias;

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * Constructor
     * @param ModelsImport $import
     * @param ModelsLanguage $language
     * @param ModelsUser $user
     * @param ModelsCategory $category
     * @param ModelsProduct $product
     * @param ModelsProductClass $product_class
     * @param ModelsStore $store
     * @param ModelsAlias $alias
     * @param ModelsSku $sku
     * @param ModelsCurrency $currency
     * @param Csv $csv
     */
    public function __construct(ModelsImport $import, ModelsLanguage $language,
            ModelsUser $user, ModelsCategory $category, ModelsProduct $product,
            ModelsProductClass $product_class, ModelsStore $store,
            ModelsAlias $alias, ModelsSku $sku, ModelsCurrency $currency,
            Csv $csv)
    {
        $this->csv = $csv;
        $this->sku = $sku;
        $this->user = $user;
        $this->store = $store;
        $this->alias = $alias;
        $this->import = $import;
        $this->product = $product;
        $this->language = $language;
        $this->category = $category;
        $this->currency = $currency;
        $this->product_class = $product_class;
    }

    /**
     * Processes one AJAX requests
     * @param array $job
     * @param integer $done
     * @param array $context
     * @return array
     */
    public function process(array $job, $done, array $context)
    {
        $operation = $job['data']['operation'];
        $header = $operation['csv']['header'];
        $limit = $job['data']['limit'];
        $delimiter = $this->import->getCsvDelimiter();

        $this->csv->setFile($job['data']['filepath'], $job['data']['filesize'])
                ->setHeader($header)
                ->setLimit($limit)
                ->setDelimiter($delimiter);

        $offset = isset($context['offset']) ? $context['offset'] : 0;
        $line = isset($context['line']) ? $context['line'] : 2; // 2 - skip 0 and header

        if (empty($offset)) {
            $this->csv->skipHeader();
        } else {
            $this->csv->setOffset($offset);
        }

        $rows = $this->csv->parse();

        if (empty($rows)) {
            return array('done' => $job['total']);
        }

        $position = $this->csv->getOffset();
        $result = $this->import($rows, $line, $job);
        $line += count($rows);
        $bytes = empty($position) ? $job['total'] : $position;

        $errors = $this->import->getErrors($result['errors'], $operation);

        return array(
            'done' => $bytes,
            'increment' => false,
            'errors' => $errors['count'],
            'updated' => $result['updated'],
            'inserted' => $result['inserted'],
            'context' => array('offset' => $position, 'line' => $line));
    }

    /**
     * Imports products
     * @param array $rows
     * @param integer $line
     * @param array $job
     * @return array
     */
    public function import(array $rows, $line, array $job)
    {
        $inserted = 0;
        $updated = 0;
        $errors = array();
        $operation = $job['data']['operation'];

        foreach ($rows as $index => $row) {
            $line += $index;
            $data = array_filter(array_map('trim', $row));
            $update = (isset($data['product_id']) && is_numeric($data['product_id']));

            if ($update && !$this->user->access('product_edit')) {
                continue;
            }

            if (!$update && !$this->user->access('product_add')) {
                continue;
            }

            if ($update) {
                $data['update_product'] = $this->product->get($data['product_id']);
            }

            if (!$this->validateTitle($data, $errors, $line)) {
                continue;
            }

            if (!empty($options['unique']) && !$this->validateUnique($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateStore($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateCurrency($data, $errors, $line)) {
                continue;
            }

            if (!$this->validatePrice($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateStock($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateClass($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateCategory($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateBrand($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateSku($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateAlias($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateImages($data, $errors, $line, $operation)) {
                continue;
            }

            if (!$this->validateRelated($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateDimension($data, $errors, $line)) {
                continue;
            }

            $this->validateStatus($data, $errors, $line);

            if ($update) {
                $updated += $this->update($data['product_id'], $data);
                continue;
            }

            $inserted += $this->add($data, $errors, $line);
        }

        return array('inserted' => $inserted, 'updated' => $updated, 'errors' => $errors);
    }

    /**
     * Validates titles
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateTitle(array &$data, array &$errors, $line)
    {
        if (isset($data['title']) && mb_strlen($data['title']) > 255) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Title must not be longer than 255 characters')));
            return false;
        }

        if (isset($data['meta_title']) && mb_strlen($data['meta_title']) > 255) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Meta title must not be longer than 255 characters')));
            return false;
        }

        if (isset($data['meta_description']) && mb_strlen($data['meta_description']) > 255) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Meta description must not be longer than 255 characters')));
            return false;
        }

        return true;
    }

    /**
     * Whether the product is unique
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateUnique(array &$data, array &$errors, $line)
    {
        if (!isset($data['title'])) {
            return true;
        }

        $existing = $this->getProduct($data['title']);

        if ((isset($data['update_product']) && isset($existing['product_id'])) && ($existing['product_id'] == $data['product_id'])) {
            return true;
        }

        if (empty($existing)) {
            return true;
        }

        $errors[] = $this->language->text('Line @num: @error', array(
            '@num' => $line,
            '@error' => $this->language->text('Product name already exists')));

        return false;
    }

    /**
     * Loads a product from the database
     * @param integer|string $product_id
     * @return array
     */
    protected function getProduct($product_id)
    {
        if (is_numeric($product_id)) {
            return $this->product->get($product_id);
        }

        $products = $this->product->getList(array('title' => $product_id));

        $matches = array();
        foreach ($products as $product) {
            if ($product['title'] === $product_id) {
                $matches[] = $product;
            }
        }

        return (count($matches) == 1) ? reset($matches) : $matches;
    }

    /**
     * Validates a store
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateStore(array &$data, array &$errors, $line)
    {
        if (!isset($data['store_id'])) {
            if (isset($data['update_product']['store_id'])) {
                $data['store_id'] = $data['update_product']['store_id']; // Needs to regenerate sku
            } else {
                $data['store_id'] = $this->store->getDefault();
            }
        }

        $store = $this->getStore($data['store_id']);

        if (empty($store['store_id'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Store @id neither exists or unique', array(
                    '@id' => $data['store_id']))));
            return false;
        }

        $data['store_id'] = $store['store_id'];
        return true;
    }

    /**
     * Loads a store from the database
     * @param integer|string $store_id
     * @return array
     */
    protected function getStore($store_id)
    {
        if (is_numeric($store_id)) {
            return $this->store->get($store_id);
        }

        $matches = array();
        foreach ($this->store->getList(array('name' => $store_id)) as $store) {
            if ($store['name'] === $store_id) {
                $matches[] = $store;
            }
        }

        return (count($matches) == 1) ? reset($matches) : $matches;
    }

    /**
     * Validates a currency
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateCurrency(array &$data, array &$errors, $line)
    {
        if (!isset($data['currency'])) {
            return true;
        }

        if ($this->currency->get($data['currency'])) {
            return true;
        }

        $errors[] = $this->language->text('Line @num: @error', array(
            '@num' => $line,
            '@error' => $this->language->text('Currency @code not found', array('@code' => $data['currency']))));

        return false;
    }

    /**
     * Validates a price value
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validatePrice(array &$data, array &$errors, $line)
    {
        if (!isset($data['price'])) {
            return true;
        }

        if (is_numeric($data['price']) && strlen($data['price']) <= 6) {
            return true;
        }

        $errors[] = $this->language->text('Line @num: @error', array(
            '@num' => $line,
            '@error' => $this->language->text('Price must be numeric')));

        return false;
    }

    /**
     * Validates a stock value
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateStock(array &$data, array &$errors, $line)
    {
        if (!isset($data['stock'])) {
            return true;
        }

        if (!is_numeric($data['stock']) || strlen($data['stock']) > 10) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Stock must be numeric')));
            return false;
        }

        $data['stock'] = (int) $data['stock'];
        return true;
    }

    /**
     * Validates a product class
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateClass(array &$data, array &$errors, $line)
    {
        if (!isset($data['product_class_id'])) {
            return true;
        }

        $class = $this->getClass($data['product_class_id']);

        if (empty($class['product_class_id'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Product class @id neither exists or unique', array(
                    '@id' => $data['product_class_id']))));
            return false;
        }

        $data['product_class_id'] = $class['product_class_id'];
        return true;
    }

    /**
     * Loads a product class from the database
     * @param integer|string $product_class_id
     * @return array
     */
    protected function getClass($product_class_id)
    {
        if (is_numeric($product_class_id)) {
            return $this->product_class->get($product_class_id);
        }

        $matches = array();
        foreach ($this->product_class->getList(array('title' => $product_class_id)) as $class) {
            if ($class['title'] === $product_class_id) {
                $matches[] = $class;
            }
        }

        return (count($matches) == 1) ? reset($matches) : $matches;
    }

    /**
     * Validates a category
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateCategory(array &$data, array &$errors, $line)
    {
        if (!isset($data['category_id'])) {
            return true;
        }

        $category = $this->getCategory($data['category_id'], 'catalog');

        if (empty($category['category_id'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Category @id neither exists or unique', array(
                    '@id' => $data['category_id']))));

            return false;
        }

        $data['category_id'] = $category['category_id'];
        return true;
    }

    /**
     * Loads a category from the database
     * @param integer|string $category_id
     * @param string $type
     * @return array
     */
    protected function getCategory($category_id, $type)
    {
        if (is_numeric($category_id)) {
            return $this->category->get($category_id);
        }

        $categories = $this->category->getList(array('title' => $category_id, 'type' => $type));

        $matches = array();
        foreach ($categories as $category) {
            if ($category['title'] === $category_id) {
                $matches[] = $category;
            }
        }

        return (count($matches) == 1) ? reset($matches) : $matches;
    }

    /**
     *
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateBrand(array &$data, array &$errors, $line)
    {
        if (!isset($data['brand_category_id'])) {
            return true;
        }

        $category = $this->getCategory($data['brand_category_id'], 'brand');

        if (empty($category['category_id'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Brand @id neither exists or unique', array(
                    '@id' => $data['brand_category_id']))));

            return false;
        }

        $data['brand_category_id'] = $category['category_id'];
        return true;
    }

    /**
     * Validates product SKU
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateSku(array &$data, array &$errors, $line)
    {
        if (!isset($data['sku'])) {
            return true;
        }

        if ($data['sku'] === $this->import->getCsvAutoTag()) {
            $data['sku'] = $this->product->createSku($data);
            return true;
        }

        if (mb_strlen($data['sku']) > 255) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('SKU must not be longer than 255 characters')));
            return false;
        }

        $store_id = isset($data['store_id']) ? $data['store_id'] : null;
        $skus = $this->sku->getList(array('sku' => $data['sku'], 'store_id' => $store_id));

        $exists = 0;
        foreach ($skus as $sku) {
            if (isset($data['update_product']) && $sku['product_id'] == $data['product_id']) {
                continue;
            }

            if ($sku['sku'] === $data['sku']) {
                $exists++;
            }
        }

        if (empty($exists)) {
            return true;
        }

        $errors[] = $this->language->text('Line @num: @error', array(
            '@num' => $line,
            '@error' => $this->language->text('SKU already exists for store @store', array('@store' => $store_id))));

        return false;
    }

    /**
     * Validates a URL alias
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateAlias(array &$data, array &$errors, $line)
    {
        if (!isset($data['alias'])) {
            return true;
        }

        if ($data['alias'] === $this->import->getCsvAutoTag()) {
            $data['alias'] = $this->product->createAlias($data);
            return true;
        }

        if (mb_strlen($data['alias']) > 255) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Alias must not be longer than 255 characters')));
            return false;
        }

        $unique = true;
        $alias = $this->alias->exists($data['alias']);
        if (isset($alias['id_value'])) {
            $unique = false;
        }

        if ((isset($data['update_product']) && isset($alias['id_value'])) && ($alias['id_value'] == $data['product_id'])) {
            $unique = true;
            $data['alias'] = null;
        }

        if ($unique) {
            return true;
        }

        $errors[] = $this->language->text('Line @num: @error', array(
            '@num' => $line,
            '@error' => $this->language->text('URL alias already exists')));

        return false;
    }

    /**
     * Validates and downloads images
     * @param array $data
     * @param array $errors
     * @param array $operation
     * @return boolean
     */
    protected function validateImages(array &$data, array &$errors, $line,
            array $operation)
    {
        if (!isset($data['images'])) {
            return true;
        }

        $download = $this->import->getImages($data['images'], $operation);

        if (!empty($download['errors'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => implode(',', $download['errors'])));
        }
        $data['images'] = $download['images'];
        return true;
    }

    /**
     * Validates related products
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateRelated(array &$data, array &$errors, $line)
    {
        if (!isset($data['related'])) {
            return true;
        }

        $related = array_filter(array_map('trim', explode($this->import->getCsvDelimiterMultiple(), $data['related'])));

        if (empty($related)) {
            return true;
        }

        $product_ids = array();
        foreach ($related as $product_id) {
            $product = $this->getProduct($product_id);

            if (empty($product['product_id'])) {
                $errors[] = $this->language->text('Line @num: @error', array(
                    '@num' => $line,
                    '@error' => $this->language->text('Related product @id neither exists or unique', array(
                        '@id' => $product_id))));
                continue;
            }

            if ($product['store_id'] != $data['store_id']) {
                $errors[] = $this->language->text('Line @num: @error', array(
                    '@num' => $line,
                    '@error' => $this->language->text('Related product @id does not belong to store ID @store', array(
                        '@id' => $product_id, '@store' => $data['store_id']))));
                continue;
            }

            $product_ids[] = $product['product_id'];
        }

        $data['related'] = $product_ids;
        return true;
    }

    /**
     * Validates product dimensions
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateDimension(array &$data, array &$errors, $line)
    {
        if (isset($data['width']) && (!is_numeric($data['width']) || strlen($data['width']) > 10)) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Dimension values must be numeric')));
            return false;
        }

        if (isset($data['height']) && (!is_numeric($data['height']) || strlen($data['height']) > 10)) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Dimension values must be numeric')));
            return false;
        }

        if (isset($data['length']) && (!is_numeric($data['length']) || strlen($data['length']) > 10)) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Dimension values must be numeric')));
            return false;
        }

        if (isset($data['weight']) && (!is_numeric($data['weight']) || strlen($data['weight']) > 10)) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Weight must be numeric')));
            return false;
        }

        $allowed = array('g', 'kg', 'lb', 'oz');

        if (isset($data['weight_unit']) && !in_array($data['weight_unit'], $allowed)) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Allowed weight units: ' . implode(',', $allowed))));
            return false;
        }

        $allowed = array('mm', 'cm', 'in');

        if (isset($data['volume_unit']) && !in_array($data['volume_unit'], $allowed)) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Allowed dimension units: ' . implode(',', $allowed))));
            return false;
        }

        return true;
    }

    /**
     * Validates product status
     * @param array $data
     * @param array $errors
     * @param integer $line
     */
    protected function validateStatus(array &$data, array &$errors, $line)
    {
        if (isset($data['status'])) {
            $data['status'] = $this->import->toBool($data['status']);
        }
    }

    /**
     * Updates a product
     * @param integer $product_id
     * @param array $data
     * @return integer
     */
    protected function update($product_id, array $data)
    {
        return (int) $this->product->update($product_id, $data);
    }

    /**
     * Adds a product
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return integer
     */
    protected function add(array &$data, array &$errors, $line)
    {
        if (!isset($data['meta_title']) && isset($data['title'])) {
            $data['meta_title'] = $data['title'];
        }

        if (!isset($data['meta_description']) && isset($data['description'])) {
            $data['meta_description'] = mb_strimwidth($data['description'], 0, 255);
        }

        if (!isset($data['user_id'])) {
            $data['user_id'] = $this->user->id();
        }

        if (empty($data['title'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Title cannot be empty')));
            return 0;
        }

        $added = $this->product->add($data);
        return empty($added) ? 0 : 1;
    }

}
