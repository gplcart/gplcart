<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\models\Alias as ModelsAlias;
use core\models\Category as ModelsCategory;
use core\models\Currency as ModelsCurrency;
use core\models\Image as ModelsImage;
use core\models\Price as ModelsPrice;
use core\models\Product as ModelsProduct;
use core\models\ProductClass as ModelsProductClass;
use core\controllers\admin\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to products
 */
class Product extends BackendController
{

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Product class model instance
     * @var \core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * Category model instance
     * @var \core\models\Category $category
     */
    protected $category;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Url model instance
     * @var \core\models\Alias $alias
     */
    protected $alias;

    /**
     * Constructor
     * @param ModelsProduct $product
     * @param ModelsProductClass $product_class
     * @param ModelsCategory $category
     * @param ModelsPrice $price
     * @param ModelsCurrency $currency
     * @param ModelsImage $image
     * @param ModelsAlias $alias
     */
    public function __construct(ModelsProduct $product,
            ModelsProductClass $product_class, ModelsCategory $category,
            ModelsPrice $price, ModelsCurrency $currency, ModelsImage $image,
            ModelsAlias $alias)
    {
        parent::__construct();

        $this->alias = $alias;
        $this->image = $image;
        $this->price = $price;
        $this->product = $product;
        $this->category = $category;
        $this->currency = $currency;
        $this->product_class = $product_class;
    }

    /**
     * Displays the product overview page
     */
    public function listProduct()
    {
        $this->actionProduct();

        $query = $this->getFilterQuery();
        $total = $this->getTotalProduct($query);
        $limit = $this->setPager($total, $query);

        $products = $this->getListProduct($limit, $query);
        $stores = $this->store->getNames();
        $currencies = $this->currency->getList();

        $this->setData('stores', $stores);
        $this->setData('products', $products);
        $this->setData('currencies', $currencies);

        $filters = array('title', 'sku', 'price', 'stock', 'status',
            'store_id', 'product_id', 'currency');

        $this->setFilter($filters, $query);
        $this->setTitleListProduct();
        $this->setBreadcrumbListProduct();
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

        return null;
    }

    /**
     * Return a number of total products for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalProduct(array $query)
    {
        $query['count'] = true;
        return $this->product->getList($query);
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

        $stores = $this->store->getList();
        $products = $this->product->getList($query);

        if (empty($products)) {
            return array();
        }

        foreach ($products as &$product) {
            $product['view_url'] = '';
            if (isset($stores[$product['store_id']])) {
                $store = $stores[$product['store_id']];
                $product['view_url'] = rtrim("{$this->scheme}{$store['domain']}/{$store['basepath']}", "/")
                        . "/product/{$product['product_id']}";
            }

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
        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumbs($breadcrumbs);
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
        $product = $this->getProduct($product_id);

        $stores = $this->store->getNames();
        $currency = $this->currency->getDefault();
        $related = $this->getRelatedProduct($product);
        $classes = $this->product_class->getList(array('status' => 1));

        $this->setData('stores', $stores);
        $this->setData('product', $product);
        $this->setData('related', $related);
        $this->setData('classes', $classes);
        $this->setData('default_currency', $currency);

        $this->submitProduct($product);

        $this->setDataEditProduct();
        $this->setJsEditProduct($product);
        $this->setTitleEditProduct($product);
        $this->setBreadcrumbEditProduct();
        $this->outputEditProduct();
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
        $categories = array();
        foreach (array('brand', 'catalog') as $type) {
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
    protected function getProduct($product_id)
    {
        if (!is_numeric($product_id)) {
            return array();
        }

        $product = $this->product->get($product_id);

        if (empty($product)) {
            $this->outputError(404);
        }

        if (!empty($product['combination'])) {

            $preset = $this->config('admin_image_preset', 2);

            foreach ($product['combination'] as &$combination) {

                $combination['path'] = '';
                $combination['thumb'] = '';

                if (!empty($product['images'][$combination['file_id']])) {
                    $combination['path'] = $product['images'][$combination['file_id']]['path'];
                    $combination['thumb'] = $this->image->url($preset, $combination['path']);
                }

                $combination['price'] = $this->price->decimal($combination['price'], $product['currency']);
            }
        }

        $product['alias'] = $this->alias->get('product_id', $product_id);
        $product['price'] = $this->price->decimal($product['price'], $product['currency']);

        return $product;
    }

    /**
     * Returns an array of related products
     * @param array $product
     * @return array
     */
    protected function getRelatedProduct(array $product)
    {
        if (empty($product['product_id'])) {
            return array();
        }

        $stores = $this->store->getList();
        $products = $this->product->getRelated($product['product_id'], true, array(
            'store_id' => $product['store_id']
        ));

        foreach ($products as &$product) {
            $product['view_url'] = '';
            if (isset($stores[$product['store_id']])) {
                $store = $stores[$product['store_id']];
                $product['view_url'] = rtrim("{$this->scheme}{$store['domain']}/{$store['basepath']}", "/")
                        . "/product/{$product['product_id']}";
            }
        }

        return $products;
    }

    /**
     * Saves a product
     * @param array $product
     * @return null|void
     */
    protected function submitProduct(array $product)
    {
        if ($this->isPosted('delete')) {
            return $this->deleteProduct($product);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('product', null, 'raw');

        $this->validateProduct($product);

        if ($this->hasErrors('product')) {
            return null;
        }

        if (isset($product['product_id'])) {
            return $this->updateProduct($product);
        }

        return $this->addProduct();
    }

    /**
     * Deletes a product
     * @param array $product
     * @return null
     */
    protected function deleteProduct(array $product)
    {
        $this->controlAccess('product_delete');

        $deleted = $this->product->delete($product['product_id']);

        if ($deleted) {
            $message = $this->text('Product has been deleted');
            $this->redirect('admin/content/product', $message, 'success');
        }

        $message = $this->text('Unable to delete this product');
        $this->redirect('admin/content/product', $message, 'danger');
    }

    /**
     * Validates an array of submitted product data
     * @param array $product
     */
    protected function validateProduct(array $product)
    {
        $this->setSubmittedBool('status');
        $this->setSubmittedBool('subtract');
        $this->setSubmitted('update', $product);

        if (isset($product['product_id'])) {
            $this->setSubmitted('user_id', $product['user_id']);
            $this->setSubmitted('created', $product['created']);
            $this->setSubmitted('modified', $product['modified']);
            $this->setSubmitted('product_id', $product['product_id']);
            $this->setSubmitted('currency', $product['currency']);
        }

        $this->validate('product');
    }

    /**
     * Updates a product with submitted values
     * @param array $product
     */
    protected function updateProduct(array $product)
    {
        $this->controlAccess('product_edit');

        $submitted = $this->getSubmitted();
        $this->product->update($product['product_id'], $submitted);

        $this->deleteImagesProduct();

        $message = $this->text('Product has been updated');
        $this->redirect('admin/content/product', $message, 'success');
    }

    /**
     * Deletes product images
     */
    protected function deleteImagesProduct()
    {
        $images = (array) $this->request->post('delete_image', array());

        if (empty($images)) {
            return null;
        }

        foreach (array_values($images) as $file_id) {
            $this->image->delete($file_id);
        }

        return null;
    }

    /**
     * Adds a new product using an array of submitted values
     */
    protected function addProduct()
    {
        $this->controlAccess('product_add');

        $submitted = $this->getSubmitted();

        $submitted += array(
            'user_id' => $this->uid,
            'currency' => $this->currency->getDefault()
        );

        $this->product->add($submitted);

        $message = $this->text('Product has been added');
        $this->redirect('admin/content/product', $message, 'success');
    }

    /**
     * Renders the product field forms
     */
    protected function setDataEditProduct()
    {
        $user_id = $this->getData('product.user_id');

        if (isset($user_id)) {
            $user = $this->user->get($user_id);
            $this->setData('product.author', $user['email']);
        }

        $output_field_form = false;
        $product_class_id = $this->getData('product.product_class_id', 0);
        $get_product_class_id = $this->request->get('product_class_id');

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

        $related = $this->getData('product.related');

        if (!empty($related)) {
            $products = $this->product->getList(array('product_id' => $related));
            $this->setData('related', $products);
        }

        $images = $this->getData('product.images');

        if (!empty($images)) {

            $preset = $this->config('admin_image_preset', 2);

            foreach ($images as &$image) {
                $image['thumb'] = $this->image->url($preset, $image['path']);
                $image['uploaded'] = filemtime(GC_FILE_DIR . "/{$image['path']}");
            }

            $data = array(
                'images' => $images,
                'name_prefix' => 'product',
                'languages' => $this->languages
            );

            $attached = $this->render('common/image/attache', $data);
            $this->setData('attached_images', $attached);
        }

        $store_id = $this->getData('store_id');
        $categories = $this->getListCategoryProduct($store_id);

        $this->setData('categories', $categories);
    }

    /**
     * Sets Java scripts on the edit product page
     * @param array $product
     */
    protected function setJsEditProduct(array $product)
    {
        $this->setJsSettings('product', $product);
    }

    /**
     * Sets titles on the product edit form
     * @param array $product
     */
    protected function setTitleEditProduct(array $product)
    {
        $title = $this->text('Add product');

        if (isset($product['product_id'])) {
            $title = $this->text('Edit %title', array('%title' => $product['title']));
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
