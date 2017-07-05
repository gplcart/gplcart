<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Review as ReviewModel,
    gplcart\core\models\Product as ProductModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to user reviews
 */
class Review extends BackendController
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
     * An array of review data
     * @var array
     */
    protected $data_review = array();

    /**
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
     * Displays the reviews overview page
     */
    public function listReview()
    {
        $this->actionListReview();

        $this->setTitleListReview();
        $this->setBreadcrumbListReview();

        $this->setFilterListReview();
        $this->setTotalListReview();
        $this->setPagerLimit();

        $this->setData('reviews', $this->getListReview());
        $this->setDataListReview();

        $this->outputListReview();
    }

    /**
     * Set filter on the reviews overview page
     */
    protected function setFilterListReview()
    {
        $allowed = array('product_id', 'email',
            'status', 'created', 'text', 'review_id');

        $this->setFilter($allowed);
    }

    /**
     * Applies an action to the selected reviews
     */
    protected function actionListReview()
    {
        $value = (string) $this->getPosted('value');
        $action = (string) $this->getPosted('action');
        $selected = (array) $this->getPosted('selected', array());

        if (empty($action)) {
            return null;
        }

        $updated = $deleted = 0;
        foreach ($selected as $review_id) {

            if ($action == 'status' && $this->access('review_edit')) {
                $updated += (int) $this->review->update($review_id, array('status' => $value));
            }

            if ($action == 'delete' && $this->access('review_delete')) {
                $deleted += (int) $this->review->delete($review_id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Updated %num items', array('%num' => $updated));
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 1) {
            $message = $this->text('Deleted %num items', array('%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Sets a total number of reviews found for the filter conditions
     */
    protected function setTotalListReview()
    {
        $query = $this->query_filter;
        $query['count'] = true;
        $this->total = (int) $this->review->getList($query);
    }

    /**
     * Returns an array of reviews
     * @return array
     */
    protected function getListReview()
    {
        $query = $this->query_filter;
        $query['limit'] = $this->limit;
        $reviews = (array) $this->review->getList($query);
        return $this->prepareListReview($reviews);
    }

    /**
     * Prepare an array of reviews
     * @param array $reviews
     * @return array
     */
    protected function prepareListReview(array $reviews)
    {
        foreach ($reviews as &$review) {
            $product = $this->product->get($review['product_id']);
            $review['product'] = empty($product) ? '' : $product['title'];
        }

        return $reviews;
    }

    /**
     * Prepare the template data on the overview reviews page
     */
    protected function setDataListReview()
    {
        $product_id = $this->getQuery('product_id');

        if (!empty($product_id)) {
            $product = $this->product->get($product_id);
        }

        $this->setData('product', isset($product['title']) ? $product['title'] : '');
    }

    /**
     * Sets title on the reviews overview page
     */
    protected function setTitleListReview()
    {
        $this->setTitle($this->text('Reviews'));
    }

    /**
     * Sets breadcrumbs on the reviews overview page
     */
    protected function setBreadcrumbListReview()
    {
        $breadcrumb = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the reviews overview page
     */
    protected function outputListReview()
    {
        $this->output('content/review/list');
    }

    /**
     * Displays the review edit form
     * @param integer|null $review_id
     */
    public function editReview($review_id = null)
    {
        $this->setReview($review_id);

        $this->setTitleEditReview();
        $this->setBreadcrumbEditReview();

        $this->setData('review', $this->data_review);

        $this->submitEditReview();
        $this->setDataEditReview();
        $this->outputEditReview();
    }

    /**
     * Set a review data
     * @param integer $review_id
     */
    protected function setReview($review_id)
    {
        if (is_numeric($review_id)) {
            $this->data_review = $this->review->get($review_id);
            if (empty($this->data_review)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Handles a submitted review
     */
    protected function submitEditReview()
    {
        if ($this->isPosted('delete')) {
            $this->deleteReview();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateEditReview()) {
            return null;
        }

        if (isset($this->data_review['review_id'])) {
            $this->updateReview();
        } else {
            $this->addReview();
        }
    }

    /**
     * Validates a submitted review
     * @return bool
     */
    protected function validateEditReview()
    {
        $this->setSubmitted('review');
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_review);

        $this->validateComponent('review');

        return !$this->hasErrors();
    }

    /**
     * Deletes a review
     */
    protected function deleteReview()
    {
        $this->controlAccess('review_delete');
        $this->review->delete($this->data_review['review_id']);

        $message = $this->text('Review has been deleted');
        $this->redirect('admin/content/review', $message, 'success');
    }

    /**
     * Updates a review
     */
    protected function updateReview()
    {
        $this->controlAccess('review_edit');

        $values = $this->getSubmitted();
        $this->review->update($this->data_review['review_id'], $values);

        $message = $this->text('Review has been updated');
        $this->redirect('admin/content/review', $message, 'success');
    }

    /**
     * Adds a new review
     */
    protected function addReview()
    {
        $this->controlAccess('review_add');

        $this->review->add($this->getSubmitted());

        $message = $this->text('Review has been added');
        $this->redirect('admin/content/review', $message, 'success');
    }

    /**
     * Set a template data on the edit review page
     */
    protected function setDataEditReview()
    {
        $user_id = $this->getData('review.user_id');
        $product_id = $this->getData('review.product_id');

        $user = $this->user->get($user_id);
        $email = isset($user['email']) ? $user['email'] : '';
        $this->setData('review.email', $email);

        $product = $this->product->get($product_id);
        $title = isset($product['title']) ? $product['title'] : '';
        $this->setData('review.product', $title);
    }

    /**
     * Sets title on the edit review page
     */
    protected function setTitleEditReview()
    {
        $title = $this->text('Add review');

        if (isset($this->data_review['review_id'])) {
            $title = $this->text('Edit rewiew');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit review page
     */
    protected function setBreadcrumbEditReview()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Reviews'),
            'url' => $this->url('admin/content/review')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the edit review page
     */
    protected function outputEditReview()
    {
        $this->output('content/review/edit');
    }

}
