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
     * Pager limit
     * @var array
     */
    protected $data_limit;

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
        $this->setPagerListReview();

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
        list($selected, $action, $value) = $this->getPostedAction();

        $updated = $deleted = 0;
        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('review_edit')) {
                $updated += (int) $this->review->update($id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('review_delete')) {
                $deleted += (int) $this->review->delete($id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Updated %num item(s)', array('%num' => $updated));
            $this->setMessage($message, 'success');
        }

        if ($deleted > 1) {
            $message = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListReview()
    {
        $options = $this->query_filter;
        $options['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->review->getList($options)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of reviews
     * @return array
     */
    protected function getListReview()
    {
        $options = $this->query_filter;
        $options['limit'] = $this->data_limit;
        $reviews = (array) $this->review->getList($options);

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

        $product = null;
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
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
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
        } else if ($this->isPosted('save') && $this->validateEditReview()) {
            if (isset($this->data_review['review_id'])) {
                $this->updateReview();
            } else {
                $this->addReview();
            }
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
        $this->redirect('admin/content/review', $this->text('Review has been deleted'), 'success');
    }

    /**
     * Updates a review
     */
    protected function updateReview()
    {
        $this->controlAccess('review_edit');

        $this->review->update($this->data_review['review_id'], $this->getSubmitted());
        $this->redirect('admin/content/review', $this->text('Review has been updated'), 'success');
    }

    /**
     * Adds a new review
     */
    protected function addReview()
    {
        $this->controlAccess('review_add');

        $this->review->add($this->getSubmitted());
        $this->redirect('admin/content/review', $this->text('Review has been added'), 'success');
    }

    /**
     * Set a template data on the edit review page
     */
    protected function setDataEditReview()
    {
        $user_id = $this->getData('review.user_id');
        $product_id = $this->getData('review.product_id');

        if (!empty($user_id)) {
            $user = $this->user->get($user_id);
            $email = isset($user['email']) ? $user['email'] : '';
            $this->setData('review.email', $email);
        }

        if (!empty($product_id)) {
            $product = $this->product->get($product_id);
            $title = isset($product['title']) ? $product['title'] : '';
            $this->setData('review.product', $title);
        }
    }

    /**
     * Sets title on the edit review page
     */
    protected function setTitleEditReview()
    {
        if (isset($this->data_review['review_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->text('Review')));
        } else {
            $title = $this->text('Add review');
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
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
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
