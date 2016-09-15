<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\models\Review as ModelsReview;
use core\models\Rating as ModelsRating;
use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to reviews
 */
class Review extends FrontendController
{

    /**
     * Review model instance
     * @var \core\models\Review $review
     */
    protected $review;

    /**
     * Rating model instance
     * @var \core\model\Rating $rating
     */
    protected $rating;

    /**
     * Constructor
     * @param ModelsReview $review
     * @param ModelsRating $rating
     */
    public function __construct(ModelsReview $review, ModelsRating $rating)
    {
        parent::__construct();

        $this->rating = $rating;
        $this->review = $review;
    }

    /**
     * Displays the review edit page
     * @param integer $product_id
     * @param integer|null $review_id
     */
    public function editReview($product_id, $review_id = null)
    {
        $this->controlAccessEditReview();

        $product = $this->getProductReview($product_id);
        $review = $this->getReview($review_id, $product_id);

        $this->submitReview($review, $product);

        $honeypot = $this->getHoneypot();
        $deletable = (bool) $this->config('review_deletable', 1);

        $this->setData('review', $review);
        $this->setData('product', $product);
        $this->setData('honeypot', $honeypot);
        $this->setData('deletable', $deletable);

        $this->setDataImageReview($product);
        $this->setDataRatingReview($review, $product);

        $this->setTitleEditReview($product);
        $this->outputEditReview();
    }

    /**
     * Renders and outputs review edit page
     */
    protected function outputEditReview()
    {
        $this->output('review/edit');
    }

    /**
     * Sets titles on the review edit page
     * @param array $product
     */
    protected function setTitleEditReview(array $product)
    {
        $text = $this->text('Review of %product', array(
            '%product' => $product['title']));
        
        $this->setTitle($text, false);
    }

    /**
     * Sets product image
     * @param array $product
     */
    protected function setDataImageReview(array $product)
    {
        $options = array('imagestyle' => $this->setting('image_style_product', 5));
        $this->setItemThumb($product, $options);

        if (!empty($product['images'])) {
            $image = reset($product['images']); // Get only first image
            $this->setData('image', $image);
        }
    }

    /**
     * Sets rating widget
     * @param array $review
     * @param array $product
     */
    protected function setDataRatingReview(array $review, array $product)
    {
        $options = array(
            'review' => $review,
            'product' => $product,
            'unvote' => (bool) $this->config('rating_unvote', 1)
        );

        $html = $this->render('common/rating/edit', $options);
        $this->setData('rating', $html);
    }

    /**
     * Saves a submitted review
     * @param array $review
     * @param array $product
     * @return null
     */
    protected function submitReview(array $review, array $product)
    {
        $this->controlSpam('review');

        if ($this->isPosted('delete')) {
            return $this->deleteReview($review, $product);
        }

        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('review');
        $this->validateReview($review, $product);

        if ($this->hasErrors('review')) {
            return;
        }

        $this->setRatingReview($product);

        if (isset($review['review_id'])) {
            return $this->updateReview($review, $product);
        }

        $this->addReview($product);
    }

    /**
     * Updates a submitted review
     * @param array $review
     * @param array $product
     */
    protected function updateReview(array $review, array $product)
    {
        $submitted = $this->getSubmitted();
        $updated = $this->review->update($review['review_id'], $submitted);

        if (!$updated) {
            $this->redirect("product/{$product['product_id']}");
        }

        $message = $this->text('Your review has been updated');

        if (empty($submitted['status'])) {
            $message = $this->text('Your review has been updated and will be visible after approval');
        }

        $this->redirect("product/{$product['product_id']}", $message, 'success');
    }

    /**
     * Adds a submitted review
     * @param array $product
     */
    protected function addReview(array $product)
    {
        $submitted = $this->getSubmitted();
        $added = $this->review->add($submitted);
        

        if (empty($added)) {
            $message = $this->text('Your review has not been added');
            $this->redirect('', $message, 'warning');
        }
        
        $message = $this->text('Your review has been added');

        if (empty($submitted['status'])) {
            $message = $this->text('Your review has been added and will be visible after approval');
        }
        
        $this->redirect("product/{$product['product_id']}", $message, 'success');
    }

    /**
     * Sets a rating to the product
     * @param array $product
     */
    protected function setRatingReview(array $product)
    {
        $rating = $this->getSubmitted('rating');
        $user_id = $this->getSubmitted('user_id');

        if (isset($rating)) {
            $this->rating->set($product['product_id'], $user_id, $rating);
        }
    }

    /**
     * Validates an array of submitted review data
     * @param array $review
     * @return null
     */
    protected function validateReview(array $review, array $product)
    {
        $this->setSubmitted('user_id', $this->uid);
        $this->setSubmitted('product_id', $product['product_id']);

        $status = (bool) $this->config('review_status', 1);
        $this->setSubmitted('status', $status);

        $this->addValidator('text', array(
            'length' => $this->review->getLimits()));

        $this->setValidators($review);
    }

    /**
     * Deletes a review
     * @param array $review
     * @param array $product
     */
    protected function deleteReview(array $review, array $product)
    {
        $deletable = $this->config('review_deletable', 1);

        if (empty($deletable) || empty($review['review_id'])) {
            return;
        }

        $deleted = $this->review->delete($review['review_id']);

        if ($deleted) {
            $message = $this->text('Your review has been deleted');
            $this->redirect("product/{$product['product_id']}", $message, 'success');
        }

        $message = $this->text('Your review has not been deleted');
        $this->redirect("product/{$product['product_id']}", $message, 'warning');
    }

    /**
     * Returns a review
     * @param mixed $review_id
     * @param integer $product_id
     * @return array
     */
    protected function getReview($review_id, $product_id)
    {
        if (!is_numeric($review_id)) {
            return array();
        }

        $review = $this->review->get($review_id);

        if (empty($review)) {
            $this->outputError(404);
        }

        if ($review['user_id'] != $this->uid) {
            $this->outputError(403);
        }

        $rating = $this->rating->getByUser($product_id, $this->uid);
        $review['rating'] = isset($rating['rating']) ? $rating['rating'] : 0;

        return $review;
    }

    /**
     * Loads a product from the database
     * @param integer $product_id
     * @return array
     */
    protected function getProductReview($product_id)
    {
        $product = $this->product->get($product_id);

        if (empty($product['status']) || $product['store_id'] != $this->store_id) {
            $this->outputError(404);
        }
        
        $this->setItemPrice($product);

        return $product;
    }

    /**
     * Controls access to the review
     */
    protected function controlAccessEditReview()
    {
        $editable = $this->config('review_editable', 1);

        // We only accept logged in users and check if 
        // review editing is enabled
        if (empty($editable) || empty($this->uid)) {
            $this->outputError(403);
        }
    }

}
