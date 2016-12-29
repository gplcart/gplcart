<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Cache;
use core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to import functionality
 */
class Import extends Model
{
    /**
     * Validator model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();
        $this->language = $language;
    }

    /**
     * Returns an import operation
     * @param string $id
     * @return array
     */
    public function getOperation($id)
    {
        $operations = $this->getOperations();

        $operation = array();
        if (isset($operations[$id])) {
            $operation = $operations[$id];
            $operation['id'] = $id;
        }
        return $operation;
    }

    /**
     * Returns an array of all import operations
     * @return array
     */
    public function getOperations()
    {
        $operations = &Cache::memory('import.operations');

        if (isset($operations)) {
            return $operations;
        }

        $operations = array();

        $operations['category'] = array(
            'name' => $this->language->text('Categories'),
            'job_id' => 'import_category',
            'validator' => 'category',
            'entity_id' => 'category_id',
            'access' => array('add' => 'category_add', 'update' => 'category_edit'),
            'csv' => array(
                'header' => array(
                    'category_id' => 'Category ID', 'title' => 'Title',
                    'parent_id' => 'Parent category ID', 'category_group_id' => 'Category group ID',
                    'description_1' => 'Description 1', 'description_2' => 'Description 1',
                    'meta_title' => 'Meta title', 'meta_description' => 'Meta description',
                    'status' => 'Enabled', 'alias' => 'Alias', 'images' => 'Images',
                ),
                'template' => GC_PRIVATE_EXAMPLES_DIR . '/import_category.csv'
            ),
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/import_category_errors.csv'),
        );

        $operations['product'] = array(
            'name' => $this->language->text('Products'),
            'job_id' => 'import_product',
            'validator' => 'product',
            'entity_id' => 'product_id',
            'access' => array('add' => 'product_add', 'update' => 'product_edit'),
            'csv' => array(
                'header' => array(
                    'product_id' => 'Product ID', 'title' => 'Title', 'sku' => 'SKU',
                    'price' => 'Price', 'currency' => 'Currency', 'stock' => 'Stock',
                    'product_class_id' => 'Product class ID', 'store_id' => 'Store ID',
                    'category_id' => 'Category ID', 'brand_category_id' => 'Brand category ID',
                    'alias' => 'Alias', 'images' => 'Images',
                    'status' => 'Enabled', 'description' => 'Description',
                    'meta_title' => 'Meta title', 'meta_description' => 'Meta description',
                    'related' => 'Related product ID', 'width' => 'Width', 'height' => 'Height',
                    'length' => 'Length', 'volume_unit' => 'Dimension unit',
                    'weight' => 'Weight', 'weight_unit' => 'Weight unit',
                ),
                'template' => GC_PRIVATE_EXAMPLES_DIR . '/import_product.csv'
            ),
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/import_product_errors.csv'),
        );

        $this->hook->fire('import.operations', $operations);
        return $operations;
    }

    /**
     * Returns a character used to separate key/value pair
     * @return string
     */
    public function getCsvDelimiterKeyValue()
    {
        return $this->config->get('csv_delimiter_key_value', ":");
    }

    /**
     * Returns processing limit value
     * @return type
     */
    public function getLimit()
    {
        return (int) $this->config->get('import_limit', 10);
    }

    /**
     * Returns a character used to separate CSV columns
     * @return string
     */
    public function getCsvDelimiter()
    {
        return $this->config->get('csv_delimiter', ",");
    }

}
