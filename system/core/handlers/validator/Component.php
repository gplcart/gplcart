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
        $field = 'title';
        $label = $this->language->text('Title');
        $title = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($title)) {
            return null;
        }

        if (empty($title) || mb_strlen($title) > 255) {
            $this->setErrorLengthRange($field, $label);
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
        $field = 'name';
        $label = $this->language->text('Name');
        $name = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($name)) {
            return null;
        }

        if (empty($name) || mb_strlen($name) > 255) {
            $this->setErrorLengthRange($field, $label);
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
        $field = 'meta_title';
        $label = $this->language->text('Meta title');
        $meta_title = $this->getSubmitted($field);

        if (isset($meta_title) && mb_strlen($meta_title) > 60) {
            $this->setErrorLengthRange($field, $label, 0, 60);
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
        $field = 'meta_description';
        $label = $this->language->text('Meta description');
        $meta_description = $this->getSubmitted($field);

        if (isset($meta_description) && mb_strlen($meta_description) > 160) {
            $this->setErrorLengthRange($field, $label, 0, 160);
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
        $field = 'description';
        $label = $this->language->text('Description');
        $description = $this->getSubmitted($field);

        if (isset($description) && mb_strlen($description) > 65535) {
            $this->setErrorLengthRange($field, $label, 0, 65535);
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
        $field = 'weight';
        $label = $this->language->text('Weight');
        $weight = $this->getSubmitted($field);

        if (isset($weight) && !is_numeric($weight)) {
            $this->setErrorNumeric($field, $label);
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
        $field = 'status';
        $status = $this->getSubmitted($field);

        if (isset($status)) {
            $value = (int) gplcart_string_bool($status);
            $this->setSubmitted($field, $value);
        }
        return true;
    }

    /**
     * Sets "Default" field to integer value
     * @return boolean
     */
    protected function validateDefaultComponent()
    {
        $field = 'default';
        $default = $this->getSubmitted($field);

        if (isset($default)) {
            $value = (int) gplcart_string_bool($default);
            $this->setSubmitted($field, $value);
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

        $lengths = array(
            'meta_title' => 60,
            'meta_description' => 160
        );

        foreach ($translations as $lang => $translation) {
            foreach ($translation as $field => $value) {

                if ($value === '') {
                    unset($translations[$lang][$field]);
                    continue;
                }

                $max = isset($lengths[$field]) ? $lengths[$field] : 255;

                if (mb_strlen($value) > $max) {
                    $label = ucfirst(str_replace('_', ' ', $field));
                    $this->setErrorLengthRange("translation.$lang.$field", $label, 0, $max);
                }
            }

            if (empty($translations[$lang])) {
                unset($translations[$lang]);
            }
        }

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
        $field = 'alias';
        $label = $this->language->text('Alias');
        $alias = $this->getSubmitted($field);

        if (empty($alias)) {
            return null;
        }

        if (mb_strlen($alias) > 255) {
            $this->setErrorLengthRange($field, $label, 0, 255);
            return false;
        }

        if (preg_match('/^[A-Za-z0-9_.-]+$/', $alias) !== 1) {
            $this->setErrorInvalidValue($field, $label);
            return false;
        }

        $updating = $this->getUpdating();

        if (isset($alias)//
                && isset($updating['alias'])//
                && ($updating['alias'] === $alias)) {
            return true; // Do not check own alias on update
        }

        if ($this->alias->exists($alias)) {
            $this->setErrorExists($field, $label);
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
        $field = 'store_id';
        $label = $this->language->text('Store');
        $store_id = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($store_id)) {
            return null;
        }

        if (empty($store_id)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($store_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $store = $this->store->get($store_id);

        if (empty($store)) {
            $this->setErrorUnavailable($field, $label);
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
        $field = 'user_id';
        $label = $this->language->text('User');
        $user_id = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($user_id)) {
            return null;
        }

        if (empty($user_id)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($user_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $user = $this->user->get($user_id);

        if (empty($user)) {
            $this->setErrorUnavailable($field, $label);
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
        $field = 'user_id';
        $label = $this->language->text('User');
        $user_id = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($user_id)) {
            return null;
        }

        if (empty($user_id)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (strlen($user_id) > 255) {
            $this->setErrorLengthRange($field, $label, 0, 255);
            return false;
        }

        if (!is_numeric($user_id)) {
            return true; // Anonymous user
        }

        $user = $this->user->get($user_id);

        if (empty($user)) {
            $this->setErrorUnavailable($field, $label);
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
        $field = 'email';
        $label = $this->language->text('E-mail');
        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->setErrorInvalidValue($field, $label);
            return false;
        }
        return true;
    }

}
