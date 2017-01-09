<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\Container;
use gplcart\core\Controller as BaseController;

/**
 * Contents specific to the backend methods
 */
class Controller extends BaseController
{

    /**
     * Current job
     * @var array
     */
    protected $current_job = array();

    /**
     * Job model instance
     * @var \gplcart\core\models\Job $job
     */
    protected $job;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setJobProperties();
        $this->processCurrentJob();
        $this->setDataFrontend();

        $this->hook->fire('init.backend', $this);
    }

    /**
     * Sets template data
     */
    protected function setDataFrontend()
    {
        $this->data['admin_menu'] = $this->getAdminMenu();
        $this->data['store_list'] = $this->store->getList();
    }

    /**
     * Sets a batch job from the current URL
     * @return null
     */
    protected function setJobProperties()
    {
        $this->job = Container::instance('gplcart\\core\\models\\Job');

        $job_id = (string) $this->request->get('job_id');

        if (!empty($job_id)) {
            $this->current_job = $this->job->get($job_id);
        }
    }

    /**
     * Processes the current job
     * @return null
     */
    protected function processCurrentJob()
    {
        if (empty($this->current_job['status'])) {
            return null;
        }

        $this->setJsSettings('job', $this->current_job);
        $process_job_id = (string) $this->request->get('process_job');

        if ($this->request->isAjax() && $process_job_id == $this->current_job['id']) {
            $response = $this->job->process($this->current_job);
            $this->response->json($response);
        }

        return null;
    }

    /**
     * Submits a new job
     * @param array $job
     */
    protected function setJob(array $job)
    {
        $this->job->delete($job['id']);

        if (!empty($job['data']['operation']['log']['errors'])) {
            // create an empty error log file
            file_put_contents($job['data']['operation']['log']['errors'], '');
        }

        $this->job->set($job);
        $this->url->redirect('', array('job_id' => $job['id']));
    }

    /**
     * Returns rendered admin menu
     * @return string
     */
    public function getAdminMenu()
    {
        $items = $this->getAdminMenuArray();
        return $this->render('common/menu', array('items' => $items));
    }

    /**
     * Returns an array of admin menu items
     * @return array
     */
    protected function getAdminMenuArray()
    {
        $routes = $this->route->getList();

        $array = array();
        foreach ($routes as $path => $route) {

            // Exclude non-admin routes
            if (0 !== strpos($path, 'admin/')) {
                continue;
            }

            // Exclude hidden items
            if (empty($route['menu']['admin'])) {
                continue;
            }

            // Check access
            if (isset($route['access']) && !$this->access($route['access'])) {
                continue;
            }

            $data = array(
                'url' => $this->url($path),
                'depth' => (substr_count($path, '/') - 1),
                'text' => $this->text($route['menu']['admin'])
            );

            $array[$path] = $data;
        }

        ksort($array);
        return $array;
    }

    /**
     * Displays nested admin categories
     */
    public function adminSections()
    {
        $this->redirect('admin'); // TODO: replace with real content
    }

    /**
     * Returns a rendered job widget
     * @return string
     */
    public function getJob()
    {
        if (empty($this->current_job['status'])) {
            return '';
        }

        if (!empty($this->current_job['widget'])) {
            return $this->render($this->current_job['widget'], array('job' => $this->current_job));
        }

        return $this->render('common/job/widget', array('job' => $this->current_job));
    }

}
