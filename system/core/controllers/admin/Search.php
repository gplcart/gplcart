<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\controllers\admin\Controller as BackendController;
use core\models\Job as ModelsJob;
use core\models\Search as ModelsSearch;

/**
 * Handles incoming requests and outputs data related to search functionality
 */
class Search extends BackendController
{

    /**
     * Search model instance
     * @var \core\models\Search $search
     */
    protected $search;

    /**
     * Job model instance
     * @var \core\models\Job $job
     */
    protected $job;

    /**
     * Constructor
     * @param ModelsSearch $search
     * @param ModelsJob $job
     */
    public function __construct(ModelsSearch $search, ModelsJob $job)
    {
        parent::__construct();

        $this->job = $job;
        $this->search = $search;
    }

    /**
     * Displays the admin search page
     */
    public function listSearch()
    {
        $query = $this->getFilterQuery();
        $term = isset($query['q']) ? $query['q'] : '';

        $search_id = (string)$this->request->get('search_id');
        $total = $this->getTotalResultsSearch($search_id, $term);
        $limit = $this->setPager($total, $query);

        $handlers = $this->getHandlersSearch();
        $results = $this->getListResultsSearch($search_id, $term, $limit);

        $this->setData('query', $term);
        $this->setData('results', $results);
        $this->setData('handlers', $handlers);
        $this->setData('search_id', $search_id);

        $this->setTitleListSearch($handlers, $search_id);
        $this->setBreadcrumbListSearch();
        $this->outputListSearch();
    }

    /**
     * Returns a total number of results
     * @param string $search_id
     * @param string $query
     * @return integer
     */
    protected function getTotalResultsSearch($search_id, $query)
    {
        $options = array(
            'count' => true,
            'language' => $this->langcode
        );

        $result = $this->search->search($search_id, $query, $options);
        return (int)$result; // Cast (int), the result can be an array
    }

    /**
     * Returns an array of existing search handlers
     * @return array
     */
    protected function getHandlersSearch()
    {
        return $this->search->getHandlers();
    }

    /**
     * Returns an array of rendered search results
     * @param string $search_id
     * @param array $query
     * @param int $total
     * @return array
     */
    protected function getListResultsSearch($search_id, array $query, $total)
    {
        $options = array(
            'prepare' => true,
            'limit' => $total,
            'language' => $this->langcode,
            'imagestyle' => $this->config('admin_image_style', 2)
        );

        $entityname = preg_replace('/_id$/', '', $search_id);
        $results = $this->search->search($search_id, $query, $options);

        $items = array();
        foreach ($results as $result) {
            $items[] = $this->render("search/results/$entityname", array(
                $entityname => $result
            ));
        }

        return $items;
    }

    /**
     * Sets titles on the admin search page
     * @param array $handlers
     * @param string $search_id
     */
    protected function setTitleListSearch(array $handlers, $search_id)
    {
        if (empty($handlers[$search_id]['name'])) {
            $title = $this->text('Search');
        } else {
            $title = $this->text('Search %type', array(
                '%type' => $handlers[$search_id]['name']
            ));
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the admin search page
     */
    protected function setBreadcrumbListSearch()
    {
        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the admin search page
     */
    protected function outputListSearch()
    {
        $this->output('search/list');
    }

    /**
     * Displays search index page
     */
    public function indexSearch()
    {
        $job = $this->getJob();
        $handlers = $this->getHandlersSearch();

        $this->setData('job', $job);
        $this->setData('handlers', $handlers);

        $this->submitIndexSearch();

        $this->setTitleIndexSearch();
        $this->setBreadcrumbIndexSearch();
        $this->outputIndexSearch();
    }

    /**
     * Processes indexing
     */
    protected function submitIndexSearch()
    {
        if ($this->isPosted('index')) {

            $limit = $this->config('search_index_limit', 50);
            $entity_id = (string)$this->request->post('index');

            $job = array(
                'id' => "index_$entity_id",
                'data' => array('index_limit' => $limit),
                'total' => $this->search->total($entity_id)
            );

            $this->job->submit($job);
        }
    }

    /**
     * Sets titles on the search index form page
     */
    protected function setTitleIndexSearch()
    {
        $this->setTitle($this->text('Search'));
    }

    /**
     * Sets breadcrumbs on the search index form page
     */
    protected function setBreadcrumbIndexSearch()
    {
        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the search index form page
     */
    protected function outputIndexSearch()
    {
        $this->output('tool/search');
    }

}
