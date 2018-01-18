<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\Product as ProductModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate product comparison data
 */
class Compare extends ComponentValidator
{

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * @param ProductModel $product
     */
    public function __construct(ProductModel $product)
    {
        parent::__construct();

        $this->product = $product;
    }

    /**
     * Performs full product comparison data validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function compare(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateProductCompare();

        return $this->getResult();
    }

    /**
     * Validates a compared product ID
     * @return boolean
     */
    protected function validateProductCompare()
    {
        $field = 'product_id';
        $label = $this->translation->text('Product');
        $value = $this->getSubmitted($field);

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $product = $this->product->get($value);

        if (empty($product['status'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        $this->setSubmitted('product', $product);
        return true;
    }

}
