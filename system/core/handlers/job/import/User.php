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
use core\models\Store as ModelsStore;
use core\models\Import as ModelsImport;
use core\models\Language as ModelsLanguage;
use core\models\UserRole as ModelsUserRole;

/**
 * Imports users from CSV file
 */
class User
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
     * CSV parser class instance
     * @var \core\classes\Csv $csv
     */
    protected $csv;

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * User role model instance
     * @var \core\models\UserRole $role
     */
    protected $role;

    /**
     * Store model instance
     * @var \core\models\Store $store
     */
    protected $store;

    /**
     * Constructor
     * @param ModelsImport $import
     * @param ModelsLanguage $language
     * @param ModelsUser $user
     * @param ModelsStore $store
     * @param ModelsUserRole $role
     * @param Csv $csv
     */
    public function __construct(ModelsImport $import, ModelsLanguage $language,
            ModelsUser $user, ModelsStore $store, ModelsUserRole $role, Csv $csv)
    {
        $this->csv = $csv;
        $this->user = $user;
        $this->role = $role;
        $this->store = $store;
        $this->import = $import;
        $this->language = $language;
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

        foreach ($rows as $index => $row) {
            $line += $index;
            $data = array_filter(array_map('trim', $row));
            $update = (isset($data['user_id']) && is_numeric($data['user_id']));

            if ($update && !$this->user->access('user_edit')) {
                continue;
            }

            if (!$update && !$this->user->access('user_add')) {
                continue;
            }

            if (!$this->validateName($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateEmail($data, $errors, $line, $update)) {
                continue;
            }

            if (!$this->validatePassword($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateRole($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateStore($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateCreate($data, $errors, $line)) {
                continue;
            }

            if ($update) {
                $updated += $this->update($data['field_value_id'], $data);
                continue;
            }

            $inserted += $this->add($data, $errors, $line);
        }

        return array('inserted' => $inserted, 'updated' => $updated, 'errors' => $errors);
    }

    /**
     * Validates a user name
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateName(array &$data, array &$errors, $line)
    {
        if (isset($data['name']) && mb_strlen($data['name']) > 255) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Title must not be longer than 255 characters')));
            return false;
        }

        return true;
    }

    /**
     * Validates an e-mail
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @param boolean $update
     * @return boolean
     */
    protected function validateEmail(array &$data, array &$errors, $line,
            $update)
    {
        if (!isset($data['email'])) {
            return true;
        }

        $existing = $this->user->getByEmail($data['email']);
        $unique = empty($existing);

        if ($update && isset($existing['user_id']) && $existing['user_id'] == $data['user_id']) {
            $unique = true;
            $data['email'] = null;
        }

        if (!$unique) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('E-mail already exists')));
            return false;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Invalid E-mail')));
            return false;
        }

        return true;
    }

    /**
     * Validates a password
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validatePassword(array &$data, array &$errors, $line)
    {
        if (!isset($data['password'])) {
            return true;
        }

        if ($data['password'] === $this->import->getCsvAutoTag()) {
            $data['password'] = $this->user->generatePassword();
            return true;
        }

        $password_length = mb_strlen($data['password']);
        $limits = $this->user->getPasswordLength();

        if (($limits['min'] <= $password_length) && ($password_length <= $limits['max'])) {
            return true;
        }

        $error = $this->language->text('Password must be %min - %max characters long', array(
            '%min' => $limits['min'], '%max' => $limits['max']));

        $errors[] = $this->language->text('Line @num: @error', array(
            '@num' => $line,
            '@error' => $error));

        return false;
    }

    /**
     * Validates a role
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateRole(array &$data, array &$errors, $line)
    {
        if (!isset($data['role_id'])) {
            return true;
        }

        $role = $this->getRole($data['role_id']);

        if (empty($role['role_id'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Role @id neither exists or unique', array(
                    '@id' => $data['role_id']))));
            return false;
        }

        $data['role_id'] = $role['role_id'];
        return true;
    }

    /**
     * Returns an array of role data
     * @param integer|string $role_id
     * @return array
     */
    protected function getRole($role_id)
    {
        if (is_numeric($role_id)) {
            return $this->role->get($role_id);
        }

        $matches = array();
        foreach ($this->role->getList(array('name' => $role_id)) as $role) {
            if ($role['name'] === $role_id) {
                $matches[] = $role;
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
            return true;
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
     * Returns an array of store data
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
     * Validates the user created date
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateCreate(array &$data, array &$errors, $line)
    {
        if (!isset($data['created'])) {
            return true;
        }

        $timestamp = strtotime($data['created']);

        if (empty($timestamp) || $timestamp > GC_TIME) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Invalid date @date', array(
                    '@date' => $data['created']))));
            return false;
        }

        $data['created'] = $timestamp;
        return true;
    }

    /**
     * Updates a user
     * @param integer $user_id
     * @param array $data
     * @return integer
     */
    protected function update($user_id, array $data)
    {
        return (int) $this->user->update($user_id, $data);
    }

    /**
     * Adds a new user
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return integer
     */
    protected function add(array &$data, array &$errors, $line)
    {
        if (empty($data['name'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Name cannot be empty, skipped')));
            return 0;
        }

        if (empty($data['email'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('E-mail cannot be empty, skipped')));
            return 0;
        }

        if (empty($data['password'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Password cannot be empty, skipped')));
            return 0;
        }

        $result = $this->user->add($data);
        return empty($result) ? 0 : 1;
    }

}
