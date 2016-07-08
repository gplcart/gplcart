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
    public function __construct(ModelsProduct $product, ModelsReview $review, ModelsPrice $price, ModelsRating $rating, ModelsImage $image)
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
        $editable = (bool) $this->config->get('review_editable', 1);
        
        if (!$editable || empty($this->uid)) {
            $this->outputError(403);
        }

        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            $this->outputError(404);
        }

        $review = array();

        if (is_numeric($review_id)) {
            $review = $this->review->get($review_id);
            if (empty($review)) {
                $this->outputError(404);
            }

            if ($review['user_id'] != $this->uid) {
                $this->outputError(403);
            }

            $rating = $this->rating->getByUser($product_id, $this->uid);
            $review['rating'] = isset($rating['rating']) ? $rating['rating'] : 0;
        }

        $this->data['review'] = $review;

        if ($this->request->post('save')) {
            $this->controlSpam('review');

            $submitted = $this->request->post('review');
            $this->validate($submitted, $review);
            
            $errors = $this->formErrors(false);

            if (!empty($errors)) {
                $this->data['review'] = $submitted;
            } else {
                $submitted += array('product_id' => $product_id, 'user_id' => $this->uid);

                if (isset($submitted['rating'])) {
                    $this->rating->set($product_id, $submitted['user_id'], $submitted['rating']);
                }

                if (isset($review['review_id'])) {
                    $this->review->update($review['review_id'], $submitted);
                    $this->redirect("product/$product_id");
                }

                $this->review->add($submitted);
                $this->redirect("product/$product_id");
            }
        }

        $deletable = (bool) $this->config->get('review_deletable', 1);

        if ($this->request->post('delete') && isset($review['review_id']) && $deletable) {
            $this->review->delete($review['review_id']);
            $this->redirect("product/$product_id");
        }

        $this->data['product'] = $product;

        $this->data['max_length'] = $this->config->get('review_length', 1000);
        $this->data['deletable'] = $deletable;
        $this->data['rating'] = $this->render('common/rating/edit', array(
            'product' => $product,
            'unvote' => $this->config->get('rating_unvote', 1),
            'review' => $this->data['review']
        ));

        $this->data['image'] = array();

        if (!empty($product['images'])) {
            $image = reset($product['images']);
            $imagestyle = $this->store->config('image_style_product');
            $image['thumb'] = $this->image->url($imagestyle, $image['path']);
            $this->data['image'] = $image;
        }

        $this->data['price'] = $this->price->format($product['price'], $product['currency']);

        $this->setTitle($this->text('Review of %product', array('%product' => $product['title'])), false);
        $this->output('content/review/edit');
    }

    /**
     * Validates an array of submitted review data
     * @param array $submitted
     * @param array $review
     */
    protected function validate(&$submitted, $review)
    {
        if (empty($submitted['text'])) {
            $this->data['form_errors']['text'] = $this->text('Please write a review');
            return;
        }

        $submitted['status'] = $this->config->get('review_status', 1);
        $submitted['text'] = $this->truncate($submitted['text'], $this->config->get('review_length', 1000));

        if (!$submitted['status']) {
            $this->session->setMessage($this->text('Your review will be visible after approval'));
        }
    }
}
