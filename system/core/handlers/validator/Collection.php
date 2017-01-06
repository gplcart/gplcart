<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Collection as CollectionModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate collection data
 */
class Collection extends BaseValidator
{

    /**
     * Collection model instance
     * @var \gplcart\core\models\Collection $collection
     */
    protected $collection;

    /**
     * Constructor
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
        $this->submitted = &$submitted;

        $this->validateCollection($options);
        $this->validateStatus($options);
        $this->validateTitle($options);
        $this->validateDescription($options);
        $this->validateTranslation($options);
        $this->validateStoreId($options);
        $this->validateTypeCollection($options);

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
            $vars = array('@name' => $this->language->text('Collection'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setSubmitted('update', $data);
        return true;
    }

    /**
     * Validates collection type field
     * @param array $options
     * @return boolean
     */
    protected function validateTypeCollection(array $options)
    {
        if ($this->isUpdating()) {
            return true; // Type cannot be updated
        }

        $type = $this->getSubmitted('type', $options);

        if (empty($type)) {
            $vars = array('@field' => $this->language->text('Type'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('type', $error, $options);
            return false;
        }

        $types = $this->collection->getTypes();

        if (!isset($types[$type])) {
            $vars = array('@name' => $this->language->text('Type'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('type', $error, $options);
            return false;
        }

        return true;
    }

}
