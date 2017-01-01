<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\Wishlist as WishlistModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate wishlist data
 */
class Wishlist extends BaseValidator
{
    /**
     * Wishlist model instance
     * @var \gplcart\core\models\Wishlist $wishlist
     */
    protected $wishlist;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
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
     * @param array $options
     * @return array|boolean
     */
    public function wishlist(array &$submitted, array $options)
    {
        $this->submitted = &$submitted;

        $this->validateWishlist($options);
        $this->validateProductWishlist($options);
        $this->validateUserCartId($options);
        $this->validateStoreId($options);

        return $this->getResult();
    }

    /**
     * Validates wishlist data to be updated
     * @param array $options
     * @return boolean|null
     */
    protected function validateWishlist(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->wishlist->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Wishlist'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a wishlist product ID
     * @param array $options
     * @return boolean|null
     */
    protected function validateProductWishlist(array $options)
    {
        $value = $this->getSubmitted('product_id', $options);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        $product = $this->product->get($value);

        if (empty($product['status'])) {
            $vars = array('@name' => $this->language->text('Product'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        return true;
    }

}
