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
        $this->setPagerIndexCategory();

        $this->setListProductCategory();
        $this->setChildrenCategory();

        $this->setDataImagesIndexCategory();
        $this->setDataProductsIndexCategory();
        $this->setDataChildrenIndexCategory();
        $this->setDataNavbarIndexCategory();

        $this->setRegionMenuIndexCategory();
        $this->setRegionContentIndexCategory();

        $this->setData('category', $this->data_category);

        $this->setMetaIndexCategory();
        $this->outputIndexCategory();
    }

    /**
     * Sets HTML filter
     */
    protected function setHtmlFilterIndexCategory()
    {
        $this->setHtmlFilter($this->data_category);
    }

    /**
     * Set pager on the category page
     */
    protected function setPagerIndexCategory()
    {
        $limit = $this->settings('catalog_limit', 20);
        $this->limit = $this->setPager($this->total, $this->query_filter, $limit);
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

        $this->setFilter(array(), $this->getFilterQuery($default));
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
     * Puts main category content into content region
     */
    protected function setRegionContentIndexCategory()
    {
        $data = $this->data;
        $data['category'] = $this->data_category;
        $this->setRegion('region_content', $this->render('category/content', $data));
    }

    /**
     * Sets meta tags on the category page
     */
    protected function setMetaIndexCategory()
    {
        $this->setMetaEntity($this->data_category);

        if (empty($this->data_children) && empty($this->data_products)) {
            $this->setMeta(array('name' => 'robots', 'content' => 'noindex'));
        }
    }

    /**
     * Sets navigation menu on the category page
     */
    protected function setRegionMenuIndexCategory()
    {
        $options = array(
            'template' => 'category/menu',
            'items' => $this->data_categories
        );

        $this->setRegion('region_left', $this->renderMenu($options));
    }

    /**
     * Sets rendered category navbar
     */
    protected function setDataNavbarIndexCategory()
    {
        $data = array(
            'query' => $this->query,
            'total' => $this->total,
            'view' => $this->query_filter['view'],
            'quantity' => count($this->data_products),
            'sort' => "{$this->query_filter['sort']}-{$this->query_filter['order']}"
        );

        $this->setData('navbar', $this->render('category/navbar', $data));
    }

    /**
     * Sets rendered product list
     */
    protected function setDataProductsIndexCategory()
    {
        $html = $this->render('product/list', array('products' => $this->data_products));
        $this->setData('products', $html);
    }

    /**
     * Sets rendered category images
     */
    protected function setDataImagesIndexCategory()
    {
        $options = array(
            'imagestyle' => $this->settings('image_style_category', 3));

        $this->attachItemThumb($this->data_category, $options);

        $data = array('category' => $this->data_category);
        $this->setData('images', $this->render('category/images', $data));
    }

    /**
     * Sets an array of children categories for the given category
     */
    protected function setChildrenCategory()
    {
        $this->data_children = $this->category->getChildren($this->data_category['category_id'], $this->data_categories);
    }

    /**
     * Sets rendered category children
     */
    protected function setDataChildrenIndexCategory()
    {
        $html = $this->render('category/children', array('children' => $this->data_children));
        $this->setData('children', $html);
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
        $this->output();
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
