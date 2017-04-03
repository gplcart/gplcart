<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Rating as RatingModel,
    gplcart\core\models\Product as ProductModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate product rating data
 */
class Rating extends ComponentValidator
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
        $value = $this->getSubmitted('product_id');

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('product_id', $error);
            return false;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('product_id', $error);
            return false;
        }

        $product = $this->product->get($value);

        if (empty($product['product_id'])) {
            $vars = array('@name' => $this->language->text('Product'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('product_id', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a rating value
     * @return boolean
     */
    protected function validateValueRating()
    {
        $value = $this->getSubmitted('rating');

        if (!isset($value)) {
            $vars = array('@field' => $this->language->text('Rating'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('rating', $error);
            return false;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Rating'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('rating', $error);
            return false;
        }

        if ((float) $value > 5) {
            $error = $this->language->text('Rating must not be greater than @num', array('@num' => 5));
            $this->setError('rating', $error);
            return false;
        }

        return true;
    }

}
