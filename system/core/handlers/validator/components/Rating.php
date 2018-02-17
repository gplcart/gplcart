<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\Rating as RatingModel;

/**
 * Provides methods to validate product rating data
 */
class Rating extends Component
{

    /**
     * Rating model instance
     * @var \gplcart\core\models\Rating $rating
     */
    protected $rating;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * @param RatingModel $rating
     * @param ProductModel $product
     */
    public function __construct(RatingModel $rating, ProductModel $product)
    {
        parent::__construct();

        $this->rating = $rating;
        $this->product = $product;
    }

    /**
     * Performs full rating data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function rating(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateProductRating();
        $this->validateUserId();
        $this->validateValueRating();

        return $this->getResult();
    }

    /**
     * Validates a submitted product ID
     * @return boolean
     */
    protected function validateProductRating()
    {
        $field = 'product_id';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);
        $label = $this->translation->text('Product');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $product = $this->product->get($value);

        if (empty($product['product_id'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        // We need the store id later
        $this->setSubmitted('store_id', $product['store_id']);
        return true;
    }

    /**
     * Validates a rating value
     * @return boolean
     */
    protected function validateValueRating()
    {
        $field = 'rating';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);
        $label = $this->translation->text('Rating');

        if (!isset($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if ($value > 5) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        return true;
    }

}
