<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\job\import;

use core\models\Import;
use core\models\Language;
use core\models\User;
use core\models\Field as F;
use core\classes\Csv;

class Field
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
     * Field model instance
     * @var \core\models\Field $field
     */
    protected $field;

    public function __construct(Import $import, Language $language, User $user, F $field, Csv $csv)
    {
        $this->import = $import;
        $this->language = $language;
        $this->user = $user;
        $this->field = $field;
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

        foreach ($rows as $index => $row) {
            $line += $index;
            $data = array_filter(array_map('trim', $row));
            $update = (isset($data['field_id']) && is_numeric($data['field_id']));

            if ($update && !$this->user->access('field_edit')) {
                continue;
            }

            if (!$update && !$this->user->access('field_add')) {
                continue;
            }
            
            if (!$this->validateTitle($data, $errors, $line)) {
                continue;
            }

            if (!empty($options['unique']) && !$this->validateUnique($data, $errors, $line, $update)) {
                continue;
            }

            if (!$this->validateWidget($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateType($data, $errors, $line)) {
                continue;
            }

            if (isset($data['weight'])) {
                $data['weight'] = (int) $data['weight'];
            }

            if ($update) {
                $updated += $this->update($data['field_id'], $data);
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

        return true;
    }

    /**
     *
     * @param type $data
     * @param type $errors
     * @param type $line
     * @param type $update
     * @return boolean
     */
    protected function validateUnique(&$data, &$errors, $line, $update)
    {
        if (!isset($data['title'])) {
            return true;
        }

        $unique = true;
        $existing = $this->getField($data['title']);
        if ($existing) {
            $unique = false;
        }

        if ($update && isset($existing['field_id']) && $existing['field_id'] == $data['field_id']) {
            $unique = true;
            $data['title'] = null;
        }

        if (!$unique) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Field name already exists')));
            return false;
        }

        return true;
    }

    /**
     *
     * @param type $field_id
     * @return type
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
     *
     * @param array $data
     * @param type $errors
     * @param type $line
     * @return boolean
     */
    protected function validateWidget(&$data, &$errors, $line)
    {
        if (!isset($data['widget'])) {
            return true;
        }

        $data['widget'] = strtolower($data['widget']);

        $types = $this->field->widgetTypes();

        if (empty($types[$data['widget']])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Invalid field widget')));
            return false;
        }

        return true;
    }

    /**
     *
     * @param array $data
     * @param type $errors
     * @param type $line
     * @return boolean
     */
    protected function validateType(&$data, &$errors, $line)
    {
        if (!isset($data['type'])) {
            return true;
        }

        $data['type'] = strtolower($data['type']);

        if (!in_array($data['type'], array('option', 'attribute'))) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Invalid field type')));
            return false;
        }

        return true;
    }

    /**
     *
     * @param type $field_id
     * @param type $data
     * @return type
     */
    protected function update($field_id, $data)
    {
        unset($data['type']);
        return (int) $this->field->update($field_id, $data);
    }

    /**
     *
     * @param type $data
     * @param type $errors
     * @return boolean
     */
    protected function add(&$data, &$errors, $line)
    {
        if (empty($data['title'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Field title is required')));
            return 0;
        }

        if (empty($data['widget'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Widget type is required')));
            return 0;
        }

        if (empty($data['type'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Field type is required')));
            return 0;
        }

        if (!isset($data['weight'])) {
            $data['weight'] = $line;
        }

        return $this->field->add($data) ? 1 : 0;
    }
}
