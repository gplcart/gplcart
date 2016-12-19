<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\frontend;

use core\models\Search as SearchModel;
use core\models\CategoryGroup as CategoryGroupModel;
use core\controllers\frontend\Controller as FrontendController;

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
        $children = $this->getChildrenCategory($category_id);

        $this->setDataImagesCategory($category);
        $this->setDataProductsCategory($products);
        $this->setDataChildrenCategory($children);
        $this->setDataNavbarCategory($products, $total, $query);

        $this->setRegionMenuCategory();
        $this->setRegionContentCategory($category);

        $this->setTitleIndexCategory($category);
        $this->setMetaIndexCategory($category, $children, $products);
        $this->outputIndexCategory();
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
     * @param array $category
     * @param array $children
     * @param array $products
     */
    protected function setMetaIndexCategory($category, $children, $products)
    {
        $this->setMetaEntity($category);

        if (empty($children) && empty($products)) {
            // Forbid Google to index empty pages
            $this->setMeta(array('name' => 'robots', 'content' => 'noindex'));
        }
    }

    /**
     * Sets navigation menu on the category page
     */
    protected function setRegionMenuCategory()
    {
        $menu = $this->renderMenu();
        $this->setRegion('region_left', $menu);
    }

    /**
     * Sets rendered category navbar
     * @param array $products
     * @param integer $total
     * @param array $query
     */
    protected function setDataNavbarCategory($products, $total, $query)
    {
        $options = array(
            'total' => $total,
            'view' => $query['view'],
            'quantity' => count($products),
            'sort' => "{$query['sort']}-{$query['order']}"
        );

        $html = $this->render('category/blocks/navbar', $options);
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

        $html = $this->render('category/blocks/images', array('category' => $category));
        $this->setData('images', $html);
    }

    /**
     * Returns an array of children categories for the given category ID
     * @param integer $category_id
     * @return array
     */
    protected function getChildrenCategory($category_id)
    {
        return $this->category->getChildren($category_id, $this->category_tree);
    }

    /**
     * Sets rendered category children
     * @param array $categories
     */
    protected function setDataChildrenCategory($categories)
    {
        $html = $this->render('category/blocks/children', array('children' => $categories));
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
     * @param array $limit
     * @param array $query
     * @param integer $cid
     * @return array
     */
    protected function getListProductCategory(array $limit, array $query, $cid)
    {
        $options = array(
            'limit' => $limit,
            'category_id' => $cid,
            'placeholder' => true,
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
        return (int) $this->product->getList($options);
    }

}
