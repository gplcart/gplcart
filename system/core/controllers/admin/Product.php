<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Sku as ModelsSku;
use core\models\Price as ModelsPrice;
use core\models\Image as ModelsImage;
use core\models\Alias as ModelsAlias;
use core\models\Field as ModelsField;
use core\models\Product as ModelsProduct;
use core\models\Currency as ModelsCurrency;
use core\models\Category as ModelsCategory;
use core\models\ProductClass as ModelsProductClass;

/**
 * Handles incoming requests and outputs data related to products
 */
class Product extends Controller
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
     * Field model instance
     * @var \core\models\Field $field
     */
    protected $field;

    /**
     * Sku model instance
     * @var \core\models\Sku $sku
     */
    protected $sku;

    /**
     * Constructor
     * @param ModelsProduct $product
     * @param ModelsProductClass $product_class
     * @param ModelsCategory $category
     * @param ModelsPrice $price
     * @param ModelsCurrency $currency
     * @param ModelsImage $image
     * @param ModelsAlias $alias
     * @param ModelsField $field
     * @param ModelsSku $sku
     */
    public function __construct(ModelsProduct $product,
            ModelsProductClass $product_class, ModelsCategory $category,
            ModelsPrice $price, ModelsCurrency $currency, ModelsImage $image,
            ModelsAlias $alias, ModelsField $field, ModelsSku $sku)
    {
        parent::__construct();

        $this->sku = $sku;
        $this->alias = $alias;
        $this->image = $image;
        $this->field = $field;
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
        if ($this->isPosted('action')) {
            $this->actionProduct();
        }

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
            'store_id', 'currency');

        $this->setFilter($filters, $query);

        if ($this->isPosted('save')) {
            $this->submitProduct();
        }

        $this->setTitleListProduct();
        $this->setBreadcrumbListProduct();
        $this->outputListProduct();
    }

    /**
     * Displays the product edit form
     * @param integer|null $product_id
     */
    public function editProduct($product_id = null)
    {
        $this->outputCategoriesProduct();

        $product = $this->getProduct($product_id);
        $this->setData('product', $product);

        if (!empty($product)) {
            $related = $this->getRelatedProduct($product);
            $this->setData('related', $related);
        }

        if ($this->isPosted('delete')) {
            $this->deleteProduct($product);
        }

        if ($this->isPosted('save')) {
            $this->submitProduct($product);
        }

        $this->setDataEditProduct();

        $stores = $this->store->getNames();
        $currency = $this->currency->getDefault();
        $classes = $this->product_class->getList(array('status' => 1));

        $this->setData('stores', $stores);
        $this->setData('classes', $classes);
        $this->setData('default_currency', $currency);

        $this->setJsEditProduct($product);

        $this->setTitleEditProduct($product);
        $this->setBreadcrumbEditProduct();
        $this->outputEditProduct();
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
     * Outputs a JSON string containing brand and catalog categories
     */
    protected function outputCategoriesProduct()
    {
        $store_id = (int) $this->request->get('store_id');

        if (!empty($store_id) && $this->request->isAjax()) {

            $brand = $this->category->getOptionListByStore($store_id, 'brand');
            $catalog = $this->category->getOptionListByStore($store_id, 'catalog');

            $response = array(
                'brand' => reset($brand),
                'catalog' => reset($catalog)
            );

            $this->response->json($response);
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
        return $this->product->getList($query);
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
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));
    }

    /**
     * Renders product overview page templates
     */
    protected function outputListProduct()
    {
        $this->output('content/product/list');
    }

    /**
     * Applies an action to products
     */
    protected function actionProduct()
    {
        $value = (int) $this->request->post('value');
        $action = (string) $this->request->post('action');
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
     * Updates option combination
     * @param array $product
     * @return integer
     */
    protected function updateCombinationProduct(array $product)
    {
        if (empty($product['combination'])) {
            return 0;
        }

        $updated = 0;
        foreach ($product['combination'] as $combination_id => $combination) {
            $updated += (int) $this->product->updateCombination($combination_id, $combination);
        }

        return $updated;
    }

    /**
     * Sets titles on the product edit form
     * @param array $product
     */
    protected function setTitleEditProduct(array $product)
    {
        if (isset($product['product_id'])) {
            $title = $this->text('Edit %title', array('%title' => $product['title']));
        } else {
            $title = $this->text('Add product');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the product edit page
     */
    protected function setBreadcrumbEditProduct()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));

        $this->setBreadcrumb(array(
            'text' => $this->text('Products'),
            'url' => $this->url('admin/content/product')));
    }

    /**
     * Renders product edit page templates
     */
    protected function outputEditProduct()
    {
        $this->output('content/product/edit');
    }

    /**
     * Returns an array of related products
     * @param array $product
     * @return array
     */
    protected function getRelatedProduct(array $product)
    {
        $stores = $this->store->getList();
        $products = $this->product->getRelated($product['product_id'], true, array(
            'store_id' => $product['store_id']));

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

        if (!empty($product['images'])) {
            foreach ($product['images'] as &$image) {
                $image['translation'] = $this->image->getTranslations($image['file_id']);
            }
        }

        $product['alias'] = $this->alias->get('product_id', $product_id);
        $product['price'] = $this->price->decimal($product['price'], $product['currency']);

        $user = $this->user->get($product['user_id']);
        $product['author'] = $user['email'];

        return $product;
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
            $this->redirect('admin/content/product', $this->text('Product has been deleted'), 'success');
        }

        $this->redirect('admin/content/product', $this->text('Unable to delete this product. The most probable reason - it is used by one or more orders or modules'), 'danger');
    }

    /**
     * Renders the product field forms
     */
    protected function setDataEditProduct()
    {
        $output_field_form = false;

        $product_class_id = $this->getData('product.product_class_id', 0);
        $get_product_class_id = (int) $this->request->get('product_class_id');

        if (!empty($get_product_class_id)) {
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
    }

    /**
     * Saves a product
     * @param array $product
     * @return null
     */
    protected function submitProduct(array $product = array())
    {
        $this->setSubmitted('product', null, false);

        $this->validateProduct($product);

        if ($this->hasErrors('product')) {
            return;
        }

        if (isset($product['product_id'])) {
            $this->updateProduct($product);
        }

        $this->addProduct();
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

        $images = (array) $this->request->post('delete_image', array());

        if (!empty($images)) {
            foreach (array_values($images) as $file_id) {
                $this->image->delete($file_id);
            }
        }

        $message = $this->text('Product has been updated');
        $this->redirect('admin/content/product', $message, 'success');
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
     * Validates an array of submitted product data
     * @param array $product
     */
    protected function validateProduct(array $product = array())
    {
        $this->setSubmittedBool('status');

        $this->addValidator('price', array(
            'numeric' => array(),
            'length' => array('max' => 10)
        ));

        $this->addValidator('stock', array(
            'numeric' => array(),
            'length' => array('max' => 10)
        ));

        $this->addValidator('title', array(
            'length' => array('min' => 1, 'max' => 255)
        ));

        $this->addValidator('description', array(
            'length' => array('max' => 65535)
        ));

        $this->addValidator('meta_title', array(
            'length' => array('max' => 255)
        ));

        $this->addValidator('meta_description', array(
            'length' => array('max' => 255)
        ));

        $this->addValidator('translation', array(
            'translation' => array()
        ));

        $this->addValidator('width', array(
            'numeric' => array(),
            'length' => array('max' => 10)
        ));

        $this->addValidator('height', array(
            'numeric' => array(),
            'length' => array('max' => 10)
        ));

        $this->addValidator('length', array(
            'numeric' => array(),
            'length' => array('max' => 10)
        ));

        $this->addValidator('weight', array(
            'numeric' => array(),
            'length' => array('max' => 10)
        ));

        $this->addValidator('images', array(
            'images' => array()
        ));

        $alias = $this->getSubmitted('alias');

        if (empty($alias) && isset($product['product_id'])) {
            $submitted = $this->getSubmitted();
            $alias = $this->product->createAlias($submitted);
            $this->setSubmitted('alias', $alias);
        }

        $this->addValidator('alias', array(
            'length' => array('max' => 255),
            'regexp' => array('pattern' => '/^[A-Za-z0-9_.-]+$/'),
            'alias_unique' => array()
        ));

        $this->addValidator('store_id', array(
            'required' => array()
        ));

        $sku = $this->getSubmitted('sku');

        if (empty($sku) && isset($product['product_id'])) {
            $submitted = $this->getSubmitted();
            $sku = $this->product->createSku($submitted);
            $this->setSubmitted('sku', $sku);
        }

        $this->addValidator('sku', array(
            'length' => array('max' => 255),
            'product_sku_unique' => array('control_errors' => true) // Make sure that store ID is set
        ));

        $product_class_id = $this->getSubmitted('product_class_id');

        if (isset($product_class_id)) {

            $fields = $this->product_class->getFieldData($product_class_id);

            $this->addValidator('attribute', array(
                'product_attributes' => array('fields' => $fields)
            ));

            $this->addValidator('combination', array(
                'product_combinations' => array(
                    'fields' => $fields, 'control_errors' => true)
            ));
        }

        $errors = $this->setValidators($product);

        if (empty($errors)) {
            $result = $this->getValidatorResult('combination');
            if (isset($result['stock']) && isset($result['combination'])) {
                $this->setSubmitted('stock', $result['stock']);
                $this->setSubmitted('combination', $result['combination']);
            }
        }

        $related = $this->getSubmitted('related');

        if (empty($related)) {
            $this->setSubmitted('related', array()); // Need on update
        } else {
            // Remove duplicates
            $modified = array_flip($related);

            if (isset($product['product_id'])) {
                // Exclude the current product from related products
                unset($modified[$product['product_id']]);
            }

            $this->setSubmitted('related', array_flip($modified));
        }
    }

}
