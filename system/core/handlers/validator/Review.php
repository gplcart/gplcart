<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Review as ReviewModel;
use core\models\Product as ProductModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate reviews
 */
class Review extends BaseValidator
{

    /**
     * Review model instance
     * @var \core\models\Review $review
     */
    protected $review;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Constructor
     * @param ReviewModel $review
     * @param ProductModel $product
     */
    public function __construct(ReviewModel $review, ProductModel $product)
    {
        parent::__construct();

        $this->review = $review;
        $this->product = $product;
    }

    /**
     * Performs full review data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function review(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateReview($options);
        $this->validateStatus($options);
        $this->validateTextReview($options);
        $this->validateCreatedReview($options);
        $this->validateProductReview($options);
        $this->validateEmailReview($options);

        return $this->getResult();
    }

    /**
     * Validates a review to be updated
     * @param array $options
     * @return boolean|null
     */
    protected function validateReview(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->review->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Review'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a review text
     * @param array $options
     * @return boolean|null
     */
    protected function validateTextReview(array $options)
    {
        $value = $this->getSubmitted('text', $options);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Text'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('text', $error, $options);
            return false;
        }

        $limits = $this->review->getLimits();
        $length = mb_strlen($value);

        if ($length < $limits['min'] || $length > $limits['max']) {
            $vars = array('@min' => $limits['min'], '@max' => $limits['max'], '@field' => $this->language->text('Text'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('text', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a created review date
     * @param array $options
     * @return boolean|null
     */
    protected function validateCreatedReview(array $options)
    {
        $value = $this->getSubmitted('created', $options);

        if (!isset($value)) {
            return null;
        }

        $timestamp = strtotime($value);

        if (empty($timestamp)) {
            $vars = array('@field' => $this->language->text('Created'));
            $error = $this->language->text('@field is not a valid datetime description', $vars);
            $this->setError('created', $error, $options);
            return false;
        }

        $this->setSubmitted('created', $timestamp, $options);
        return true;
    }

    /**
     * Validates a product ID
     * @param array $options
     * @return boolean|null
     */
    protected function validateProductReview(array $options)
    {
        $value = $this->getSubmitted('product_id', $options);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

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
     * Validates a user E-mail
     * @param array $options
     * @return boolean|null
     */
    protected function validateEmailReview(array $options)
    {
        $value = $this->getSubmitted('email', $options);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Email'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('email', $error, $options);
            return false;
        }

        $user = $this->user->getByEmail($value);

        if (empty($user['user_id'])) {
            $vars = array('@name' => $this->language->text('Email'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('email', $error, $options);
            return false;
        }

        $this->setSubmitted('user_id', $user['user_id'], $options);
        return true;
    }

}
