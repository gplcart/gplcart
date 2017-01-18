<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\Container as Container;

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
     * An array of submitted values
     * @var array
     */
    protected $submitted = array();

    /**
     * An array of options
     * @var array
     */
    protected $options = array();

    /**
     * Alias model instance
     * @var \gplcart\core\models\Alias $alias
     */
    protected $alias;

    /**
     * Store model instance
     * @var \gplcart\core\models\Store $store
     */
    protected $store;

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user = Container::get('gplcart\\core\\models\\User');
        $this->store = Container::get('gplcart\\core\\models\\Store');
        $this->alias = Container::get('gplcart\\core\\models\\Alias');
        $this->language = Container::get('gplcart\\core\\models\\Language');
    }

    /**
     * Returns a submitted value
     * @param null|string $key
     * @return mixed
     */
    protected function getSubmitted($key = null)
    {
        if (!isset($key)) {
            return $this->submitted;
        }

        $parents = $this->getParents($key);

        if (!isset($parents)) {
            return $this->submitted;
        }

        return gplcart_array_get_value($this->submitted, $parents);
    }

    /**
     * Sets a value to an array of submitted values
     * @param string $key
     */
    public function setSubmitted($key, $value)
    {
        $parents = $this->getParents($key);
        gplcart_array_set_value($this->submitted, $parents, $value);
    }

    /**
     * Removes a value from an array of submitted values
     * @param string $key
     */
    public function unsetSubmitted($key)
    {
        $parents = $this->getParents($key);
        gplcart_array_unset_value($this->submitted, $parents);
    }

    /**
     * Whether we update the submitted object
     * @param string $key
     * @return boolean
     */
    protected function isUpdating($key = 'update')
    {
        return !empty($this->submitted[$key]);
    }

    /**
     * Sets a data of updating object to the submitted values
     * @param mixed $data
     * @param string $key
     */
    protected function setUpdating($data, $key = 'update')
    {
        $this->submitted[$key] = $data;
    }

    /**
     * Returns an array of entity to be updated
     * @param string $key
     * @return array
     */
    protected function getUpdating($key = 'update')
    {
        return empty($this->submitted[$key]) ? array() : $this->submitted[$key];
    }

    /**
     * Returns either an ID of entity to be updated or false if no ID found (adding).
     * It also returns false if an array of updating entity has been loaded and set
     * @param string $key
     * @return boolean|string|integer
     */
    protected function getUpdatingId($key = 'update')
    {
        if (empty($this->submitted[$key]) || is_array($this->submitted[$key])) {
            return false;
        }
        return $this->submitted[$key];
    }

    /**
     * Returns an array that represents a path to the nested array value
     * @param string|array $key A base key
     * @return array
     */
    protected function getParents($key)
    {
        if (empty($this->options['parents'])) {
            return $key;
        }

        if (is_string($this->options['parents'])) {
            $this->options['parents'] = explode('.', $this->options['parents']);
        }

        return array_merge((array) $this->options['parents'], (array) $key);
    }

    /**
     * Sets a validation error
     * @param string $key
     * @param string $error
     */
    protected function setError($key, $error)
    {
        $parents = $this->getParents($key);
        gplcart_array_set_value($this->errors, $parents, $error);
    }

    /**
     * Whether an error(s) exist
     * @param string|null $key
     * @return boolean
     */
    protected function isError($key = null)
    {
        if (!isset($key)) {
            return !empty($this->errors);
        }

        $result = $this->getError($key);
        return !empty($result);
    }

    /**
     * Returns an error
     * @param string|null $key
     * @return mixed
     */
    protected function getError($key = null)
    {
        if (!isset($key)) {
            return $this->errors;
        }

        $parents = $this->getParents($key);
        return gplcart_array_get_value($this->errors, $parents);
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
     * @return boolean|null
     */
    protected function validateTitle()
    {
        $title = $this->getSubmitted('title');

        if ($this->isUpdating() && !isset($title)) {
            return null;
        }

        if (empty($title) || mb_strlen($title) > 255) {
            $vars = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Title'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('title', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a name
     * @return boolean|null
     */
    protected function validateName()
    {
        $name = $this->getSubmitted('name');

        if ($this->isUpdating() && !isset($name)) {
            return null;
        }

        if (empty($name) || mb_strlen($name) > 255) {
            $vars = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Name'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('name', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a meta title
     * @return boolean
     */
    protected function validateMetaTitle()
    {
        $meta_title = $this->getSubmitted('meta_title');

        if (isset($meta_title) && mb_strlen($meta_title) > 60) {
            $vars = array('@max' => 60, '@field' => $this->language->text('Meta title'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('meta_title', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a meta description
     * @return boolean
     */
    protected function validateMetaDescription()
    {
        $meta_description = $this->getSubmitted('meta_description');

        if (isset($meta_description) && mb_strlen($meta_description) > 160) {
            $vars = array('@max' => 160, '@field' => $this->language->text('Meta description'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('meta_description', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a description field
     * @return boolean
     */
    protected function validateDescription()
    {
        $description = $this->getSubmitted('description');

        if (isset($description) && mb_strlen($description) > 65535) {
            $vars = array('@max' => 65535, '@field' => $this->language->text('Description'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('description', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a weight field
     * @return boolean
     */
    protected function validateWeight()
    {
        $weight = $this->getSubmitted('weight');

        if (isset($weight) && !is_numeric($weight)) {
            $vars = array('@field' => $this->language->text('Weight'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('weight', $error);
            return false;
        }

        return true;
    }

    /**
     * Sets "Status" field to an integer value
     * @return boolean
     */
    protected function validateStatus()
    {
        $status = $this->getSubmitted('status');

        if (isset($status)) {
            $value = (int) gplcart_string_bool($status);
            $this->setSubmitted('status', $value);
        }

        return true;
    }

    /**
     * Sets "Default" field to integer value
     * @return boolean
     */
    protected function validateDefault()
    {
        $default = $this->getSubmitted('default');

        if (isset($default)) {
            $value = (int) gplcart_string_bool($default);
            $this->setSubmitted('default', $value);
        }

        return true;
    }

    /**
     * Validates category translations
     * @return boolean|null
     */
    protected function validateTranslation()
    {
        $translations = $this->getSubmitted('translation');

        if (empty($translations)) {
            return null;
        }

        // Max allowed length per field name
        $lengths = array(
            'meta_title' => 60,
            'meta_description' => 160
        );

        foreach ($translations as $lang => $translation) {
            foreach ($translation as $field => $value) {

                // Empty fields have no sence, remove them
                if ($value === '') {
                    unset($translations[$lang][$field]);
                    continue;
                }

                // Default length is 255 chars
                $max = isset($lengths[$field]) ? $lengths[$field] : 255;

                if (mb_strlen($value) > $max) {
                    $vars = array('@field' => ucfirst(str_replace('_', ' ', $field)), '@lang' => $lang, '@max' => $max);
                    $error = $this->language->text('@field in @lang must not be longer than @max characters', $vars);
                    $this->setError("translation.$lang.$field", $error);
                }
            }

            // If all translation fields were removed, remove also the language key
            if (empty($translations[$lang])) {
                unset($translations[$lang]);
            }
        }

        // Set possible updates
        $this->setSubmitted('translation', $translations);
        return !$this->isError('translation');
    }

    /**
     * Validates / prepares an array of submitted images
     * @return null|bool
     */
    protected function validateImages()
    {
        $images = $this->getSubmitted('images');

        if (empty($images) || !is_array($images)) {
            return null;
        }

        $title = $this->getSubmitted('title');

        foreach ($images as &$image) {

            if (empty($image['title']) && !empty($title)) {
                $image['title'] = $title;
            }

            if (empty($image['description']) && !empty($title)) {
                $image['description'] = $title;
            }

            $image['title'] = mb_strimwidth($image['title'], 0, 255, '');

            if (empty($image['translation'])) {
                continue;
            }

            foreach ($image['translation'] as &$translation) {
                $translation['title'] = mb_strimwidth($translation['title'], 0, 255, '');
            }
        }

        $this->setSubmitted('images', $images);
        return true;
    }

    /**
     * Validates an alias
     * @return boolean|null
     */
    protected function validateAlias()
    {
        $alias = $this->getSubmitted('alias');

        if (empty($alias)) {
            return null;
        }

        if (mb_strlen($alias) > 255) {
            $vars = array('@max' => 255, '@field' => $this->language->text('Alias'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('alias', $error);
            return false;
        }

        if (preg_match('/^[A-Za-z0-9_.-]+$/', $alias) !== 1) {
            $error = $this->language->text('Alias must contain only alphanumeric characters, dashes, dots and underscores');
            $this->setError('alias', $error);
            return false;
        }

        if ($this->alias->exists($alias)) {
            $vars = array('@object' => $this->language->text('Alias'));
            $error = $this->language->text('@object already exists', $vars);
            $this->setError('alias', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates store ID field
     * @return boolean|null
     */
    protected function validateStoreId()
    {
        $store_id = $this->getSubmitted('store_id');

        if ($this->isUpdating() && !isset($store_id)) {
            return null;
        }

        if (empty($store_id)) {
            $vars = array('@field' => $this->language->text('Store'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('store_id', $error);
            return false;
        }

        if (!is_numeric($store_id)) {
            $vars = array('@field' => $this->language->text('Store'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('store_id', $error);
            return false;
        }

        $store = $this->store->get($store_id);

        if (empty($store)) {
            $vars = array('@name' => $this->language->text('Store'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('store_id', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a user ID
     * @return boolean|null
     */
    protected function validateUserId()
    {
        $user_id = $this->getSubmitted('user_id');

        if ($this->isUpdating() && !isset($user_id)) {
            return null;
        }

        if (empty($user_id)) {
            $vars = array('@field' => $this->language->text('User'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('user_id', $error);
            return false;
        }

        if (!is_numeric($user_id)) {
            $vars = array('@field' => $this->language->text('User'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('user_id', $error);
            return false;
        }

        $user = $this->user->get($user_id);

        if (empty($user)) {
            $vars = array('@name' => $this->language->text('User'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('user_id', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a user cart ID
     * @return boolean|null
     */
    protected function validateUserCartId()
    {
        $user_id = $this->getSubmitted('user_id');

        if ($this->isUpdating() && !isset($user_id)) {
            return null;
        }

        if (empty($user_id)) {
            $vars = array('@field' => $this->language->text('User'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('user_id', $error);
            return false;
        }

        if (strlen($user_id) > 255) {
            $vars = array('@max' => 255, '@field' => $this->language->text('User'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('user_id', $error);
            return false;
        }

        if (!is_numeric($user_id)) {
            return true; // Anonymous user
        }

        $user = $this->user->get($user_id);

        if (empty($user)) {
            $vars = array('@name' => $this->language->text('User'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('user_id', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates an E-mail
     * @return boolean
     */
    protected function validateEmail()
    {
        $value = $this->getSubmitted('email');

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('E-mail'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('email', $error);
            return false;
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $error = $this->language->text('Invalid E-mail');
            $this->setError('email', $error);
            return false;
        }

        return true;
    }

}
