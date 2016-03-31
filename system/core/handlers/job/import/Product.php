<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\handlers\job\import;

use core\models\Import;
use core\models\Language;
use core\models\Category;
use core\models\Product as P;
use core\models\Store;
use core\models\Alias;
use core\models\User;
use core\models\Sku;
use core\models\Currency;
use core\models\ProductClass;
use core\classes\Csv;

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
     * @param Import $import
     * @param Language $language
     * @param User $user
     * @param Category $category
     * @param P $product
     * @param ProductClass $product_class
     * @param Store $store
     * @param Alias $alias
     * @param Sku $sku
     * @param Currency $currency
     * @param Csv $csv
     */
    public function __construct(Import $import, Language $language, User $user, Category $category, P $product, ProductClass $product_class, Store $store, Alias $alias, Sku $sku, Currency $currency, Csv $csv)
    {
        $this->import = $import;
        $this->language = $language;
        $this->user = $user;
        $this->category = $category;
        $this->product = $product;
        $this->product_class = $product_class;
        $this->store = $store;
        $this->alias = $alias;
        $this->sku = $sku;
        $this->currency = $currency;
        $this->csv = $csv;
    }

    /**
     *
     * @param array $job
     * @param string $operation_id
     * @param integer $done
     * @param array $context
     * @param array $options
     * @return array
     */
    public function process($job, $operation_id, $done, $context, $options)
    {
        $import_operation = $options['operation'];
        $header = $import_operation['csv']['header'];
        $limit = $options['limit'];
        $delimiter = $this->import->getCsvDelimiter();

        $this->csv->setFile($options['filepath'], $options['filesize'])
                ->setHeader($header)
                ->setLimit($limit)
                ->setDelimiter($delimiter);

        $offset = isset($context['offset']) ? $context['offset'] : 0;
        $line = isset($context['line']) ? $context['line'] : 2; // 2 - skip 0 and header

        if ($offset) {
            $this->csv->setOffset($offset);
        } else {
            $this->csv->skipHeader();
        }

        $rows = $this->csv->parse();

        if (!$rows) {
            return array('done' => $job['total']);
        }

        $position = $this->csv->getOffset();
        $result = $this->import($rows, $line, $options);
        $line += count($rows);
        $bytes = $position ? $position : $job['total'];

        $errors = $this->import->getErrors($result['errors'], $import_operation);

        return array(
            'done' => $bytes,
            'increment' => false,
            'inserted' => $result['inserted'],
            'updated' => $result['updated'],
            'errors' => $errors['count'],
            'context' => array('offset' => $position, 'line' => $line));
    }

    /**
     *
     * @param type $rows
     * @param type $line
     * @param type $options
     * @return type
     */
    public function import($rows, $line, $options)
    {
        $inserted = 0;
        $updated = 0;
        $errors = array();
        $operation = $options['operation'];

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
     * @return boolean
     */
    protected function validateTitle(&$data, &$errors, $line)
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
     * @param boolean $update
     * @return boolean
     */
    protected function validateUnique(&$data, &$errors, $line)
    {
        if (!isset($data['title'])) {
            return true;
        }

        $existing = $this->getProduct($data['title']);

        if ((isset($data['update_product']) && isset($existing['product_id'])) && ($existing['product_id'] == $data['product_id'])) {
            return true;
        }

        if (!$existing) {
            return true;
        }

        $errors[] = $this->language->text('Line @num: @error', array(
            '@num' => $line,
            '@error' => $this->language->text('Product name already exists')));

        return false;
    }

    /**
     *
     * @param type $product_id
     * @return type
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
     *
     * @param type $data
     * @param type $errors
     * @param type $line
     * @return boolean
     */
    protected function validateStore(&$data, &$errors, $line)
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
     *
     * @param type $store_id
     * @return type
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
     *
     * @param type $data
     * @param array $errors
     * @param type $line
     * @return boolean
     */
    protected function validateCurrency(&$data, &$errors, $line)
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
     *
     * @param type $data
     * @param array $errors
     * @param type $line
     * @return boolean
     */
    protected function validatePrice(&$data, &$errors, $line)
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
     *
     * @param array $data
     * @param type $errors
     * @param type $line
     * @return boolean
     */
    protected function validateStock(&$data, &$errors, $line)
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
     *
     * @param array $data
     * @param type $errors
     * @param type $line
     * @return boolean
     */
    protected function validateClass(&$data, &$errors, $line)
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
     *
     * @param type $product_class_id
     * @return type
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
     *
     * @param array $data
     * @param type $errors
     * @param type $line
     * @return boolean
     */
    protected function validateCategory(&$data, &$errors, $line)
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
     *
     * @param type $category_id
     * @param type $type
     * @return type
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
     * @param type $errors
     * @param type $line
     * @return boolean
     */
    protected function validateBrand(&$data, &$errors, $line)
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
     *
     * @param type $data
     * @param array $errors
     * @param type $line
     * @return boolean
     */
    protected function validateSku(&$data, &$errors, $line)
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

        if (!$exists) {
            return true;
        }

        $errors[] = $this->language->text('Line @num: @error', array(
            '@num' => $line,
            '@error' => $this->language->text('SKU already exists for store @store', array('@store' => $store_id))));

        return false;
    }

    /**
     *
     * @param type $data
     * @param array $errors
     * @param type $line
     * @return boolean
     */
    protected function validateAlias(&$data, &$errors, $line)
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
    protected function validateImages(&$data, &$errors, $line, $operation)
    {
        if (!isset($data['images'])) {
            return true;
        }

        $download = $this->import->getImages($data['images'], $operation);
        if ($download['errors']) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => implode(',', $download['errors'])));
        }
        $data['images'] = $download['images'];

        return true;
    }

    /**
     *
     * @param array $data
     * @param type $errors
     * @param type $line
     * @return boolean
     */
    protected function validateRelated(&$data, &$errors, $line)
    {
        if (!isset($data['related'])) {
            return true;
        }

        $related = array_filter(array_map('trim', explode($this->import->getCsvDelimiterMultiple(), $data['related'])));

        if (!$related) {
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

    protected function validateDimension(&$data, &$errors, $line)
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
     *
     * @param type $data
     * @param type $errors
     * @param type $line
     */
    protected function validateStatus(&$data, &$errors, $line)
    {
        if (isset($data['status'])) {
            $data['status'] = $this->import->toBool($data['status']);
        }
    }

    /**
     *
     * @param type $product_id
     * @param type $data
     * @return type
     */
    protected function update($product_id, $data)
    {
        return (int) $this->product->update($product_id, $data);
    }

    /**
     *
     * @param type $data
     * @param type $errors
     * @return boolean
     */
    protected function add(&$data, &$errors, $line)
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

        return $this->product->add($data) ? 1 : 0;
    }
}
