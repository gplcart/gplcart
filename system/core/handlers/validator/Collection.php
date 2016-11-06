<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Collection as ModelsCollection;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate collection data
 */
class Collection extends BaseValidator
{

    /**
     * Collection model instance
     * @var \core\models\Collection $collection
     */
    protected $collection;

    /**
     * Constructor
     * @param ModelsCollection $collection
     */
    public function __construct(ModelsCollection $collection)
    {
        parent::__construct();
        $this->collection = $collection;
    }

    /**
     * Performs full collection data validation
     * @param array $submitted
     * @param array $options
     */
    public function collection(array &$submitted, array $options = array())
    {
        $this->validateCollection($submitted);
        $this->validateStatus($submitted);
        $this->validateTitle($submitted);
        $this->validateDescription($submitted);
        $this->validateTranslation($submitted);
        $this->validateStore($submitted);
        $this->validateTypeCollection($submitted);

        return empty($this->errors) ? true : $this->errors;
    }

    /**
     * Validates a collection to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateCollection(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {
            $data = $this->collection->get($submitted['update']);
            if (empty($data)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Collection')));
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

    /**
     * Validates collection type field
     * @param array $submitted
     * @return boolean
     */
    protected function validateTypeCollection(array &$submitted)
    {
        if (!empty($submitted['update'])) {
            return true; // Type cannot be updated
        }

        if (empty($submitted['type'])) {
            $this->errors['type'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Type')
            ));
            return false;
        }

        $types = $this->collection->getTypes();

        if (!isset($types[$submitted['type']])) {
            $this->errors['type'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Type')));
            return false;
        }

        return true;
    }

}
