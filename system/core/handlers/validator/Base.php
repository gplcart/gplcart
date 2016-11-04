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
    }

    /**
     * Validates title
     * @param array $submitted
     */
    protected function validateTitle(array &$submitted)
    {
        if (empty($submitted['title']) || mb_strlen($submitted['title']) > 255) {
            $options = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Title'));
            $this->errors['title'] = $this->language->text('@field must be @min - @max characters long', $options);
        }
    }

    /**
     * Validates meta title
     * @param array $submitted
     */
    protected function validateMetaTitle(array &$submitted)
    {
        if (isset($submitted['meta_title']) && mb_strlen($submitted['meta_title']) > 255) {
            $options = array('@max' => 255, '@field' => $this->language->text('Meta title'));
            $this->errors['meta_title'] = $this->language->text('@field must not be longer than @max characters', $options);
        }
    }

    /**
     * Validates meta description
     * @param array $submitted
     */
    protected function validateMetaDescription(array &$submitted)
    {
        if (isset($submitted['meta_description']) && mb_strlen($submitted['meta_description']) > 255) {
            $options = array('@max' => 255, '@field' => $this->language->text('Meta description'));
            $this->errors['meta_description'] = $this->language->text('@field must not be longer than @max characters', $options);
        }
    }

    /**
     * Validates description field
     * @param array $submitted
     */
    protected function validateDescription(array &$submitted)
    {
        if (isset($submitted['description']) && mb_strlen($submitted['description']) > 65535) {
            $options = array('@max' => 65535, '@field' => $this->language->text('Description'));
            $this->errors['description'] = $this->language->text('@field must not be longer than @max characters', $options);
        }
    }

    /**
     * Validates weight field
     * @param array $submitted
     */
    protected function validateWeight(array &$submitted)
    {
        if (isset($submitted['weight']) && !is_numeric($submitted['weight'])) {
            $options = array('@field' => $this->language->text('Weight'));
            $this->errors['weight'] = $this->language->text('@field must be numeric', $options);
        }
    }

    /**
     * Sets status field to bool value
     * @param array $submitted
     */
    protected function validateStatus(array &$submitted)
    {
        if (isset($submitted['status'])) {
            $submitted['status'] = (int) Tool::toBool($submitted['status']);
        }
    }

    /**
     * Validates category translations
     * @param array $submitted
     * @return boolean
     */
    protected function validateTranslation(array &$submitted)
    {
        if (empty($submitted['translation'])) {
            return true;
        }

        foreach ($submitted['translation'] as $lang => $translation) {
            foreach ($translation as $field => $value) {
                if (mb_strlen($value) > 255) {
                    $options = array('@field' => ucfirst(str_replace('_', '', $field)), '@lang' => $lang, '@max' => 255);
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
        if (empty($submitted['images']) || isset($this->errors['title'])) {
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
     * @return boolean
     */
    protected function validateAlias(array &$submitted)
    {
        if (empty($submitted['alias'])) {
            $this->errors['alias'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Alias')
            ));

            return false;
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
            $this->errors['alias'] = $this->language->text('Alias already exists');
            return false;
        }

        return true;
    }

    /**
     * Validates store ID field
     * @param array $submitted
     * @return boolean
     */
    protected function validateStore(array $submitted)
    {
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
            $this->errors['store_id'] = $this->language->text('Invalid store ID');
            return false;
        }

        return true;
    }

}
