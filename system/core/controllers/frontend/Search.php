<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Search as SearchModel;

/**
 * Handles incoming requests and outputs data related to search functionality
 */
class Search extends Controller
{

    /**
     * Search model instance
     * @var \gplcart\core\models\Search $search
     */
    protected $search;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

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
        $this->setPagerListSearch();
        $this->setResultsSearch();
        $this->setDataNavbarListSearch();
        $this->setDataProductsListSearch();

        $this->outputListSearch();
    }

    /**
     * Sets results on the search page
     */
    protected function setDataProductsListSearch()
    {
        $this->setData('results', $this->render('product/list', array('products' => $this->data_results)));
    }

    /**
     * Sets the navbar on the search page
     */
    protected function setDataNavbarListSearch()
    {
        $options = array(
            'total' => $this->data_limit,
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
        $this->data_term = $this->getQuery('q', '');
    }

    /**
     * Sets filter on the search page
     */
    protected function setFilterQueryListSearch()
    {
        $default = array(
            'view' => $this->configTheme('catalog_view', 'grid'),
            'sort' => $this->configTheme('catalog_sort', 'price'),
            'order' => $this->configTheme('catalog_order', 'asc')
        );

        $this->setFilter(array(), $this->getFilterQuery($default));
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListSearch()
    {
        $options = array(
            'status' => 1,
            'count' => true,
            'store_id' => $this->store_id,
            'language' => $this->langcode
        );

        $pager = array(
            'query' => $this->query_filter,
            'limit' => $this->configTheme('catalog_limit', 20),
            'total' => (int) $this->search->search('product', $this->data_term, $options)
        );

        return $this->data_limit = $this->setPager($pager);
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
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Sets an array of search results
     */
    protected function setResultsSearch()
    {
        $options = array(
            'status' => 1,
            'entity' => 'product',
            'limit' => $this->data_limit,
            'language' => $this->langcode,
            'store_id' => $this->store_id
        );

        $options += $this->query_filter;

        $this->data_results = $this->search->search('product', $this->data_term, $options);

        if (!empty($this->data_results)) {

            settype($this->data_results, 'array');

            $options['placeholder'] = true;
            $this->prepareEntityItems($this->data_results, $options);
        }
    }

}
