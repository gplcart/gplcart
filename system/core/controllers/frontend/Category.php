<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Search as SearchModel,
    gplcart\core\models\CategoryGroup as CategoryGroupModel;
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
     * An array of query parameters
     * @var array
     */
    protected $data_query = array();

    /**
     * The amount of products found on the page
     * @var integer
     */
    protected $data_total;

    /**
     * The current pager limits
     * @var array
     */
    protected $data_limit;

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
        $this->setFilterQueryIndexCategory();
        $this->setTotalProductCategory();

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
     * Set pager limits
     * @return array
     */
    protected function setPagerIndexCategory()
    {
        $max = $this->settings('catalog_limit', 20);
        return $this->data_limit = $this->setPager($this->data_total, $this->data_query, $max);
    }

    /**
     * Returns an array of sorting query
     * @return array
     */
    protected function setFilterQueryIndexCategory()
    {
        $filter = array(
            'view' => $this->settings('catalog_view', 'grid'),
            'sort' => $this->settings('catalog_sort', 'price'),
            'order' => $this->settings('catalog_order', 'asc')
        );

        return $this->data_query = $this->getFilterQuery($filter);
    }

    /**
     * Puts main category content into content region
     */
    protected function setRegionContentIndexCategory()
    {
        $data = $this->data;
        $data['category'] = $this->data_category;

        $html = $this->render('category/content', $data);
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
    protected function setRegionMenuIndexCategory()
    {
        $options = array(
            'items' => $this->data_categories,
            'template' => 'category/menu'
        );

        $menu = $this->renderMenu($options);
        $this->setRegion('region_left', $menu);
    }

    /**
     * Sets rendered category navbar
     */
    protected function setDataNavbarIndexCategory()
    {
        $data = array(
            'query' => $this->query,
            'total' => $this->data_total,
            'view' => $this->data_query['view'],
            'quantity' => count($this->data_products),
            'sort' => "{$this->data_query['sort']}-{$this->data_query['order']}"
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
     * Returns an array of children categories for the given category ID
     * @return array
     */
    protected function setChildrenCategory()
    {
        $children = $this->category->getChildren($this->data_category['category_id'], $this->data_categories);
        return $this->data_children = $children;
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
     * @return array
     */
    protected function setListProductCategory()
    {
        $options = array('placeholder' => true) + $this->data_query;

        $conditions = array(
            'limit' => $this->data_limit,
            'category_id' => $this->data_category['category_id']) + $this->data_query;

        return $this->data_products = $this->getProducts($conditions, $options);
    }

    /**
     * Returns total number of products for the category ID
     * @return integer
     */
    protected function setTotalProductCategory()
    {
        $options = array(
            'count' => true,
            'category_id' => $this->data_category['category_id']
        );

        $options += $this->data_query;
        return $this->data_total = (int) $this->product->getList($options);
    }

}
