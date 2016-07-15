<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Hook;
use core\Config;
use core\classes\Cache;
use core\models\Import as ModelsImport;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to export functionality
 */
class Export
{

    /**
     * Hook class instance
     * @var core\Hook $hook
     */
    protected $hook;

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
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Constructor
     * @param ModelsImport $import
     * @param ModelsLanguage $language
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(ModelsImport $import, ModelsLanguage $language,
                                Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->import = $import;
        $this->config = $config;
        $this->language = $language;
    }

    /**
     * Returns an export operation
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
     * Returns an array of operations
     * @return array
     */
    public function getOperations()
    {
        $operations = &Cache::memory('export.operations');

        if (isset($operations)) {
            return $operations;
        }

        $operations = array();

        $operations['state'] = array(
            'name' => $this->language->text('Country states'),
            'description' => '',
            'job_id' => 'export_state',
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/export_state_errors.csv'),
            'file' => GC_PRIVATE_DOWNLOAD_DIR . '/export_state.csv'
        );

        $operations['city'] = array(
            'name' => $this->language->text('Cities'),
            'job_id' => 'export_city',
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/export_city_errors.csv'),
            'file' => GC_PRIVATE_DOWNLOAD_DIR . '/export_city.csv'
        );

        $operations['category'] = array(
            'name' => $this->language->text('Categories'),
            'job_id' => 'export_category',
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/export_category_errors.csv'),
            'file' => GC_PRIVATE_DOWNLOAD_DIR . '/export_category.csv'
        );

        $operations['field'] = array(
            'name' => $this->language->text('Fields'),
            'job_id' => 'export_field',
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/export_field_errors.csv'),
            'file' => GC_PRIVATE_DOWNLOAD_DIR . '/export_field.csv'
        );

        $operations['field_value'] = array(
            'name' => $this->language->text('Field values'),
            'job_id' => 'export_field_value',
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/export_field_value_errors.csv'),
            'file' => GC_PRIVATE_DOWNLOAD_DIR . '/export_field_value.csv'
        );

        $operations['user'] = array(
            'name' => $this->language->text('Users'),
            'job_id' => 'export_user',
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/export_user_errors.csv'),
            'file' => GC_PRIVATE_DOWNLOAD_DIR . '/export_user.csv'
        );

        $operations['product'] = array(
            'name' => $this->language->text('Products'),
            'job_id' => 'export_product',
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/export_product_errors.csv'),
            'file' => GC_PRIVATE_DOWNLOAD_DIR . '/export_product.csv'
        );

        // Add the same CSV header from import operations
        foreach ($this->import->getOperations() as $id => $data) {
            if (isset($operations[$id]) && isset($data['csv']['header'])) {
                $operations[$id]['csv']['header'] = $data['csv']['header'];
            }
        }

        $this->hook->fire('export.operations', $operations);
        return $operations;
    }

    /**
     * Logs errors
     * @param array $data
     * @param array $operation
     * @return array
     */
    public function getErrors(array $data, array $operation)
    {
        $errors = array_filter((array) $data);

        if (!empty($operation['log']['errors'])) {
            foreach ($errors as $error) {
                Logger::csv($operation['log']['errors'], '', $error, 'warning');
            }
        }

        return array('count' => count($errors), 'message' => end($errors));
    }

    /**
     * Returns an array of CSV fields based on the header info
     * @param array $header
     * @param array $data
     * @return array
     */
    public function getFields(array $header, array $data)
    {
        $fields = array();
        foreach ($header as $key => $value) {
            $fields[$key] = isset($data[$key]) ? $data[$key] : '';
        }
        return $fields;
    }

    /**
     * Returns a character used to separate CSV columns
     * @return string
     */
    public function getCsvDelimiter()
    {
        return $this->config->get('csv_delimiter', ",");
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
     * Returns a number of items to be processed for one iteration
     * @return integer
     */
    public function getLimit()
    {
        return (int) $this->config->get('export_limit', 50);
    }
}
