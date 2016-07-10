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
        $selected = $this->request->post('selected', array());
        $value = $this->request->post('value');
        $action = $this->request->post('action');

        if (!empty($action)) {
            $this->action($selected, $action, $value);
            $this->response->json(array('success' => 1));
        }

        $query = $this->getFilterQuery();
        $total = $this->setPager($this->getTotalProducts($query), $query);

        $this->data['products'] = $this->getProducts($total, $query);
        $this->data['stores'] = $this->store->getNames();
        $this->data['currencies'] = $this->currency->getList();

        $filters = array('title', 'sku', 'price', 'stock', 'status', 'store_id', 'currency', 'front');
        $this->setFilter($filters, $query);

        if ($this->request->post('save')) {
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
        if ($this->request->isAjax()) {
            $store_id = $this->request->get('store_id', null);
            if (isset($store_id)) {
                $this->response->json($this->getFields($store_id));
            }
        }

        $this->data['product'] = $product = $this->get($product_id);

        if (!empty($product)) {
            $this->data['related'] = $this->getRelated($product);
        }

        if ($this->request->post('delete')) {
            $this->delete($product);
        }

        if ($this->request->post('save')) {
            $this->submit($product);
        }

        $this->setFieldForms();
        $this->setImages();

        $this->data['stores'] = $this->store->getNames();
        $this->data['default_currency'] = $this->currency->getDefault();
        $this->data['classes'] = $this->product_class->getList(array('status' => 1));

        $this->setJsSettings('product', $product);

        $this->setTitleEdit($product);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Return a number of total products for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalProducts(array $query)
    {
        return $this->product->getList(array('count' => true) + $query);
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
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
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
     * @param array $selected
     * @param string $action
     * @param string $value
     * @return boolean
     */
    protected function action(array $selected, $action, $value)
    {
        if ($action == 'get_options') {
            $product_id = $this->request->post('product_id');
            $product = $this->product->get($product_id);

            $data = array();
            $data['product'] = $product;
            $combinations = $this->product->getCombinations($product_id);
            foreach ($combinations as &$combination) {
                $combination['price'] = $this->price->decimal($combination['price'], $product['currency']);
            }

            $data['combinations'] = $combinations;
            $data['fields'] = $this->product_class->getFieldData($product['product_class_id']);
            $this->response->html($this->render('content/product/combinations', $data));
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
            return true;
        }

        if ($deleted > 0) {
            $this->session->setMessage($this->text('Deleted %num products', array('%num' => $deleted)), 'success');
            return true;
        }

        return false;
    }

    /**
     * Returns an array of products
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getProducts(array $limit, array $query)
    {
        $stores = $this->store->getList();
        $products = $this->product->getList(array('limit' => $limit) + $query);

        foreach ($products as &$product) {
            $product['view_url'] = '';
            if (isset($stores[$product['store_id']])) {
                $store = $stores[$product['store_id']];
                $product['view_url'] = rtrim("{$store['scheme']}{$store['domain']}/{$store['basepath']}", "/") . "/product/{$product['product_id']}";
            }

            $product['price'] = $this->price->decimal($product['price'], $product['currency']);
        }

        return $products;
    }

    /**
     * Updates option combination
     * @param array $product
     * @return boolean|integer
     */
    protected function updateCombination(array $product)
    {
        if (empty($product['combination'])) {
            return false;
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
        $title = $this->text('Add product');

        if (isset($product['product_id'])) {
            $title = $this->text('Edit product %title', array('%title' => $product['title']));
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the product edit page
     */
    protected function setBreadcrumbEdit()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
        $this->setBreadcrumb(array('text' => $this->text('Products'), 'url' => $this->url('admin/content/product')));
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
                $product['view_url'] = rtrim("{$store['scheme']}{$store['domain']}/{$store['basepath']}", "/") . "/product/{$product['product_id']}";
            }
        }

        return $products;
    }

    /**
     * Returns an array of fields for a given store
     * @param integer $store_id
     * @return array
     */
    protected function getFields($store_id)
    {
        $catalog = $this->category->getOptionListByStore($store_id, 'catalog');
        $brand = $this->category->getOptionListByStore($store_id, 'brand');
        return array('catalog' => reset($catalog), 'brand' => reset($brand));
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
            foreach ($product['combination'] as &$combination) {
                $combination['path'] = $combination['thumb'] = '';
                if (!empty($product['images'][$combination['file_id']])) {
                    $combination['path'] = $product['images'][$combination['file_id']]['path'];
                    $combination['thumb'] = $this->image->url($this->config->get('admin_image_preset', 2), $combination['path']);
                }
                $combination['price'] = $this->price->decimal($combination['price'], $product['currency']);
            }
        }

        if (!empty($product['images'])) {
            foreach ($product['images'] as &$image) {
                $image['translation'] = $this->image->getTranslations($image['file_id']);
            }
        }

        if ($product) {
            $product['alias'] = $this->alias->get('product_id', $product_id);
            $product['price'] = $this->price->decimal($product['price'], $product['currency']);

            $user = $this->user->get($product['user_id']);
            $product['author'] = $user['email'];
        }

        return $product;
    }

    /**
     * Deletes a product
     * @param array $product
     * @return null
     */
    protected function delete(array $product)
    {
        if (empty($product['product_id'])) {
            return;
        }

        $this->controlAccess('product_delete');
        if ($this->product->delete($product['product_id'])) {
            $this->redirect('admin/content/product', $this->text('Product has been deleted'), 'success');
        }

        $this->redirect('admin/content/product', $this->text('Unable to delete this product. The most probable reason - it is used by one or more orders or modules'), 'danger');
    }

    /**
     * Renders the product field forms
     */
    protected function setFieldForms()
    {
        $product_class_id = 0;
        if (isset($this->data['product']['product_class_id'])) {
            $product_class_id = $this->data['product']['product_class_id'];
        }

        $output_field_form = false;
        if ($this->request->get('product_class_id', null) !== null) {
            $product_class_id = $this->request->get('product_class_id');
            $output_field_form = true;
        }

        $data = array(
            'product' => $this->data['product'],
            'fields' => $this->product_class->getFieldData($product_class_id),
            'form_errors' => isset($this->data['form_errors']) ? $this->data['form_errors'] : array()
        );

        $this->data['attribute_form'] = $this->render('content/product/attributes', $data);
        $this->data['option_form'] = $this->render('content/product/options', $data);

        if ($output_field_form) {
            $this->response->html($this->data['attribute_form'] . $this->data['option_form']);
        }
    }

    /**
     * Adds product images
     * @return null
     */
    protected function setImages()
    {
        if (empty($this->data['product']['images'])) {
            return;
        }

        foreach ($this->data['product']['images'] as &$image) {
            $image['thumb'] = $this->image->url($this->config->get('admin_image_preset', 2), $image['path']);
            $image['uploaded'] = filemtime(GC_FILE_DIR . '/' . $image['path']);
        }

        $this->data['attached_images'] = $this->render('common/image/attache', array(
            'name_prefix' => 'product',
            'languages' => $this->languages,
            'images' => $this->data['product']['images'])
        );
    }

    /**
     * Saves a product
     * @param array $product
     * @return null
     */
    protected function submit(array $product = array())
    {
        $this->submitted = $this->request->post('product', array(), false);

        $this->validate($product);

        $errors = $this->formErrors();

        if (!empty($errors)) {
            if ($this->request->isAjax()) {
                $this->response->json(array('error' => $this->data['form_errors']));
            }

            $this->data['product'] = $this->submitted + $product;

            if (!empty($this->submitted['related'])) {
                $this->data['related'] = $this->getProducts(null, array('product_id' => $this->submitted['related']));
            }
            return;
        }

        if ($this->request->isAjax()) {
            if (!$this->access('product_edit')) {
                $this->response->json(array('error' => $this->text('You are not permitted to perform this operation')));
            }

            if (!empty($this->submitted['update_combinations'])) {
                $this->updateCombination($this->submitted);
                $this->response->json(array('success' => $this->text('Product has been updated')));
            }

            if (empty($this->submitted['product_id'])) {
                $this->response->json(array('error' => $this->text('You are not permitted to perform this operation')));
            }

            $this->product->update($this->submitted['product_id'], $this->submitted);
            $this->response->json(array('success' => $this->text('Product has been updated')));
        }

        if (isset($product['product_id'])) {
            $this->controlAccess('product_edit');
            foreach ($this->request->post('delete_image', array()) as $file_id) {
                $this->image->delete($file_id);
            }

            $this->product->update($product['product_id'], $this->submitted);
            $this->redirect('admin/content/product', $this->text('Product has been updated'), 'success');
        }

        $this->controlAccess('product_add');

        $this->submitted += array(
            'currency' => $this->currency->getDefault(),
            'user_id' => $this->uid
        );

        $this->product->add($this->submitted);
        $this->redirect('admin/content/product', $this->text('Product has been added'), 'success');
    }

    /**
     * Validates an array of submitted product data
     * @param array $product
     */
    protected function validate(array $product = array())
    {
        $this->validatePrice();
        $this->validateStock();
        $this->validateAlias($product);
        $this->validateSku($product);
        $this->validateTitle();
        $this->validateMetaTitle();
        $this->validateMetaDescription();
        $this->validateTranslation();
        $this->validateAttributes();
        $this->validateDimensions();
        $this->validateImages();
        $this->validateCombinations($product);
        $this->validateRelated($product);

        $this->submitted['status'] = !empty($this->submitted['status']);
        $this->submitted['front'] = !empty($this->submitted['front']);
    }

    /**
     * Validates product price
     * @return boolean
     */
    protected function validatePrice()
    {
        if (!isset($this->submitted['price'])) {
            return true;
        }

        if (empty($this->submitted['price'])) {
            $this->submitted['price'] = 0;
            return true;
        }

        if (!is_numeric($this->submitted['price']) || strlen($this->submitted['price']) > 10) {
            $this->data['form_errors']['price'] = $this->text('Only numeric value and no more than %s digits', array('%s' => 10));
            return false;
        }

        return true;
    }

    /**
     * Validates product stock
     * @return boolean
     */
    protected function validateStock()
    {
        if (!isset($this->submitted['stock'])) {
            return true;
        }

        if (empty($this->submitted['price'])) {
            $this->submitted['price'] = 0;
            return true;
        }

        if (!is_numeric($this->submitted['stock']) || strlen($this->submitted['stock']) > 10) {
            $this->data['form_errors']['stock'] = $this->text('Only numeric value and no more than %s digits', array('%s' => 10));
            return false;
        }

        return true;
    }

    /**
     * Validates product alias
     * @param array $product
     * @return boolean
     */
    protected function validateAlias($product)
    {
        if (!isset($this->submitted['alias'])) {
            return true;
        }

        if (!empty($this->submitted['alias'])) {
            $check_alias = (isset($product['alias']) && ($product['alias'] !== $this->submitted['alias']));
            if ($check_alias && $this->alias->exists($this->submitted['alias'])) {
                $this->data['form_errors']['alias'] = $this->text('URL alias already exists');
                return false;
            }
            return true;
        }

        if (isset($product['product_id'])) {
            $this->submitted['alias'] = $this->product->createAlias($product);
        }

        return true;
    }

    /**
     * Validates product SKU
     * @param array $product
     * @return boolean
     */
    protected function validateSku(array $product)
    {
        if (!isset($this->submitted['sku'])) {
            return true;
        }

        $product_id = isset($product['product_id']) ? $product['product_id'] : null;
        $store_id = isset($this->submitted['store_id']) ? $this->submitted['store_id'] : $this->store->getDefault();

        if (!empty($this->submitted['sku'])) {
            if ($this->sku->get($this->submitted['sku'], $store_id, $product_id)) {
                $this->data['form_errors']['sku'] = $this->text('SKU must be unique per store');
                return false;
            }

            if (mb_strlen($this->submitted['sku']) > 255) {
                $this->data['form_errors']['sku'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                return false;
            }

            return true;
        }

        if (!empty($product_id)) {
            $this->submitted['sku'] = $this->product->createSku($product);
        }

        if (!empty($this->submitted['sku'])) {
            static::$processed_skus[$this->submitted['sku']] = true;
        }

        return true;
    }

    /**
     * Validates product titles
     * @return boolean
     */
    protected function validateTitle()
    {
        if (!isset($this->submitted['title'])) {
            return true;
        }

        if (empty($this->submitted['title']) || (mb_strlen($this->submitted['title']) > 255)) {
            $this->data['form_errors']['title'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates product meta title
     * @return boolean
     */
    protected function validateMetaTitle()
    {
        if (!isset($this->submitted['meta_title'])) {
            return true;
        }

        if (mb_strlen($this->submitted['meta_title']) > 255) {
            $this->data['form_errors']['meta_title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates product description
     * @return boolean
     */
    protected function validateMetaDescription()
    {
        if (!isset($this->submitted['meta_description'])) {
            return true;
        }

        if (mb_strlen($this->submitted['meta_description']) > 255) {
            $this->data['form_errors']['meta_description'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates translations
     * @return boolean
     */
    protected function validateTranslation()
    {
        if (empty($this->submitted['translation'])) {
            return true;
        }

        $has_errors = false;
        foreach ($this->submitted['translation'] as $langcode => $translation) {
            if (mb_strlen($translation['title']) > 255) {
                $this->data['form_errors']['translation'][$langcode]['title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $has_errors = true;
            }

            if (mb_strlen($translation['meta_title']) > 255) {
                $this->data['form_errors']['translation'][$langcode]['meta_title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $has_errors = true;
            }

            if (mb_strlen($translation['meta_description']) > 255) {
                $this->data['form_errors']['translation'][$langcode]['meta_description'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $has_errors = true;
            }
        }

        return !$has_errors;
    }

    /**
     * Validates attributes
     * @return boolean
     */
    protected function validateAttributes()
    {
        if (!isset($this->submitted['product_class_id'])) {
            return true;
        }

        $product_fields = $this->product_class->getFieldData($this->submitted['product_class_id']);
        $this->submitted['product_fields'] = $product_fields;

        if (empty($product_fields['attribute'])) {
            return true;
        }

        $has_errors = false;
        foreach ($product_fields['attribute'] as $field_id => $field) {
            if ($field['required'] && empty($this->submitted['field']['attribute'][$field_id])) {
                $this->data['form_errors']['attribute'][$field_id] = $this->text('Required field');
                $has_errors = true;
            }
        }

        return !$has_errors;
    }

    /**
     * Validates dimensions
     * @return boolean
     */
    protected function validateDimensions()
    {
        $has_errors = false;

        if (isset($this->submitted['width']) && (!is_numeric($this->submitted['width']) || strlen($this->submitted['width']) > 10)) {
            $this->data['form_errors']['width'] = $this->text('Only numeric value and no more than %s digits', array('%s' => 10));
            $has_errors = true;
        }

        if (isset($this->submitted['height']) && (!is_numeric($this->submitted['height']) || strlen($this->submitted['height']) > 10)) {
            $this->data['form_errors']['height'] = $this->text('Only numeric value and no more than %s digits', array('%s' => 10));
            $has_errors = true;
        }

        if (isset($this->submitted['length']) && (!is_numeric($this->submitted['length']) || strlen($this->submitted['length']) > 10)) {
            $this->data['form_errors']['length'] = $this->text('Only numeric value and no more than %s digits', array('%s' => 10));
            $has_errors = true;
        }

        if (isset($this->submitted['weight']) && (!is_numeric($this->submitted['weight']) || strlen($this->submitted['weight']) > 10)) {
            $this->data['form_errors']['weight'] = $this->text('Only numeric value and no more than %s digits', array('%s' => 10));
            $has_errors = true;
        }

        return !$has_errors;
    }

    /**
     * Validates product images
     * @return boolean
     */
    protected function validateImages()
    {
        if (empty($this->submitted['images'])) {
            return true;
        }

        foreach ($this->submitted['images'] as &$image) {
            if (empty($image['title']) && isset($this->submitted['title'])) {
                $image['title'] = $this->submitted['title'];
            }

            if (empty($image['description']) && isset($this->submitted['title'])) {
                $image['description'] = $this->submitted['title'];
            }

            $image['title'] = $this->truncate($image['title'], 255);

            if (empty($image['translation'])) {
                continue;
            }

            foreach ($image['translation'] as &$translation) {
                $translation['title'] = $this->truncate($translation['title'], 255);
            }
        }
    }

    /**
     * Validates option combinations
     * @return boolean
     */
    protected function validateCombinations($product)
    {
        if (empty($this->submitted['combination'])) {
            return true;
        }

        foreach ($this->submitted['combination'] as $index => &$combination) {
            if (empty($combination['fields'])) {
                unset($this->submitted['combination'][$index]);
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

        $this->submitted['stock'] = array_sum(static::$stock_amount);

        if (!empty($repeating_combinations)) {
            $this->data['form_errors']['combination']['repeating_options'] = true;
            $this->setMessage($this->text('Option combinations must be unique'), 'danger');
        }
    }

    /**
     * Validates combination fields
     * @param string $index
     * @param array $combination
     * @return boolean
     */
    protected function validateCombinationOptions($index, $combination)
    {
        if (!isset($this->submitted['product_fields']['option'])) {
            return true;
        }

        $options = $this->submitted['product_fields']['option'];

        $has_errors = false;
        foreach ($options as $field_id => $field) {
            if (!empty($field['required']) && !isset($combination['fields'][$field_id])) {
                $this->data['form_errors']['combination'][$index]['fields'][$field_id] = $this->text('Required field');
                $has_errors = true;
            }
        }

        return !$has_errors;
    }

    /**
     * Validates combination SKU
     * @param string $index
     * @param array $combination
     * @return boolean
     */
    protected function validateCombinationSku($index, &$combination, $product)
    {
        if (!isset($combination['sku'])) {
            return true;
        }

        $store_id = $this->submitted['store_id'];
        $product_id = isset($product['product_id']) ? $product['product_id'] : null;

        if (isset($this->submitted['product_id'])) {
            $product_id = $this->submitted['product_id'];
        }

        if (!empty($combination['sku'])) {
            if (mb_strlen($combination['sku']) > 255) {
                $this->data['form_errors']['combination'][$index]['sku'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                return false;
            }

            if (isset(static::$processed_skus[$combination['sku']])) {
                $this->data['form_errors']['combination'][$index]['sku'] = $this->text('SKU must be unique per store');
                return false;
            }

            if ($this->sku->get($combination['sku'], $store_id, $product_id)) {
                $this->data['form_errors']['combination'][$index]['sku'] = $this->text('SKU must be unique per store');
                return false;
            }

            static::$processed_skus[$combination['sku']] = true;
            return true;
        }

        if (empty($this->data['form_errors']['sku']) && !empty($product_id)) {
            $sku_pattern = $this->submitted['sku'] . '-' . $index;
            $combination['sku'] = $this->sku->generate($sku_pattern, false, array('store_id' => $store_id));
        }

        return true;
    }

    /**
     * Validates combination stock price
     * @param string $index
     * @param array $combination
     * @return boolean
     */
    protected function validateCombinationPrice($index, array &$combination)
    {
        if (empty($combination['price']) && empty($this->data['form_errors']['price'])) {
            $combination['price'] = $this->submitted['price'];
        }

        if (is_numeric($combination['price']) && strlen($combination['price']) <= 10) {
            return true;
        }

        $this->data['form_errors']['combination'][$index]['price'] = $this->text('Only numeric value and no more than %s digits', array('%s' => 10));
        return false;
    }

    /**
     * Validates combination stock level
     * @param string $index
     * @param array $combination
     * @return boolean
     */
    protected function validateCombinationStock($index, array &$combination)
    {
        if (empty($combination['stock'])) {
            return true;
        }

        if (is_numeric($combination['stock']) && strlen($combination['stock']) <= 10) {
            return true;
        }

        $this->data['form_errors']['combination'][$index]['stock'] = $this->text('Only numeric value and no more than %s digits', array('%s' => 10));
        return false;
    }

    /**
     * Validates related products
     * @param array $product
     * @return boolean
     */
    protected function validateRelated(array $product)
    {
        if (empty($this->submitted['related'])) {
            $this->submitted['related'] = array(); // Need on update
            return true;
        }

        // Remove duplicates
        $this->submitted['related'] = array_unique($this->submitted['related']);

        if (isset($product['product_id'])) {
            // Exclude the current product from related products
            $this_product = array_search($product['product_id'], $this->submitted['related']);
            if ($this_product !== false) {
                unset($this->submitted['related'][$this_product]);
            }
        }

        return true;
    }
}
