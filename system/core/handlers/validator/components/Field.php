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
// Parent
use gplcart\core\models\Field as FieldModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate field data
 */
class Field extends ComponentValidator
{

    /**
     * Field model instance
     * @var \gplcart\core\models\Field $field
     */
    protected $field;

    /**
     * @param Config $config
     * @param LanguageModel $language
     * @param FileModel $file
     * @param UserModel $user
     * @param StoreModel $store
     * @param AliasModel $alias
     * @param RequestHelper $request
     * @param FieldModel $field
     */
    public function __construct(Config $config, LanguageModel $language, FileModel $file,
            UserModel $user, StoreModel $store, AliasModel $alias, RequestHelper $request,
            FieldModel $field)
    {
        parent::__construct($config, $language, $file, $user, $store, $alias, $request);

        $this->field = $field;
    }

    /**
     * Performs full field data validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function field(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateField();
        $this->validateTitle();
        $this->validateWeight();
        $this->validateTranslation();
        $this->validateTypeField();
        $this->validateWidgetTypeField();

        return $this->getResult();
    }

    /**
     * Validates a field to be updated
     * @return boolean|null
     */
    protected function validateField()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->field->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->language->text('Field'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a field type
     * @return boolean|null
     */
    protected function validateTypeField()
    {
        if ($this->isUpdating()) {
            return null; // Cannot change type of existing field
        }

        $field = 'type';
        $label = $this->language->text('Type');
        $type = $this->getSubmitted($field);

        if (empty($type)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $types = $this->field->getTypes();

        if (empty($types[$type])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }
        return true;
    }

    /**
     * Validates a field widget type
     * @return boolean|null
     */
    protected function validateWidgetTypeField()
    {
        $field = 'widget';
        $label = $this->language->text('Widget');
        $type = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($type)) {
            return null;
        }

        if (empty($type)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $types = $this->field->getWidgetTypes();

        if (empty($types[$type])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }
        return true;
    }

}
