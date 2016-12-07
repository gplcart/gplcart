<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Product as ProductModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate product comparison data
 */
class Compare extends BaseValidator
{

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Constructor
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
     */
    public function compare(array &$submitted)
    {
        $this->validateProductCompare($submitted);

        return $this->getResult();
    }

    /**
     * Validates a compared product ID
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateProductCompare(array &$submitted)
    {
        if (empty($submitted['product_id'])) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('product_id', $error);
            return false;
        }

        if (!is_numeric($submitted['product_id'])) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('product_id', $error);
            return false;
        }

        $product = $this->product->get($submitted['product_id']);

        if (empty($product['status'])) {
            $vars = array('@name' => $this->language->text('Product'));
            $error = $this->language->text('Object @name does not exist', $vars);
            $this->setError('product_id', $error);
            return false;
        }

        $submitted['product'] = $product;
        return true;
    }

}
