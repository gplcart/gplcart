<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\ProductClass as ProductClassModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate a product class data
 */
class ProductClass extends BaseValidator
{

    /**
     * Product class model instance
     * @var \core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * Constructor
     * @param ProductClassModel $product_class
     */
    public function __construct(ProductClassModel $product_class)
    {
        parent::__construct();

        $this->product_class = $product_class;
    }

    /**
     * Performs full product class validation
     * @param array $submitted
     */
    public function productClass(array &$submitted, array $options = array())
    {
        $this->validateProductClass($submitted);
        $this->validateStatus($submitted);
        $this->validateTitle($submitted);

        return $this->getResult();
    }

    /**
     * Validates a product class ID
     * @param array $submitted
     * @return boolean
     */
    protected function validateProductClass(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {
            $data = $this->product_class->get($submitted['update']);
            if (empty($data)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Product class')));
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

}
