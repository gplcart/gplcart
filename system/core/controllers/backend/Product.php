<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Alias as AliasModel,
    gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Product as ProductModel,
    gplcart\core\models\Category as CategoryModel,
    gplcart\core\models\Currency as CurrencyModel,
    gplcart\core\models\ProductClass as ProductClassModel,
    gplcart\core\models\CategoryGroup as CategoryGroupModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to products
 */
class Product extends BackendController
{

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
     * URL model instance
     * @var \gplcart\core\models\Alias $alias
     */
    protected $alias;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * The current updating product
     * @var array
     */
    protected $data_product = array();

    /**
     * @param ProductModel $product
     * @param ProductClassModel $product_class
     * @param CategoryModel $category
     * @param CategoryGroupModel $category_group
     * @param PriceModel $price
     * @param CurrencyModel $currency
     * @param AliasModel $alias
     */
    public function __construct(ProductModel $product,
            ProductClassModel $product_class, CategoryModel $category,
            CategoryGroupModel $category_group, PriceModel $price,
            CurrencyModel $currency, AliasModel $alias)
    {
        parent::__construct();

        $this->alias = $alias;
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
        $this->actionListProduct();

        $this->setTitleListProduct();
        $this->setBreadcrumbListProduct();

        $this->setFilterListProduct();
        $this->setPagerListProduct();

        $this->setData('products', $this->getListProduct());
        $this->setData('currencies', $this->currency->getList(true));

        $this->outputListProduct();
    }

    /**
     * Set filter on the product overview page
     */
    protected function setFilterListProduct()
    {
        $allowed = array('title', 'sku_like', 'price', 'stock', 'status',
            'store_id', 'product_id', 'currency');

        $this->setFilter($allowed);
    }

    /**
     * Set pager
     * @return array
     */
    protected function setPagerListProduct()
    {
        $options = $this->query_filter;
        $options['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->product->getList($options)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Applies an action to the selected products
     */
    protected function actionListProduct()
    {
        list($selected, $action, $value) = $this->getPostedAction();

        $deleted = $updated = 0;
        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('product_edit')) {
                $updated += (int) $this->product->update($id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('product_delete')) {
                $deleted += (int) $this->product->delete($id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Updated %num item(s)', array('%num' => $updated));
            $this->setMessage($message, 'success');
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Returns an array of products
     * @return array
     */
    protected function getListProduct()
    {
        $options = $this->query_filter;
        $options['limit'] = $this->data_limit;
        $products = (array) $this->product->getList($options);

        return empty($products) ? array() : $this->prepareListProduct($products);
    }

    /**
     * Prepare an array of products
     * @param array $products
     * @return array
     */
    protected function prepareListProduct(array $products)
    {
        $this->attachEntityUrl($products, 'product');

        foreach ($products as &$product) {
            $product['price'] = $this->price->decimal($product['price'], $product['currency']);
        }

        return $products;
    }

    /**
     * Sets title on the product overview page
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
        $this->setBreadcrumbHome();
    }

    /**
     * Render and output the product overview page
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
        $this->setProduct($product_id);

        $this->setTitleEditProduct();
        $this->setBreadcrumbEditProduct();

        $this->setData('product', $this->data_product);
        $this->setData('related', $this->getRelatedProduct());
        $this->setData('classes', $this->getClassesProduct());
        $this->setData('size_units', $this->product->getSizeUnits());
        $this->setData('weight_units', $this->product->getWeightUnits());
        $this->setData('default_currency', $this->currency->getDefault());
        $this->setData('subtract_default', $this->config->get('product_subtract', 0));
        $this->setData('languages', $this->language->getList(false, true));

        $this->submitEditProduct();

        $this->setDataFieldsEditProduct();
        $this->setDataAuthorEditProduct();
        $this->setDataRelatedEditProduct();
        $this->setDataCategoriesEditProduct();
        $this->setDataImagesEditProduct();

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
     * Returns an array of categories keyed by a type
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
     * Set a product data
     * @param integer $product_id
     */
    protected function setProduct($product_id)
    {
        if (is_numeric($product_id)) {
            $product = $this->product->get($product_id);
            if (empty($product)) {
                $this->outputHttpStatus(404);
            }
            $this->data_product = $this->prepareProduct($product);
        }
    }

    /**
     * Prepare an array of product data
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
     * Prepare an array of product combination data
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
                $this->attachThumb($combination);
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

        $options = array(
            'load' => true,
            'store_id' => $this->data_product['store_id'],
            'product_id' => $this->data_product['product_id']
        );

        $products = $this->product->getRelated($options);
        $this->attachEntityUrl($products, 'product');

        return $products;
    }

    /**
     * Handles a submitted product
     */
    protected function submitEditProduct()
    {
        if ($this->isPosted('delete')) {
            $this->deleteProduct();
        } else if ($this->isPosted('save') && $this->validateEditProduct()) {
            $this->deleteImages($this->data_product, 'product');
            if (isset($this->data_product['product_id'])) {
                $this->updateProduct();
            } else {
                $this->addProduct();
            }
        }
    }

    /**
     * Deletes a product
     */
    protected function deleteProduct()
    {
        $this->controlAccess('product_delete');
        if ($this->product->delete($this->data_product['product_id'])) {
            $this->redirect('admin/content/product', $this->text('Product has been deleted'), 'success');
        }
        $this->redirect('admin/content/product', $this->text('Unable to delete'), 'danger');
    }

    /**
     * Validates an array of submitted product data
     * @return bool
     */
    protected function validateEditProduct()
    {
        $this->setSubmitted('product', null, false);

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

        $this->validateComponent('product');

        return !$this->hasErrors();
    }

    /**
     * Updates a product
     */
    protected function updateProduct()
    {
        $this->controlAccess('product_edit');
        $this->product->update($this->data_product['product_id'], $this->getSubmitted());
        $this->redirect('admin/content/product', $this->text('Product has been updated'), 'success');
    }

    /**
     * Adds a new product
     */
    protected function addProduct()
    {
        $this->controlAccess('product_add');
        $this->product->add($this->getSubmitted());
        $this->redirect('admin/content/product', $this->text('Product has been added'), 'success');
    }

    /**
     * Sets the product author data
     */
    protected function setDataAuthorEditProduct()
    {
        $user_id = $this->getData('product.user_id');

        if (!empty($user_id)) {
            $user = $this->user->get($user_id);
            $this->setData('product.author', $user['email']);
        }
    }

    /**
     * Sets the product categories data
     */
    protected function setDataCategoriesEditProduct()
    {
        $store_id = $this->getData('store_id');
        $categories = $this->getListCategoryProduct($store_id);
        $this->setData('categories', $categories);
    }

    /**
     * Sets attributes/options product data
     */
    protected function setDataFieldsEditProduct()
    {
        $output_field_form = false;
        $get_product_class_id = $this->getQuery('product_class_id');
        $product_class_id = $this->getData('product.product_class_id', 0);

        if (isset($get_product_class_id)) {
            $output_field_form = true;
            $product_class_id = (int) $get_product_class_id;
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
            $this->response->outputHtml($attributes . $options);
        }
    }

    /**
     * Sets related products
     */
    protected function setDataRelatedEditProduct()
    {
        $related = $this->getData('product.related');

        if (!empty($related)) {
            $products = (array) $this->product->getList(array('product_id' => $related));
            $this->attachEntityUrl($products, 'product');
            $this->setData('related', $products);
        }
    }

    /**
     * Sets product attached data
     */
    protected function setDataImagesEditProduct()
    {
        $images = $this->getData('product.images', array());
        $this->attachThumbs($images);
        $this->setDataAttachedImages($images, 'product');
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
        if (isset($this->data_product['product_id'])) {
            $vars = array('%name' => $this->data_product['title']);
            $title = $this->text('Edit %name', $vars);
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
        $this->setBreadcrumbHome();

        $breadcrumb = array(
            'text' => $this->text('Products'),
            'url' => $this->url('admin/content/product')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the product edit page
     */
    protected function outputEditProduct()
    {
        $this->output('content/product/edit');
    }

}
