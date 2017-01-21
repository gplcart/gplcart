<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Search as SearchModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to search functionality
 */
class Search extends FrontendController
{

    /**
     * Search model instance
     * @var \gplcart\core\models\Search $search
     */
    protected $search;

    /**
     * The current search term
     * @var string
     */
    protected $data_search = '';

    /**
     * Constructor
     * @param SearchModel $search
     */
    public function __construct(SearchModel $search)
    {
        parent::__construct();

        $this->search = $search;
    }

    /**
     * Displays search results page
     */
    public function listSearch()
    {
        $this->setSearch();

        $this->setTitleListSearch();
        $this->setBreadcrumbListSearch();

        $total = $this->getTotalSearch();
        $max = $this->settings('catalog_limit', 20);
        $query = $this->getFilterQueryListSearch();
        $limit = $this->setPager($total, $query, $max);

        $products = $this->getResultsSearch($limit, $query);

        $this->setDataResultSearch($products);
        $this->setDataNavbarSearch($products, $total, $query);

        $this->outputListSearch();
    }

    /**
     * Returns the current search term
     * @return string
     */
    protected function setSearch()
    {
        $term = (string) $this->request->get('q', '');

        $this->data_search = $term;
        return $term;
    }

    /**
     * Returns an array of parameters used for sorting and filtering
     * @return array
     */
    protected function getFilterQueryListSearch()
    {
        $filter = array(
            'view' => $this->settings('catalog_view', 'grid'),
            'sort' => $this->settings('catalog_sort', 'price'),
            'order' => $this->settings('catalog_order', 'asc')
        );

        return $this->getFilterQuery($filter);
    }

    /**
     * Sets titles on the search page
     */
    protected function setTitleListSearch()
    {
        $title = $this->text('Search');

        if ($this->data_search !== '') {
            $title = $this->text('Search for «@term»', array('@term' => $this->data_search));
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
     * @return integer
     */
    protected function getTotalSearch()
    {
        $options = array(
            'status' => 1,
            'count' => true,
            'store_id' => $this->store_id,
            'language' => $this->langcode
        );

        return (int) $this->search->search('product', $this->data_search, $options);
    }

    /**
     * Returns an array of search results
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getResultsSearch(array $limit, array $query = array())
    {
        $options = array(
            'status' => 1,
            'limit' => $limit,
            'language' => $this->langcode,
            'store_id' => $this->store_id
        );

        $options += $query;
        $results = $this->search->search('product', $this->data_search, $options);

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
