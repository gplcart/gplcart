<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\models\Search as ModelsSearch;
use core\models\CategoryGroup as ModelsCategoryGroup;
use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to product catalog
 */
class Category extends FrontendController
{

    /**
     * Category group model instance
     * @var \core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Search model instance
     * @var \core\models\Search $search
     */
    protected $search;

    /**
     * Constructor
     * @param ModelsSearch $search
     * @param ModelsCategoryGroup $category_group
     */
    public function __construct(ModelsSearch $search,
            ModelsCategoryGroup $category_group)
    {
        parent::__construct();

        $this->search = $search;
        $this->category_group = $category_group;
    }

    /**
     * Displays the category page
     * @param integer $category_id
     */
    public function indexCategory($category_id)
    {
        $category = $this->getCategory($category_id);

        $this->setData('category', $category);

        $filter = array(
            'view' => $this->setting('catalog_view', 'grid'),
            'sort' => $this->setting('catalog_sort', 'price'),
            'order' => $this->setting('catalog_order', 'asc')
        );

        $query = $this->getFilterQuery($filter);
        $total = $this->getTotalProductCategory($category_id, $query);

        $max = $this->setting('catalog_limit', 20);
        $limit = $this->setPager($total, $query, $max);
        $products = $this->getListProductCategory($limit, $query, $category_id);

        $this->setDataImagesCategory($category);
        $this->setDataProductsCategory($products);
        $this->setDataChildrenCategory($category_id);
        $this->setDataNavbarCategory($products, $total, $query);

        $this->setMetaIndexCategory($category);
        $this->setTitleIndexCategory($category);
        $this->setBreadcrumbIndexCategory($category);
        $this->outputIndexCategory();
    }

    /**
     * Sets rendered category navbar
     * @param array $products
     * @param integer $total
     * @param array $query
     */
    protected function setDataNavbarCategory(array $products, $total,
            array $query)
    {
        $options = array(
            'total' => $total,
            'view' => $query['view'],
            'quantity' => count($products),
            'sort' => "{$query['sort']}-{$query['order']}"
        );

        $html = $this->render('category/navbar', $options);
        $this->setData('navbar', $html);
    }

    /**
     * Sets rendered product list
     * @param array $products
     */
    protected function setDataProductsCategory(array $products)
    {
        $html = $this->render('product/list', array('products' => $products));
        $this->setData('products', $html);
    }

    /**
     * Sets rendered category images
     * @param array $category
     */
    protected function setDataImagesCategory(array $category)
    {
        $options = array(
            'imagestyle' => $this->setting('image_style_category', 3));

        $this->setItemThumb($category, $options);

        $html = $this->render('category/images', array('category' => $category));
        $this->setData('images', $html);
    }

    /**
     * Sets rendered category children
     * @param integer $category_id
     */
    protected function setDataChildrenCategory($category_id)
    {
        $children = $this->category->getChildren($category_id, $this->category_tree);
        $html = $this->render('category/children', array('children' => $children));
        $this->setData('children', $html);
    }

    /**
     * Sets titles on the category page
     * @param array $category
     */
    protected function setTitleIndexCategory(array $category)
    {
        $metatitle = $category['meta_title'];

        if (empty($metatitle)) {
            $metatitle = $category['title'];
        }

        $this->setTitle($metatitle, false);
        $this->setPageTitle($category['title']);
    }

    /**
     * Sets breadcrumbs on the category page
     * @param array $category
     */
    protected function setBreadcrumbIndexCategory(array $category)
    {
        $breadcrumb = array(
            'text' => $this->text('Home'),
            'url' => $this->url('/'));

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Sets metatags on the category page
     * @param array $category
     */
    protected function setMetaIndexCategory(array $category)
    {
        $meta_description = $category['meta_description'];

        if ($meta_description === '') {
            $meta_description = $this->truncate($category['description_1'], 160, '');
        }

        $this->setMeta(array('name' => 'description', 'content' => $meta_description));
        $this->setMeta(array('rel' => 'canonical', 'href' => $this->path));
    }

    /**
     * Renders the category page
     */
    protected function outputIndexCategory()
    {
        $this->output('category/category');
    }

    /**
     * Returns a category
     * @param integer $category_id
     * @return array
     */
    protected function getCategory($category_id)
    {
        $category = $this->category->get($category_id, $this->langcode, $this->store_id);

        if (empty($category['status'])) {
            $this->outputError(404);
        }

        return $category;
    }

    /**
     * Returns an array of prepared products
     * @param integer $limit
     * @param array $query
     * @param integer $category_id
     * @return array
     */
    protected function getListProductCategory($limit, array $query, $category_id)
    {
        $options = array(
            'limit' => $limit,
            'category_id' => $category_id,
                //'language' => $this->langcode
        );

        $options += $query;
        return $this->getProducts($options, $options);
    }

    /**
     * Returns total number of products for the category ID
     * @param integer $category_id
     * @param array $query
     * @return integer
     */
    protected function getTotalProductCategory($category_id, array $query)
    {
        $options = array(
            'count' => true,
            'category_id' => $category_id,
                //'language' => $this->langcode
        );

        $options += $query;
        return $this->product->getList($options);
    }

}
