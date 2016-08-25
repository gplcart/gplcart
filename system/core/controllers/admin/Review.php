<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Review as ModelsReview;
use core\models\Product as ModelsProduct;

/**
 * Handles incoming requests and outputs data related to user reviews
 */
class Review extends Controller
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
     * @param ModelsReview $review
     * @param ModelsProduct $product
     */
    public function __construct(ModelsReview $review, ModelsProduct $product)
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

        $filters = array('product_id', 'user_id',
            'status', 'created', 'text');

        $this->setFilter($filters, $query);
        $this->setDataListReview($query);

        $this->setTitleListReview();
        $this->setBreadcrumbListReview();
        $this->outputListReview();
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
        $this->setDataEditReview($review);

        $this->setTitleEditReview($review);
        $this->setBreadcrumbEditReview();
        $this->outputEditReview();
    }

    /**
     * Sets an additional data to be passed to templates
     * @param array $review
     */
    protected function setDataEditReview(array $review)
    {
        if (isset($review['user_id'])) {
            $user = $this->user->get($review['user_id']);
            $email = isset($user['email']) ? $user['email'] : '';
            $this->setData('review.email', $email);
        }

        if (isset($review['product_id'])) {
            $product = $this->product->get($review['product_id']);
            $title = isset($product['title']) ? $product['title'] : '';
            $this->setData('review.product', $title);
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
        return $this->review->getList($query);
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
        $reviews = $this->review->getList($query);

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
     * Applies an action to the selected reviews
     */
    protected function actionReview()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return;
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
     * Modifies template variables on the reviews page
     * @param array $query
     */
    protected function setDataListReview(array $query)
    {
        $email = $title = '';

        if (isset($query['product_id'])) {
            $product = $this->product->get($query['product_id']);
            $title = isset($product['title']) ? $product['title'] : '';
        }

        if (isset($query['user_id'])) {
            $user = $this->user->get($query['user_id']);
            $email = isset($user['email']) ? "{$user['name']} ({$user['email']})" : '';
        }

        $this->setData('user', $email);
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
        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the reviews overview page
     */
    protected function outputListReview()
    {
        $this->output('content/review/list');
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
     * Saves a review
     * @param array $review
     * @return null
     */
    protected function submitReview(array $review)
    {
        if ($this->isPosted('delete') && isset($review['review_id'])) {
            return $this->deleteReview($review);
        }

        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('review');
        $this->validateReview($review);

        if ($this->hasErrors('review')) {
            return;
        }

        if (isset($review['review_id'])) {
            return $this->updateReview($review);
        }

        $this->addReview();
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
     * Validates a submitted review
     * @param array $review
     */
    protected function validateReview(array $review)
    {
        $this->addValidator('text', array(
            'length' => array(
                'min' => $this->config('review_min_length', 10),
                'max' => $this->config('review_max_length', 1000)
        )));

        $this->addValidator('created', array(
            'required' => array(),
            'date' => array('required' => true)
        ));

        $this->addValidator('product_id', array(
            'required' => array(),
            'product_exists' => array('required' => true)
        ));

        $this->addValidator('email', array(
            'required' => array(),
            'user_email_exists' => array('required' => true)
        ));

        $this->setValidators($review);

        $user = $this->getValidatorResult('email');
        $timestamp = $this->getValidatorResult('created');

        $this->setSubmitted('created', $timestamp);
        $this->setSubmitted('user_id', $user['user_id']);
    }

    /**
     * Sets titles on the review edit page
     * @param array $review
     */
    protected function setTitleEditReview(array $review)
    {
        if (isset($review['review_id'])) {
            $title = $this->text('Edit rewiew');
        } else {
            $title = $this->text('Add review');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the review edit page
     */
    protected function setBreadcrumbEditReview()
    {
        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin'));

        $breadcrumbs[] = array(
            'text' => $this->text('Reviews'),
            'url' => $this->url('admin/content/review'));

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
