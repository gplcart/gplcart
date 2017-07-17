<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

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
     * @param CollectionModel $collection
     */
    public function __construct(CollectionModel $collection)
    {
        parent::__construct();
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
        $this->validateStatusComponent();
        $this->validateTitleComponent();
        $this->validateDescriptionComponent();
        $this->validateTranslationComponent();
        $this->validateStoreIdComponent();
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
