<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Product as ProductModel,
    gplcart\core\models\ProductBundle as ProductBundleModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to product classes
 */
class ProductBundle extends BackendController
{

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Product bundle model instance
     * @var \gplcart\core\models\ProductBundle $product_bundle
     */
    protected $product_bundle;

    /**
     * Product data
     * @var array
     */
    protected $data_product;

    /**
     * Product bundle data
     * @var array
     */
    protected $data_product_bundle;

    /**
     * @param PriceModel $price
     * @param ProductModel $product
     * @param ProductBundleModel $product_bundle
     */
    public function __construct(PriceModel $price, ProductModel $product,
            ProductBundleModel $product_bundle)
    {
        parent::__construct();

        $this->price = $price;
        $this->product = $product;
        $this->product_bundle = $product_bundle;
    }

    /**
     * Displays the edit product bundle page
     * @param integer $product_id
     */
    public function editProductBundle($product_id)
    {
        $this->setProductProductBundle($product_id);
        $this->setTitleEditProductBundle();
        $this->setBreadcrumbEditProductBundle();

        $this->setData('product', $this->data_product);

        $this->submitEditProductBundle();
        $this->setDataItemsProductBundle();
        $this->outputEditProductBundle();
    }

    /**
     * Sets the product data
     * @param integer $product_id
     */
    protected function setProductProductBundle($product_id)
    {
        $product = $this->product->get($product_id);

        if (empty($product)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_product = $this->prepareProductBundle($product);
    }

    /**
     * Prepare an array of product data
     * @param array $product
     * @return array
     */
    protected function prepareProductBundle(array $product)
    {
        $product['bundle'] = $this->product_bundle->getBundledProducts($product['product_id']);
        return $product;
    }

    /**
     * Sets related products
     */
    protected function setDataItemsProductBundle()
    {
        $product_ids = $this->getData('product.bundle', array());

        $products = array();
        foreach ($product_ids as $product_id) {
            $product = $this->product->get($product_id);
            if (!empty($product)) {
                $this->setItemProductSuggestion($product, $this->image, $this->price);
                $products[] = $product;
            }
        }

        $options = array(
            'multiple' => true,
            'name' => 'bundle',
            'products' => $products,
            'store_id' => $this->data_product['store_id']
        );

        $this->setData('product_picker', $this->getWidgetProductPicker($this, $options));
    }

    /**
     * Handles a submitted product bundle
     */
    protected function submitEditProductBundle()
    {
        if ($this->isPosted('save') && $this->validateEditProductBundle()) {
            $this->saveProductBundle();
        }
    }

    /**
     * Validates a product bundle data
     * @return bool
     */
    protected function validateEditProductBundle()
    {
        $this->setSubmitted('product');
        $this->setSubmitted('product_id', $this->data_product['product_id']);

        $this->validateComponent('product_bundle');

        return !$this->hasErrors();
    }

    /**
     * Saves a product bundle
     */
    protected function saveProductBundle()
    {
        $this->controlAccess('product_bundle_edit');

        $product_id = $this->getSubmitted('product_id');
        $products = $this->getSubmitted('products', array());

        $this->product_bundle->set($product_id, $products);
        $this->redirect('admin/content/product', $this->text('Product bundle has been updated'), 'success');
    }

    /**
     * Sets title on the edit product bundle page
     */
    protected function setTitleEditProductBundle()
    {
        $vars = array('%name' => $this->data_product['title']);
        $this->setTitle($this->text('Edit bundle for %name', $vars));
    }

    /**
     * Sets breadcrumbs on the edit product bundle page
     */
    protected function setBreadcrumbEditProductBundle()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Products'),
            'url' => $this->url('admin/content/product')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the edit product bundle page
     */
    protected function outputEditProductBundle()
    {
        $this->output('content/product/bundle/edit');
    }

}
