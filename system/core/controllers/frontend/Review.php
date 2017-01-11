<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Review as ReviewModel;
use gplcart\core\models\Rating as RatingModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to reviews
 */
class Review extends FrontendController
{

    /**
     * Review model instance
     * @var \gplcart\core\models\Review $review
     */
    protected $review;

    /**
     * Rating model instance
     * @var \gplcart\core\models\Rating $rating
     */
    protected $rating;

    /**
     * Constructor
     * @param ReviewModel $review
     * @param RatingModel $rating
     */
    public function __construct(ReviewModel $review, RatingModel $rating)
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

        $honeypot = $this->renderHoneyPotField();
        $can_delete = $this->canDeleteReview($review);

        $this->setData('review', $review);
        $this->setData('product', $product);
        $this->setData('honeypot', $honeypot);
        $this->setData('can_delete', $can_delete);

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
        $options = array('imagestyle' => $this->settings('image_style_product', 5));
        $this->setItemThumb($product, $options);

        if (!empty($product['images'])) {
            // Get only first image
            $image = reset($product['images']);
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
            'unvote' => $this->config('rating_unvote', 1)
        );

        $html = $this->render('common/rating/edit', $options);
        $this->setData('rating', $html);
    }

    /**
     * Saves a submitted review
     * @param array $review
     * @param array $product
     * @return null|void
     */
    protected function submitReview(array $review, array $product)
    {
        $this->controlSpam('review');

        if ($this->isPosted('delete')) {
            return $this->deleteReview($review, $product);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('review');
        $this->validateReview($review, $product);

        if ($this->hasErrors('review')) {
            return null;
        }

        $this->submitRatingReview();

        if (isset($review['review_id'])) {
            return $this->updateReview($review, $product);
        }

        return $this->addReview($product);
    }

    /**
     * Saves a submitted rating
     */
    protected function submitRatingReview()
    {
        $this->validateRatingReview();

        if (!$this->isError()) {
            $this->setRatingReview();
        }
    }

    /**
     * Validates a submitted rating
     */
    protected function validateRatingReview()
    {
        $this->validate('rating');
    }

    /**
     * Sets a rating to the product
     */
    protected function setRatingReview()
    {
        $submitted = $this->getSubmitted();
        $this->rating->set($submitted);
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
     * Validates an array of submitted review data
     * @param array $review
     * @return null
     */
    protected function validateReview(array $review, array $product)
    {
        $this->setSubmitted('update', $review);
        $this->setSubmitted('user_id', $this->uid);
        $this->setSubmitted('product_id', $product['product_id']);
        $this->setSubmitted('status', (int) $this->config('review_status', 1));

        $this->validate('review');
    }

    /**
     * Whether the review can be deleted
     * @param array $review
     * @return boolean
     */
    protected function canDeleteReview(array $review)
    {
        return isset($review['review_id']) && $this->config('review_deletable', 1);
    }

    /**
     * Deletes a review
     * @param array $review
     * @param array $product
     */
    protected function deleteReview(array $review, array $product)
    {
        if ($this->canDeleteReview($review)) {

            $deleted = $this->review->delete($review['review_id']);

            if ($deleted) {
                $message = $this->text('Your review has been deleted');
                $this->redirect("product/{$product['product_id']}", $message, 'success');
            }
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
            $this->outputHttpStatus(404);
        }

        if ($review['user_id'] != $this->uid) {
            $this->outputHttpStatus(403);
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
            $this->outputHttpStatus(404);
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
            $this->outputHttpStatus(403);
        }
    }

}
