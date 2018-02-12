<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Rating as RatingModel;
use gplcart\core\models\Review as ReviewModel;

/**
 * Handles incoming requests and outputs data related to reviews
 */
class Review extends Controller
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
     * An array of review data
     * @var array
     */
    protected $data_review = array();

    /**
     * An array of product data
     * @var array
     */
    protected $data_product = array();

    /**
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
     * Displays the edit review page
     * @param integer $product_id
     * @param integer|null $review_id
     */
    public function editReview($product_id, $review_id = null)
    {
        $this->setProductReview($product_id);
        $this->setReview($review_id);
        $this->setTitleEditReview();
        $this->setBreadcrumbEditReview();

        $this->setData('review', $this->data_review);
        $this->setData('product', $this->data_product);
        $this->setData('can_delete', $this->canDeleteReview());

        $this->submitEditReview();
        $this->setDataRatingEditReview();
        $this->outputEditReview();
    }

    /**
     * Sets a product data
     * @param integer $product_id
     */
    protected function setProductReview($product_id)
    {
        $this->data_product = $this->product->get($product_id);

        if (empty($this->data_product['status']) || $this->data_product['store_id'] != $this->store_id) {
            $this->outputHttpStatus(404);
        }

        $this->prepareProductReview($this->data_product);
    }

    /**
     * Render and output the edit review page
     */
    protected function outputEditReview()
    {
        $this->output('review/edit');
    }

    /**
     * Sets titles on the edit review page
     */
    protected function setTitleEditReview()
    {
        $this->setTitle($this->text('Add review'));
    }

    /**
     * Sets breadcrumbs on the edit review page
     */
    protected function setBreadcrumbEditReview()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $breadcrumbs[] = array(
            'url' => $this->url("product/{$this->data_product['product_id']}"),
            'text' => $this->data_product['title']
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Sets rating widget
     */
    protected function setDataRatingEditReview()
    {
        $options = array(
            'product' => $this->data_product,
            'review' => $this->getData('review'),
            'unvote' => $this->config('rating_unvote', 1)
        );

        $this->setData('rating', $this->render('common/rating/edit', $options));
    }

    /**
     * Handles a submitted review
     */
    protected function submitEditReview()
    {
        $this->controlSpam();

        if ($this->isPosted('delete')) {
            $this->deleteReview();
        } else if ($this->isPosted('save') && $this->validateEditReview()) {
            $this->submitRatingReview();
            if (isset($this->data_review['review_id'])) {
                $this->updateReview();
            } else {
                $this->addReview();
            }
        }
    }

    /**
     * Validates an array of submitted review data
     * @return bool
     */
    protected function validateEditReview()
    {
        $this->setSubmitted('review');
        $this->filterSubmitted(array('text', 'rating'));
        $this->setSubmitted('user_id', $this->uid);
        $this->setSubmitted('update', $this->data_review);
        $this->setSubmitted('product_id', $this->data_product['product_id']);
        $this->setSubmitted('status', (int) $this->config('review_status', 1));

        $this->validateComponent('review');

        return !$this->hasErrors(false);
    }

    /**
     * Handles a submitted rating
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
        $this->validateComponent('rating');

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

        if ($this->review->update($this->data_review['review_id'], $submitted)) {

            $message = $this->text('Review has been updated');

            if (empty($submitted['status'])) {
                $message = $this->text('Review has been updated and will be published after approval');
            }

            $this->redirect("product/{$this->data_product['product_id']}", $message, 'success');
        }

        $this->redirect("product/{$this->data_product['product_id']}");
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
            $message = $this->text('Review has been added and will be published after approval');
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
     */
    protected function deleteReview()
    {
        if (!$this->canDeleteReview()) {
            $message = $this->text('Unable to delete');
            $this->redirect("product/{$this->data_product['product_id']}", $message, 'warning');
        }

        if ($this->review->delete($this->data_review['review_id'])) {
            $message = $this->text('Review has been deleted');
            $this->redirect("product/{$this->data_product['product_id']}", $message, 'success');
        }
    }

    /**
     * Sets a review data
     * @param mixed $review_id
     */
    protected function setReview($review_id)
    {
        $this->data_review = array();

        if (is_numeric($review_id)) {

            $this->data_review = $this->review->get($review_id);

            if (empty($this->data_review)) {
                $this->outputHttpStatus(404);
            }
        }

        $this->controlAccessEditReview();
        $this->prepareReview($this->data_review);
    }

    /**
     * Controls access to the edit review page
     */
    protected function controlAccessEditReview()
    {
        if (!$this->config('review_enabled', 1) || empty($this->uid)) {
            $this->outputHttpStatus(403);
        }

        if (!$this->config('review_editable', 1)) {
            $this->outputHttpStatus(403);
        }

        if (isset($this->data_review['review_id']) && $this->data_review['user_id'] != $this->uid) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Prepares an array of review data
     * @param array $review
     */
    protected function prepareReview(array &$review)
    {
        $rating = $this->rating->getByUser($this->data_product['product_id'], $this->uid);
        $review['rating'] = isset($rating['rating']) ? $rating['rating'] : 0;
    }

    /**
     * Prepares an array of product data
     * @param array $product
     */
    protected function prepareProductReview(array &$product)
    {
        $this->setItemImages($product, 'product', $this->image);
        $this->setItemThumbProduct($product, $this->image);
        $this->setItemPriceCalculated($product, $this->product);
        $this->setItemPriceFormatted($product, $this->price, $this->current_currency);
    }

}
