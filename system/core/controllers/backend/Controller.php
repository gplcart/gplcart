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

        $this->job = Container::get('gplcart\\core\\models\\Job');
        $this->image = Container::get('gplcart\\core\\models\\Image');

        $this->processCurrentJob();

        $this->hook->fire('construct.controller.backend', $this);
        $this->controlHttpStatus();
    }

    /**
     * Loads the current job from the current URL
     * @return array
     */
    protected function getCurrentJob()
    {
        $id = (string) $this->request->get('job_id');
        return empty($id) ? array() : $this->job->get($id);
    }

    /**
     * Processes the current job
     * @return null
     */
    protected function processCurrentJob()
    {
        $data = $this->getCurrentJob();

        if (empty($data['status'])) {
            return null;
        }

        $cancel = $this->request->get('cancel_job');

        if (!empty($cancel)) {
            $this->job->delete($cancel);
            return null;
        }

        $this->setJsSettings('job', $data);
        $process_job_id = (string) $this->request->get('process_job');

        if ($this->request->isAjax() && $process_job_id == $data['id']) {
            $this->response->json($this->job->process($data));
        }
    }

    /**
     * Returns a rendered job widget
     * @param array $job
     * @return string
     */
    public function renderJob(array $job)
    {
        if (empty($job['status'])) {
            return '';
        }

        $job += array('widget' => 'common/job/widget');
        return $this->render($job['widget'], array('job' => $job));
    }

    /**
     * Set the current job
     */
    protected function setJob()
    {
        $job = $this->getCurrentJob();
        $this->setData('job', $this->renderJob($job));
    }

    /**
     * Returns rendered admin menu
     * @param array $options
     * @return string
     */
    public function menu(array $options = array())
    {
        $items = array();
        foreach ($this->route->getList() as $path => $route) {

            if (strpos($path, 'admin/') !== 0 || empty($route['menu']['admin'])) {
                continue;
            }

            if (isset($route['access']) && !$this->access($route['access'])) {
                continue;
            }

            $items[$path] = array(
                'url' => $this->url($path),
                'depth' => (substr_count($path, '/') - 1),
                'text' => $this->text($route['menu']['admin'])
            );
        }

        ksort($items);

        $options += array('items' => $items);
        return $this->renderMenu($options);
    }

    /**
     * Displays parent admin menu items
     * @todo Output real content
     */
    public function adminSections()
    {
        $this->redirect('admin');
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
        $imagestyle = $this->config('image_style_admin', 2);
        $item['thumb'] = $this->image->url($imagestyle, $item['path']);
    }

    /**
     * Adds full store url for every entity in the array
     * @param array $items
     * @param string $entity
     * @return array
     */
    protected function setEntityUrl(array &$items, $entity)
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
        $data = array('images' => $images, 'name_prefix' => $entity);
        $html = $this->render('common/image/attache', $data);
        $this->setData('attached_images', $html);
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
