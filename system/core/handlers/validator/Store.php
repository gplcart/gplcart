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
use core\helpers\Regexp as RegexpHelper;
use core\helpers\Request as RequestHelper;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate store data
 */
class Store extends BaseValidator
{

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
     * @return array|boolean
     */
    public function store(array &$submitted)
    {
        $this->validateStore($submitted);
        $this->validateStatus($submitted);
        $this->validateDomainStore($submitted);
        $this->validateBasepathStore($submitted);
        $this->validateName($submitted);
        $this->validateEmailStore($submitted);
        $this->validateMapStore($submitted);
        $this->validateTitleStore($submitted);
        $this->validateMetaTitleStore($submitted);
        $this->validateMetaDescriptionStore($submitted);
        $this->validateTranslationStore($submitted);
        $this->validateThemeStore($submitted);
        $this->validateFaviconStore($submitted);
        $this->validateLogoStore($submitted);

        return $this->getResult();
    }

    /**
     * Validates a store to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateStore(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {

            $data = $this->store->get($submitted['update']);

            if (empty($data)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Store')));
                return false;
            }

            $submitted['update'] = $data;
        }

        $submitted['default'] = (isset($submitted['update']['store_id'])//
                && $this->store->isDefault($submitted['update']['store_id']));

        return true;
    }

    /**
     * Validates a domain
     * @param array $submitted
     * @return boolean
     */
    protected function validateDomainStore(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['domain'])) {
            return null;
        }

        if (!empty($submitted['default'])) {
            unset($submitted['domain']);
            return null; // Cannot update domain of default store
        }

        if (isset($submitted['update']['domain'])//
                && ($submitted['update']['domain'] === $submitted['domain'])) {
            return true;
        }

        if (!RegexpHelper::matchDomain($submitted['domain'])) {
            $this->errors['domain'] = $this->language->text('Invalid domain');
            return false;
        }

        $existing = $this->store->get($submitted['domain']);

        if (empty($existing)) {
            return true;
        }

        $this->errors['domain'] = $this->language->text('@object already exists', array(
            '@object' => $this->language->text('Domain')));
        return false;
    }

    /**
     * Validates a store base path
     * @param array $submitted
     * @return boolean
     */
    protected function validateBasepathStore(array &$submitted)
    {
        if (isset($this->errors['domain'])) {
            return null;
        }

        if (!empty($submitted['update']) && !isset($submitted['basepath'])) {
            return null;
        }

        if (!empty($submitted['default'])) {
            unset($submitted['basepath']);
            return null; // Cannot update basepath of default store
        }

        if (isset($submitted['update']['basepath'])//
                && $submitted['update']['basepath'] === $submitted['basepath']//
                && $submitted['update']['domain'] === $submitted['domain']) {
            return true;
        }

        if (!preg_match('/^[a-z0-9]{0,50}$/', $submitted['basepath'])) {
            $this->errors['basepath'] = $this->language->text('Invalid basepath');
            return false;
        }

        $stores = $this->store->getList(array(
            'domain' => $submitted['domain'],
            'basepath' => $submitted['basepath']
        ));

        foreach ($stores as $store_id => $data) {

            if (isset($submitted['update']['store_id'])//
                    && $submitted['update']['store_id'] == $store_id) {
                continue;
            }

            if ($data['domain'] === $submitted['domain']//
                    && $data['basepath'] === $submitted['basepath']) {
                $this->errors['basepath'] = $this->language->text('@object already exists', array(
                    '@object' => $this->language->text('Base path')));
                return false;
            }
        }

        return true;
    }

    /**
     * Validates E-mails
     * @param array $submitted
     * @return boolean
     */
    protected function validateEmailStore(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['data']['email'])) {
            return null;
        }

        if (empty($submitted['data']['email'])) {
            $this->errors['data']['email'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('E-mail')
            ));
            return false;
        }

        $emails = $submitted['data']['email'];

        $filtered = array_filter($emails, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if (count($emails) == count($filtered)) {
            return true;
        }

        $this->errors['data']['email'] = $this->language->text('Invalid E-mail');
        return false;
    }

    /**
     * Validates map coordinates
     * @param array $submitted
     * @return boolean
     */
    protected function validateMapStore(array &$submitted)
    {
        if (empty($submitted['data']['map'])) {
            return null;
        }

        $map = $submitted['data']['map'];

        if (count($map) != 2) {
            $this->errors['data']['map'] = $this->language->text('Invalid map coordinates');
            return false;
        }

        $filtered = array_filter($map, 'is_numeric');

        if (count($map) == count($filtered)) {
            return true;
        }

        $this->errors['data']['map'] = $this->language->text('Invalid map coordinates');
        return false;
    }

    /**
     * Validates a store title
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateTitleStore(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['data']['title'])) {
            return null;
        }

        if (empty($submitted['data']['title']) || mb_strlen($submitted['data']['title']) > 255) {
            $options = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Title'));
            $this->errors['data']['title'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a store meta title
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateMetaTitleStore(array &$submitted)
    {
        if (empty($submitted['data']['meta_title'])) {
            return null;
        }

        if (mb_strlen($submitted['data']['meta_title']) > 60) {
            $options = array('@max' => 60, '@field' => $this->language->text('Meta title'));
            $this->errors['data']['meta_title'] = $this->language->text('@field must not be longer than @max characters', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a store meta description
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateMetaDescriptionStore(array &$submitted)
    {
        if (empty($submitted['data']['meta_description'])) {
            return null;
        }

        if (mb_strlen($submitted['data']['meta_description']) > 160) {
            $options = array('@max' => 160, '@field' => $this->language->text('Meta description'));
            $this->errors['data']['meta_description'] = $this->language->text('@field must not be longer than @max characters', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates store translatable fields
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateTranslationStore(array &$submitted)
    {
        if (empty($submitted['data']['translation'])) {
            return null;
        }

        $lengths = array('meta_title' => 60, 'meta_description' => 160);

        foreach ($submitted['data']['translation'] as $lang => $translation) {
            foreach ($translation as $field => $value) {

                $max = isset($lengths[$field]) ? $lengths[$field] : 255;

                if (mb_strlen($value) > $max) {
                    $options = array('@field' => ucfirst(str_replace('_', '', $field)), '@lang' => $lang, '@max' => $max);
                    $this->errors['data']['translation'][$lang][$field] = $this->language->text('@field in @lang must not be longer than @max characters', $options);
                }
            }
        }

        return empty($this->errors['data']['translation']);
    }

    /**
     * Validates uploaded favicon
     * @param array $submitted
     * @return boolean
     */
    protected function validateFaviconStore(array &$submitted)
    {
        if (!empty($this->errors)) {
            return null;
        }

        if (!empty($submitted['delete_favicon'])) {
            $submitted['data']['favicon'] = '';
        }

        $file = $this->request->file('favicon');

        if (!empty($submitted['update']) && empty($file)) {
            return null;
        }

        if (empty($file)) {
            return true;
        }

        $result = $this->file->setUploadPath('image/upload/store')->upload($file);

        if ($result !== true) {
            $this->errors['favicon'] = $result;
            return false;
        }

        $uploaded = $this->file->getUploadedFile(true);
        $submitted['data']['favicon'] = $uploaded;
        return true;
    }

    /**
     * Validates uploaded logo
     * @param array $submitted
     * @return boolean
     */
    protected function validateLogoStore(array &$submitted)
    {
        if (!empty($this->errors)) {
            return null;
        }

        if (!empty($submitted['delete_logo'])) {
            $submitted['data']['logo'] = '';
        }

        $file = $this->request->file('logo');

        if (!empty($submitted['update']) && empty($file)) {
            return null;
        }

        if (empty($file)) {
            return true;
        }

        $result = $this->file->setUploadPath('image/upload/store')->upload($file);

        if ($result !== true) {
            $this->errors['logo'] = $result;
            return false;
        }

        $uploaded = $this->file->getUploadedFile(true);
        $submitted['data']['logo'] = $uploaded;
        return true;
    }

    /**
     * Validates theme module IDs
     * @param array $submitted
     * @return boolean
     */
    protected function validateThemeStore(array $submitted)
    {
        $fields = array('theme', 'theme_mobile', 'theme_tablet');

        $error = false;
        foreach ($fields as $field) {

            if (!empty($submitted['update']) && !isset($submitted['data'][$field])) {
                continue;
            }

            if (empty($submitted['data'][$field])) {
                $error = true;
                $this->errors['data'][$field] = $this->language->text('@field is required', array('@field' => $field));
                continue;
            }

            if (!$this->module->isInstalled($submitted['data'][$field])) {
                $error = true;
                $this->errors['data'][$field] = $this->language->text('Object @name does not exist', array('@name' => $field));
                continue;
            }
        }

        return !$error;
    }

}
