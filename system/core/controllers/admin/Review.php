<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Review as R;
use core\models\Product;

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
     * @param R $review
     * @param Product $product
     */
    public function __construct(R $review, Product $product)
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

        $selected = $this->request->post('selected', array());
        $action = $this->request->post('action');
        $value = $this->request->post('value');

        if ($action) {
            $this->action($selected, $action, $value);
        }

        $query = $this->getFilterQuery();
        $total = $this->setPager($this->getTotalReviews($query), $query);

        $this->data['reviews'] = $this->getReviews($total, $query);

        $filters = array('product_id', 'user_id', 'status', 'created', 'text');
        $this->setFilter($filters, $query);

        $this->prepareFilter($query);

        $this->setTitleReviews();
        $this->setBreadcrumbReviews();
        $this->outputReviews();
    }

    /**
     * Returns a number of total reviews for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalReviews($query)
    {
        return $this->review->getList(array('count' => true) + $query);
    }

    /**
     * Returns an array of reviews
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getReviews($limit, $query)
    {
        $reviews = $this->review->getList(array('limit' => $limit) + $query);

        foreach ($reviews as &$review) {
            $review['product'] = '';
            $product = $this->product->get($review['product_id']);
            if ($product) {
                $review['product'] = $product['title'];
            }
        }

        return $reviews;
    }

    /**
     * Applies an action to the selected reviews
     * @param array $selected
     * @param string $action
     * @param string $value
     * @return boolean
     */
    protected function action($selected, $action, $value)
    {
        $updated = $deleted = 0;
        foreach ($selected as $review_id) {

            if ($action == 'status' && $this->access('review_edit')) {
                $updated += (int) $this->review->update($review_id, array('status' => $value));
            }

            if ($action == 'delete' && $this->access('review_delete')) {
                $deleted += (int) $this->review->delete($review_id);
            }
        }


        if ($updated) {
            $this->session->setMessage($this->text('Updated %num reviews', array('%num' => $updated)), 'success');
            return true;
        }

        if ($deleted) {
            $this->session->setMessage($this->text('Deleted %num reviews', array('%num' => $deleted)), 'success');
            return true;
        }

        return false;
    }

    /**
     * Modifies filter values
     * @param array $query
     */
    protected function prepareFilter($query)
    {
        $this->data['product'] = '';
        $this->data['user'] = '';

        if (isset($query['product_id'])) {
            $product = $this->product->get($query['product_id']);
            $this->data['product'] = $product ? $product['title'] : '';
        }

        if (isset($query['user_id'])) {
            $user = $this->user->get($query['user_id']);
            $this->data['user'] = $user ? "{$user['name']} ({$user['email']})" : '';
        }
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
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
    }

    /**
     * Renders the reviews overview page
     */
    protected function outputReviews()
    {
        $this->output('content/review/list');
    }

    /**
     * Displays the review add/edit form
     * @param integer|null $review_id
     */
    public function edit($review_id = null)
    {
        $review = $this->get($review_id);

        $this->data['review'] = $review;

        if ($this->request->post('delete')) {
            $this->delete($review);
        }

        if ($this->request->post('save')) {
            $this->submit($review);
        }

        if (isset($review['user_id'])) {
            $user = $this->user->get($review['user_id']);
            $this->data['review']['email'] = $user ? $user['email'] : '';
        }

        if (isset($review['product_id'])) {
            $product = $this->product->get($review['product_id']);
            $this->data['review']['product'] = $product ? $product['title'] : '';
        }

        $this->setTitleEdit($review);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
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

        if ($review) {
            return $review;
        }

        $this->outputError(404);
    }

    /**
     * Deletes a review
     * @param array $review
     * @return null
     */
    protected function delete($review)
    {
        if (empty($review['review_id'])) {
            return;
        }

        $this->controlAccess('review_delete');
        $this->review->delete($review['review_id']);
        $this->redirect('admin/content/review', $this->text('Review has been deleted'), 'success');
    }

    /**
     * Saves a review
     * @param array $review
     * @return null
     */
    protected function submit($review)
    {
        $this->submitted = $this->request->post('review');

        $this->validate();

        if ($this->formErrors()) {
            $this->data['review'] = $this->submitted;
            return;
        }

        if (isset($review['review_id'])) {
            $this->controlAccess('review_edit');
            $this->review->update($review['review_id'], $this->submitted);
            $this->redirect('admin/content/review', $this->text('Review has been updated'), 'success');
        }

        $this->controlAccess('review_add');
        $this->review->add($this->submitted);
        $this->redirect('admin/content/review', $this->text('Review has been added'), 'success');
    }

    /**
     * Validates a review
     */
    protected function validate()
    {
        $this->validateText();
        $this->validateCreated();
        $this->validateProduct();
        $this->validateUser();
    }

    /**
     * Validates review text
     * @return boolean
     */
    protected function validateText()
    {

        if (empty($this->submitted['text'])) {
            $this->data['form_errors']['text'] = $this->text('Required field');
            return false;
        }

        $limit = (int) $this->config->get('review_length', 1000);

        if ($limit) {
            $this->submitted['text'] = $this->truncate($this->submitted['text'], $limit);
        }

        return true;
    }

    /**
     * Validates the review created date
     * @return boolean
     */
    protected function validateCreated()
    {
        if (empty($this->submitted['created'])) {
            $this->submitted['created'] = GC_TIME;
            return true;
        }

        $this->submitted['created'] = strtotime($this->submitted['created']);

        if (!$this->submitted['created']) {
            $this->data['form_errors']['created'] = $this->text('Only valid English textual datetime allowed');
            return false;
        }

        return true;
    }

    /**
     * Validates a product
     * @return boolean
     */
    protected function validateProduct()
    {
        if (isset($this->submitted['product_id']) && !$this->product->get($this->submitted['product_id'])) {
            $this->data['form_errors']['product'] = $this->text('Product does not exist');
            return false;
        }

        return true;
    }

    /**
     * Validates a user
     * @return boolean
     */
    protected function validateUser()
    {
        if (empty($this->submitted['email'])) {
            $this->data['form_errors']['email'] = $this->text('Required field');
            return false;
        }

        $user = $this->user->getByEmail($this->submitted['email']);

        if (isset($user['user_id'])) {
            $this->submitted['user_id'] = $user['user_id'];
            return true;
        }

        $this->data['form_errors']['email'] = $this->text('User does not exist');
        return false;
    }

    /**
     * Sets titles on the review edit page
     * @param array $review
     */
    protected function setTitleEdit($review)
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
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
        $this->setBreadcrumb(array('text' => $this->text('Reviews'), 'url' => $this->url('admin/content/review')));
    }

    /**
     * Renders the review edit page
     */
    protected function outputEdit()
    {
        $this->output('content/review/edit');
    }

}
