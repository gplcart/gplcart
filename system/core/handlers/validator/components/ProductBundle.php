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
use gplcart\core\models\Product as ProductModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate a product bundle data
 */
class ProductBundle extends ComponentValidator
{

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
     * @param ProductModel $product
     */
    public function __construct(Config $config, LanguageModel $language, FileModel $file,
            UserModel $user, StoreModel $store, AliasModel $alias, RequestHelper $request,
            ProductModel $product)
    {
        parent::__construct($config, $language, $file, $user, $store, $alias, $request);

        $this->product = $product;
    }

    /**
     * Performs validation of submitted product bundle data
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function productBundle(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateProductProductBundle();
        $this->validateItemsProductBundle();

        return $this->getResult();
    }

    /**
     * Validates product that contains bundled items
     * @return boolean
     */
    protected function validateProductProductBundle()
    {
        $field = 'product_id';
        $label = $this->language->text('Product');

        $product_id = $this->getSubmitted($field);

        if (!ctype_digit($product_id)) {
            $this->setErrorInteger($field, $label);
            return false;
        }

        $product = $this->product->get($product_id);

        if (empty($product)) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        $this->setSubmitted('product', $product);
        return true;
    }

    /**
     * Validates bundled products
     * @return boolean|null
     */
    protected function validateItemsProductBundle()
    {
        $field = 'bundle';
        $label = $this->language->text('Bundle');

        $skus = $this->getSubmitted($field);
        $main_product = $this->getSubmitted('product');

        if (empty($skus)) {
            return null;
        }

        $processed = $loaded = array();

        foreach ($skus as $sku) {

            if (in_array($sku, $processed)) {
                $this->setErrorExists($field, $label);
                return false;
            }

            $processed[] = $sku;

            $product = $this->product->getBySku($sku, $main_product['store_id']);

            if (empty($product['status'])) {
                $this->setErrorUnavailable($field, $label);
                return false;
            }

            if ($main_product['product_id'] == $product['product_id']) {
                $this->setError($field, $this->language->text('Bundled products cannot be the same as the main product'));
                return false;
            }

            if ($main_product['store_id'] != $product['store_id']) {
                $this->setError($field, $this->language->text("Bundled products must belong to the main product's store"));
                return false;
            }

            $loaded[$sku] = $product;
        }

        $this->setSubmitted('products', $loaded);
        return true;
    }

}
