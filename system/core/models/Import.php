<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use core\Hook;
use core\Logger;
use core\Config;
use core\classes\Csv;
use core\classes\Cache;
use core\models\File as ModelsFile;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to import functionality
 */
class Import
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * CSV class instance
     * @var \core\classes\Csv $csv
     */
    protected $csv;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsFile $file
     * @param Csv $csv
     * @param Hook $hook
     * @param Logger $logger
     * @param Config $config
     */
    public function __construct(ModelsLanguage $language, ModelsFile $file,
                                Csv $csv, Hook $hook, Logger $logger,
                                Config $config)
    {
        $this->csv = $csv;
        $this->file = $file;
        $this->hook = $hook;
        $this->config = $config;
        $this->logger = $logger;
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

        $operations['state'] = array(
            'name' => $this->language->text('Country states'),
            'description' => '',
            'job_id' => 'import_state',
            'csv' => array(
                'header' => array(
                    'state_id' => 'State ID',
                    'name' => 'State name',
                    'code' => 'State code',
                    'country' => 'Country code',
                    'status' => 'Enabled'
                ),
                'template' => GC_PRIVATE_EXAMPLES_DIR . '/import_state.csv',
            ),
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/import_state_errors.csv'),
        );

        $operations['city'] = array(
            'name' => $this->language->text('Cities'),
            'job_id' => 'import_city',
            'csv' => array(
                'header' => array(
                    'city_id' => 'City ID',
                    'name' => 'City name',
                    'state_code' => 'State code',
                    'country' => 'Country code',
                    'status' => 'Enabled'
                ),
                'template' => GC_PRIVATE_EXAMPLES_DIR . '/import_city.csv'
            ),
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/import_city_errors.csv'),
        );

        $operations['category'] = array(
            'name' => $this->language->text('Categories'),
            'job_id' => 'import_category',
            'csv' => array(
                'header' => array(
                    'category_id' => 'Category ID',
                    'title' => 'Title',
                    'parent_id' => 'Parent category',
                    'category_group_id' => 'Category group',
                    'description_1' => 'Description 1',
                    'description_2' => 'Description 1',
                    'meta_title' => 'Meta title',
                    'meta_description' => 'Meta description',
                    'status' => 'Enabled',
                    'alias' => 'Alias',
                    'image' => 'Image',
                ),
                'template' => GC_PRIVATE_EXAMPLES_DIR . '/import_category.csv'
            ),
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/import_category_errors.csv'),
        );

        $operations['field'] = array(
            'name' => $this->language->text('Fields'),
            'job_id' => 'import_field',
            'csv' => array(
                'header' => array(
                    'field_id' => 'Field ID',
                    'title' => 'Title',
                    'type' => 'Type',
                    'widget' => 'Widget',
                    'weight' => 'Weight',
                ),
                'template' => GC_PRIVATE_EXAMPLES_DIR . '/import_field.csv'
            ),
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/import_field_errors.csv'),
        );

        $operations['field_value'] = array(
            'name' => $this->language->text('Field values'),
            'job_id' => 'import_field_value',
            'csv' => array(
                'header' => array(
                    'field_value_id' => 'Field value ID',
                    'title' => 'Title',
                    'field_id' => 'Field',
                    'color' => 'Color code',
                    'image' => 'Image',
                    'weight' => 'Weight',
                ),
                'template' => GC_PRIVATE_EXAMPLES_DIR . '/import_field_value.csv'
            ),
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/import_field_value_errors.csv'),
        );

        $operations['user'] = array(
            'name' => $this->language->text('Users'),
            'job_id' => 'import_user',
            'csv' => array(
                'header' => array(
                    'user_id' => 'User ID',
                    'name' => 'Name',
                    'email' => 'Email',
                    'password' => 'Password',
                    'role_id' => 'Role',
                    'store_id' => 'Store',
                    'status' => 'Enabled',
                    'created' => 'Created',
                ),
                'template' => GC_PRIVATE_EXAMPLES_DIR . '/import_user.csv'
            ),
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/import_user_errors.csv'),
        );

        $operations['product'] = array(
            'name' => $this->language->text('Products'),
            'job_id' => 'import_product',
            'csv' => array(
                'header' => array(
                    'product_id' => 'Product ID',
                    'title' => 'Title',
                    'sku' => 'SKU',
                    'price' => 'Price',
                    'currency' => 'Currency',
                    'stock' => 'Stock',
                    'product_class_id' => 'Class',
                    'store_id' => 'Store',
                    'category_id' => 'Category',
                    'brand_category_id' => 'Brand',
                    'alias' => 'Alias',
                    'images' => 'Images',
                    'status' => 'Enabled',
                    'description' => 'Description',
                    'meta_title' => 'Meta title',
                    'meta_description' => 'Meta description',
                    'related' => 'Related',
                    'width' => 'Width',
                    'height' => 'Height',
                    'length' => 'Length',
                    'volume_unit' => 'Dimension unit',
                    'weight' => 'Weight',
                    'weight_unit' => 'Weight unit',
                ),
                'template' => GC_PRIVATE_EXAMPLES_DIR . '/import_product.csv'
            ),
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/import_product_errors.csv'),
        );

        $operations['option_combination'] = array(
            'name' => $this->language->text('Option combinations'),
            'job_id' => 'import_option_combination',
            'csv' => array(
                'header' => array(
                    'combination_id' => 'Combination ID',
                    'fields' => 'Field values',
                    'product_id' => 'Product',
                    'sku' => 'SKU',
                    'price' => 'Price',
                    'stock' => 'Stock',
                    'file_id' => 'Image',
                ),
                'template' => GC_PRIVATE_EXAMPLES_DIR . '/import_option_combination.csv'
            ),
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/import_option_combination_errors.csv'),
        );

        $this->hook->fire('import.operations', $operations);
        return $operations;
    }

    /**
     * Converts string to boolean type
     * @param string $var
     * @return boolean
     */
    public function toBool($var)
    {
        if (!is_string($var)) {
            return (bool) $var;
        }

        switch (strtolower($var)) {
            case '1':
            case 'true':
            case 'on':
            case 'yes':
            case 'y':
                return true;
            default:
                return false;
        }
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
     * Returns a character used to indicate an autogenerated field
     * @return string
     */
    public function getCsvAutoTag()
    {
        return $this->config->get('csv_auto', '*');
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
     * Logs an returns prepared errors
     * @param array $data
     * @param string $operation
     * @return array
     */
    public function getErrors(array $data, $operation)
    {
        $errors = array_filter((array) $data);

        if (!empty($operation['log']['errors'])) {
            foreach ($errors as $error) {
                $this->logger->csv($operation['log']['errors'], '', $error, 'warning');
            }
        }

        return array('count' => count($errors), 'message' => end($errors));
    }

    /**
     * Downloads remoted images
     * @param array $data
     * @param array $operation
     * @return array
     */
    public function getImages(array $data, array $operation)
    {
        $return = array('errors' => array(), 'images' => array());
        $images = array_filter(array_map('trim', explode($this->getCsvDelimiterMultiple(), $data)));

        if (!$images) {
            return $return;
        }

        $destination = 'image/upload/' . $this->config->get("{$operation['id']}_image_dirname", $operation['id']);
        $this->file->setUploadPath($destination)->setHandler('image');

        foreach ($images as $image) {
            if (0 === strpos($image, 'http')) {
                $result = $this->file->download($image);
                if ($result === true) {
                    $return['images'][] = array('path' => $this->file->path($this->file->getUploadedFile()));
                    continue;
                }

                $download_errors = (array) $result;
                $return['errors'] = array_merge($return['errors'], $download_errors);
                continue;
            }

            $path = GC_FILE_DIR . '/' . trim($image, '/');

            if (file_exists($path) && $this->file->validate($path) === true) {
                $return['images'][] = array('path' => $image);
            }
        }

        return $return;
    }

    /**
     * Returns a character used to separate multiple values
     * @return string
     */
    public function getCsvDelimiterMultiple()
    {
        return $this->config->get('csv_delimiter_multiple', "|");
    }

    /**
     * Validates CSV header
     * @param string $file
     * @param array $operation
     * @return boolean|string
     */
    public function validateCsvHeader($file, array $operation)
    {
        $header = $operation['csv']['header'];
        $real_header = $this->csv->setFile($file)
                ->setHeader($header)
                ->setDelimiter($this->getCsvDelimiter())
                ->getHeader();

        $header_id = reset($header);
        $real_header_id = reset($real_header);

        if (($header_id !== $real_header_id) || array_diff($header, $real_header)) {
            $error = $this->language->text('Missing some header columns. Required format: %format', array(
                '%format' => implode(' | ', $header)));
            return $error;
        }

        return true;
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
