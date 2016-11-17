<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\backend;

use core\Container;
use core\helpers\Tool;
use core\Controller as BaseController;

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
     * @var \core\models\Job $job
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
        $this->data['help_summary'] = $this->getHelpSummary();
        $this->data['store_list'] = $this->store->getList();
    }

    /**
     * Sets a batch job from the current URL
     * @return null
     */
    protected function setJobProperties()
    {
        /* @var $job \core\models\Job */
        $this->job = Container::instance('core\\models\\Job');

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

        $this->setJsSettings('job', $this->current_job, -60);
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
                'text' => $this->text($route['menu']['admin']),
                    //'weight' => isset($route['weight']) ? $route['weight'] : 0
            );

            $array[$path] = $data;
        }

        //Tool::sortWeight($array);

        ksort($array);
        return $array;
    }

    /**
     * Returns a rendered help link depending on the current URL
     * @return string
     */
    public function getHelpSummary()
    {
        $folder = $this->langcode ? $this->langcode : 'en';
        $directory = GC_HELP_DIR . "/$folder";

        $file = Tool::contexUrltFile($directory, 'php', $this->path);

        if (empty($file)) {
            return '';
        }

        $content = $this->render($file['path'], array(), true);
        $parts = $this->explodeText($content);

        if (empty($parts)) {
            return '';
        }

        $data = array('content' => array_map('trim', $parts), 'file' => $file);
        return $this->render('help/summary', $data);
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
