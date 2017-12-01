<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\Controller as BaseController;
use gplcart\core\traits\Job as JobTrait,
    gplcart\core\traits\Item as ItemTrait,
    gplcart\core\traits\ItemPrice as ItemPriceTrait,
    gplcart\core\traits\Widget as WidgetTrait;

/**
 * Contents methods related to admin backend
 */
class Controller extends BaseController
{

    use ItemTrait,
        WidgetTrait,
        JobTrait,
        ItemPriceTrait;

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

        $this->setInstancesBackend();
        $this->setJob($this, $this->job);
        $this->setCron();
        $this->setDataBackend();
        $this->getPostedAction();

        $this->hook->attach('construct.controller.backend', $this);
        $this->controlHttpStatus();
    }

    /**
     * Used by traits
     * @return $this
     */
    protected function getController()
    {
        return $this;
    }

    /**
     * Sets default class instances
     */
    protected function setInstancesBackend()
    {
        foreach (array('job', 'help', 'bookmark') as $class) {
            $this->{$class} = $this->getInstance("gplcart\\core\\models\\$class");
        }
    }

    /**
     * Sets default variables for backend templates
     */
    protected function setDataBackend()
    {
        $this->data['_stores'] = (array) $this->store->getList();
        $this->data['_job'] = $this->getWidgetJob($this, $this->job);
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
        $last_run = (int) $this->config('cron_last_run', 0);
        $interval = (int) $this->config('cron_interval', 24 * 60 * 60);

        if (!empty($interval) && (GC_TIME - $last_run) > $interval) {
            $key = $this->config('cron_key', '');
            $url = $this->url('cron', array('key' => $key));
            $js = "\$(function(){\$.get('$url', function(data){});});";
            $this->setJs($js, array('position' => 'bottom'));
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

    /**
     * 
     * @param array $product_ids
     * @param array $options
     * @return type
     */
    protected function prepareProductPicker(array $product_ids, array $options = array())
    {
        $products = array();
        if (!empty($product_ids)) {
            $products = (array) $this->product->getList(array('product_id' => $product_ids));
        }

        $options += array(
            'entity' => 'product',
            'entity_id' => $product_ids,
            'template_item' => 'backend|content/product/suggestion'
        );

        foreach ($products as &$product) {
            $this->setItemThumb($product, $this->image, $options);
            $this->setItemPriceFormatted($product, $this->price);
            $this->setItemRendered($product, array('item' => $product), $options);
        }

        return $products;
    }

}
