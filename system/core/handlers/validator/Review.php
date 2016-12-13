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
     */
    public function review(array &$submitted, array $options = array())
    {
        $this->validateReview($submitted);
        $this->validateStatus($submitted);
        $this->validateTextReview($submitted);
        $this->validateCreatedReview($submitted);
        $this->validateProductReview($submitted);
        $this->validateEmailReview($submitted);

        return $this->getResult();
    }

    /**
     * Validates a review to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateReview(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {

            $data = $this->review->get($submitted['update']);

            if (empty($data)) {
                $this->errors['update'] = $this->language->text('@name is unavailable', array(
                    '@name' => $this->language->text('Review')));
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

    /**
     * Validates a review text
     * @param array $submitted
     * @return boolean
     */
    protected function validateTextReview(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['text'])) {
            return null;
        }

        if (empty($submitted['text'])) {
            $this->errors['text'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Text')
            ));
            return false;
        }

        $limits = $this->review->getLimits();
        $length = mb_strlen($submitted['text']);

        if ($length < $limits['min'] || $length > $limits['max']) {
            $options = array('@min' => $limits['min'], '@max' => $limits['max'], '@field' => $this->language->text('Text'));
            $this->errors['text'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a created review date
     * @param array $submitted
     * @return boolean
     */
    protected function validateCreatedReview(array &$submitted)
    {
        if (!isset($submitted['created'])) {
            return null;
        }

        $timestamp = strtotime($submitted['created']);

        if (empty($timestamp)) {
            $options = array('@field' => $this->language->text('Created'));
            $this->errors['created'] = $this->language->text('@field is not a valid datetime description', $options);
            return false;
        }

        $submitted['created'] = $timestamp;
        return true;
    }

    /**
     * Validates a product ID
     * @param array $submitted
     * @return boolean
     */
    protected function validateProductReview(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['product_id'])) {
            return null;
        }

        if (empty($submitted['product_id'])) {
            $this->errors['product_id'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Product')
            ));
            return false;
        }

        if (!is_numeric($submitted['product_id'])) {
            $options = array('@field' => $this->language->text('Product'));
            $this->errors['product_id'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        $product = $this->product->get($submitted['product_id']);

        if (empty($product)) {
            $this->errors['product_id'] = $this->language->text('@name is unavailable', array(
                '@name' => $this->language->text('Product')));
            return false;
        }
        return true;
    }

    /**
     * Validates a user E-mail
     * @param array $submitted
     * @return boolean
     */
    protected function validateEmailReview(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['email'])) {
            return null;
        }

        if (empty($submitted['email'])) {
            $this->errors['email'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Email')
            ));
            return false;
        }

        $user = $this->user->getByEmail($submitted['email']);

        if (empty($user)) {
            $this->errors['email'] = $this->language->text('@name is unavailable', array(
                '@name' => $this->language->text('Email')));
            return false;
        }

        $submitted['user_id'] = $user['user_id'];
        return true;
    }

}
