<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Product as ProductModel;
use core\models\Wishlist as WishlistModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate wishlist data
 */
class Wishlist extends BaseValidator
{

    /**
     * Wishlist model instance
     * @var \core\models\Wishlist $wishlist
     */
    protected $wishlist;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Constructor
     * @param WishlistModel $wishlist
     * @param ProductModel $product
     */
    public function __construct(WishlistModel $wishlist, ProductModel $product)
    {
        parent::__construct();

        $this->product = $product;
        $this->wishlist = $wishlist;
    }

    /**
     * Performs full wishlist data validation
     * @param array $submitted
     */
    public function wishlist(array &$submitted)
    {
        $this->validateWishlist($submitted);
        $this->validateProductWishlist($submitted);
        $this->validateUserCartId($submitted);
        $this->validateStoreId($submitted);

        return $this->getResult();
    }

    /**
     * Validates wishlist data to be updated
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateWishlist(array &$submitted)
    {
        if (empty($submitted['update']) || !is_numeric($submitted['update'])) {
            return null;
        }

        $data = $this->wishlist->get($submitted['update']);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Wishlist'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $submitted['update'] = $data;
        return true;
    }

    /**
     * Validates a wishlist product ID
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateProductWishlist(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['product_id'])) {
            return null;
        }

        if (empty($submitted['product_id'])) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('product_id', $error);
            return false;
        }

        if (!is_numeric($submitted['product_id'])) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('product_id', $error);
            return false;
        }

        $product = $this->product->get($submitted['product_id']);

        if (empty($product['status'])) {
            $vars = array('@name' => $this->language->text('Product'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('product_id', $error);
            return false;
        }

        return true;
    }

}
