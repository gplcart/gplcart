<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Price as PriceModel;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\ProductBundle as ProductBundleModel;

/**
 * Handles incoming requests and outputs data related to product classes
 */
class ProductBundle extends Controller
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
    public function __construct(PriceModel $price, ProductModel $product, ProductBundleModel $product_bundle)
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
        $this->data_product = $this->product->get($product_id);

        if (empty($this->data_product)) {
            $this->outputHttpStatus(404);
        }

        $this->prepareProductBundle($this->data_product);
    }

    /**
     * Prepare an array of product data
     * @param array $product
     */
    protected function prepareProductBundle(array &$product)
    {
        $product['bundle'] = $this->product_bundle->getItems($product['product_id']);
    }

    /**
     * Sets related products
     */
    protected function setDataItemsProductBundle()
    {
        $product_ids = $this->getData('product.bundle', array());

        $products = array();

        if (!empty($product_ids)) {
            $products = (array) $this->product->getList(array('product_id' => $product_ids));
        }

        $options = array(
            'entity' => 'product',
            'entity_id' => $product_ids,
            'template_item' => 'backend|content/product/suggestion'
        );

        foreach ($products as &$product) {
            $this->setItemThumb($product, $this->image, $options);
            $this->setItemPriceFormatted($product, $this->price);
            $this->setItemRendered($product, array('item' => $product), $options);
        }

        $widget = array(
            'multiple' => true,
            'name' => 'product[bundle]',
            'products' => $products,
            'error' => $this->error('bundle'),
            'store_id' => $this->data_product['store_id'],
            'label' => $this->text('Bundled products'),
            'description' => $this->text('Select one or several related products to be offered for sale as one combined product')
        );

        $this->setData('product_picker', $this->getWidgetProductPicker($widget));
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

        if ($this->product_bundle->set($product_id, $products)) {
            $this->redirect('admin/content/product', $this->text('Product bundle has been updated'), 'success');
        }

        $this->redirect('', $this->text('Product bundle has not been updated'), 'warning');
    }

    /**
     * Sets title on the edit product bundle page
     */
    protected function setTitleEditProductBundle()
    {
        $text = $this->text('Edit bundle for %name', array('%name' => $this->data_product['title']));
        $this->setTitle($text);
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
