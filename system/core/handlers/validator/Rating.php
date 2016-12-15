<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Rating as RatingModel;
use core\models\Product as ProductModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate product rating data
 */
class Rating extends BaseValidator
{

    /**
     * Rating model instance
     * @var \core\models\Rating $rating
     */
    protected $rating;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Constructor
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
        $this->submitted = &$submitted;

        $this->validateProductRating($options);
        $this->validateUserId($options);
        $this->validateValueRating($options);

        return $this->getResult();
    }

    /**
     * Validates a submitted product ID
     * @param array $options
     * @return boolean
     */
    protected function validateProductRating(array $options)
    {
        $value = $this->getSubmitted('product_id', $options);

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        $product = $this->product->get($value);

        if (empty($product['product_id'])) {
            $vars = array('@name' => $this->language->text('Product'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a rating value
     * @param array $options
     * @return boolean
     */
    protected function validateValueRating(array $options)
    {
        $value = $this->getSubmitted('rating', $options);

        if (!isset($value)) {
            $vars = array('@field' => $this->language->text('Rating'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('rating', $error, $options);
            return false;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Rating'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('rating', $error, $options);
            return false;
        }

        if ((float) $value > 5) {
            $error = $this->language->text('Rating must not be greater than @max', array('@max' => 5));
            $this->setError('rating', $error, $options);
            return false;
        }

        return true;
    }

}
