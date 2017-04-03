<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\Review as ReviewModel,
    gplcart\core\models\Product as ProductModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate reviews
 */
class Review extends ComponentValidator
{

    /**
     * Review model instance
     * @var \gplcart\core\models\Review $review
     */
    protected $review;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
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
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateReview();
        $this->validateStatus();
        $this->validateTextReview();
        $this->validateCreatedReview();
        $this->validateProductReview();
        $this->validateEmailReview();
        $this->validateUserId();

        return $this->getResult();
    }

    /**
     * Validates a review to be updated
     * @return boolean|null
     */
    protected function validateReview()
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
     * @return boolean|null
     */
    protected function validateTextReview()
    {
        $value = $this->getSubmitted('text');

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Text'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('text', $error);
            return false;
        }

        $limits = $this->review->getLimits();
        $length = mb_strlen($value);

        if ($length < $limits['min'] || $length > $limits['max']) {
            $vars = array('@min' => $limits['min'], '@max' => $limits['max'], '@field' => $this->language->text('Text'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('text', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a created review date
     * @return boolean|null
     */
    protected function validateCreatedReview()
    {
        $value = $this->getSubmitted('created');

        if (!isset($value)) {
            return null;
        }

        $timestamp = strtotime($value);

        if (empty($timestamp)) {
            $vars = array('@field' => $this->language->text('Created'));
            $error = $this->language->text('@field is not a valid datetime description', $vars);
            $this->setError('created', $error);
            return false;
        }

        $this->setSubmitted('created', $timestamp);
        return true;
    }

    /**
     * Validates a product ID
     * @return boolean|null
     */
    protected function validateProductReview()
    {
        $value = $this->getSubmitted('product_id');

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

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
     * Validates a user E-mail
     * @return boolean|null
     */
    protected function validateEmailReview()
    {
        $value = $this->getSubmitted('email');

        if (!isset($value)) {
            return null;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Email'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('email', $error);
            return false;
        }

        $user = $this->user->getByEmail($value);

        if (empty($user['user_id'])) {
            $vars = array('@name' => $this->language->text('Email'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('email', $error);
            return false;
        }

        $this->setSubmitted('user_id', $user['user_id']);
        return true;
    }

}
