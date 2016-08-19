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
     * Processed during validation SKUs
     * @var array
     */
    protected static $processed_skus = array();

    /**
     * Option combination amounts
     * @var array
     */
    protected static $stock_amount = array();

    /**
     * Validated option combinations
     * @var array
     */
    protected static $processed_combinations = array();

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
    public function products()
    {
        if ($this->isPosted('action')) {
            $this->action();
        }

        $query = $this->getFilterQuery();
        $total = $this->getTotalProducts($query);
        $limit = $this->setPager($total, $query);

        $products = $this->getProducts($limit, $query);
        $stores = $this->store->getNames();
        $currencies = $this->currency->getList();

        $this->setData('stores', $stores);
        $this->setData('products', $products);
        $this->setData('currencies', $currencies);

        $filters = array('title', 'sku', 'price', 'stock', 'status',
            'store_id', 'currency', 'front');

        $this->setFilter($filters, $query);

        if ($this->isPosted('save')) {
            $this->submit();
        }

        $this->setTitleProducts();
        $this->setBreadcrumbProducts();
        $this->outputProducts();
    }

    /**
     * Displays the product edit form
     * @param integer|null $product_id
     */
    public function edit($product_id = null)
    {
        $this->outputCategories();

        $product = $this->get($product_id);
        $this->setData('product', $product);

        if (!empty($product)) {
            $related = $this->getRelated($product);
            $this->setData('related', $related);
        }

        if ($this->isPosted('delete')) {
            $this->delete($product);
        }

        if ($this->isPosted('save')) {
            $this->submit($product);
        }

        $this->setDataFieldForm();
        $this->setDataImages();

        $stores = $this->store->getNames();
        $currency = $this->currency->getDefault();
        $classes = $this->product_class->getList(array('status' => 1));

        $this->setData('stores', $stores);
        $this->setData('classes', $classes);
        $this->setData('default_currency', $currency);

        $this->setJsSettings('product', $product);

        $this->setTitleEdit($product);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Outputs a JSON string containing brand and catalog categories
     */
    protected function outputCategories()
    {
        $store_id = (int) $this->request->get('store_id');

        if (!empty($store_id) && $this->request->isAjax()) {

            $catalog = $this->category->getOptionListByStore($store_id, 'catalog');
            $brand = $this->category->getOptionListByStore($store_id, 'brand');

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
    protected function getTotalProducts(array $query)
    {
        $query['count'] = true;
        return $this->product->getList($query);
    }

    /**
     * Sets titles on the product overview page
     */
    protected function setTitleProducts()
    {
        $this->setTitle($this->text('Products'));
    }

    /**
     * Sets breadcrumbs on the product overview page
     */
    protected function setBreadcrumbProducts()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));
    }

    /**
     * Renders product overview page templates
     */
    protected function outputProducts()
    {
        $this->output('content/product/list');
    }

    /**
     * Applies an action to products
     */
    protected function action()
    {
        $value = (int) $this->request->post('value');
        $action = (string) $this->request->post('action');
        $selected = (array) $this->request->post('selected', array());

        if ($action == 'get_options') {
            $product_id = (int) $this->request->post('product_id');
            $product = $this->product->get($product_id);

            $data = array();
            $data['product'] = $product;
            $combinations = $this->product->getCombinations($product_id);
            foreach ($combinations as &$combination) {
                $combination['price'] = $this->price->decimal($combination['price'], $product['currency']);
            }

            $data['combinations'] = $combinations;
            $data['fields'] = $this->product_class->getFieldData($product['product_class_id']);
            $html = $this->render('content/product/combinations', $data);
            $this->response->html($html);
        }

        $deleted = $updated = 0;
        foreach ($selected as $id) {

            if ($action == 'status' && $this->access('product_edit')) {
                $updated += (int) $this->product->update($id, array('status' => $value));
            }

            if ($action == 'front' && $this->access('product_edit')) {
                $updated += (int) $this->product->update($id, array('front' => $value));
            }

            if ($action == 'delete' && $this->access('product_delete')) {
                $deleted += (int) $this->product->delete($id);
            }
        }

        if ($updated > 0) {
            $this->session->setMessage($this->text('Updated %num products', array('%num' => $updated)), 'success');
            $this->response->json(array('success' => 1));
        }

        if ($deleted > 0) {
            $this->session->setMessage($this->text('Deleted %num products', array('%num' => $deleted)), 'success');
            $this->response->json(array('success' => 1));
        }

        $this->response->json(array('success' => 1));
    }

    /**
     * Returns an array of products
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getProducts(array $limit, array $query)
    {

        $query['limit'] = $limit;

        $stores = $this->store->getList();
        $products = $this->product->getList($query);

        foreach ($products as &$product) {
            $product['view_url'] = '';
            if (isset($stores[$product['store_id']])) {
                $store = $stores[$product['store_id']];
                $product['view_url'] = rtrim("{$this->scheme}{$store['domain']}/{$store['basepath']}", "/") . "/product/{$product['product_id']}";
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
    protected function updateCombination(array $product)
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
    protected function setTitleEdit(array $product)
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
    protected function setBreadcrumbEdit()
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
    protected function outputEdit()
    {
        $this->output('content/product/edit');
    }

    /**
     * Returns an array of related products
     * @param array $product
     * @return array
     */
    protected function getRelated(array $product)
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
    protected function get($product_id)
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
    protected function delete(array $product)
    {
        $this->controlAccess('product_delete');

        if ($this->product->delete($product['product_id'])) {
            $this->redirect('admin/content/product', $this->text('Product has been deleted'), 'success');
        }

        $this->redirect('admin/content/product', $this->text('Unable to delete this product. The most probable reason - it is used by one or more orders or modules'), 'danger');
    }

    /**
     * Renders the product field forms
     */
    protected function setDataFieldForm()
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
    }

    /**
     * Adds rendered product images form
     */
    protected function setDataImages()
    {
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
    protected function submit(array $product = array())
    {

        $this->setSubmitted('product', null, false);

        $this->validate($product);

        if ($this->hasErrors('product')) {

            if ($this->request->isAjax()) {
                $this->response->json(array('error' => $this->getError()));
            }

            $related = $this->getSubmitted('related');

            if (!empty($related)) {
                $products = $this->getProducts(null, array('product_id' => $related));
                $this->setData('related', $products);
            }
            return;
        }

        $submitted = $this->getSubmitted();

        if ($this->request->isAjax()) {

            if (!$this->access('product_edit')) {
                $this->response->json(array('error' => $this->text('You are not permitted to perform this operation')));
            }

            $product_id = $this->getSubmitted('product_id');
            $update_combinations = $this->getSubmitted('update_combinations');

            if ($update_combinations) {
                $this->updateCombination($this->submitted);
                $this->response->json(array('success' => $this->text('Product has been updated')));
            }

            if (empty($product_id)) {
                $this->response->json(array('error' => $this->text('You are not permitted to perform this operation')));
            }

            $this->product->update($product_id, $submitted);
            $this->response->json(array('success' => $this->text('Product has been updated')));
        }

        if (isset($product['product_id'])) {
            $this->deleteImages();
            $this->product->update($product['product_id'], $submitted);
            $this->redirect('admin/content/product', $this->text('Product has been updated'), 'success');
        }

        $this->controlAccess('product_add');

        $submitted += array(
            'user_id' => $this->uid,
            'currency' => $this->currency->getDefault()
        );

        $this->product->add($submitted);
        $this->redirect('admin/content/product', $this->text('Product has been added'), 'success');
    }

    /**
     * Deletes submitted images
     * @return int
     */
    protected function deleteImages()
    {
        $images = (array) $this->request->post('delete_image', array());

        if (empty($images)) {
            return 0;
        }

        $this->controlAccess('product_edit');

        $deleted = 0;
        foreach (array_values($images) as $file_id) {
            $deleted += (int) $this->image->delete($file_id);
        }

        return $deleted;
    }

    /**
     * Validates an array of submitted product data
     * @param array $product
     */
    protected function validate(array $product = array())
    {
        $this->setSubmittedBool('status');

        $this->addValidator('price', array(
            'numeric' => array(),
            'length' => array('max' => 10)));

        $this->addValidator('stock', array(
            'numeric' => array(),
            'length' => array('max' => 10)));

        $this->addValidator('title', array(
            'length' => array('min' => 1, 'max' => 255)));

        $this->addValidator('meta_title', array(
            'length' => array('max' => 255)));

        $this->addValidator('meta_description', array(
            'length' => array('max' => 255)));

        $this->addValidator('translation', array(
            'translation' => array()));

        $this->addValidator('width', array(
            'numeric' => array(),
            'length' => array('max' => 10)));

        $this->addValidator('height', array(
            'numeric' => array(),
            'length' => array('max' => 10)));

        $this->addValidator('length', array(
            'numeric' => array(),
            'length' => array('max' => 10)));

        $this->addValidator('weight', array(
            'numeric' => array(),
            'length' => array('max' => 10)));

        $this->addValidator('images', array(
            'images' => array()));

        $alias = $this->getSubmitted('alias');

        if (isset($alias) && $alias === '' && isset($product['product_id'])) {
            $generated = $this->product->createAlias($this->getSubmitted());
            $this->setSubmitted('alias', $generated);
        }

        $this->addValidator('alias', array(
            'regexp' => array('pattern' => '/^[A-Za-z0-9_.-]+$/'),
            'alias' => array()));


        $this->setValidators($product);

        $this->validateSku($product);
        $this->validateAttributes();
        $this->validateCombinations($product);
        $this->validateRelated($product);
    }

    /**
     * Validates product SKU
     * @param array $product
     * @return null
     */
    protected function validateSku(array $product)
    {
        $sku = $this->getSubmitted('sku');

        if (!isset($sku)) {
            return;
        }

        $default_store_id = $this->store->getDefault();
        $store_id = $this->getSubmitted('store_id', $default_store_id);
        $product_id = isset($product['product_id']) ? $product['product_id'] : null;

        if ($sku !== '') {

            $existing = $this->sku->get($sku, $store_id, $product_id);

            if (!empty($existing)) {
                $this->setError('sku', $this->text('SKU must be unique per store'));
                return;
            }

            if (mb_strlen($sku) > 255) {
                $message = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $this->setError('sku', $message);
                return;
            }

            return;
        }

        if (!empty($product_id)) {
            $sku = $this->product->createSku($product);
        }

        if ($sku !== '') {
            static::$processed_skus[$sku] = true;
        }

        $this->setSubmitted('sku', $sku);
    }

    /**
     * Validates attributes
     * @return null
     */
    protected function validateAttributes()
    {
        $product_class_id = $this->getSubmitted('product_class_id');

        if (!isset($product_class_id)) {
            return;
        }

        $product_fields = $this->product_class->getFieldData($product_class_id);
        $this->setSubmitted('product_fields', $product_fields);

        if (empty($product_fields['attribute'])) {
            return;
        }

        foreach ($product_fields['attribute'] as $field_id => $field) {
            $value = $this->getSubmitted("field.attribute.$field_id");
            if (!empty($field['required']) && empty($value)) {
                $this->setError("attribute.$field_id", $this->text('Required field'));
            }
        }
    }

    /**
     * Validates option combinations
     * @return boolean
     */
    protected function validateCombinations($product)
    {
        $combinations = $this->getSubmitted('combination');

        if (empty($combinations)) {
            return true;
        }

        foreach ($combinations as $index => &$combination) {
            if (empty($combination['fields'])) {
                unset($combinations[$index]);
                continue;
            }

            if (!$this->validateCombinationOptions($index, $combination)) {
                continue;
            }

            $combination_id = $this->product->getCombinationId($combination['fields']);
            $repeating_combinations = isset(static::$processed_combinations[$combination_id]);

            $this->validateCombinationSku($index, $combination, $product);
            $this->validateCombinationPrice($index, $combination);
            $this->validateCombinationStock($index, $combination);

            foreach ($combination['fields'] as $field_value_id) {
                if (!isset(static::$stock_amount[$field_value_id])) {
                    static::$stock_amount[$field_value_id] = (int) $combination['stock'];
                }
            }

            static::$processed_combinations[$combination_id] = true;
        }

        $stock = array_sum(static::$stock_amount);

        $this->setSubmitted('stock', $stock);
        $this->getSubmitted('combination', $combinations);

        if (empty($repeating_combinations)) {
            return true;
        }

        $this->setError('combination.repeating_options', true);
        $this->setMessage($this->text('Option combinations must be unique'), 'danger');
        return false;
    }

    /**
     * Validates combination fields
     * @param string $index
     * @param array $combination
     * @return boolean
     */
    protected function validateCombinationOptions($index, $combination)
    {
        $options = $this->getSubmitted('product_fields.option');

        if (!isset($options)) {
            return true;
        }

        $has_errors = false;
        foreach ($options as $field_id => $field) {
            if (!empty($field['required']) && !isset($combination['fields'][$field_id])) {
                $this->setError("combination.$index.fields.$field_id", $this->text('Required field'));
                $has_errors = true;
            }
        }

        return !$has_errors;
    }

    /**
     * Validates combination SKU
     * @param string $index
     * @param array $combination
     * @return null
     */
    protected function validateCombinationSku($index, &$combination, $product)
    {
        if (!isset($combination['sku'])) {
            return;
        }

        $store_id = $this->getSubmitted('store_id');
        $existing_product_id = isset($product['product_id']) ? $product['product_id'] : null;
        $product_id = $this->getSubmitted('product_id', $existing_product_id);

        if (!empty($combination['sku'])) {

            if (mb_strlen($combination['sku']) > 255) {
                $error = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $this->setError("combination.$index.sku", $error);
                return;
            }

            if (isset(static::$processed_skus[$combination['sku']])) {
                $error = $this->text('SKU must be unique per store');
                $this->setError("combination.$index.sku", $error);
                return;
            }

            if ($this->sku->get($combination['sku'], $store_id, $product_id)) {
                $error = $this->text('SKU must be unique per store');
                $this->setError("combination.$index.sku", $error);
                return;
            }

            static::$processed_skus[$combination['sku']] = true;
            return;
        }

        if (!$this->isError('sku') && !empty($product_id)) {
            $sku = $this->getSubmitted('sku');
            $combination['sku'] = $this->sku->generate("$sku-$index", false, array('store_id' => $store_id));
        }
    }

    /**
     * Validates combination stock price
     * @param string $index
     * @param array $combination
     * @return null
     */
    protected function validateCombinationPrice($index, array &$combination)
    {
        if (empty($combination['price']) && !$this->isError('price')) {
            $combination['price'] = $this->getSubmitted('price');
        }

        if (is_numeric($combination['price']) && strlen($combination['price']) <= 10) {
            return;
        }

        $message = $this->text('Only numeric values allowed');
        $message .= $this->text('Content must not exceed %s characters', array('%s' => 10));

        $this->setError("combination.$index.price", $message);
    }

    /**
     * Validates combination stock level
     * @param string $index
     * @param array $combination
     * @return null
     */
    protected function validateCombinationStock($index, array &$combination)
    {
        if (empty($combination['stock'])) {
            return;
        }

        if (is_numeric($combination['stock']) && strlen($combination['stock']) <= 10) {
            return;
        }

        $message = $this->text('Only numeric values allowed');
        $message .= $this->text('Content must not exceed %s characters', array('%s' => 10));

        $this->setError("combination.$index.stock", $message);
    }

    /**
     * Validates related products
     * @param array $product
     * @return null
     */
    protected function validateRelated(array $product)
    {
        $related = $this->getSubmitted('related');

        if (empty($related)) {
            $this->setSubmitted('related', array()); // Need on update
            return;
        }

        // Remove duplicates
        $modified = array_flip($related);

        if (isset($product['product_id'])) {
            // Exclude the current product from related products
            unset($modified[$product['product_id']]);
        }

        $this->setSubmitted('related', array_flip($modified));
    }

}
