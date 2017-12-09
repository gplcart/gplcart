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
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * Tottal number of category items
     * @var int
     */
    protected $data_total;

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
     * Displays the catalog page
     */
    public function listCategory()
    {
        $this->setTitleListCategory();
        $this->setBreadcrumbListCategory();

        $this->setData('categories', $this->data_categories);
        $this->outputListCategory();
    }

    /**
     * Sets titles on the catalog page
     */
    protected function setTitleListCategory()
    {
        $this->setTitle($this->text('Catalog'));
    }

    /**
     * Sets bread crumbs on the catalog page
     */
    protected function setBreadcrumbListCategory()
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders and outputs the catalog page templates
     */
    protected function outputListCategory()
    {
        $this->output('category/list');
    }

    /**
     * Displays the category page
     * @param integer $category_id
     */
    public function indexCategory($category_id)
    {
        $this->setCategory($category_id);
        $this->setTitleIndexCategory();
        $this->setBreadcrumbIndexCategory();
        $this->setHtmlFilterIndexCategory();
        $this->setTotalIndexCategory();
        $this->setFilterIndexCategory();
        $this->setPagerIndexCategory();
        $this->setListProductCategory();
        $this->setChildrenCategory();

        $this->setData('category', $this->data_category);

        $this->setDataMenuIndexCategory();
        $this->setDataImagesIndexCategory();
        $this->setDataNavbarIndexCategory();
        $this->setDataProductsIndexCategory();
        $this->setDataChildrenIndexCategory();

        $this->setMetaIndexCategory();
        $this->outputIndexCategory();
    }

    /**
     * Sets filter on the category page
     */
    protected function setFilterIndexCategory()
    {
        $default = array(
            'view' => $this->configTheme('catalog_view', 'grid'),
            'sort' => $this->configTheme('catalog_sort', 'price'),
            'order' => $this->configTheme('catalog_order', 'asc')
        );

        $this->setFilter(array(), $this->getFilterQuery($default));
    }

    /**
     * Sets a total number of products found for the category
     * @return int
     */
    protected function setTotalIndexCategory()
    {
        $options = $this->query_filter;
        $options['count'] = true;
        $options['category_id'] = $this->data_category['category_id'];

        return $this->data_total = (int) $this->product->getList($options);
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerIndexCategory()
    {
        $pager = array(
            'total' => $this->data_total,
            'query' => $this->query_filter,
            'limit' => $this->configTheme('catalog_limit', 20)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Sets bread crumbs on the category page
     */
    protected function setBreadcrumbIndexCategory()
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumb($breadcrumb);
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
        $options = array('imagestyle' => $this->configTheme('image_style_category', 3));
        $this->setItemThumb($this->data_category, $this->image, $options);
        $this->setData('images', $this->render('category/images', array('category' => $this->data_category)));
    }

    /**
     * Sets navigation bar on the category page
     */
    protected function setDataNavbarIndexCategory()
    {
        $data = array(
            'total' => $this->data_total,
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
     * Sets the navigation menu on the category page
     */
    protected function setDataMenuIndexCategory()
    {
        $options = array(
            'template' => 'category/menu',
            'items' => $this->data_categories
        );

        $this->setData('menu', $this->getWidgetMenu($options));
    }

    /**
     * Sets HTML filter
     */
    protected function setHtmlFilterIndexCategory()
    {
        $this->setHtmlFilter($this->data_category);
    }

    /**
     * Sets an array of products for the category
     * @return array
     */
    protected function setListProductCategory()
    {
        $options = array('placeholder' => true) + $this->query_filter;

        $conditions = array(
            'limit' => $this->data_limit,
            'category_id' => $this->data_category['category_id']) + $this->query_filter;

        return $this->data_products = $this->getProducts($conditions, $options);
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
     * Sets an array of children categories for the given category
     * @return array
     */
    protected function setChildrenCategory()
    {
        $children = array();
        foreach ($this->data_categories as $item) {
            if (in_array($this->data_category['category_id'], $item['parents'])) {
                $children[] = $item;
            }
        }

        return $this->data_children = $children;
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
     * @return array
     */
    protected function setCategory($category_id)
    {
        $this->data_category = $this->category->get($category_id, $this->langcode, $this->store_id);

        if (empty($this->data_category['status'])) {
            $this->outputHttpStatus(404);
        }

        return $this->data_category;
    }

}
