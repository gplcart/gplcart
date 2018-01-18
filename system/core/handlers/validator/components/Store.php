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
     * Performs store data validation
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
        $this->validateDataStore();
        $this->validateImagesStore();
        $this->validateDefaultStore();

        $this->unsetSubmitted('update');

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
            return null; // Adding a new store
        }

        $data = $this->store->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('Store'));
            return false;
        }

        $this->setUpdating($data);
        $this->setSubmitted('default', $this->store->isDefault($data['store_id']));
        return true;
    }

    /**
     * Validates a domain
     * @return boolean|null
     */
    protected function validateDomainStore()
    {
        $field = 'domain';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        if ($this->getSubmitted('default')) {
            $this->unsetSubmitted('domain'); // Cannot update domain of default store
            return null;
        }


        $label = $this->translation->text('Domain');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
        }

        $updating = $this->getUpdating();
        if (isset($updating['domain']) && $updating['domain'] === $value) {
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
        $field = 'basepath';

        if ($this->isExcluded($field) || $this->isError('domain')) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        if ($this->getSubmitted('default')) {
            $this->unsetSubmitted($field);
            return null; // Cannot update basepath of default store
        }

        $updating = $this->getUpdating();

        if (isset($updating['basepath'])//
            && $updating['basepath'] === $value//
            && $updating['domain'] === $this->getSubmitted('domain')) {
            return true;
        }

        if (preg_match('/^[a-z0-9-]{0,50}$/', $value) !== 1) {
            $this->setErrorInvalid($field, $this->translation->text('Path'));
            return false;
        }

        return true;
    }

    /**
     * Validate "data" field
     */
    protected function validateDataStore()
    {
        $field = 'data';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating()) {
            if (!isset($value)) {
                $this->unsetSubmitted($field);
                return null;
            }
        } else if (empty($value)) {
            $value = $this->store->getConfig(null, $this->store->getDefault());
            // Will be set later
            unset($value['title'], $value['meta_title']);
        }

        if (!is_array($value)) {
            $this->setErrorInvalid($field, $this->translation->text('Settings'));
            return false;
        }

        $this->setSubmitted('data', $value);

        $this->validateDataTitleStore();
        $this->validateDataEmailStore();
        $this->validateDataMapStore();
        $this->validateDataMetaTitleStore();
        $this->validateDataMetaDescriptionStore();
        $this->validateDataTranslationStore();
        $this->validateDataThemeStore();
    }

    /**
     * Validates a store title
     * @return boolean|null
     */
    protected function validateDataTitleStore()
    {
        $field = 'data.title';
        $value = $this->getSubmitted($field);

        if (empty($value) && !$this->isError('name')) {
            $this->setSubmitted($field, $this->getSubmitted('name'));
        }

        $options = $this->options;
        $this->options += array('parents' => 'data');
        $result = $this->validateTitle();
        $this->options = $options;

        return $result;
    }

    /**
     * Validates E-mails
     * @return boolean|null
     */
    protected function validateDataEmailStore()
    {
        $field = 'data.email';
        $value = $this->getSubmitted($field);
        $label = $this->translation->text('E-mail');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_array($value)) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $filtered = array_filter($value, function ($email) {
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
    protected function validateDataMapStore()
    {
        $field = 'data.map';
        $value = $this->getSubmitted($field);

        if (empty($value)) {
            return null;
        }

        $label = $this->translation->text('Map');

        if (!is_array($value) || count($value) != 2) {
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
     * Validates "meta_title" field
     * @return boolean|null
     */
    protected function validateDataMetaTitleStore()
    {
        $field = 'data.meta_title';
        $value = $this->getSubmitted($field);

        if (empty($value) && !$this->isError('name')) {
            $this->setSubmitted($field, $this->getSubmitted('name'));
        }

        $options = $this->options;
        $this->options += array('parents' => 'data');
        $result = $this->validateMetaTitle();
        $this->options = $options;

        return $result;
    }

    /**
     * Validates "meta_description" field
     * @return boolean|null
     */
    protected function validateDataMetaDescriptionStore()
    {
        $options = $this->options;
        $this->options += array('parents' => 'data');
        $result = $this->validateMetaDescription();
        $this->options = $options;

        return $result;
    }

    /**
     * Validates translatable fields
     * @return boolean|null
     */
    protected function validateDataTranslationStore()
    {
        $options = $this->options;
        $this->options += array('parents' => 'data');
        $result = $this->validateTranslation();
        $this->options = $options;

        return $result;
    }

    /**
     * Validates theme module IDs
     * @return boolean
     */
    protected function validateDataThemeStore()
    {
        $field = 'data.theme';
        $value = $this->getSubmitted($field);
        $module = $this->module->get($value);

        if (isset($module['type']) && $module['type'] === 'theme' && !empty($module['status'])) {
            return true;
        }

        $this->setErrorUnavailable($field, $this->translation->text('Theme'));
        return false;
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

            if (empty($file)) {
                continue;
            }

            $result = $this->file_transfer->upload($file, null, self::UPLOAD_PATH);

            if ($result !== true) {
                $error = true;
                $this->setError($field, (string) $result);
                continue;
            }

            $this->setSubmitted("data.$field", $this->file_transfer->getTransferred(true));
        }

        return empty($error);
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
