<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Search as SearchModel;
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
     * Search model instance
     * @var \gplcart\core\models\Search $search
     */
    protected $search;

    /**
     * The current category
     * @var array
     */
    protected $data_category = array();

    /**
     * An array of products for the current category
     * @var array
     */
    protected $data_products = array();

    /**
     * An array of children categories
     * @var array
     */
    protected $data_children = array();

    /**
     * Constructor
     * @param SearchModel $search
     * @param CategoryGroupModel $category_group
     */
    public function __construct(SearchModel $search,
            CategoryGroupModel $category_group)
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
        $this->setCategory($category_id);

        $this->setTitleIndexCategory();

        $this->setHtmlFilter($this->data_category);

        $query = $this->getFilterQueryIndexCategory();
        $total = $this->getTotalProductCategory($category_id, $query);

        $max = $this->settings('catalog_limit', 20);
        $limit = $this->setPager($total, $query, $max);

        $this->setListProductCategory($limit, $query, $category_id);
        $this->setChildrenCategory($category_id);

        $this->setDataImagesCategory();
        $this->setDataProductsCategory();
        $this->setDataChildrenCategory();

        $this->setRegionMenuCategory();
        $this->setRegionContentCategory();
        $this->setDataNavbarCategory($total, $query);

        $this->setData('category', $this->data_category);

        $this->setMetaIndexCategory();
        $this->outputIndexCategory();
    }

    /**
     * Returns an array of sorting query
     * @return array
     */
    protected function getFilterQueryIndexCategory()
    {
        $filter = array(
            'view' => $this->settings('catalog_view', 'grid'),
            'sort' => $this->settings('catalog_sort', 'price'),
            'order' => $this->settings('catalog_order', 'asc')
        );

        return $this->getFilterQuery($filter);
    }

    /**
     * Puts main category content into content region
     */
    protected function setRegionContentCategory()
    {
        $html = $this->render('category/content', $this->data);
        $this->setRegion('region_content', $html);
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
    protected function setRegionMenuCategory()
    {
        $options = array(
            'items' => $this->data_categories,
            'template' => 'category/blocks/menu'
        );

        $menu = $this->renderMenuTrait($this, $options);
        $this->setRegion('region_left', $menu);
    }

    /**
     * Sets rendered category navbar
     * @param integer $total
     * @param array $query
     */
    protected function setDataNavbarCategory($total, $query)
    {
        $options = array(
            'total' => $total,
            'view' => $query['view'],
            'quantity' => count($this->data_products),
            'sort' => "{$query['sort']}-{$query['order']}"
        );

        $html = $this->render('category/blocks/navbar', $options);
        $this->setData('navbar', $html);
    }

    /**
     * Sets rendered product list
     */
    protected function setDataProductsCategory()
    {
        $html = $this->render('product/list', array('products' => $this->data_products));
        $this->setData('products', $html);
    }

    /**
     * Sets rendered category images
     */
    protected function setDataImagesCategory()
    {
        $options = array(
            'imagestyle' => $this->settings('image_style_category', 3));

        $this->setThumbTrait($this->image, $this->data_category, $options);

        $data = array('category' => $this->data_category);
        $html = $this->render('category/blocks/images', $data);
        $this->setData('images', $html);
    }

    /**
     * Returns an array of children categories for the given category ID
     * @param integer $category_id
     * @return array
     */
    protected function setChildrenCategory($category_id)
    {
        $children = $this->category->getChildren($category_id, $this->data_categories);
        return $this->data_children = $children;
    }

    /**
     * Sets rendered category children
     */
    protected function setDataChildrenCategory()
    {
        $html = $this->render('category/blocks/children', array('children' => $this->data_children));
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
     * Renders the category page
     */
    protected function outputIndexCategory()
    {
        $this->output();
    }

    /**
     * Returns a category
     * @param integer $category_id
     * @return array
     */
    protected function setCategory($category_id)
    {
        $category = $this->category->get($category_id, $this->langcode, $this->store_id);

        if (empty($category['status'])) {
            $this->outputHttpStatus(404);
        }

        return $this->data_category = $category;
    }

    /**
     * Returns an array of prepared products
     * @param array $limit
     * @param array $query
     * @param integer $cid
     * @return array
     */
    protected function setListProductCategory(array $limit, array $query, $cid)
    {
        $options = array('placeholder' => true);
        $conditions = array('limit' => $limit, 'category_id' => $cid) + $query;
        return $this->data_products = $this->getProducts($conditions, $options);
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
            'category_id' => $category_id
        );

        $options += $query;
        return (int) $this->product->getList($options);
    }

}
