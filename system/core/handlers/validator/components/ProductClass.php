<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\ProductClass as ProductClassModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate a product class data
 */
class ProductClass extends ComponentValidator
{

    /**
     * Product class model instance
     * @var \gplcart\core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
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
     * @param array $options
     * @return array|boolean
     */
    public function productClass(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateProductClass();
        $this->validateStatusComponent();
        $this->validateTitleComponent();

        return $this->getResult();
    }

    /**
     * Validates a product class ID
     * @return boolean|null
     */
    protected function validateProductClass()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->product_class->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->language->text('Product class'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

}
