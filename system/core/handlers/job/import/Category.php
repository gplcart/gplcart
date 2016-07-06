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
use core\models\Category as C;
use core\models\CategoryGroup;
use core\models\Alias;
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
     * Category model instance
     * @var \core\models\Category $category
     */
    protected $category;

    /**
     * Category group model instance
     * @var \core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Url model instance
     * @var \core\models\Alias $alias
     */
    protected $alias;

    /**
     *
     * @param Import $import
     * @param Language $language
     * @param User $user
     * @param C $category
     * @param CategoryGroup $category_group
     * @param Alias $alias
     * @param Csv $csv
     */
    public function __construct(Import $import, Language $language, User $user, C $category, CategoryGroup $category_group, Alias $alias, Csv $csv)
    {
        $this->import = $import;
        $this->language = $language;
        $this->category = $category;
        $this->category_group = $category_group;
        $this->alias = $alias;
        $this->user = $user;
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
            $update = (isset($data['category_id']) && is_numeric($data['category_id']));

            if ($update && !$this->user->access('category_edit')) {
                continue;
            }

            if (!$update && !$this->user->access('category_add')) {
                continue;
            }

            if (!$this->validateTitle($data, $errors, $line)) {
                continue;
            }

            if (!empty($options['unique']) && !$this->validateUnique($data, $errors, $line, $update)) {
                continue;
            }

            if (!$this->validateParent($data, $errors, $line, $update)) {
                continue;
            }

            if (!$this->validateCategoryGroup($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateAlias($data, $errors, $line, $update)) {
                continue;
            }

            if (!$this->validateImages($data, $errors, $line, $operation)) {
                continue;
            }

            if ($update) {
                $updated += $this->update($data['category_id'], $data);
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
        // Convert "yes, y, true, on" into boolean value
        if (isset($data['status'])) {
            $data['status'] = $this->import->toBool($data['status']);
        }

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
     * Whether the category is unique
     * @param array $data
     * @param array $errors
     * @param boolean $update
     * @return boolean
     */
    protected function validateUnique(&$data, &$errors, $line, $update)
    {
        if (!isset($data['title'])) {
            return true;
        }

        $unique = true;
        $existing = $this->getCategory($data['title']);
        if ($existing) {
            $unique = false;
        }

        if ($update && isset($existing['category_id']) && $existing['category_id'] == $data['category_id']) {
            $unique = true;
        }

        if (!$unique) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Category name already exists')));
            return false;
        }

        return true;
    }

    /**
     *
     * @param type $category_id
     * @return array
     */
    protected function getCategory($category_id)
    {
        if (is_numeric($category_id)) {
            return $this->category->get($category_id);
        }

        $categories = $this->category->getList(array('title' => $category_id));

        $matches = array();
        foreach ($categories as $category) {
            if ($category['title'] === $category_id) {
                $matches[] = $category;
            }
        }

        return (count($matches) == 1) ? reset($matches) : $matches;
    }

    /**
     * Validates a parent category
     * @param array $data
     * @param array $errors
     * @param boolean $update
     * @return boolean
     */
    protected function validateParent(&$data, &$errors, $line, $update)
    {
        if (!isset($data['parent_id'])) {
            return true;
        }

        $category = $this->getCategory($data['parent_id']);

        if (empty($category['category_id'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Parent category @id neither exists or unique', array(
                    '@id' => $data['parent_id']))));
            return false;
        }

        $data['parent_id'] = $category['category_id'];

        // Parent category ID cannot be the same as category ID
        if ($update && $data['parent_id'] == $data['category_id']) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Category ID @cid must not match the parent ID @pid', array(
                    '@pid' => $data['parent_id'], '@cid' => $data['category_id']))));
            return false;
        }

        return true;
    }

    /**
     * Validates a category group
     * @param array $data
     * @param array $errors
     * @return boolean
     */
    protected function validateCategoryGroup(&$data, &$errors, $line)
    {
        if (!isset($data['category_group_id'])) {
            return true;
        }

        $group = $this->getCategoryGroup($data['category_group_id']);
        if (empty($group['category_group_id'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Invalid category group @id', array(
                    '@id' => $data['category_group_id']))));
            return false;
        }
        $data['category_group_id'] = $group['category_group_id'];
        return true;
    }

    /**
     *
     * @param type $category_group_id
     * @return array
     */
    protected function getCategoryGroup($category_group_id)
    {
        if (is_numeric($category_group_id)) {
            return $this->category_group->get($category_group_id);
        }

        $matches = array();
        foreach ($this->category_group->getList(array('title' => $category_group_id)) as $group) {
            if ($group['title'] === $category_group_id) {
                $matches[] = $group;
            }
        }

        return (count($matches) == 1) ? reset($matches) : $matches;
    }

    /**
     * Validates an alias
     * @param array $data
     * @param array $errors
     * @param boolean $update
     * @return boolean
     */
    protected function validateAlias(&$data, &$errors, $line, $update)
    {
        if (!isset($data['alias'])) {
            return true;
        }

        if ($data['alias'] === $this->import->getCsvAutoTag()) {
            $data['alias'] = $this->category->createAlias($data);
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

        if ($update && isset($alias['id_value']) && $alias['id_value'] == $data['category_id']) {
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
        if (!isset($data['image'])) {
            return true;
        }

        $download = $this->import->getImages($data['image'], $operation);
        if ($download['errors']) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => implode(',', $download['errors'])));
        }
        $data['image'] = $download['images'];
        return true;
    }

    protected function update($category_id, $data)
    {
        return (int) $this->category->update($category_id, $data);
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

        if (!isset($data['meta_description']) && isset($data['description_1'])) {
            $data['meta_description'] = mb_strimwidth($data['description_1'], 0, 255);
        }

        if (empty($data['title'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Empty category title')));
            return 0;
        }

        if (empty($data['category_group_id'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Empty category group')));
            return 0;
        }

        return $this->category->add($data) ? 1 : 0;
    }
}
