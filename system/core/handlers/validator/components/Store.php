<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\Module;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate store data
 */
class Store extends ComponentValidator
{

    /**
     * File upload path
     */
    const UPLOAD_PATH = 'image/upload/store';

    /**
     * Module class instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

    /**
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        parent::__construct();

        $this->module = $module;
    }

    /**
     * Performs full store data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function store(array &$submitted, array $options)
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateStore();
        $this->validateStatus();
        $this->validateDomainStore();
        $this->validateBasepathStore();
        $this->validateName();
        $this->validateEmailStore();
        $this->validateMapStore();
        $this->validateTitleStore();
        $this->validateMetaTitleStore();
        $this->validateMetaDescriptionStore();
        $this->validateTranslationStore();
        $this->validateThemeStore();
        $this->validateImagesStore();
        $this->validateDefaultStore();

        return $this->getResult();
    }

    /**
     * Validates a store to be updated
     * @return boolean|null
     */
    protected function validateStore()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->store->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->language->text('Store'));
            return false;
        }

        $this->setUpdating($data);
        $default = $this->store->isDefault($data['store_id']);
        $this->setSubmitted('default', $default);
        return true;
    }

    /**
     * Validates a domain
     * @return boolean|null
     */
    protected function validateDomainStore()
    {
        $field = 'domain';
        $label = $this->language->text('Domain');
        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if ($this->getSubmitted('default')) {
            $this->unsetSubmitted('domain');
            return null; // Cannot update domain of default store
        }

        $updating = $this->getUpdating();

        if (isset($updating['domain']) && ($updating['domain'] === $value)) {
            return true;
        }

        $existing = $this->store->get($value);

        if (!empty($existing)) {
            $this->setErrorExists($field, $label);
            return false;
        }
        return true;
    }

    /**
     * Validates a store base path
     * @return boolean|null
     */
    protected function validateBasepathStore()
    {
        if ($this->isError('domain')) {
            return null;
        }

        $field = 'basepath';
        $label = $this->language->text('Base path');
        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if ($this->getSubmitted('default')) {
            $this->unsetSubmitted($field);
            return null; // Cannot update basepath of default store
        }

        $updating = $this->getUpdating();
        $domain = $this->getSubmitted('domain');

        if (isset($updating['basepath'])//
                && $updating['basepath'] === $value//
                && $updating['domain'] === $domain) {
            return true;
        }

        if (preg_match('/^[a-z0-9-]{0,50}$/', $value) !== 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $conditions = array('domain' => $domain, 'basepath' => $value);
        $stores = (array) $this->store->getList($conditions);

        foreach ($stores as $store_id => $data) {
            if (isset($updating['store_id']) && $updating['store_id'] == $store_id) {
                continue;
            }
            if ($data['domain'] === $domain && $data['basepath'] === $value) {
                $this->setErrorExists($field, $label);
                return false;
            }
        }
        return true;
    }

    /**
     * Validates E-mails
     * @return boolean|null
     */
    protected function validateEmailStore()
    {
        $field = 'data.email';
        $label = $this->language->text('E-mail');
        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $filtered = array_filter($value, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if (count($value) != count($filtered)) {
            $this->setErrorInvalid($field, $label);
            return false;
        }
        return true;
    }

    /**
     * Validates map coordinates
     * @return boolean|null
     */
    protected function validateMapStore()
    {
        $field = 'data.map';
        $label = $this->language->text('Map');
        $value = $this->getSubmitted($field);

        if (empty($value)) {
            return null;
        }

        if (count($value) != 2) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        if (count($value) != count(array_filter($value, 'is_numeric'))) {
            $this->setErrorInvalid($field, $label);
            return false;
        }
        return true;
    }

    /**
     * Validates a store title
     * @return boolean|null
     */
    protected function validateTitleStore()
    {
        $options = $this->options;
        $this->options += array('parents' => 'data');

        $result = $this->validateTitle();

        $this->options = $options;
        return $result;
    }

    /**
     * Validates a store meta title
     * @return boolean|null
     */
    protected function validateMetaTitleStore()
    {
        $options = $this->options;
        $this->options += array('parents' => 'data');

        $result = $this->validateMetaTitle();

        $this->options = $options;
        return $result;
    }

    /**
     * Validates a store meta description
     * @return boolean|null
     */
    protected function validateMetaDescriptionStore()
    {
        $options = $this->options;
        $this->options += array('parents' => 'data');

        $result = $this->validateMetaDescription();

        $this->options = $options;
        return $result;
    }

    /**
     * Validates store translatable fields
     * @return boolean|null
     */
    protected function validateTranslationStore()
    {
        $options = $this->options;
        $this->options += array('parents' => 'data');

        $result = $this->validateTranslation();

        $this->options = $options;
        return $result;
    }

    /**
     * Validates uploaded favicon
     * @return boolean
     */
    protected function validateImagesStore()
    {
        if ($this->isError()) {
            return null;
        }

        $error = false;
        foreach (array('logo', 'favicon') as $field) {

            if ($this->getSubmitted("delete_$field")) {
                $this->setSubmitted("data.$field", '');
            }

            $file = $this->request->file($field);

            if ($this->isUpdating() && empty($file)) {
                continue;
            }

            if (empty($file)) {
                continue;
            }

            $result = $this->file->upload($file, null, self::UPLOAD_PATH);

            if ($result !== true) {
                $error = true;
                $this->setError($field, (string) $result);
                continue;
            }

            $uploaded = $this->file->getTransferred(true);
            $this->setSubmitted("data.$field", $uploaded);
        }

        return empty($error);
    }

    /**
     * Validates theme module IDs
     * @return boolean
     */
    protected function validateThemeStore()
    {
        $field = 'data.theme';
        $label = $this->language->text('Theme');
        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $module = $this->module->get($value);

        if (isset($module['type'])//
                || $module['type'] === 'theme'//
                && !empty($module['status'])) {
            return true;
        }

        $this->setErrorUnavailable($field, $label);
        return false;
    }

    /**
     * Validates default store
     */
    protected function validateDefaultStore()
    {
        $id = $this->getUpdatingId();

        if (!empty($id) && $this->store->isDefault($id)) {
            $this->unsetSubmitted('domain');
            $this->unsetSubmitted('basepath');
        }
    }

}
