<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

// Parent
use gplcart\core\Config;
use gplcart\core\models\File as FileModel,
    gplcart\core\models\User as UserModel,
    gplcart\core\models\Store as StoreModel,
    gplcart\core\models\Alias as AliasModel,
    gplcart\core\helpers\Request as RequestHelper,
    gplcart\core\models\Language as LanguageModel;
// New
use gplcart\core\models\Rating as RatingModel,
    gplcart\core\models\Product as ProductModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate product rating data
 */
class Rating extends ComponentValidator
{

    /**
     * Rating model instance
     * @var \gplcart\core\models\Rating $rating
     */
    protected $rating;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * @param Config $config
     * @param LanguageModel $language
     * @param FileModel $file
     * @param UserModel $user
     * @param StoreModel $store
     * @param AliasModel $alias
     * @param RequestHelper $request
     * @param RatingModel $rating
     * @param ProductModel $product
     */
    public function __construct(Config $config, LanguageModel $language, FileModel $file,
            UserModel $user, StoreModel $store, AliasModel $alias, RequestHelper $request,
            RatingModel $rating, ProductModel $product)
    {
        parent::__construct($config, $language, $file, $user, $store, $alias, $request);

        $this->rating = $rating;
        $this->product = $product;
    }

    /**
     * Performs full rating data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function rating(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateProductRating();
        $this->validateUserId();
        $this->validateValueRating();

        return $this->getResult();
    }

    /**
     * Validates a submitted product ID
     * @return boolean
     */
    protected function validateProductRating()
    {
        $field = 'product_id';
        $label = $this->language->text('Product');
        $value = $this->getSubmitted($field);

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $product = $this->product->get($value);

        if (empty($product['product_id'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        // We need the store id later
        $this->setSubmitted('store_id', $product['store_id']);
        return true;
    }

    /**
     * Validates a rating value
     * @return boolean
     */
    protected function validateValueRating()
    {
        $field = 'rating';
        $label = $this->language->text('Rating');
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if ((float) $value > 5) {
            $this->setErrorInvalid($field, $label);
            return false;
        }
        return true;
    }

}
