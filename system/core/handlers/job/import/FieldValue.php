<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\job\import;

use core\classes\Csv;
use core\models\User as ModelsUser;
use core\models\Field as ModelsField;
use core\models\Import as ModelsImport;
use core\models\Language as ModelsLanguage;
use core\models\FieldValue as ModelsFieldValue;

/**
 * Imports field values from CSV file
 */
class FieldValue
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
     * Field value model instance
     * @var \core\models\FieldValue $field_value
     */
    protected $field_value;

    /**
     * Field model instance
     * @var \core\models\Field $field
     */
    protected $field;

    /**
     * Constructor
     * @param Import $import
     * @param Language $language
     * @param User $user
     * @param Fv $field_value
     * @param Field $field
     * @param Csv $csv
     */
    public function __construct(ModelsImport $import, ModelsLanguage $language,
            ModelsUser $user, ModelsFieldValue $field_value, ModelsField $field,
            Csv $csv)
    {
        $this->csv = $csv;
        $this->user = $user;
        $this->field = $field;
        $this->import = $import;
        $this->language = $language;
        $this->field_value = $field_value;
    }

    /**
     * Processes one job iteration
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
     * Adds/updates from an array of rows
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
            $update = (isset($data['field_value_id']) && is_numeric($data['field_value_id']));

            if ($update && !$this->user->access('field_value_edit')) {
                continue;
            }

            if (!$update && !$this->user->access('field_value_add')) {
                continue;
            }

            if (!$this->validateTitle($data, $errors, $line)) {
                continue;
            }

            if (!empty($job['data']['unique']) && !$this->validateUnique($data, $errors, $line, $update)) {
                continue;
            }

            if (!$this->validateField($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateColor($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateImages($data, $errors, $line, $operation)) {
                continue;
            }

            $this->validateWeight($data, $errors, $line);

            if ($update) {
                $updated += $this->update($data['field_value_id'], $data);
                continue;
            }

            $inserted += $this->add($data, $errors, $line);
        }

        return array('inserted' => $inserted, 'updated' => $updated, 'errors' => $errors);
    }

    /**
     * Validates field value weight
     * @param array $data
     * @param array $errors
     * @param integer $line
     */
    protected function validateWeight(array &$data, array &$errors, $line)
    {
        if (isset($data['weight'])) {
            $data['weight'] = (int) $data['weight'];
        }
    }

    /**
     * Validates titles
     * @param array $data
     * @param array $errors
     * @param type $line
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

        return true;
    }

    /**
     * Check if a fied value exists
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @param boolean $update
     * @return boolean
     */
    protected function validateUnique(array &$data, array &$errors, $line,
            $update)
    {
        if (!isset($data['title'])) {
            return true;
        }

        $existing = $this->getFieldValue($data['title']);
        $unique = empty($existing);

        if ($update && isset($existing['field_value_id']) && $existing['field_value_id'] == $data['field_value_id']) {
            $unique = true;
            $data['title'] = null;
        }

        if (!$unique) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Field value name already exists')));
            return false;
        }

        return true;
    }

    /**
     * Returns an array of field value data by ID or title
     * @param integer|string $field_value_id
     * @return array
     */
    protected function getFieldValue($field_value_id)
    {
        if (is_numeric($field_value_id)) {
            return $this->field_value->get($field_value_id);
        }

        $matches = array();
        foreach ($this->field_value->getList(array('title' => $field_value_id)) as $field_value) {
            if ($field_value['title'] === $field_value_id) {
                $matches[] = $field_value;
            }
        }

        return (count($matches) == 1) ? reset($matches) : $matches;
    }

    /**
     * Checks if a field exists and unique
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateField(array &$data, array &$errors, $line)
    {
        if (!isset($data['field_id'])) {
            return true;
        }

        $field = $this->getField($data['field_id']);

        if (empty($field['field_id'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Field @id neither exists or unique', array(
                    '@id' => $data['field_id']))));
            return false;
        }

        $data['field_id'] = $field['field_id'];
        return true;
    }

    /**
     * Returns an array of field data by ID or title
     * @param integer|string $field_id
     * @return array
     */
    protected function getField($field_id)
    {
        if (is_numeric($field_id)) {
            return $this->field->get($field_id);
        }

        $matches = array();
        foreach ($this->field->getList(array('title' => $field_id)) as $field) {
            if ($field['title'] === $field_id) {
                $matches[] = $field;
            }
        }

        return (count($matches) == 1) ? reset($matches) : $matches;
    }

    /**
     * Validates HEX color code
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateColor(array &$data, array &$errors, $line)
    {
        if (isset($data['color']) && !preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $data['color'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Color @code in not a valid HEX code', array('@code' => $data['color']))));
            return false;
        }

        return true;
    }

    /**
     * Downloads and/or validates images
     * @param array $data
     * @param array $errors
     * @param array $operation
     * @return boolean
     */
    protected function validateImages(array &$data, array &$errors, $line,
            array $operation)
    {
        if (!isset($data['image'])) {
            return true;
        }

        $download = $this->import->getImages($data['image'], $operation);

        if (!empty($download['errors'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => implode(',', $download['errors'])));
        }

        $image = $download['images'] ? reset($download['images']) : array();

        if (isset($image['path'])) {
            $data['path'] = $image['path'];
        }

        return true;
    }

    /**
     * Updates a field value
     * @param integer $field_value_id
     * @param array $data
     * @return integer
     */
    protected function update($field_value_id, array $data)
    {
        return (int) $this->field_value->update($field_value_id, $data);
    }

    /**
     * Adds a new field value
     * @param array $data
     * @param array $errors
     * @return integer
     */
    protected function add(array &$data, array &$errors, $line)
    {
        if (empty($data['title'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Empty field value title, skipped')));
            return 0;
        }

        if (empty($data['field_id'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Empty field, skipped')));
            return 0;
        }

        if (!isset($data['weight'])) {
            $data['weight'] = $line;
        }

        $added = $this->field_value->add($data);
        return empty($added) ? 0 : 1;
    }

}
