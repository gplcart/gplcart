<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Review as ReviewModel;
use gplcart\core\models\Product as ProductModel;
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
     * The current review
     * @var array
     */
    protected $data_review = array();

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
     * Displays the reviews overview page
     */
    public function listReview()
    {
        $this->actionReview();

        $this->setTitleListReview();
        $this->setBreadcrumbListReview();

        $query = $this->getFilterQuery();

        $filters = array('product_id', 'email',
            'status', 'created', 'text', 'review_id');
        $this->setFilter($filters, $query);

        $total = $this->getTotalReview($query);
        $limit = $this->setPager($total, $query);

        $this->setData('reviews', $this->getListReview($limit, $query));
        $this->setDataListReview($query);

        $this->outputListReview();
    }

    /**
     * Applies an action to the selected reviews
     */
    protected function actionReview()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

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
            $message = $this->text('Updated %num reviews', array('%num' => $updated));
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 1) {
            $message = $this->text('Deleted %num reviews', array('%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Returns a number of total reviews for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalReview(array $query)
    {
        $query['count'] = true;
        return (int) $this->review->getList($query);
    }

    /**
     * Returns an array of reviews
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListReview(array $limit, array $query)
    {
        $query['limit'] = $limit;
        $reviews = (array) $this->review->getList($query);

        foreach ($reviews as &$review) {
            $review['product'] = '';
            $product = $this->product->get($review['product_id']);
            if (!empty($product)) {
                $review['product'] = $product['title'];
            }
        }

        return $reviews;
    }

    /**
     * Modifies template variables on the reviews page
     * @param array $query
     */
    protected function setDataListReview(array $query)
    {
        $title = '';
        if (isset($query['product_id'])) {
            $product = $this->product->get($query['product_id']);
            $title = isset($product['title']) ? $product['title'] : '';
        }

        $this->setData('product', $title);
    }

    /**
     * Sets titles on the reviews overview page
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
     * Renders the reviews overview page
     */
    protected function outputListReview()
    {
        $this->output('content/review/list');
    }

    /**
     * Displays the review add/edit form
     * @param integer|null $review_id
     */
    public function editReview($review_id = null)
    {
        $this->setReview($review_id);

        $this->setTitleEditReview();
        $this->setBreadcrumbEditReview();

        $this->setData('review', $this->data_review);

        $this->submitReview();
        $this->setDataEditReview();

        $this->outputEditReview();
    }

    /**
     * Returns a review
     * @param integer $review_id
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

        $this->data_review = $review;
        return $review;
    }

    /**
     * Saves a review
     * @return null|void
     */
    protected function submitReview()
    {
        if ($this->isPosted('delete')) {
            $this->deleteReview();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateReview()) {
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
    protected function validateReview()
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
     * Updates a review with submitted data
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
     * Adds a new review using an array of submitted values
     */
    protected function addReview()
    {
        $this->controlAccess('review_add');

        $this->review->add($this->getSubmitted());

        $message = $this->text('Review has been added');
        $this->redirect('admin/content/review', $message, 'success');
    }

    /**
     * Sets an additional data to be passed to templates
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
     * Sets titles on the review edit page
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
     * Sets breadcrumbs on the review edit page
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
     * Renders the review edit page
     */
    protected function outputEditReview()
    {
        $this->output('content/review/edit');
    }

}
