<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\Container,
    gplcart\core\Controller as BaseController;

/**
 * Contents methods related to admin backend
 */
class Controller extends BaseController
{

    /**
     * Job model instance
     * @var \gplcart\core\models\Job $job
     */
    protected $job;

    /**
     * Image model instance
     * @var \gplcart\core\models\Image $image
     */
    protected $image;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setInstancePropertiesBackend();
        $this->processCurrentJob();
        $this->setJsCron();
        $this->setDefaultDataBackend();

        $this->hook->attach('construct.controller.backend', $this);
        $this->controlHttpStatus();
    }

    /**
     * Sets class instances
     */
    protected function setInstancePropertiesBackend()
    {
        $this->job = Container::get('gplcart\\core\\models\\Job');
        $this->image = Container::get('gplcart\\core\\models\\Image');
    }

    /**
     * Sets default variables for backend templates
     */
    protected function setDefaultDataBackend()
    {
        $this->data['_job'] = $this->renderJob();
        $this->data['_menu'] = $this->renderAdminMenu();
        $this->data['_stores'] = $this->store->getList(array('status' => 1));
    }

    /**
     * Adds JS code to call cron URL
     */
    protected function setJsCron()
    {
        $key = $this->config('cron_key', '');
        $last_run = (int) $this->config('cron_last_run', 0);
        $interval = (int) $this->config('cron_interval', 86400);

        if (!empty($interval) && (GC_TIME - $last_run) > $interval) {
            $url = $this->url('cron', array('key' => $key));
            $js = "\$(function(){\$.get('$url', function(data){});});";
            $this->setJs($js, array('position' => 'bottom'));
        }
    }

    /**
     * Processes the current job
     */
    protected function processCurrentJob()
    {
        $cancel_job_id = $this->getQuery('cancel_job', '', 'string');

        if (!empty($cancel_job_id)) {
            $this->job->delete($cancel_job_id);
            return null;
        }

        $job_id = $this->getQuery('job_id', '', 'string');
        $job = $this->job->get($job_id);

        if (empty($job['status'])) {
            return null;
        }

        $this->setJsSettings('job', $job);

        if ($this->getQuery('process_job') === $job['id'] && $this->isAjax()) {
            $this->response->json($this->job->process($job));
        }
    }

    /**
     * Returns the rendered job widget
     * @param array|null $job
     * @return string
     */
    public function renderJob($job = null)
    {
        if (!isset($job)) {
            $job_id = $this->getQuery('job_id', '', 'string');
            $job = $this->job->get($job_id);
        }

        if (empty($job['status'])) {
            return '';
        }

        $job += array('widget' => 'common/job');
        return $this->render($job['widget'], array('job' => $job));
    }

    /**
     * Returns the rendered admin menu
     * @param array $options
     * @return string
     */
    public function renderAdminMenu($parent = 'admin', array $options = array())
    {
        if (!$this->access('admin')) {
            return '';
        }

        $items = array();
        foreach ($this->route->getList() as $path => $route) {

            if (strpos($path, "$parent/") !== 0 || empty($route['menu']['admin'])) {
                continue;
            }
            if (isset($route['access']) && !$this->access($route['access'])) {
                continue;
            }

            $items[$path] = array(
                'url' => $this->url($path),
                'text' => $this->text($route['menu']['admin']),
                'depth' => substr_count(substr($path, strlen("$parent/")), '/'),
            );
        }

        ksort($items);
        $options += array('items' => $items);
        return $this->renderMenu($options);
    }

    /**
     * Adds thumb to an array of files
     * @param array $items
     */
    protected function attachThumbs(&$items)
    {
        foreach ($items as &$item) {
            $this->attachThumb($item);
        }
    }

    /**
     * Adds a single thumb
     * @param array $item
     */
    protected function attachThumb(&$item)
    {
        $imagestyle = $this->config('image_style_ui', 2);
        $item['thumb'] = $this->image->url($imagestyle, $item['path']);
    }

    /**
     * Adds full store url for every entity in the array
     * @param array $items
     * @param string $entity
     * @return array
     */
    protected function attachEntityUrl(array &$items, $entity)
    {
        $stores = $this->store->getList();
        foreach ($items as &$item) {
            $item['url'] = '';
            if (isset($stores[$item['store_id']])) {
                $url = $this->store->url($stores[$item['store_id']]);
                $item['url'] = "$url/$entity/{$item["{$entity}_id"]}";
            }
        }
        return $items;
    }

    /**
     * Adds rendered images to the edit entity form
     * @param array $images
     * @param string $entity
     */
    protected function setDataAttachedImages(array $images, $entity)
    {
        $data = array(
            'images' => $images,
            'name_prefix' => $entity,
            'languages' => $this->language->getList()
        );
        $this->setData('attached_images', $this->render('common/image', $data));
    }

    /**
     * Deletes submitted image file IDs
     * @param array $data
     * @param string $entity
     */
    protected function deleteImages(array $data, $entity)
    {
        $file_ids = $this->request->post('delete_images', array(), true, 'array');

        if (empty($file_ids) || empty($data["{$entity}_id"])) {
            return null;
        }

        $options = array(
            'file_id' => $file_ids,
            'file_type' => 'image',
            'id_key' => "{$entity}_id",
            'id_value' => $data["{$entity}_id"]
        );

        return $this->image->deleteMultiple($options);
    }

    /**
     * Set a single breadcrumb item that points to the dashboard
     */
    protected function setBreadcrumbBackend()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

}
