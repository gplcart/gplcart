<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\Controller;
use core\models\Product;
use core\models\Price;
use core\models\Review as R;
use core\models\Rating;
use core\models\Image;

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
     * Constaructor
     * @param Product $product
     * @param R $review
     * @param Price $price
     * @param Rating $rating
     * @param Image $image
     */
    public function __construct(Product $product, R $review, Price $price, Rating $rating, Image $image)
    {
        parent::__construct();

        $this->product = $product;
        $this->review = $review;
        $this->price = $price;
        $this->rating = $rating;
        $this->image = $image;
    }

    /**
     * Displays the review edit page
     * @param integer $product_id
     * @param integer $review_id
     */
    public function edit($product_id, $review_id = null)
    {
        if (!$this->config->get('review_editable', 1) || !$this->uid) {
            $this->outputError(403);
        }

        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            $this->outputError(404);
        }

        $review = array();

        if (is_numeric($review_id)) {
            $review = $this->review->get($review_id);
            if (!$review) {
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

            if ($this->formErrors(false)) {
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

        $deletable = $this->config->get('review_deletable', 1);

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
