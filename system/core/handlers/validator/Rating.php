<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Rating as ModelsRating;
use core\models\Product as ModelsProduct;
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
     * @param ModelsRating $rating
     * @param ModelsProduct $product
     */
    public function __construct(ModelsRating $rating, ModelsProduct $product)
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
        $this->validateProductRating($submitted, $options);
        $this->validateUserId($submitted, $options);
        $this->validateValueRating($submitted, $options);

        return empty($this->errors) ? true : $this->errors;
    }

    /**
     * Validates a submitted product ID
     * @param array $submitted
     * @param array $options
     * @return boolean
     */
    protected function validateProductRating(array &$submitted, array $options)
    {
        if (empty($submitted['product_id'])) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        if (!is_numeric($submitted['product_id'])) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        $product = $this->product->get($submitted['product_id']);

        if (empty($product)) {
            $vars = array('@name' => $this->language->text('Product'));
            $error = $this->language->text('Object @name does not exist', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a rating value
     * @param array $submitted
     * @param array $options
     * @return boolean
     */
    protected function validateValueRating(array &$submitted, array $options)
    {
        if (!isset($submitted['rating'])) {
            $vars = array('@field' => $this->language->text('Rating'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('rating', $error, $options);
            return false;
        }

        if (!is_numeric($submitted['rating'])) {
            $vars = array('@field' => $this->language->text('Rating'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('rating', $error, $options);
            return false;
        }

        if ((float) $submitted['rating'] > 5) {
            $error = $this->language->text('Rating must not be greater than @max', array('@max' => 5));
            $this->setError('rating', $error, $options);
            return false;
        }

        return true;
    }

}
