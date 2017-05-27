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

        $this->setBackendInstanceProperties();

        $this->processCurrentJob();
        $this->setBackendDefaultData();

        $this->hook->fire('construct.controller.backend', $this);
        $this->controlHttpStatus();
    }

    /**
     * Sets class instances
     */
    protected function setBackendInstanceProperties()
    {
        $this->job = Container::get('gplcart\\core\\models\\Job');
        $this->image = Container::get('gplcart\\core\\models\\Image');
    }

    /**
     * Sets default variables for backend templates
     */
    protected function setBackendDefaultData()
    {
        $this->data['_job'] = $this->renderJob();
        $this->data['_menu'] = $this->renderAdminMenu();
        $this->data['_stores'] = $this->store->getList(array('status' => 1));
    }

    /**
     * Processes the current job
     */
    protected function processCurrentJob()
    {
        $cancel_job_id = $this->request->get('cancel_job');

        if (!empty($cancel_job_id)) {
            $this->job->delete($cancel_job_id);
            return null;
        }

        $job = $this->job->get($this->request->get('job_id'));

        if (empty($job['status'])) {
            return null;
        }

        $this->setJsSettings('job', $job);

        if ($this->request->get('process_job') === $job['id'] && $this->isAjax()) {
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
            $job = $this->job->get($this->request->get('job_id'));
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
        $file_ids = $this->request->post('delete_images', array());

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

}
