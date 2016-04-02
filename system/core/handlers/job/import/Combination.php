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
use core\models\User;
use core\models\Image;
use core\classes\Csv;

class Category
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
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;
    
    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Constaructor
     * @param Import $import
     * @param Language $language
     * @param User $user
     * @param Image $image
     * @param Csv $csv
     */
    public function __construct(Import $import, Language $language, User $user, Image $image, Csv $csv)
    {
        $this->csv = $csv;
        $this->user = $user;
        $this->image = $image;
        $this->import = $import;
        $this->language = $language;
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
     * @param array $rows
     * @param integer $line
     * @param array $options
     * @return array
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
            $update = (isset($data['combination_id']) && is_numeric($data['combination_id']));

            if ($update && !$this->user->access('product_edit')) {
                continue;
            }

            if (!$update && !$this->user->access('product_add')) {
                continue;
            }

            if (!$this->validateFields($data, $errors, $line)) {
                continue;
            }
            
            if(!$this->validateProduct($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateImages($data, $errors, $line, $operation)) {
                continue;
            }

            if ($update) {
                $updated += $this->update($data['combination_id'], $data);
                continue;
            }

            $inserted += $this->add($data, $errors, $line);
        }

        return array('inserted' => $inserted, 'updated' => $updated, 'errors' => $errors);
    }

    /**
     * Validates fields
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateFields(&$data, &$errors, $line){

        if (!isset($data['fields'])) {
            return true;
        }

        $field_value_ids = array();
        $components = array_filter(array_map('trim', explode($this->import->getCsvDelimiterMultiple(), $data['fields'])));

        foreach($components as $component) {

            $field_id = null;
            $keyvalue = array_filter(array_map('trim', explode($this->import->getCsvDelimiterKeyValue(), $component)));

            if(count($keyvalue) == 1) {
                $field_value_id = reset($keyvalue);
            } else if(count($keyvalue) == 2) {
                list($field_id, $field_value_id) = $keyvalue;
            } else {
                $errors[] = $this->language->text('Line @num: @error', array(
                    '@num' => $line,
                    '@error' => $this->language->text('Invalid field combination')));
                return false;
            }

            if(isset($field_id)) {
                $field = $this->getField($field_id);
                if(empty($field['field_id'])) {
                    $errors[] = $this->language->text('Line @num: @error', array(
                        '@num' => $line,
                        '@error' => $this->language->text('Field @id neither exists or unique', array('@id' => $field_id))));
                    return false;
                }

                $field_id = $field['field_id'];
            }

            $field_value = $this->getFieldValue($field_value_id, $field_id);

            if(empty($field_value['field_value_id'])) {
                $errors[] = $this->language->text('Line @num: @error', array(
                    '@num' => $line,
                    '@error' => $this->language->text('Field value @id neither exists or unique', array(
                        '@id' => $field_value_id))));
                return false;
            }

            $field_value_ids[] = $field_value['field_value_id'];
        }

        $data['fields'] = $field_value_ids;
        return true;
    }

    /**
     * Returns a field
     * @param integer $field_id
     * @return array
     */
    protected function getField($field_id)
    {
        if (is_numeric($field_id)) {
            return $this->field->get($field_id);
        }

        $fields = $this->field->getList(array('title' => $field_id));

        $matches = array();
        foreach ($fields as $field) {
            if ($field['title'] === $field_id) {
                $matches[] = $field;
            }
        }

        return (count($matches) == 1) ? reset($matches) : $matches;
    }
    
    /**
     * Returns a field value
     * @param integer $field_value_id
     * @param integer $field_id
     * @return array
     */
    protected function getFieldValue($field_value_id, $field_id)
    {
        if (is_numeric($field_value_id)) {
            return $this->field_value->get($field_value_id);
        }

        $field_values = $this->field_value->getList(array('title' => $field_value_id, 'field_id' => $field_id));

        $matches = array();
        foreach ($field_values as $field_value) {
            if ($field_value['title'] === $field_value_id) {
                $matches[] = $field_value;
            }
        }

        return (count($matches) == 1) ? reset($matches) : $matches;
    }
    
    /**
     * Validates a product
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateProduct(&$data, &$errors, $line)
    {

        if (!isset($data['product_id'])) {
            return true;
        }

        $product = $this->getProduct($data['product_id']);

        if (empty($product['product_id'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Product @id neither exists or unique', array(
                    '@id' => $data['product_id']))));
            return false;
        }

        $data['product_id'] = $product['product_id'];
        return true;
    }

    /**
     * Returns a product
     * @param integer $product_id
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
     * Validates images
     * @param array $data
     * @param array $errors
     * @param array $operation
     * @return boolean
     */
    protected function validateImages(&$data, &$errors, $line, $operation)
    {
        if (!isset($data['file_id'])) {
            return true;
        }

        $download = $this->import->getImages($data['file_id'], $operation);
        if ($download['errors']) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => implode(',', $download['errors'])));
        }

        $image = $download['images'] ? reset($download['images']) : array();

        if (isset($image['path'])) {
            $data['file_id'] = $this->image->add($image);
        }

        return true;
    }
    
    /**
     * Updates a combination
     * @param string $combination_id
     * @param array $data
     * @return boolean
     */
    protected function update($combination_id, $data)
    {
        return (int) $this->product->updateCombination($combination_id, $data);
    }
    
    /**
     * Adds a combination
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function add(&$data, &$errors, $line)
    {

        // validate

        return $this->product->addCombination($data) ? 1 : 0;
    }
    
}
