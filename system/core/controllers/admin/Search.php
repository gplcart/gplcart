<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Job as ModelsJob;
use core\models\Search as ModelsSearch;

/**
 * Handles incoming requests and outputs data related to search functionality
 */
class Search extends Controller
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
    public function search()
    {
        $query = $this->getFilterQuery();
        $term = isset($query['q']) ? $query['q'] : '';

        $search_id = (string) $this->request->get('search_id');
        $total = $this->setPager($this->getTotalResults($search_id, $term), $query);

        $this->data['query'] = $term;
        $this->data['search_id'] = $search_id;
        $this->data['handlers'] = $handlers = $this->getHandlers();
        $this->data['results'] = $this->getResults($search_id, $term, $total);

        $this->setTitleSearch($handlers, $search_id);
        $this->setBreadcrumbSearch();
        $this->outputSearch();
    }

    /**
     * Displays search index page
     */
    public function index()
    {
        $this->data['job'] = $this->getJob();
        $this->data['handlers'] = $this->getHandlers();

        $entity_id = (string) $this->request->post('index');

        if (!empty($entity_id)) {
            $this->submit($entity_id);
        }

        $this->setTitleIndex();
        $this->setBreadcrumbIndex();
        $this->outputIndex();
    }

    /**
     * Returns a total number of results
     * @param string $search_id
     * @param string $query
     * @return integer
     */
    protected function getTotalResults($search_id, $query)
    {
        $total = $this->search->search($search_id, $query, array(
            'count' => true,
            'language' => $this->langcode));

        return (int) $total;
    }

    /**
     * Returns an array of rendered search results
     * @param string $search_id
     * @param string $query
     * @return array
     */
    protected function getResults($search_id, $query, $total)
    {
        $results = $this->search->search($search_id, $query, array(
            'language' => $this->langcode,
            'prepare' => true,
            'imagestyle' => $this->config->get('admin_image_style', 2),
            'limit' => $total));

        $entityname = preg_replace('/_id$/', '', $search_id);

        $items = array();
        foreach ($results as $result) {
            $items[] = $this->render("search/results/$entityname", array($entityname => $result));
        }

        return $items;
    }

    /**
     * Sets titles on the admin search page
     */
    protected function setTitleSearch($handlers, $search_id)
    {
        if (!empty($handlers[$search_id]['name'])) {
            $title = $this->text('Search %type', array('%type' => $handlers[$search_id]['name']));
        } else {
            $title = $this->text('Search');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the admin search page
     */
    protected function setBreadcrumbSearch()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));
    }

    /**
     * Renders the admin search page
     */
    protected function outputSearch()
    {
        $this->output('search/list');
    }

    /**
     * Processes indexing
     * @param string $entity_id
     */
    protected function submit($entity_id)
    {

        $job = array(
            'id' => "index_$entity_id",
            'total' => $this->search->total($entity_id),
            'data' => array('index_limit' => $this->config->get('search_index_limit', 50)),
        );

        $this->job->submit($job);
    }

    /**
     * Returns an array of existing search handlers
     * @return array
     */
    protected function getHandlers()
    {
        return $this->search->getHandlers();
    }

    /**
     * Sets titles on the search index form page
     */
    protected function setTitleIndex()
    {
        $this->setTitle($this->text('Search'));
    }

    /**
     * Sets breadcrumbs on the search index form page
     */
    protected function setBreadcrumbIndex()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
    }

    /**
     * Renders the search index form page
     */
    protected function outputIndex()
    {
        $this->output('tool/search');
    }

}
