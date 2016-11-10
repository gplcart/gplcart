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
        /* @var $language \core\models\Language */
        $this->language = Container::instance('core\\models\\Language');

        /* @var $alias \core\models\Alias */
        $this->alias = Container::instance('core\\models\\Alias');

        /* @var $store \core\models\Store */
        $this->store = Container::instance('core\\models\\Store');

        /* @var $user \core\models\User */
        $this->user = Container::instance('core\\models\\User');
    }

    /**
     * Validates a title
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateTitle(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['title'])) {
            return null;
        }

        if (empty($submitted['title']) || mb_strlen($submitted['title']) > 255) {
            $options = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Title'));
            $this->errors['title'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a name
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateName(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['name'])) {
            return null;
        }

        if (empty($submitted['name']) || mb_strlen($submitted['name']) > 255) {
            $options = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Name'));
            $this->errors['name'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates meta title
     * @param array $submitted
     * @return boolean
     */
    protected function validateMetaTitle(array &$submitted)
    {
        if (isset($submitted['meta_title']) && mb_strlen($submitted['meta_title']) > 60) {
            $options = array('@max' => 60, '@field' => $this->language->text('Meta title'));
            $this->errors['meta_title'] = $this->language->text('@field must not be longer than @max characters', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates meta description
     * @param array $submitted
     * @return boolean
     */
    protected function validateMetaDescription(array &$submitted)
    {
        if (isset($submitted['meta_description']) && mb_strlen($submitted['meta_description']) > 160) {
            $options = array('@max' => 160, '@field' => $this->language->text('Meta description'));
            $this->errors['meta_description'] = $this->language->text('@field must not be longer than @max characters', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates description field
     * @param array $submitted
     * @return boolean
     */
    protected function validateDescription(array &$submitted)
    {
        if (isset($submitted['description']) && mb_strlen($submitted['description']) > 65535) {
            $options = array('@max' => 65535, '@field' => $this->language->text('Description'));
            $this->errors['description'] = $this->language->text('@field must not be longer than @max characters', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates weight field
     * @param array $submitted
     * @return boolean
     */
    protected function validateWeight(array &$submitted)
    {
        if (isset($submitted['weight']) && !is_numeric($submitted['weight'])) {
            $options = array('@field' => $this->language->text('Weight'));
            $this->errors['weight'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        return true;
    }

    /**
     * Sets "Status" field to integer value
     * @param array $submitted
     * @return boolean
     */
    protected function validateStatus(array &$submitted)
    {
        if (isset($submitted['status'])) {
            $submitted['status'] = (int) Tool::toBool($submitted['status']);
        }

        return true;
    }

    /**
     * Sets "Default" field to integer value
     * @param array $submitted
     * @return boolean
     */
    protected function validateDefault(array &$submitted)
    {
        if (isset($submitted['default'])) {
            $submitted['default'] = (int) Tool::toBool($submitted['default']);
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
                    $options = array('@field' => ucfirst(str_replace('_', '', $field)), '@lang' => $lang, '@max' => $max);
                    $this->errors['translation'][$lang][$field] = $this->language->text('@field in @lang must not be longer than @max characters', $options);
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
        if (empty($submitted['images'])) {
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
            $this->errors['alias'] = $this->language->text('@field must not be longer than @max characters', array(
                '@max' => 255,
                '@field' => $this->language->text('Alias')
            ));

            return false;
        }

        if (!preg_match('/^[A-Za-z0-9_.-]+$/', $submitted['alias'])) {
            $this->errors['alias'] = $this->language->text('Alias must contain only alphanumeric characters, dashes, dots and underscores');
            return false;
        }

        if ($this->alias->exists($submitted['alias'])) {
            $this->errors['alias'] = $this->language->text('@object already exists', array(
                '@object' => $this->language->text('Alias')));
            return false;
        }

        return true;
    }

    /**
     * Validates store ID field
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateStoreId(array $submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['store_id'])) {
            return null;
        }

        if (empty($submitted['store_id'])) {
            $this->errors['store_id'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Store')
            ));
            return false;
        }

        if (!is_numeric($submitted['store_id'])) {
            $options = array('@field' => $this->language->text('Store'));
            $this->errors['store_id'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        $store = $this->store->get($submitted['store_id']);

        if (empty($store)) {
            $this->errors['store_id'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Store')));
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
            $this->errors['user_id'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('User')
            ));
            return false;
        }

        if (!is_numeric($submitted['user_id'])) {
            $options = array('@field' => $this->language->text('User'));
            $this->errors['user_id'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        $user = $this->user->get($submitted['user_id']);

        if (empty($user)) {
            $this->errors['user_id'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('User')));
            return false;
        }

        return true;
    }

}
