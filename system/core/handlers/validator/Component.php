<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\Container as Container;
use gplcart\core\handlers\validator\Element as ElementValidator;

/**
 * Methods to validate components
 */
class Component extends ElementValidator
{

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
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->user = Container::get('gplcart\\core\\models\\User');
        $this->store = Container::get('gplcart\\core\\models\\Store');
        $this->alias = Container::get('gplcart\\core\\models\\Alias');
    }

    /**
     * Validates a title
     * @return boolean|null
     */
    protected function validateTitleComponent()
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
    protected function validateNameComponent()
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
    protected function validateMetaTitleComponent()
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
    protected function validateMetaDescriptionComponent()
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
    protected function validateDescriptionComponent()
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
    protected function validateWeightComponent()
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
    protected function validateStatusComponent()
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
    protected function validateDefaultComponent()
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
    protected function validateTranslationComponent()
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
    protected function validateImagesComponent()
    {
        $images = $this->getSubmitted('images');

        if (empty($images) || !is_array($images)) {
            return null;
        }

        foreach ($images as &$image) {

            if (isset($image['title'])) {
                $image['title'] = mb_strimwidth($image['title'], 0, 255, '');
            }

            if (isset($image['description'])) {
                $image['description'] = mb_strimwidth($image['description'], 0, 255, '');
            }

            if (empty($image['translation'])) {
                continue;
            }

            foreach ($image['translation'] as $lang => &$translation) {
                foreach ($translation as $field => &$value) {
                    if ($value === '') {
                        unset($image['translation'][$lang][$field]);
                        continue;
                    }

                    $value = mb_strimwidth($value, 0, 255, '');
                }

                if (empty($image['translation'][$lang])) {
                    unset($image['translation'][$lang]);
                    continue;
                }
            }
        }

        $this->setSubmitted('images', $images);
        return true;
    }

    /**
     * Validates an alias
     * @return boolean|null
     */
    protected function validateAliasComponent()
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

        $updating = $this->getUpdating();

        if (isset($alias)//
                && isset($updating['alias'])//
                && ($updating['alias'] === $alias)) {
            return true; // Do not check own alias on update
        }

        if ($this->alias->exists($alias)) {
            $vars = array('@name' => $this->language->text('Alias'));
            $error = $this->language->text('@name already exists', $vars);
            $this->setError('alias', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates store ID field
     * @return boolean|null
     */
    protected function validateStoreIdComponent()
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
    protected function validateUserIdComponent()
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
    protected function validateUserCartIdComponent()
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
    protected function validateEmailComponent()
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
