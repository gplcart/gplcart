<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\Container;
use gplcart\core\handlers\validator\Element as ElementValidator;

/**
 * Methods to validate different components
 */
class Component extends ElementValidator
{

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

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
     * File model class instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * File transfer class instance
     * @var \gplcart\core\models\FileTransfer $file_transfer
     */
    protected $file_transfer;

    /**
     * Request helper class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->config = Container::get('gplcart\\core\\Config');
        $this->file = Container::get('gplcart\\core\\models\\File');
        $this->user = Container::get('gplcart\\core\\models\\User');
        $this->store = Container::get('gplcart\\core\\models\\Store');
        $this->alias = Container::get('gplcart\\core\\models\\Alias');
        $this->request = Container::get('gplcart\\core\\helpers\\Request');
        $this->file_transfer = Container::get('gplcart\\core\\models\\FileTransfer');
    }

    /**
     * Validates "data" field
     * @return bool|null
     */
    protected function validateData()
    {
        $field = 'data';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        if (!is_array($value)) {
            $this->setErrorInvalid($field, $this->translation->text('Data'));
            return false;
        }

        return true;
    }

    /**
     * Validates a title
     * @return boolean|null
     */
    protected function validateTitle()
    {
        $field = 'title';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        if (empty($value) || mb_strlen($value) > 255) {
            $this->setErrorLengthRange($field, $this->translation->text('Title'));
            return false;
        }

        return true;
    }

    /**
     * Validates a name
     * @return boolean|null
     */
    protected function validateName()
    {
        $field = 'name';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        if (empty($value) || mb_strlen($value) > 255) {
            $this->setErrorLengthRange($field, $this->translation->text('Name'));
            return false;
        }

        return true;
    }

    /**
     * Validates a meta title
     * @return boolean
     */
    protected function validateMetaTitle()
    {
        $field = 'meta_title';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return true;
        }

        if (mb_strlen($value) > 60) {
            $this->setErrorLengthRange($field, $this->translation->text('Meta title'), 0, 60);
            return false;
        }

        return true;
    }

    /**
     * Validates a meta description
     * @return boolean
     */
    protected function validateMetaDescription()
    {
        $field = 'meta_description';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return true;
        }

        if (mb_strlen($value) > 160) {
            $this->setErrorLengthRange($field, $this->translation->text('Meta description'), 0, 160);
            return false;
        }

        return true;
    }

    /**
     * Validates a description field
     * @return boolean
     */
    protected function validateDescription()
    {
        $field = 'description';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return true;
        }

        if (mb_strlen($value) > 65535) {
            $this->setErrorLengthRange($field, $this->translation->text('Description'), 0, 65535);
            return false;
        }

        return true;
    }

    /**
     * Validates a weight field
     * @return boolean
     */
    protected function validateWeight()
    {
        $field = 'weight';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return true;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $this->translation->text('Weight'));
            return false;
        }

        return true;
    }

    /**
     * Sets "Status" field to an integer value
     * @return boolean
     */
    protected function validateStatus()
    {
        $field = 'status';
        $value = $this->getSubmitted($field);

        if (isset($value)) {
            $this->setSubmitted($field, (int) filter_var($value, FILTER_VALIDATE_BOOLEAN));
        }

        return true;
    }

    /**
     * Sets "Default" field to an integer value
     * @return boolean
     */
    protected function validateDefault()
    {
        $field = 'default';
        $value = $this->getSubmitted($field);

        if (isset($value)) {
            $this->setSubmitted($field, (int) filter_var($value, FILTER_VALIDATE_BOOLEAN));
        }

        return true;
    }

    /**
     * Validates category translations
     * @return boolean|null
     */
    protected function validateTranslation()
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
    protected function validateImages()
    {
        $field = 'images';
        $images = $this->getSubmitted($field);

        if (empty($images) || !is_array($images)) {
            $this->unsetSubmitted($field);
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
                foreach ($translation as $key => &$value) {
                    if ($value === '') {
                        unset($image['translation'][$lang][$key]);
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

        $this->setSubmitted($field, $images);
        return true;
    }

    /**
     * Validates uploaded images
     * @param string $entity
     * @return bool|null
     */
    protected function validateUploadImages($entity)
    {
        $files = $this->request->file('files');

        if (empty($files['name'][0])) {
            return null;
        }

        $directory = $this->config->get("{$entity}_image_dirname", $entity);
        $results = $this->file_transfer->uploadMultiple($files, 'image', "image/upload/$directory");

        foreach ($results['transferred'] as $key => $path) {
            $this->setSubmitted("images.$key.path", $path);
        }

        if (!empty($results['errors'])) {
            $this->setError('images', implode('<br>', (array) $results['errors']));
            return false;
        }

        return true;
    }

    /**
     * Validates an alias
     * @return boolean|null
     */
    protected function validateAlias()
    {
        $field = 'alias';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Alias');

        if (mb_strlen($value) > 255) {
            $this->setErrorLengthRange($field, $label, 0, 255);
            return false;
        }

        if (preg_match('/^[A-Za-z0-9_.-]+$/', $value) !== 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $updating = $this->getUpdating();

        if (isset($updating['alias']) && ($updating['alias'] === $value)) {
            return true; // Do not check own alias on update
        }

        if ($this->alias->exists($value)) {
            $this->setErrorExists($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates the store ID field
     * @return boolean|null
     */
    protected function validateStoreId()
    {
        $field = 'store_id';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Store');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $store = $this->store->get($value);

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
    protected function validateUserId()
    {
        $field = 'user_id';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('User');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $user = $this->user->get($value);

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
    protected function validateUserCartId()
    {
        $field = 'user_id';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('User');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (strlen($value) > 255) {
            $this->setErrorLengthRange($field, $label, 0, 255);
            return false;
        }

        if (!is_numeric($value)) {
            return true; // Anonymous user
        }

        $user = $this->user->get($value);

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
    protected function validateEmail()
    {
        $field = 'email';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('E-mail');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        return true;
    }

}
