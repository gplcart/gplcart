<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\Container;
use core\classes\Tool;

/**
 * Base validator class
 */
class Base
{

    /**
     * An array of validation errors
     * @var array
     */
    protected $errors = array();

    /**
     * Alias model instance
     * @var \core\models\Alias $alias
     */
    protected $alias;

    /**
     * Store model instance
     * @var \core\models\Store $store
     */
    protected $store;

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     */
    public function __construct()
    {
        /* @var $user \core\models\User */
        $this->user = Container::instance('core\\models\\User');

        /* @var $store \core\models\Store */
        $this->store = Container::instance('core\\models\\Store');

        /* @var $alias \core\models\Alias */
        $this->alias = Container::instance('core\\models\\Alias');

        /* @var $language \core\models\Language */
        $this->language = Container::instance('core\\models\\Language');
    }

    /**
     * Returns a submitted value
     * @param string $key
     * @param array $submitted
     * @param array $options
     * @return mixed
     */
    protected function getSubmitted($key, $submitted, $options = array())
    {
        $parents = $this->getParents($key, $options);
        return Tool::getArrayValue($submitted, $parents);
    }

    /**
     * Sets a value to an array of submitted values
     * @param string $key
     * @param array $submitted
     * @param array $options
     */
    public function setSubmitted($key, $value, &$submitted, $options = array())
    {
        $parents = $this->getParents($key, $options);
        Tool::setArrayValue($submitted, $parents, $value);
    }

    /**
     * Removes a value(s) from an array of submitted data
     * @param string|array $key
     */
    public function unsetSubmitted($key, &$submitted, $options = array())
    {
        $parents = $this->getParents($key, $options);
        Tool::unsetArrayValue($submitted, $parents);
    }

    /**
     * Returns an array that represents a path to the nested array value
     * @param string $key A base key
     * @param array $options
     * @return array
     */
    protected function getParents($key, array $options)
    {
        if (empty($options['parents'])) {
            return $key;
        }

        if (is_string($options['parents'])) {
            $parents = implode('.', $options['parents']);
        } else {
            $parents = (array) $options['parents'];
        }

        return array_merge((array) $key, $parents);
    }

    /**
     * Sets an error
     * @param string|array $key
     * @param mixed $value
     */
    protected function setError($key, $value, array $options = array())
    {
        $parents = $this->getParents($key, $options);
        Tool::setArrayValue($this->errors, $parents, $value);
    }

    /**
     * Whether an error(s) exist
     * @param string|null $key
     * @param array $options
     * @return boolean
     */
    protected function isError($key = null, $options = array())
    {
        if (!isset($key)) {
            return !empty($this->errors);
        }

        $result = $this->getError($key, $options);
        return !empty($result);
    }

    /**
     * Returns an error
     * @param string $key
     * @param array $options
     * @return mixed
     */
    protected function getError($key, array $options = array())
    {
        $parents = $this->getParents($key, $options);
        return Tool::getArrayValue($this->errors, $parents);
    }

    /**
     * Returns validation results
     * @return array|boolean
     */
    protected function getResult()
    {
        $result = empty($this->errors) ? true : $this->errors;
        $this->errors = array(); // Important. Reset all errors
        return $result;
    }

    /**
     * Validates a title
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateTitle(array &$submitted, array $options = array())
    {
        $title = $this->getSubmitted('title', $submitted, $options);

        if (!empty($submitted['update']) && !isset($title)) {
            return null;
        }

        if (empty($title) || mb_strlen($title) > 255) {
            $vars = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Title'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('title', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a name
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateName(array &$submitted, array $options = array())
    {
        $name = $this->getSubmitted('name', $submitted, $options);

        if (!empty($submitted['update']) && !isset($name)) {
            return null;
        }

        if (empty($name) || mb_strlen($name) > 255) {
            $vars = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Name'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('name', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a meta title
     * @param array $submitted
     * @param array $options
     * @return boolean
     */
    protected function validateMetaTitle(array &$submitted, $options = array())
    {
        $meta_title = $this->getSubmitted('meta_title', $submitted, $options);

        if (isset($meta_title) && mb_strlen($meta_title) > 60) {
            $vars = array('@max' => 60, '@field' => $this->language->text('Meta title'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('meta_title', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a meta description
     * @param array $submitted
     * @param array $options
     * @return boolean
     */
    protected function validateMetaDescription(&$submitted, $options = array())
    {
        $meta_description = $this->getSubmitted('meta_description', $submitted, $options);

        if (isset($meta_description) && mb_strlen($meta_description) > 160) {
            $vars = array('@max' => 160, '@field' => $this->language->text('Meta description'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('meta_description', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a description field
     * @param array $submitted
     * @param array $options
     * @return boolean
     */
    protected function validateDescription(&$submitted, $options = array())
    {
        $description = $this->getSubmitted('description', $submitted, $options);

        if (isset($description) && mb_strlen($description) > 65535) {
            $vars = array('@max' => 65535, '@field' => $this->language->text('Description'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('description', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a weight field
     * @param array $submitted
     * @param array $options
     * @return boolean
     */
    protected function validateWeight(&$submitted, $options = array())
    {
        $weight = $this->getSubmitted('weight', $submitted, $options);

        if (isset($weight) && !is_numeric($weight)) {
            $vars = array('@field' => $this->language->text('Weight'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('weight', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Sets "Status" field to integer value
     * @param array $submitted
     * @param array $options
     * @return boolean
     */
    protected function validateStatus(array &$submitted, $options = array())
    {
        $status = $this->getSubmitted('status', $submitted, $options);

        if (isset($status)) {
            $value = (int) Tool::toBool($status);
            $this->setSubmitted('status', $value, $submitted, $options);
        }

        return true;
    }

    /**
     * Sets "Default" field to integer value
     * @param array $submitted
     * @param array $options
     * @return boolean
     */
    protected function validateDefault(array &$submitted, $options = array())
    {
        $default = $this->getSubmitted('default', $submitted, $options);

        if (isset($default)) {
            $value = (int) Tool::toBool($default);
            $this->setSubmitted('default', $value, $submitted, $options);
        }

        return true;
    }

    /**
     * Validates category translations
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateTranslation(array &$submitted)
    {
        if (empty($submitted['translation'])) {
            return null;
        }

        $lengths = array('meta_title' => 60, 'meta_description' => 160);

        foreach ($submitted['translation'] as $lang => $translation) {
            foreach ($translation as $field => $value) {
                $max = isset($lengths[$field]) ? $lengths[$field] : 255;
                if (mb_strlen($value) > $max) {
                    $vars = array('@field' => ucfirst(str_replace('_', ' ', $field)), '@lang' => $lang, '@max' => $max);
                    $this->errors['translation'][$lang][$field] = $this->language->text('@field in @lang must not be longer than @max characters', $vars);
                }
            }
        }

        return empty($this->errors['translation']);
    }

    /**
     * Validates / prepares an array of submitted images
     * @param array $submitted
     * @return null|bool
     */
    protected function validateImages(array &$submitted)
    {
        if (empty($submitted['images']) || !is_array($submitted['images'])) {
            return null;
        }

        foreach ($submitted['images'] as &$image) {

            if (empty($image['title']) && !empty($submitted['title'])) {
                $image['title'] = $submitted['title'];
            }

            if (empty($image['description']) && !empty($submitted['title'])) {
                $image['description'] = $submitted['title'];
            }

            $image['title'] = mb_strimwidth($image['title'], 0, 255, '');

            if (empty($image['translation'])) {
                continue;
            }

            foreach ($image['translation'] as &$translation) {
                $translation['title'] = mb_strimwidth($translation['title'], 0, 255, '');
            }
        }

        return true;
    }

    /**
     * Validates an alias
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateAlias(array &$submitted)
    {
        if (empty($submitted['alias'])) {
            return null;
        }

        if (mb_strlen($submitted['alias']) > 255) {
            $vars = array('@max' => 255, '@field' => $this->language->text('Alias'));
            $this->errors['alias'] = $this->language->text('@field must not be longer than @max characters', $vars);
            return false;
        }

        if (!preg_match('/^[A-Za-z0-9_.-]+$/', $submitted['alias'])) {
            $this->errors['alias'] = $this->language->text('Alias must contain only alphanumeric characters, dashes, dots and underscores');
            return false;
        }

        if ($this->alias->exists($submitted['alias'])) {
            $vars = array('@object' => $this->language->text('Alias'));
            $this->errors['alias'] = $this->language->text('@object already exists', $vars);
            return false;
        }

        return true;
    }

    /**
     * Validates store ID field
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateStoreId(array $submitted,
            array $options = array())
    {
        $store_id = $this->getSubmitted('store_id', $submitted, $options);

        if (!empty($submitted['update']) && !isset($store_id)) {
            return null;
        }

        if (empty($store_id)) {
            $vars = array('@field' => $this->language->text('Store'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('store_id', $error, $options);
            return false;
        }

        if (!is_numeric($store_id)) {
            $vars = array('@field' => $this->language->text('Store'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('store_id', $error, $options);
            return false;
        }

        $store = $this->store->get($store_id);

        if (empty($store)) {
            $vars = array('@name' => $this->language->text('Store'));
            $error = $this->language->text('Object @name does not exist', $vars);
            $this->setError('store_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a user ID
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateUserId(array $submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['user_id'])) {
            return null;
        }

        if (empty($submitted['user_id'])) {
            $vars = array('@field' => $this->language->text('User'));
            $this->errors['user_id'] = $this->language->text('@field is required', $vars);
            return false;
        }

        if (!is_numeric($submitted['user_id'])) {
            $vars = array('@field' => $this->language->text('User'));
            $this->errors['user_id'] = $this->language->text('@field must be numeric', $vars);
            return false;
        }

        $user = $this->user->get($submitted['user_id']);

        if (empty($user)) {
            $vars = array('@name' => $this->language->text('User'));
            $this->errors['user_id'] = $this->language->text('Object @name does not exist', $vars);
            return false;
        }

        return true;
    }

    /**
     * Validates a user cart ID
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateUserCartId(array &$submitted,
            array $options = array())
    {
        $user_id = $this->getSubmitted('user_id', $submitted, $options);

        if (!empty($submitted['update']) && !isset($user_id)) {
            return null;
        }

        if (empty($user_id)) {
            $vars = array('@field' => $this->language->text('User'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('user_id', $error, $options);
            return false;
        }

        if (strlen($user_id) > 255) {
            $vars = array('@max' => 255, '@field' => $this->language->text('User'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('user_id', $error, $options);
            return false;
        }

        if (!is_numeric($user_id)) {
            return true; // Anonymous user
        }

        $user = $this->user->get($user_id);

        if (empty($user)) {
            $vars = array('@name' => $this->language->text('User'));
            $error = $this->language->text('Object @name does not exist', $vars);
            $this->setError('user_id', $error, $options);
            return false;
        }

        return true;
    }

}
