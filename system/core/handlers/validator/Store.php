<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\File as FileModel;
use core\models\Module as ModuleModel;
use core\helpers\Request as RequestHelper;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate store data
 */
class Store extends BaseValidator
{

    const UPLOAD_PATH = 'image/upload/store';

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Module model instance
     * @var \core\models\Module $module
     */
    protected $module;

    /**
     * Request class instance
     * @var \core\helpers\Request $request
     */
    protected $request;

    /**
     * Constructor
     * @param FileModel $file
     * @param ModuleModel $module
     * @param RequestHelper $request
     */
    public function __construct(FileModel $file, ModuleModel $module,
            RequestHelper $request)
    {
        parent::__construct();

        $this->file = $file;
        $this->module = $module;
        $this->request = $request;
    }

    /**
     * Performs full store data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function store(array &$submitted, array $options)
    {
        $this->submitted = &$submitted;

        $this->validateStore($options);
        $this->validateStatus($options);
        $this->validateDomainStore($options);
        $this->validateBasepathStore($options);
        $this->validateName($options);
        $this->validateEmailStore($options);
        $this->validateMapStore($options);
        $this->validateTitleStore($options);
        $this->validateMetaTitleStore($options);
        $this->validateMetaDescriptionStore($options);
        $this->validateTranslationStore($options);
        $this->validateThemeStore($options);
        $this->validateImagesStore($options);

        return $this->getResult();
    }

    /**
     * Validates a store to be updated
     * @param array $options
     * @return boolean|null
     */
    protected function validateStore(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->store->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Store'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        $default = $this->store->isDefault($data['store_id']);
        $this->setSubmitted('default', $default);
        return true;
    }

    /**
     * Validates a domain
     * @param array $options
     * @return boolean|null
     */
    protected function validateDomainStore(array $options)
    {
        $value = $this->getSubmitted('domain', $options);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if ($this->getSubmitted('default')) {
            $this->unsetSubmitted('domain', $options);
            return null; // Cannot update domain of default store
        }

        $updating = $this->getUpdating();

        if (isset($updating['domain']) && ($updating['domain'] === $value)) {
            return true;
        }

        if (!gplcart_valid_domain($value)) {
            $vars = array('@field' => $this->language->text('Domain'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('domain', $error, $options);
            return false;
        }

        $existing = $this->store->get($value);

        if (!empty($existing)) {
            $vars = array('@object' => $this->language->text('Domain'));
            $error = $this->language->text('@object already exists', $vars);
            $this->setError('domain', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a store base path
     * @param array $options
     * @return boolean|null
     */
    protected function validateBasepathStore(array $options)
    {
        if ($this->isError('domain', $options)) {
            return null;
        }

        $value = $this->getSubmitted('basepath', $options);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if ($this->getSubmitted('default')) {
            $this->unsetSubmitted('basepath', $options);
            return null; // Cannot update basepath of default store
        }

        $updating = $this->getUpdating();
        $domain = $this->getSubmitted('domain', $options);

        if (isset($updating['basepath'])//
                && $updating['basepath'] === $value//
                && $updating['domain'] === $domain) {
            return true;
        }

        if (preg_match('/^[a-z0-9]{0,50}$/', $value) !== 1) {
            $vars = array('@field' => $this->language->text('Base path'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('basepath', $error, $options);
            return false;
        }

        $conditions = array('domain' => $domain, 'basepath' => $value);
        $stores = $this->store->getList($conditions);

        foreach ($stores as $store_id => $data) {

            if (isset($updating['store_id']) && $updating['store_id'] == $store_id) {
                continue;
            }

            if ($data['domain'] === $domain && $data['basepath'] === $value) {
                $vars = array('@object' => $this->language->text('Base path'));
                $error = $this->language->text('@object already exists', $vars);
                $this->setError('basepath', $error, $options);
                return false;
            }
        }

        return true;
    }

    /**
     * Validates E-mails
     * @param array $options
     * @return boolean|null
     */
    protected function validateEmailStore(array $options)
    {
        $value = $this->getSubmitted('data.email', $options);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('E-mail'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('data.email', $error, $options);
            return false;
        }

        $filtered = array_filter($value, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if (count($value) != count($filtered)) {
            $error = $this->language->text('Invalid E-mail');
            $this->setError('data.email', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates map coordinates
     * @param array $options
     * @return boolean|null
     */
    protected function validateMapStore(array $options)
    {
        $value = $this->getSubmitted('data.map', $options);

        if (empty($value)) {
            return null;
        }

        if (count($value) != 2) {
            $vars = array('@field' => $this->language->text('Map'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('data.map', $error, $options);
            return false;
        }

        $filtered = array_filter($value, 'is_numeric');

        if (count($value) != count($filtered)) {
            $vars = array('@field' => $this->language->text('Map'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('data.map', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a store title
     * @param array $options
     * @return boolean|null
     */
    protected function validateTitleStore(array $options)
    {
        $options += array('parents' => 'data');
        return $this->validateTitle($options);
    }

    /**
     * Validates a store meta title
     * @param array $options
     * @return boolean|null
     */
    protected function validateMetaTitleStore(array $options)
    {
        $options += array('parents' => 'data');
        return $this->validateMetaTitle($options);
    }

    /**
     * Validates a store meta description
     * @param array $options
     * @return boolean|null
     */
    protected function validateMetaDescriptionStore(array $options)
    {
        $options += array('parents' => 'data');
        return $this->validateMetaDescription($options);
    }

    /**
     * Validates store translatable fields
     * @param array $options
     * @return boolean|null
     */
    protected function validateTranslationStore(array $options)
    {
        $options += array('parents' => 'data');
        return $this->validateTranslation($options);
    }

    /**
     * Validates uploaded favicon
     * @param array $options
     * @return boolean
     */
    protected function validateImagesStore(array $options)
    {
        if ($this->isError()) {
            return null;
        }

        $error = false;
        foreach (array('logo', 'favicon') as $field) {

            if ($this->getSubmitted("delete_$field", $options)) {
                $this->setSubmitted("data.$field", '', $options);
            }

            $file = $this->request->file($field);

            if ($this->isUpdating() && empty($file)) {
                continue;
            }

            if (empty($file)) {
                continue;
            }

            $result = $this->file->setUploadPath(self::UPLOAD_PATH)
                    ->upload($file);

            if ($result !== true) {
                $error = true;
                $this->setError($field, $result, $options);
                continue;
            }

            $uploaded = $this->file->getUploadedFile(true);
            $this->setSubmitted("data.$field", $uploaded, $options);
        }

        return empty($error);
    }

    /**
     * Validates theme module IDs
     * @param array $options
     * @return boolean
     */
    protected function validateThemeStore(array $options)
    {
        $mapping = array(
            'theme' => $this->language->text('Theme'),
            'theme_mobile' => $this->language->text('Mobile theme'),
            'theme_tablet' => $this->language->text('Tablet theme')
        );

        foreach ($mapping as $field => $name) {

            $value = $this->getSubmitted("data.$field", $options);

            if ($this->isUpdating() && !isset($value)) {
                continue;
            }

            if (empty($value)) {
                $error = $this->language->text('@field is required', array('@field' => $name));
                $this->setError("data.$field", $error, $options);
                continue;
            }

            $module = $this->module->get($value);

            if (isset($module['type']) || $module['type'] === 'theme' && !empty($module['status'])) {
                continue;
            }

            $error = $this->language->text('@name is unavailable', array('@name' => $name));
            $this->setError("data.$field", $error, $options);
        }

        return !isset($error);
    }

}
