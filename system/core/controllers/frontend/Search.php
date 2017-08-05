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
     * The search term
     * @var string
     */
    protected $data_term = '';

    /**
     * An array of search results
     * @var array
     */
    protected $data_results = array();

    /**
     * @param SearchModel $search
     */
    public function __construct(SearchModel $search)
    {
        parent::__construct();

        $this->search = $search;
    }

    /**
     * Displays the search page
     */
    public function listSearch()
    {
        $this->setTermSearch();

        $this->setTitleListSearch();
        $this->setBreadcrumbListSearch();

        $this->setFilterQueryListSearch();
        $this->setTotalListSearch();
        $this->setPagerLimit($this->settings('catalog_limit', 20));

        $this->setResultsSearch();

        $this->setDataNavbarListSearch();
        $this->setDataProductsListSearch();

        $this->outputListSearch();
    }

    /**
     * Sets results on the search page
     * @return string
     */
    protected function setDataProductsListSearch()
    {
        $this->setData('results', $this->render('product/list', array('products' => $this->data_results)));
    }

    /**
     * Sets the navbar on the search page
     * @return string
     */
    protected function setDataNavbarListSearch()
    {
        $options = array(
            'total' => $this->total,
            'query' => $this->query_filter,
            'view' => $this->query_filter['view'],
            'quantity' => count($this->data_results),
            'sort' => "{$this->query_filter['sort']}-{$this->query_filter['order']}"
        );

        $this->setData('navbar', $this->render('category/navbar', $options));
    }

    /**
     * Sets the current search term
     */
    protected function setTermSearch()
    {
        $this->data_term = $this->getQuery('q', '', 'string');
    }

    /**
     * Sets filter on the search page
     */
    protected function setFilterQueryListSearch()
    {
        $default = array(
            'view' => $this->settings('catalog_view', 'grid'),
            'sort' => $this->settings('catalog_sort', 'price'),
            'order' => $this->settings('catalog_order', 'asc')
        );

        $this->setFilter(array(), $this->getFilterQuery($default));
    }

    /**
     * Sets a total number of results found for the filter conditions
     */
    protected function setTotalListSearch()
    {
        $options = array(
            'status' => 1,
            'count' => true,
            'store_id' => $this->store_id,
            'language' => $this->langcode
        );

        $this->total = (int) $this->search->search('product', $this->data_term, $options);
    }

    /**
     * Sets titles on the search page
     */
    protected function setTitleListSearch()
    {
        $title = $this->text('Search');

        if ($this->data_term !== '') {
            $title = $this->text('Search for «@term»', array('@term' => $this->data_term));
        }

        $this->setTitle($title);
    }

    /**
     * Render and output the search page
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
        $this->setBreadcrumbHome();
    }

    /**
     * Sets an array of search results
     */
    protected function setResultsSearch()
    {
        $options = array(
            'status' => 1,
            'entity' => 'product',
            'limit' => $this->limit,
            'language' => $this->langcode,
            'store_id' => $this->store_id
        );

        $options += $this->query_filter;
        $results = $this->search->search('product', $this->data_term, $options);

        if (!empty($results)) {
            $options['placeholder'] = true;
            $this->data_results = $this->prepareEntityItems($results, $options);
        }
    }

}
