<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\Controller;
use core\models\Price as ModelsPrice;
use core\models\Image as ModelsImage;
use core\models\Review as ModelsReview;
use core\models\Rating as ModelsRating;
use core\models\Product as ModelsProduct;

/**
 * Handles incoming requests and outputs data related to reviews
 */
class Review extends Controller
{

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Review model instance
     * @var \core\models\Review $review
     */
    protected $review;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Rating model instance
     * @var \core\model\Rating $rating
     */
    protected $rating;

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Constructor
     * @param ModelsProduct $product
     * @param ModelsReview $review
     * @param ModelsPrice $price
     * @param ModelsRating $rating
     * @param ModelsImage $image
     */
    public function __construct(ModelsProduct $product, ModelsReview $review,
            ModelsPrice $price, ModelsRating $rating, ModelsImage $image)
    {
        parent::__construct();

        $this->price = $price;
        $this->image = $image;
        $this->rating = $rating;
        $this->review = $review;
        $this->product = $product;
    }

    /**
     * Displays the review edit page
     * @param integer $product_id
     * @param integer|null $review_id
     */
    public function edit($product_id, $review_id = null)
    {
        $this->controlAccessEdit();
        
        $product = $this->getProduct($product_id);
        $review = $this->get($review_id, $product_id);

        if ($this->request->post('save')) {
            $this->submit($review, $product);
        }

        $deletable = (bool) $this->config->get('review_deletable', 1);
        
        if ($this->request->post('delete') && isset($review['review_id']) && $deletable) {
            $this->delete($review, $product);
        }

        $this->data['review'] = $review;
        $this->data['product'] = $product;
        $this->data['deletable'] = $deletable;
        $this->data['max_length'] = $this->config->get('review_max_length', 1000);
        $this->data['price'] = $this->price->format($product['price'], $product['currency']);

        $this->setEditForm($review, $product);
        $this->setProductImage($product);

        $this->setTitleEdit($product);
        $this->outputEdit();
    }

    /**
     * Renders and outputs review edit page
     */
    protected function outputEdit()
    {
        $this->output('review/edit');
    }

    /**
     * Sets titles on the review edit page
     * @param array $product
     */
    protected function setTitleEdit(array $product)
    {
        $this->setTitle($this->text('Review of %product', array('%product' => $product['title'])), false);
    }

    /**
     * Sets product image
     * @param array $product
     */
    protected function setProductImage(array $product)
    {
        $this->data['image'] = array();

        if (!empty($product['images'])) {
            $image = reset($product['images']);
            $imagestyle = $this->getSettings('image_style_product', 5);
            $image['thumb'] = $this->image->url($imagestyle, $image['path']);
            $this->data['image'] = $image;
        }
    }

    /**
     * Sets review edit form
     * @param array $review
     * @param array $product
     */
    protected function setEditForm($review, $product)
    {
        $this->data['rating'] = $this->render('common/rating/edit', array(
            'review' => $review,
            'product' => $product,
            'unvote' => (bool) $this->config->get('rating_unvote', 1)
        ));
    }

    /**
     * Saves a submitted review
     * @param array $review
     * @param array $product
     * @return null
     */
    protected function submit(array $review, array $product)
    {
        $this->controlSpam('review');

        $this->submitted = $this->request->post('review');
        $this->validate($review);

        $errors = $this->getError();

        if (!empty($errors)) {
            $this->data['review'] = $this->submitted;
            return;
        }

        $this->submitted += array('product_id' => $product['product_id'], 'user_id' => $this->uid);

        if (isset($this->submitted['rating'])) {
            $this->rating->set($product['product_id'], $this->submitted['user_id'], $this->submitted['rating']);
        }

        if (isset($review['review_id'])) {
            $this->review->update($review['review_id'], $this->submitted);
            $this->redirect("product/{$product['product_id']}");
        }

        $this->review->add($this->submitted);
        $this->redirect("product/{$product['product_id']}");
    }

    /**
     * Deletes a review
     * @param array $review
     * @param array $product
     */
    protected function delete(array $review, array $product)
    {
        $this->review->delete($review['review_id']);
        $this->redirect("product/{$product['product_id']}");
    }
    
    /**
     * Validates an array of submitted review data
     * @param array $review
     * @return null
     */
    protected function validate(array $review)
    {
        if (empty($this->submitted['text'])) {
            $this->errors['text'] = $this->text('Please write a review');
            return;
        }

        $status = (bool) $this->config->get('review_status', 1);
        $length = (int) $this->config->get('review_max_length', 1000);

        $this->submitted['status'] = $status;
        $this->submitted['text'] = $this->truncate($this->submitted['text'], $length);

        if (empty($this->submitted['status'])) {
            $this->session->setMessage($this->text('Your review will be visible after approval'));
        }
    }
    
    /**
     * Returns a review
     * @param mixed $review_id
     * @param integer $product_id
     * @return array
     */
    protected function get($review_id, $product_id)
    {
        if (!is_numeric($review_id)) {
            return array();
        }

        $review = $this->review->get($review_id);

        if (empty($review)) {
            $this->outputError(404);
        }

        if ($review['user_id'] != $this->uid) {
            $this->outputError(403);
        }

        $rating = $this->rating->getByUser($product_id, $this->uid);
        $review['rating'] = isset($rating['rating']) ? $rating['rating'] : 0;
        return $rating;
    }

    /**
     * Loads a product from the database
     * @param integer $product_id
     * @return array
     */
    protected function getProduct($product_id)
    {
        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            $this->outputError(404);
        }

        return $product;
    }

    /**
     * Controls access to review
     */
    protected function controlAccessEdit()
    {
        $editable = $this->config->get('review_editable', 1);

        if (empty($editable) || empty($this->uid)) {
            $this->outputError(403);
        }
    }

}
