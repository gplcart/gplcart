<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Cache;
use gplcart\core\models\Import as ImportModel,
    gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to export functionality
 */
class Export extends Model
{

    /**
     * Import model instance
     * @var \gplcart\core\models\Import $import
     */
    protected $import;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param ImportModel $import
     * @param LanguageModel $language
     */
    public function __construct(ImportModel $import, LanguageModel $language)
    {
        parent::__construct();

        $this->import = $import;
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
        $operations = &Cache::memory(__METHOD__);

        if (isset($operations)) {
            return $operations;
        }

        $operations = array();

        $operations['category'] = array(
            'name' => $this->language->text('Categories'),
            'job_id' => 'export_category',
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/export_category_errors.csv'),
            'file' => GC_PRIVATE_DOWNLOAD_DIR . '/export_category.csv'
        );

        $operations['product'] = array(
            'name' => $this->language->text('Products'),
            'job_id' => 'export_product',
            'log' => array('errors' => GC_PRIVATE_LOGS_DIR . '/export_product_errors.csv'),
            'file' => GC_PRIVATE_DOWNLOAD_DIR . '/export_product.csv'
        );

        $this->attachCsvHeader($operations);

        $this->hook->fire('export.operations', $operations);
        return $operations;
    }

    /**
     * Adds CSV header from import operations for backward compatibility
     * @param array $operations
     */
    protected function attachCsvHeader(&$operations)
    {
        foreach ($this->import->getOperations() as $id => $data) {
            if (isset($operations[$id]) && isset($data['csv']['header'])) {
                $operations[$id]['csv']['header'] = $data['csv']['header'];
            }
        }
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
