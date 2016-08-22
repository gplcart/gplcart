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
    public function reviews()
    {
        if ($this->isPosted('action')) {
            $this->action();
        }

        $query = $this->getFilterQuery();
        $total = $this->getTotalReviews($query);
        $limit = $this->setPager($total, $query);
        $reviews = $this->getReviews($limit, $query);

        $this->setData('reviews', $reviews);

        $filters = array('product_id', 'user_id',
            'status', 'created', 'text');

        $this->setFilter($filters, $query);
        $this->setDataReviewsFilter($query);

        $this->setTitleReviews();
        $this->setBreadcrumbReviews();
        $this->outputReviews();
    }

    /**
     * Displays the review add/edit form
     * @param integer|null $review_id
     */
    public function edit($review_id = null)
    {
        $review = $this->get($review_id);

        $this->setData('review', $review);

        if ($this->isPosted('delete')) {
            $this->delete($review);
        }

        if ($this->isPosted('save')) {
            $this->submit($review);
        }

        $this->setDataReview($review);

        $this->setTitleEdit($review);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Sets an additional data to be passed to templates
     * @param array $review
     */
    protected function setDataReview(array $review)
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
    protected function getTotalReviews(array $query)
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
    protected function getReviews(array $limit, array $query)
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
    protected function action()
    {
        $value = (int) $this->request->post('value');
        $action = (string) $this->request->post('action');
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
            $this->setMessage($this->text('Updated %num reviews', array(
                        '%num' => $updated)), 'success', true);
        }

        if ($deleted > 1) {
            $this->setMessage($this->text('Deleted %num reviews', array(
                        '%num' => $deleted)), 'success', true);
        }
    }

    /**
     * Modifies filter values
     * @param array $query
     */
    protected function setDataReviewsFilter(array $query)
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
    protected function setTitleReviews()
    {
        $this->setTitle($this->text('Reviews'));
    }

    /**
     * Sets breadcrumbs on the reviews overview page
     */
    protected function setBreadcrumbReviews()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));
    }

    /**
     * Renders the reviews overview page
     */
    protected function outputReviews()
    {
        $this->output('content/review/list');
    }

    /**
     * Returns a review
     * @param integer $review_id
     * @return array
     */
    protected function get($review_id)
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
    protected function delete(array $review)
    {
        $this->controlAccess('review_delete');
        $this->review->delete($review['review_id']);

        $this->redirect('admin/content/review', $this->text('Review has been deleted'), 'success');
    }

    /**
     * Saves a review
     * @param array $review
     * @return null
     */
    protected function submit(array $review)
    {
        $this->setSubmitted('review');

        $this->validate($review);

        if ($this->hasErrors('review')) {
            return;
        }

        if (isset($review['review_id'])) {
            $this->controlAccess('review_edit');
            $this->review->update($review['review_id'], $this->getSubmitted());
            $this->redirect('admin/content/review', $this->text('Review has been updated'), 'success');
        }

        $this->controlAccess('review_add');
        $this->review->add($this->getSubmitted());
        $this->redirect('admin/content/review', $this->text('Review has been added'), 'success');
    }

    /**
     * Validates a review
     * @param array $review
     */
    protected function validate(array $review)
    {
        $this->addValidator('text', array(
            'length' => array(
                'required' => true,
                'min' => $this->config('review_min_length', 10),
                'max' => $this->config('review_max_length', 1000)
        )));

        $this->addValidator('created', array(
            'date' => array(
                'required' => true
        )));

        $this->addValidator('product_id', array(
            'product_exists' => array(
                'required' => true
        )));

        $this->addValidator('email', array(
            'user_email_exists' => array(
                'required' => true
        )));

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
    protected function setTitleEdit(array $review)
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
    protected function setBreadcrumbEdit()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));

        $this->setBreadcrumb(array(
            'text' => $this->text('Reviews'),
            'url' => $this->url('admin/content/review')));
    }

    /**
     * Renders the review edit page
     */
    protected function outputEdit()
    {
        $this->output('content/review/edit');
    }

}
