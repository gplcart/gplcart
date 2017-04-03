<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\File as FileModel,
    gplcart\core\models\Module as ModuleModel;
use gplcart\core\helpers\Request as RequestHelper;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate store data
 */
class Store extends ComponentValidator
{

    const UPLOAD_PATH = 'image/upload/store';

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * Module model instance
     * @var \gplcart\core\models\Module $module
     */
    protected $module;

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
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
     * @return boolean|null
     */
    protected function validateDomainStore()
    {
        $value = $this->getSubmitted('domain');

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

        if (!gplcart_valid_domain($value)) {
            $vars = array('@field' => $this->language->text('Domain'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('domain', $error);
            return false;
        }

        $existing = $this->store->get($value);

        if (!empty($existing)) {
            $vars = array('@name' => $this->language->text('Domain'));
            $error = $this->language->text('@name already exists', $vars);
            $this->setError('domain', $error);
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

        $value = $this->getSubmitted('basepath');

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if ($this->getSubmitted('default')) {
            $this->unsetSubmitted('basepath');
            return null; // Cannot update basepath of default store
        }

        $updating = $this->getUpdating();
        $domain = $this->getSubmitted('domain');

        if (isset($updating['basepath'])//
                && $updating['basepath'] === $value//
                && $updating['domain'] === $domain) {
            return true;
        }

        if (preg_match('/^[a-z0-9]{0,50}$/', $value) !== 1) {
            $vars = array('@field' => $this->language->text('Base path'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('basepath', $error);
            return false;
        }

        $conditions = array('domain' => $domain, 'basepath' => $value);
        $stores = (array) $this->store->getList($conditions);

        foreach ($stores as $store_id => $data) {

            if (isset($updating['store_id']) && $updating['store_id'] == $store_id) {
                continue;
            }

            if ($data['domain'] === $domain && $data['basepath'] === $value) {
                $vars = array('@name' => $this->language->text('Base path'));
                $error = $this->language->text('@name already exists', $vars);
                $this->setError('basepath', $error);
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
        $value = $this->getSubmitted('data.email');

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('E-mail'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('data.email', $error);
            return false;
        }

        $filtered = array_filter($value, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if (count($value) != count($filtered)) {
            $error = $this->language->text('Invalid E-mail');
            $this->setError('data.email', $error);
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
        $value = $this->getSubmitted('data.map');

        if (empty($value)) {
            return null;
        }

        if (count($value) != 2) {
            $vars = array('@field' => $this->language->text('Map'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('data.map', $error);
            return false;
        }

        $filtered = array_filter($value, 'is_numeric');

        if (count($value) != count($filtered)) {
            $vars = array('@field' => $this->language->text('Map'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('data.map', $error);
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

            $uploaded = $this->file->getUploadedFile(true);
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
        $mapping = array(
            'theme' => $this->language->text('Theme'),
            'theme_mobile' => $this->language->text('Mobile theme'),
            'theme_tablet' => $this->language->text('Tablet theme')
        );

        foreach ($mapping as $field => $name) {

            $value = $this->getSubmitted("data.$field");

            if ($this->isUpdating() && !isset($value)) {
                continue;
            }

            if (empty($value)) {
                $error = $this->language->text('@field is required', array('@field' => $name));
                $this->setError("data.$field", $error);
                continue;
            }

            $module = $this->module->get($value);

            if (isset($module['type']) || $module['type'] === 'theme' && !empty($module['status'])) {
                continue;
            }

            $error = $this->language->text('@name is unavailable', array('@name' => $name));
            $this->setError("data.$field", $error);
        }

        return !isset($error);
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
