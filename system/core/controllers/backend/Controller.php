<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\Controller as BaseController;
use gplcart\core\traits\Item as ItemTrait;
use gplcart\core\traits\ItemPrice as ItemPriceTrait;
use gplcart\core\traits\Job as JobTrait;
use gplcart\core\traits\Widget as WidgetTrait;

/**
 * Contents methods related to admin backend
 */
class Controller extends BaseController
{

    use JobTrait, ItemTrait, WidgetTrait, ItemPriceTrait;

    /**
     * Job model instance
     * @var \gplcart\core\models\Job $job
     */
    protected $job;

    /**
     * Help model instance
     * @var \gplcart\core\models\Help $help
     */
    protected $help;

    /**
     * Bookmark model instance
     * @var \gplcart\core\models\Bookmark $bookmark
     */
    protected $bookmark;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setBackendInstances();
        $this->setJob($this->job);
        $this->setCron();
        $this->setBackendData();

        $this->hook->attach('construct.controller.backend', $this);
        $this->controlHttpStatus();
    }

    /**
     * Sets default class instances
     */
    protected function setBackendInstances()
    {
        $this->job = $this->getInstance('gplcart\\core\\models\\Job');
        $this->help = $this->getInstance('gplcart\\core\\models\\Help');
        $this->bookmark = $this->getInstance('gplcart\\core\\models\\Bookmark');
    }

    /**
     * Sets default variables for backend templates
     */
    protected function setBackendData()
    {
        $this->data['_job'] = $this->getWidgetJob($this->job);
        $this->data['_stores'] = (array) $this->store->getList();
        $this->data['_menu'] = $this->getWidgetAdminMenu($this->route);
        $this->data['_help'] = $this->help->getByPattern($this->current_route['simple_pattern'], $this->langcode);

        $bookmarks = $this->bookmark->getList(array('user_id' => $this->uid));

        $this->data['_is_bookmarked'] = isset($bookmarks[$this->path]);
        $this->data['_bookmarks'] = array_splice($bookmarks, 0, $this->config('bookmark_limit', 5));
    }

    /**
     * Set up self-executing CRON
     */
    protected function setCron()
    {
        $interval = (int) $this->config('cron_interval', 24 * 60 * 60);

        if (!empty($interval) && (GC_TIME - $this->config('cron_last_run', 0)) > $interval) {
            $url = $this->url('cron', array('key' => $this->config('cron_key', '')));
            $this->setJs("\$(function(){\$.get('$url', function(data){});});", array('position' => 'bottom'));
        }
    }

    /**
     * Returns an array of submitted bulk action
     * @param bool $message
     * @return array
     */
    protected function getPostedAction($message = true)
    {
        $action = $this->getPosted('action', array(), true, 'array');

        if (!empty($action)) {

            if (empty($action['name'])) {
                $error = $this->text('An error occurred');
            } else if (empty($action['items'])) {
                $error = $this->text('Please select at least one item');
            } else {
                $parts = explode('|', $action['name'], 2);
                return array($action['items'], $parts[0], isset($parts[1]) ? $parts[1] : null);
            }

            if (isset($error) && $message) {
                $this->setMessage($error, 'warning');
            }
        }

        return array(array(), null, null);
    }

}
