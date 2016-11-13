<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\models\Search as ModelsSearch;
use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to search functionality
 */
class Search extends FrontendController
{

    /**
     * Search model instance
     * @var \core\models\Search $search
     */
    protected $search;

    /**
     * Constructor
     * @param ModelsSearch $search
     */
    public function __construct(ModelsSearch $search)
    {
        parent::__construct();

        $this->search = $search;
    }

    /**
     * Displays search results page
     */
    public function indexSearch()
    {
        $term = (string) $this->request->get('q', '');
        $max = $this->setting('catalog_limit', 20);

        $filter = array(
            'view' => $this->setting('catalog_view', 'grid'),
            'sort' => $this->setting('catalog_sort', 'price'),
            'order' => $this->setting('catalog_order', 'asc')
        );

        $query = $this->getFilterQuery($filter);
        $total = $this->getTotalResultSearch($term);
        $limit = $this->setPager($total, $query, $max);

        $products = $this->getListResultSearch($term, $limit, $query);

        $this->setDataResultSearch($products);
        $this->setDataNavbarSearch($products, $total, $query);

        $this->setTitleIndexSearch($term);
        $this->setBreadcrumbIndexSearch();
        $this->outputIndexSearch();
    }

    /**
     * Sets titles on the search page
     * @param string $term
     */
    protected function setTitleIndexSearch($term)
    {
        $title = $this->text('Search');

        if ($term !== '') {
            $title = $this->text('Search for <small>%term</small>', array('%term' => $term));
        }

        $this->setTitle($title);
    }

    /**
     * Renders the search page templates
     */
    protected function outputIndexSearch()
    {
        $this->output('search/search');
    }

    /**
     * Sets breadcrumbs on the search page
     */
    protected function setBreadcrumbIndexSearch()
    {
        $breadcrumb = array(
            'text' => $this->text('Home'),
            'url' => $this->url('/'));

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Returns a total number of results found
     * @param string $term
     * @return integer
     */
    protected function getTotalResultSearch($term)
    {
        $options = array(
            'status' => 1,
            'count' => true,
            'store_id' => $this->store_id,
            'language' => $this->langcode
        );

        $total = $this->search->search('product_id', $term, $options);
        return (int) $total;
    }

    /**
     * Returns an array of search results
     * @param string $term
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListResultSearch($term, array $limit,
            array $query = array())
    {
        $options = array(
            'status' => 1,
            'language' => $this->langcode,
            'store_id' => $this->store_id,
            'limit' => $limit) + $query;

        $results = $this->search->search('product_id', $term, $options);

        if (empty($results)) {
            return array();
        }

        return $this->prepareProducts($results, $query);
    }

    /**
     * Sets rendered results
     * @param array $products
     */
    protected function setDataResultSearch(array $products)
    {
        $data = array('products' => $products);
        $html = $this->render('product/list', $data);
        $this->setData('results', $html);
    }

    /**
     * Sets rendered navbar
     * @param array $products
     * @param integer $total
     * @param array $query
     */
    protected function setDataNavbarSearch(array $products, $total, array $query)
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

}
