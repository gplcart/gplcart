<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Alias as AliasModel;
use gplcart\core\models\Image as ImageModel;
use gplcart\core\models\Price as PriceModel;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\Category as CategoryModel;
use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\models\ProductClass as ProductClassModel;
use gplcart\core\models\CategoryGroup as CategoryGroupModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to products
 */
class Product extends BackendController
{

    use \gplcart\core\traits\BackendController;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Product class model instance
     * @var \gplcart\core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * Category model instance
     * @var \gplcart\core\models\Category $category
     */
    protected $category;

    /**
     * Category group model instance
     * @var \gplcart\core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Image model instance
     * @var \gplcart\core\models\Image $image
     */
    protected $image;

    /**
     * Url model instance
     * @var \gplcart\core\models\Alias $alias
     */
    protected $alias;

    /**
     * The current updating product
     * @var array
     */
    protected $data_product = array();

    /**
     * Constructor
     * @param ProductModel $product
     * @param ProductClassModel $product_class
     * @param CategoryModel $category
     * @param CategoryGroupModel $category_group
     * @param PriceModel $price
     * @param CurrencyModel $currency
     * @param ImageModel $image
     * @param AliasModel $alias
     */
    public function __construct(ProductModel $product,
            ProductClassModel $product_class, CategoryModel $category,
            CategoryGroupModel $category_group, PriceModel $price,
            CurrencyModel $currency, ImageModel $image, AliasModel $alias)
    {
        parent::__construct();

        $this->alias = $alias;
        $this->image = $image;
        $this->price = $price;
        $this->product = $product;
        $this->category = $category;
        $this->currency = $currency;
        $this->product_class = $product_class;
        $this->category_group = $category_group;
    }

    /**
     * Displays the product overview page
     */
    public function listProduct()
    {
        $this->actionProduct();

        $this->setTitleListProduct();
        $this->setBreadcrumbListProduct();

        $query = $this->getFilterQuery();

        $filters = array('title', 'sku_like', 'price', 'stock', 'status',
            'store_id', 'product_id', 'currency');

        $this->setFilter($filters, $query);

        $total = $this->getTotalProduct($query);
        $limit = $this->setPager($total, $query);

        $this->setData('stores', $this->store->getNames());
        $this->setData('products', $this->getListProduct($limit, $query));
        $this->setData('currencies', $this->currency->getList());

        $this->outputListProduct();
    }

    /**
     * Applies an action to products
     */
    protected function actionProduct()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        $deleted = $updated = 0;
        foreach ($selected as $id) {

            if ($action == 'status' && $this->access('product_edit')) {
                $updated += (int) $this->product->update($id, array('status' => $value));
            }

            if ($action == 'delete' && $this->access('product_delete')) {
                $deleted += (int) $this->product->delete($id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Updated %num products', array('%num' => $updated));
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num products', array('%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Return a number of total products for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalProduct(array $query)
    {
        $query['count'] = true;
        return (int) $this->product->getList($query);
    }

    /**
     * Returns an array of products
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListProduct(array $limit, array $query)
    {
        $query['limit'] = $limit;
        $products = (array) $this->product->getList($query);

        if (empty($products)) {
            return array();
        }

        $this->attachEntityUrlTrait($this->store, $products, 'product');

        foreach ($products as &$product) {
            $product['price'] = $this->price->decimal($product['price'], $product['currency']);
        }

        return $products;
    }

    /**
     * Sets titles on the product overview page
     */
    protected function setTitleListProduct()
    {
        $this->setTitle($this->text('Products'));
    }

    /**
     * Sets breadcrumbs on the product overview page
     */
    protected function setBreadcrumbListProduct()
    {
        $breadcrumb = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders product overview page templates
     */
    protected function outputListProduct()
    {
        $this->output('content/product/list');
    }

    /**
     * Displays the product edit form
     * @param integer|null $product_id
     */
    public function editProduct($product_id = null)
    {
        $this->outputCategoriesProduct();

        $this->setProduct($product_id);

        $this->setTitleEditProduct();
        $this->setBreadcrumbEditProduct();

        $this->setData('product', $this->data_product);
        $this->setData('stores', $this->store->getNames());
        $this->setData('related', $this->getRelatedProduct());
        $this->setData('classes', $this->getClassesProduct());
        $this->setData('default_currency', $this->currency->getDefault());
        $this->setData('subtract_default', $this->config->get('product_subtract', 0));

        $this->submitProduct();

        $this->setDataFieldsProduct();
        $this->setDataAuthorProduct();
        $this->setDataRelatedProduct();
        $this->setDataCategoriesProduct();
        $this->setDataImagesProduct();

        $this->setJsEditProduct();
        $this->outputEditProduct();
    }

    /**
     * Returns an array of enabled product classes
     * @return array
     */
    protected function getClassesProduct()
    {
        return $this->product_class->getList(array('status' => 1));
    }

    /**
     * Outputs a JSON string containing brand and catalog categories
     */
    protected function outputCategoriesProduct()
    {
        $store_id = (int) $this->request->get('store_id');

        if (!empty($store_id) && $this->request->isAjax()) {
            $response = $this->getListCategoryProduct($store_id);
            $this->response->json($response);
        }
    }

    /**
     * Get list of categories keyed by type
     * @param integer $store_id
     * @return array
     */
    protected function getListCategoryProduct($store_id)
    {
        $types = $this->category_group->getTypes();

        $categories = array();
        foreach (array_keys($types) as $type) {
            $data = $this->category->getOptionListByStore($store_id, $type);
            $categories[$type] = reset($data);
        }

        return $categories;
    }

    /**
     * Returns a product
     * @param integer $product_id
     * @return array
     */
    protected function setProduct($product_id)
    {
        if (!is_numeric($product_id)) {
            return array();
        }

        $product = $this->product->get($product_id);

        if (empty($product)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_product = $this->prepareProduct($product);
    }

    /**
     * Adds an additional data to the product array data
     * @param array $product
     * @return array
     */
    protected function prepareProduct(array $product)
    {
        $product['alias'] = $this->alias->get('product_id', $product['product_id']);
        $product['price'] = $this->price->decimal($product['price'], $product['currency']);

        return $this->prepareCombinationsProduct($product);
    }

    /**
     * Adds an additional data to product combinations
     * @param array $product
     * @return array
     */
    protected function prepareCombinationsProduct(array $product)
    {
        if (empty($product['combination'])) {
            return $product;
        }

        foreach ($product['combination'] as &$combination) {
            $combination['path'] = $combination['thumb'] = '';
            if (!empty($product['images'][$combination['file_id']])) {
                $combination['path'] = $product['images'][$combination['file_id']]['path'];
                $this->attachThumbTrait($this->image, $this->config, $combination);
            }

            $combination['price'] = $this->price->decimal($combination['price'], $product['currency']);
        }

        return $product;
    }

    /**
     * Returns an array of related products
     * @return array
     */
    protected function getRelatedProduct()
    {
        if (empty($this->data_product['product_id'])) {
            return array();
        }

        $options = array('store_id' => $this->data_product['store_id']);
        $products = $this->product->getRelated($this->data_product['product_id'], true, $options);

        $this->attachEntityUrlTrait($this->store, $products, 'product');
        return $products;
    }

    /**
     * Saves a product
     * @return null
     */
    protected function submitProduct()
    {
        if ($this->isPosted('delete')) {
            $this->deleteProduct();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateProduct()) {
            return null;
        }

        $this->deleteImagesTrait($this->request, $this->image, $this->data_product, 'product');

        if (isset($this->data_product['product_id'])) {
            $this->updateProduct();
        } else {
            $this->addProduct();
        }
    }

    /**
     * Deletes a product
     */
    protected function deleteProduct()
    {
        $this->controlAccess('product_delete');

        $deleted = $this->product->delete($this->data_product['product_id']);

        if ($deleted) {
            $message = $this->text('Product has been deleted');
            $this->redirect('admin/content/product', $message, 'success');
        }

        $message = $this->text('Unable to delete this product');
        $this->redirect('admin/content/product', $message, 'danger');
    }

    /**
     * Validates an array of submitted product data
     * @return bool
     */
    protected function validateProduct()
    {
        $this->setSubmitted('product', null, 'raw');

        $this->setSubmittedBool('status');
        $this->setSubmittedBool('subtract');
        $this->setSubmitted('form', true);
        $this->setSubmitted('update', $this->data_product);

        if (isset($this->data_product['product_id'])) {
            $this->setSubmitted('user_id', $this->data_product['user_id']);
            $this->setSubmitted('created', $this->data_product['created']);
            $this->setSubmitted('modified', $this->data_product['modified']);
            $this->setSubmitted('currency', $this->data_product['currency']);
            $this->setSubmitted('product_id', $this->data_product['product_id']);
        } else {
            $this->setSubmitted('user_id', $this->uid);
            $this->setSubmitted('currency', $this->currency->getDefault());
        }

        $this->validate('product');

        return !$this->hasErrors('product');
    }

    /**
     * Updates a product with submitted values
     */
    protected function updateProduct()
    {
        $this->controlAccess('product_edit');

        $submitted = $this->getSubmitted();
        $this->product->update($this->data_product['product_id'], $submitted);

        $message = $this->text('Product has been updated');
        $this->redirect('admin/content/product', $message, 'success');
    }

    /**
     * Adds a new product using an array of submitted values
     */
    protected function addProduct()
    {
        $this->controlAccess('product_add');
        $this->product->add($this->getSubmitted());

        $message = $this->text('Product has been added');
        $this->redirect('admin/content/product', $message, 'success');
    }

    /**
     * Sets product author data
     */
    protected function setDataAuthorProduct()
    {
        $user_id = $this->getData('product.user_id');

        if (isset($user_id)) {
            $user = $this->user->get($user_id);
            $this->setData('product.author', $user['email']);
        }
    }

    /**
     * Sets products categories data
     */
    protected function setDataCategoriesProduct()
    {
        $store_id = $this->getData('store_id');
        $categories = $this->getListCategoryProduct($store_id);
        $this->setData('categories', $categories);
    }

    /**
     * Sets attributes/options product data
     */
    protected function setDataFieldsProduct()
    {
        $output_field_form = false;
        $get_product_class_id = $this->request->get('product_class_id');
        $product_class_id = $this->getData('product.product_class_id', 0);

        if (isset($get_product_class_id)) {
            $output_field_form = true;
            $product_class_id = $get_product_class_id;
        }

        $data = array(
            'product' => $this->getData('product'),
            'fields' => $this->product_class->getFieldData($product_class_id)
        );

        $options = $this->render('content/product/options', $data);
        $attributes = $this->render('content/product/attributes', $data);

        $this->setData('option_form', $options);
        $this->setData('attribute_form', $attributes);

        if ($output_field_form) {
            $this->response->html($attributes . $options);
        }
    }

    /**
     * Sets related products
     */
    protected function setDataRelatedProduct()
    {
        $related = $this->getData('product.related');

        if (!empty($related)) {
            $products = (array) $this->product->getList(array('product_id' => $related));
            $this->attachEntityUrlTrait($this->store, $products, 'product');
            $this->setData('related', $products);
        }
    }

    /**
     * Sets product attached data
     * @return null
     */
    protected function setDataImagesProduct()
    {
        $images = $this->getData('product.images', array());
        $this->attachThumbsTrait($this->image, $this->config, $images);
        $this->setImagesTrait($this, $images, 'product');
    }

    /**
     * Sets Java scripts on the edit product page
     */
    protected function setJsEditProduct()
    {
        $this->setJsSettings('product', $this->data_product);
    }

    /**
     * Sets titles on the product edit form
     */
    protected function setTitleEditProduct()
    {
        $title = $this->text('Add product');

        if (isset($this->data_product['product_id'])) {
            $vars = array('%title' => $this->data_product['title']);
            $title = $this->text('Edit %title', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the product edit page
     */
    protected function setBreadcrumbEditProduct()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Products'),
            'url' => $this->url('admin/content/product')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders product edit page templates
     */
    protected function outputEditProduct()
    {
        $this->output('content/product/edit');
    }

}
