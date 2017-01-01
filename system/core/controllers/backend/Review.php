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

        $query = $this->getFilterQuery();
        $total = $this->getTotalReview($query);
        $limit = $this->setPager($total, $query);
        $reviews = $this->getListReview($limit, $query);

        $this->setData('reviews', $reviews);

        $filters = array('product_id', 'email',
            'status', 'created', 'text', 'review_id');

        $this->setFilter($filters, $query);
        $this->setDataListReview($query);

        $this->setTitleListReview();
        $this->setBreadcrumbListReview();
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

        return null;
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
        $review = $this->getReview($review_id);
        $this->setData('review', $review);

        $this->submitReview($review);
        $this->setDataEditReview();

        $this->setTitleEditReview($review);
        $this->setBreadcrumbEditReview();
        $this->outputEditReview();
    }

    /**
     * Returns a review
     * @param integer $review_id
     * @return array
     */
    protected function getReview($review_id)
    {
        if (!is_numeric($review_id)) {
            return array();
        }

        $review = $this->review->get($review_id);

        if (empty($review)) {
            $this->outputError(404);
        }

        return $review;
    }

    /**
     * Saves a review
     * @param array $review
     * @return null|void
     */
    protected function submitReview(array $review)
    {
        if ($this->isPosted('delete') && isset($review['review_id'])) {
            return $this->deleteReview($review);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('review');
        $this->validateReview($review);

        if ($this->hasErrors('review')) {
            return null;
        }

        if (isset($review['review_id'])) {
            return $this->updateReview($review);
        }

        return $this->addReview();
    }

    /**
     * Deletes a review
     * @param array $review
     * @return null
     */
    protected function deleteReview(array $review)
    {
        $this->controlAccess('review_delete');
        $this->review->delete($review['review_id']);

        $message = $this->text('Review has been deleted');
        $this->redirect('admin/content/review', $message, 'success');
    }

    /**
     * Validates a submitted review
     * @param array $review
     */
    protected function validateReview(array $review)
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $review);
        $this->validate('review');
    }

    /**
     * Updates a review with submitted data
     * @param array $review
     */
    protected function updateReview(array $review)
    {
        $this->controlAccess('review_edit');

        $values = $this->getSubmitted();
        $this->review->update($review['review_id'], $values);

        $message = $this->text('Review has been updated');
        $this->redirect('admin/content/review', $message, 'success');
    }

    /**
     * Adds a new review using an array of submitted values
     */
    protected function addReview()
    {
        $this->controlAccess('review_add');

        $values = $this->getSubmitted();
        $this->review->add($values);

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
     * @param array $review
     */
    protected function setTitleEditReview(array $review)
    {
        $title = $this->text('Add review');

        if (isset($review['review_id'])) {
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
