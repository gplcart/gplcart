<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\CategoryGroup as CategoryGroupModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to product catalog
 */
class Category extends FrontendController
{

    /**
     * Category group model instance
     * @var \gplcart\core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * An array of category data
     * @var array
     */
    protected $data_category = array();

    /**
     * An array of products for the category
     * @var array
     */
    protected $data_products = array();

    /**
     * An array of children categories
     * @var array
     */
    protected $data_children = array();

    /**
     * @param CategoryGroupModel $category_group
     */
    public function __construct(CategoryGroupModel $category_group)
    {
        parent::__construct();

        $this->category_group = $category_group;
    }

    /**
     * Displays the category page
     * @param integer $category_id
     */
    public function indexCategory($category_id)
    {
        $this->setCategory($category_id);
        $this->setTitleIndexCategory();

        $this->setHtmlFilterIndexCategory();

        $this->setFilterIndexCategory();
        $this->setTotalIndexCategory();
        $this->setPagerLimit($this->settings('catalog_limit', 20));

        $this->setListProductCategory();
        $this->setChildrenCategory();

        $this->setData('category', $this->data_category);

        $this->setDataImagesIndexCategory();
        $this->setDataNavbarIndexCategory();
        $this->setDataProductsIndexCategory();
        $this->setDataChildrenIndexCategory();

        $this->setRegionMenuIndexCategory();

        $this->setMetaIndexCategory();
        $this->outputIndexCategory();
    }

    /**
     * Sets the children of the category
     */
    protected function setDataChildrenIndexCategory()
    {
        $this->setData('children', $this->render('category/children', array('children' => $this->data_children)));
    }

    /**
     * Sets the category images
     */
    protected function setDataImagesIndexCategory()
    {
        $options = array(
            'imagestyle' => $this->settings('image_style_category', 3));

        $this->setItemThumbTrait($this->data_category, $options, $this->image);

        $data = array('category' => $this->data_category);
        $this->setData('images', $this->render('category/images', $data));
    }

    /**
     * Sets the category navbar
     */
    protected function setDataNavbarIndexCategory()
    {
        $data = array(
            'total' => $this->total,
            'query' => $this->query_filter,
            'view' => $this->query_filter['view'],
            'quantity' => count($this->data_products),
            'sort' => "{$this->query_filter['sort']}-{$this->query_filter['order']}"
        );

        $this->setData('navbar', $this->render('category/navbar', $data));
    }

    /**
     * Sets the category product list
     */
    protected function setDataProductsIndexCategory()
    {
        $this->setData('products', $this->render('product/list', array('products' => $this->data_products)));
    }

    /**
     * Sets HTML filter
     */
    protected function setHtmlFilterIndexCategory()
    {
        $this->setHtmlFilter($this->data_category);
    }

    /**
     * Sets filter on the category page
     */
    protected function setFilterIndexCategory()
    {
        $default = array(
            'view' => $this->settings('catalog_view', 'grid'),
            'sort' => $this->settings('catalog_sort', 'price'),
            'order' => $this->settings('catalog_order', 'asc')
        );

        $this->setFilter(array(), $this->getFilterQuery($default, array_keys($default)));
    }

    /**
     * Sets a total number of products for the category
     */
    protected function setTotalIndexCategory()
    {
        $options = array(
            'count' => true,
            'category_id' => $this->data_category['category_id']
        );

        $options += $this->query_filter;
        $this->total = (int) $this->product->getList($options);
    }

    /**
     * Sets an array of products for the category
     */
    protected function setListProductCategory()
    {
        $options = array('placeholder' => true) + $this->query_filter;

        $conditions = array(
            'limit' => $this->limit,
            'category_id' => $this->data_category['category_id']) + $this->query_filter;

        $this->data_products = $this->getProducts($conditions, $options);
    }

    /**
     * Sets the meta tags on the category page
     */
    protected function setMetaIndexCategory()
    {
        $this->setMetaEntity($this->data_category);

        if (empty($this->data_children) && empty($this->data_products)) {
            $this->setMeta(array('name' => 'robots', 'content' => 'noindex'));
        }
    }

    /**
     * Sets the navigation menu on the category page
     */
    protected function setRegionMenuIndexCategory()
    {
        $options = array(
            'template' => 'category/menu',
            'items' => $this->data_categories
        );

        $this->setRegion('left', $this->renderMenu($options));
    }

    /**
     * Sets an array of children categories for the given category
     */
    protected function setChildrenCategory()
    {
        $this->data_children = $this->category->getChildren($this->data_category['category_id'], $this->data_categories);
    }

    /**
     * Sets titles on the category page
     */
    protected function setTitleIndexCategory()
    {
        $metatitle = $this->data_category['meta_title'];

        if (empty($metatitle)) {
            $metatitle = $this->data_category['title'];
        }

        $this->setTitle($metatitle, false);
        $this->setPageTitle($this->data_category['title']);
    }

    /**
     * Render and output the category page
     */
    protected function outputIndexCategory()
    {
        $this->output('category/content');
    }

    /**
     * Sets a category data
     * @param integer $category_id
     */
    protected function setCategory($category_id)
    {
        $this->data_category = $this->category->get($category_id, $this->langcode, $this->store_id);

        if (empty($this->data_category['status'])) {
            $this->outputHttpStatus(404);
        }
    }

}
