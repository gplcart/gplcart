<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\frontend;

use core\models\Search as ModelsSearch;
use core\controllers\frontend\Controller as FrontendController;

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
    public function listSearch()
    {
        $term = (string) $this->request->get('q', '');
        $max = $this->setting('catalog_limit', 20);

        $filter = array(
            'view' => $this->setting('catalog_view', 'grid'),
            'sort' => $this->setting('catalog_sort', 'price'),
            'order' => $this->setting('catalog_order', 'asc')
        );

        $query = $this->getFilterQuery($filter);
        $total = $this->getTotalSearch($term);
        $limit = $this->setPager($total, $query, $max);

        $products = $this->getResultsSearch($term, $limit, $query);

        $this->setDataResultSearch($products);
        $this->setDataNavbarSearch($products, $total, $query);

        $this->setTitleListSearch($term);
        $this->setBreadcrumbListSearch();
        $this->outputListSearch();
    }

    /**
     * Sets titles on the search page
     * @param string $term
     */
    protected function setTitleListSearch($term)
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
    protected function outputListSearch()
    {
        $this->output('search/search');
    }

    /**
     * Sets breadcrumbs on the search page
     */
    protected function setBreadcrumbListSearch()
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
    protected function getTotalSearch($term)
    {
        $options = array(
            'status' => 1,
            'count' => true,
            'store_id' => $this->store_id,
            'language' => $this->langcode
        );

        $total = $this->search->search('product', $term, $options);
        return (int) $total;
    }

    /**
     * Returns an array of search results
     * @param string $term
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getResultsSearch($term, array $limit,
            array $query = array())
    {
        $options = array(
            'status' => 1,
            'language' => $this->langcode,
            'store_id' => $this->store_id,
            'limit' => $limit) + $query;

        $results = $this->search->search('product', $term, $options);

        if (empty($results)) {
            return array();
        }
        
        $query['placeholder'] = true;
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
