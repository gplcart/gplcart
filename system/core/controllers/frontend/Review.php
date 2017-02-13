<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Review as ReviewModel,
    gplcart\core\models\Rating as RatingModel;
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
     * The current review
     * @var array
     */
    protected $data_review = array();

    /**
     * The current product
     * @var array
     */
    protected $data_product = array();

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
        $this->setProductReview($product_id);
        $this->setReview($review_id);

        $this->setTitleEditReview();
        $this->setBreadcrumbEditReview();

        $this->controlAccessEditReview();

        $this->setData('review', $this->data_review);
        $this->setData('product', $this->data_product);

        $this->setData('honeypot', $this->renderHoneyPot());
        $this->setData('can_delete', $this->canDeleteReview());

        $this->submitReview();

        $this->setDataRatingReview();
        $this->outputEditReview();
    }

    /**
     * Controls access to the review
     */
    protected function controlAccessEditReview()
    {
        if (!$this->config('review_enabled', 1) || empty($this->uid)) {
            $this->outputHttpStatus(403);
        }

        if (isset($this->data_review['review_id']) && !$this->config('review_editable', 1)) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Loads a product from the database
     * @param integer $product_id
     * @return array
     */
    protected function setProductReview($product_id)
    {
        $product = $this->product->get($product_id);

        if (empty($product['status']) || $product['store_id'] != $this->store_id) {
            $this->outputHttpStatus(404);
        }

        return $this->data_product = $product;
    }

    /**
     * Renders and outputs the review edit page
     */
    protected function outputEditReview()
    {
        $this->output('review/edit');
    }

    /**
     * Sets titles on the review edit page
     */
    protected function setTitleEditReview()
    {
        $vars = array('%name' => $this->data_product['title']);
        $text = $this->text('Review of %name', $vars);
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the add/edit review page
     */
    protected function setBreadcrumbEditReview()
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Shop')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Sets rating widget
     */
    protected function setDataRatingReview()
    {
        $options = array(
            'review' => $this->data_review,
            'product' => $this->data_product,
            'unvote' => $this->config('rating_unvote', 1)
        );

        $html = $this->render('common/rating/edit', $options);
        $this->setData('rating', $html);
    }

    /**
     * Saves a submitted review
     * @return null
     */
    protected function submitReview()
    {
        if ($this->isPosted('delete')) {
            $this->deleteReview();
            return null;
        }

        $this->controlSpam();

        if (!$this->isPosted('save') || !$this->validateReview()) {
            return null;
        }

        $this->submitRatingReview();

        if (isset($this->data_review['review_id'])) {
            $this->updateReview();
        } else {
            $this->addReview();
        }
    }

    /**
     * Validates an array of submitted review data
     * @return bool
     */
    protected function validateReview()
    {
        $this->setSubmitted('review');
        $this->setSubmitted('user_id', $this->uid);
        $this->setSubmitted('update', $this->data_review);
        $this->setSubmitted('product_id', $this->data_product['product_id']);
        $this->setSubmitted('status', (int) $this->config('review_status', 1));

        $this->validate('review');

        return !$this->hasErrors('review', false);
    }

    /**
     * Saves a submitted rating
     */
    protected function submitRatingReview()
    {
        if ($this->validateRatingReview()) {
            $this->setRatingReview();
        }
    }

    /**
     * Validates a submitted rating
     * @return bool
     */
    protected function validateRatingReview()
    {
        $this->validate('rating');

        return !$this->isError();
    }

    /**
     * Sets a rating for the product
     */
    protected function setRatingReview()
    {
        $this->rating->set($this->getSubmitted());
    }

    /**
     * Updates a submitted review
     */
    protected function updateReview()
    {
        $submitted = $this->getSubmitted();
        $updated = $this->review->update($this->data_review['review_id'], $submitted);

        if (!$updated) {
            $this->redirect("product/{$this->data_product['product_id']}");
        }

        $message = $this->text('Review has been updated');

        if (empty($submitted['status'])) {
            $message = $this->text('Review has been updated and will be visible after approval');
        }

        $this->redirect("product/{$this->data_product['product_id']}", $message, 'success');
    }

    /**
     * Adds a submitted review
     */
    protected function addReview()
    {
        $submitted = $this->getSubmitted();
        $added = $this->review->add($submitted);

        if (empty($added)) {
            $message = $this->text('Review has not been added');
            $this->redirect('', $message, 'warning');
        }

        $message = $this->text('Review has been added');

        if (empty($submitted['status'])) {
            $message = $this->text('Review has been added and will be visible after approval');
        }

        $this->redirect("product/{$this->data_product['product_id']}", $message, 'success');
    }

    /**
     * Whether the review can be deleted
     * @return boolean
     */
    protected function canDeleteReview()
    {
        return isset($this->data_review['review_id']) && $this->config('review_deletable', 1);
    }

    /**
     * Deletes a review
     * @return null
     */
    protected function deleteReview()
    {
        if (!$this->canDeleteReview()) {
            $message = $this->text('Unable to delete this review');
            $this->redirect("product/{$this->data_product['product_id']}", $message, 'warning');
        }

        $deleted = $this->review->delete($this->data_review['review_id']);

        if ($deleted) {
            $message = $this->text('Review has been deleted');
            $this->redirect("product/{$this->data_product['product_id']}", $message, 'success');
        }
    }

    /**
     * Returns a review
     * @param mixed $review_id
     * @return array
     */
    protected function setReview($review_id)
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

        return $this->data_review = $this->prepareReview($review);
    }

    /**
     * Prepares an array of review data
     * @param array $review
     * @return array
     */
    protected function prepareReview(array $review)
    {
        $rating = $this->rating->getByUser($this->data_product['product_id'], $this->uid);
        $review['rating'] = isset($rating['rating']) ? $rating['rating'] : 0;
        return $review;
    }

}
