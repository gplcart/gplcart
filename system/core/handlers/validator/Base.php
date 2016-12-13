<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\Container as Container;

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
     * @param null|string $key
     * @param array $options
     * @return mixed
     */
    protected function getSubmitted($key, $options = array())
    {
        $parents = $this->getParents($key, $options);

        if (!isset($parents)) {
            return $this->submitted;
        }

        return gplcart_array_get_value($this->submitted, $parents);
    }

    /**
     * Sets a value to an array of submitted values
     * @param string $key
     * @param array $options
     */
    public function setSubmitted($key, $value, $options = array())
    {
        $parents = $this->getParents($key, $options);
        gplcart_array_set_value($this->submitted, $parents, $value);
    }

    /**
     * Removes a value from an array of submitted values
     * @param string $key
     * @param array $options
     */
    public function unsetSubmitted($key, $options = array())
    {
        $parents = $this->getParents($key, $options);
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
            $options['parents'] = explode('.', $options['parents']);
        }

        return array_merge((array) $options['parents'], (array) $key);
    }

    /**
     * Sets an error
     * @param string|array $key
     * @param mixed $value
     */
    protected function setError($key, $error, array $options = array())
    {
        $parents = $this->getParents($key, $options);
        gplcart_array_set_value($this->errors, $parents, $error);
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
     * @param array $options
     * @return boolean|null
     */
    protected function validateTitle(array $options = array())
    {
        $title = $this->getSubmitted('title', $options);

        if ($this->isUpdating() && !isset($title)) {
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
     * @param array $options
     * @return boolean|null
     */
    protected function validateName(array $options = array())
    {
        $name = $this->getSubmitted('name', $options);

        if ($this->isUpdating() && !isset($name)) {
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
     * @param array $options
     * @return boolean
     */
    protected function validateMetaTitle(array $options = array())
    {
        $meta_title = $this->getSubmitted('meta_title', $options);

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
     * @param array $options
     * @return boolean
     */
    protected function validateMetaDescription(array $options = array())
    {
        $meta_description = $this->getSubmitted('meta_description', $options);

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
     * @param array $options
     * @return boolean
     */
    protected function validateDescription(array $options = array())
    {
        $description = $this->getSubmitted('description', $options);

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
     * @param array $options
     * @return boolean
     */
    protected function validateWeight(array $options = array())
    {
        $weight = $this->getSubmitted('weight', $options);

        if (isset($weight) && !is_numeric($weight)) {
            $vars = array('@field' => $this->language->text('Weight'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('weight', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Sets "Status" field to an integer value
     * @param array $options
     * @return boolean
     */
    protected function validateStatus(array $options = array())
    {
        $status = $this->getSubmitted('status', $options);

        if (isset($status)) {
            $value = (int) gplcart_string_bool($status);
            $this->setSubmitted('status', $value, $options);
        }

        return true;
    }

    /**
     * Sets "Default" field to integer value
     * @param array $options
     * @return boolean
     */
    protected function validateDefault(array $options = array())
    {
        $default = $this->getSubmitted('default', $options);

        if (isset($default)) {
            $value = (int) gplcart_string_bool($default);
            $this->setSubmitted('default', $value, $options);
        }

        return true;
    }

    /**
     * Validates category translations
     * @param array $options
     * @return boolean|null
     */
    protected function validateTranslation(array $options = array())
    {
        $translations = $this->getSubmitted('translation', $options);

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
                    $this->setError("translation.$lang.$field", $error, $options);
                }
            }

            // If all translation fields were removed, remove also the language key
            if (empty($translations[$lang])) {
                unset($translations[$lang]);
            }
        }

        // Set possible updates
        $this->setSubmitted('translation', $translations, $options);
        return !$this->isError('translation', $options);
    }

    /**
     * Validates / prepares an array of submitted images
     * @param array $options
     * @return null|bool
     */
    protected function validateImages(array $options = array())
    {
        $images = $this->getSubmitted('images', $options);

        if (empty($images) || !is_array($images)) {
            return null;
        }

        $title = $this->getSubmitted('title', $options);

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

        $this->setSubmitted('images', $images, $options);
        return true;
    }

    /**
     * Validates an alias
     * @param array $options
     * @return boolean|null
     */
    protected function validateAlias(array $options = array())
    {
        $alias = $this->getSubmitted('alias', $options);

        if (empty($alias)) {
            return null;
        }

        if (mb_strlen($alias) > 255) {
            $vars = array('@max' => 255, '@field' => $this->language->text('Alias'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('alias', $error, $options);
            return false;
        }

        if (!preg_match('/^[A-Za-z0-9_.-]+$/', $alias)) {
            $error = $this->language->text('Alias must contain only alphanumeric characters, dashes, dots and underscores');
            $this->setError('alias', $error, $options);
            return false;
        }

        if ($this->alias->exists($alias)) {
            $vars = array('@object' => $this->language->text('Alias'));
            $error = $this->language->text('@object already exists', $vars);
            $this->setError('alias', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates store ID field
     * @param array $options
     * @return boolean|null
     */
    protected function validateStoreId(array $options = array())
    {
        $store_id = $this->getSubmitted('store_id', $options);

        if ($this->isUpdating() && !isset($store_id)) {
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
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('store_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a user ID
     * @param array $options
     * @return boolean|null
     */
    protected function validateUserId(array $options = array())
    {
        $user_id = $this->getSubmitted('user_id', $options);

        if ($this->isUpdating() && !isset($user_id)) {
            return null;
        }

        if (empty($user_id)) {
            $vars = array('@field' => $this->language->text('User'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('user_id', $error, $options);
            return false;
        }

        if (!is_numeric($user_id)) {
            $vars = array('@field' => $this->language->text('User'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('user_id', $error, $options);
            return false;
        }

        $user = $this->user->get($user_id);

        if (empty($user)) {
            $vars = array('@name' => $this->language->text('User'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('user_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a user cart ID
     * @param array $options
     * @return boolean|null
     */
    protected function validateUserCartId(array $options = array())
    {
        $user_id = $this->getSubmitted('user_id', $options);

        if ($this->isUpdating() && !isset($user_id)) {
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
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('user_id', $error, $options);
            return false;
        }

        return true;
    }

}
