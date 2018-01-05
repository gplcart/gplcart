<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\Product as ProductModel,
    gplcart\core\models\Wishlist as WishlistModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate wishlist data
 */
class Wishlist extends ComponentValidator
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
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateWishlist();
        $this->validateProductWishlist();
        $this->validateUserCartId();
        $this->validateStoreId();

        return $this->getResult();
    }

    /**
     * Validates wishlist data to be updated
     * @return boolean|null
     */
    protected function validateWishlist()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->wishlist->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('Wishlist'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a wishlist product ID
     * @return boolean|null
     */
    protected function validateProductWishlist()
    {
        $field = 'product_id';

        if (isset($this->options['field']) && $this->options['field'] !== $field) {
            return null;
        }

        $value = $this->getSubmitted($field);
        $label = $this->translation->text('Product');

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $product = $this->product->get($value);

        if (empty($product['status'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

}
