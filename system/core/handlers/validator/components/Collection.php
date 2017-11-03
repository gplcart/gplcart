<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

// Parent
use gplcart\core\Config;
use gplcart\core\models\File as FileModel,
    gplcart\core\models\User as UserModel,
    gplcart\core\models\Store as StoreModel,
    gplcart\core\models\Alias as AliasModel,
    gplcart\core\helpers\Request as RequestHelper,
    gplcart\core\models\Language as LanguageModel;
// New
use gplcart\core\models\Collection as CollectionModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate collection data
 */
class Collection extends ComponentValidator
{

    /**
     * Collection model instance
     * @var \gplcart\core\models\Collection $collection
     */
    protected $collection;

    /**
     * @param Config $config
     * @param LanguageModel $language
     * @param FileModel $file
     * @param UserModel $user
     * @param StoreModel $store
     * @param AliasModel $alias
     * @param RequestHelper $request
     * @param CollectionModel $collection
     */
    public function __construct(Config $config, LanguageModel $language, FileModel $file,
            UserModel $user, StoreModel $store, AliasModel $alias, RequestHelper $request,
            CollectionModel $collection)
    {
        parent::__construct($config, $language, $file, $user, $store, $alias, $request);
        $this->collection = $collection;
    }

    /**
     * Performs full collection data validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function collection(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateCollection();
        $this->validateStatus();
        $this->validateTitle();
        $this->validateDescription();
        $this->validateTranslation();
        $this->validateStoreId();
        $this->validateTypeCollection();

        return $this->getResult();
    }

    /**
     * Validates a collection to be updated
     * @return boolean|null
     */
    protected function validateCollection()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->collection->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->language->text('Collection'));
            return false;
        }

        $this->setSubmitted('update', $data);
        return true;
    }

    /**
     * Validates collection type field
     * @return boolean
     */
    protected function validateTypeCollection()
    {
        if ($this->isUpdating()) {
            return true; // Type cannot be updated
        }

        $field = 'type';
        $label = $this->language->text('Type');
        $type = $this->getSubmitted($field);

        if (empty($type)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $types = $this->collection->getTypes();

        if (!isset($types[$type])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }
        return true;
    }

}
