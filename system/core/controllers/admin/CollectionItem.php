<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\models\Collection as ModelsCollection;
use core\models\CollectionItem as ModelsCollectionItem;
use core\controllers\admin\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to collections
 */
class CollectionItem extends BackendController
{

    /**
     * Collection model instance
     * @var \core\models\Collection $collection
     */
    protected $collection;

    /**
     * Collection item model instance
     * @var \core\models\CollectionItem $collection
     */
    protected $collection_item;

    /**
     * Constructor
     * @param ModelsCollection $collection
     * @param ModelsCollectionItem $collection_item
     */
    public function __construct(ModelsCollection $collection,
            ModelsCollectionItem $collection_item)
    {
        parent::__construct();

        $this->collection = $collection;
        $this->collection_item = $collection_item;
    }

    /**
     * Displays the collection items overview page
     */
    public function listCollectionItem($collection_id)
    {
        $collection = $this->getCollection($collection_id);

        $this->actionCollectionItem();

        $items = $this->getListCollectionItem($collection);

        $this->setData('items', $items);
        $this->setData('collection', $collection);

        $this->setTitleListCollectionItem($collection);
        $this->setBreadcrumbListCollectionItem();
        $this->outputListCollectionItem();
    }

    /**
     * Applies an action to the selected collections
     */
    protected function actionCollectionItem()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        $deleted = $updated = 0;

        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('collection_item_edit')) {
                $updated += (int) $this->collection_item->update($id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('collection_item_delete')) {
                $deleted += (int) $this->collection_item->delete($id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Collection items have been updated');
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Collection items have been deleted');
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Returns an array of collection items
     * @param array $collection
     */
    protected function getListCollectionItem(array $collection)
    {

        $items = $this->collection_item->getEntities($collection);

        return $items;
    }

    /**
     * Returns an collection
     * @param integer $collection_id
     * @return array
     */
    protected function getCollection($collection_id)
    {
        if (!is_numeric($collection_id)) {
            return array();
        }

        $collection = $this->collection->get($collection_id);

        if (empty($collection)) {
            $this->outputError(404);
        }

        return $collection;
    }

    /**
     * Sets title on the collection items page
     */
    protected function setTitleListCollectionItem(array $collection)
    {

        $title = $this->text('Items of collection %name', array(
            '%name' => $collection['title']));

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the collection items page
     */
    protected function setBreadcrumbListCollectionItem()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard'));

        $breadcrumbs[] = array(
            'url' => $this->url('admin/content/collection'),
            'text' => $this->text('Collections'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the collection items page
     */
    protected function outputListCollectionItem()
    {
        $this->output('content/collection/item/list');
    }

    /**
     * Displays add collection item form
     * @param integer $collection_id
     */
    public function editCollectionItem($collection_id)
    {
        $collection = $this->getCollection($collection_id);
        $this->outputEntitiesCollectionItem($collection);
        $handler = $this->getHandlerCollectionItem($collection);

        $this->setData('handler', $handler);
        $this->setData('collection', $collection);
        
        $this->submitCollectionItem($collection);

        $this->setTitleEditCollectionItem($collection);
        $this->setBreadcrumbEditCollectionItem($collection);
        $this->outputEditCollectionItem();
    }
    
    /**
     * Saves a submitted collection item
     * @param array $collection
     */
    protected function submitCollectionItem(array $collection)
    {
        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('collection_item');
        $this->validateCollectionItem();

        if (!$this->hasErrors('collection_item')) {
            return;
        }

        $this->addCollectionItem($collection);
    }

    /**
     * Validates a submitted collection item
     * @param array $collection
     */
    protected function validateCollectionItem(array $collection){
        
    }
    
    /**
     * Adds a new item to the collection
     * @param array $collection
     */
    protected function addCollectionItem(array $collection){
        
    }

    /**
     * Outputs an AJAX string with autocomplete suggestions
     * @param array $collection
     */
    protected function outputEntitiesCollectionItem(array $collection)
    {
        $title = (string) $this->request->post('term', '', 'raw');

        if ($title !== '') {
            $max = $this->config('admin_autocomplete_limit', 10);
            $options = array('title' => $title, 'limit' => array(0, $max));
            $entities = $this->collection_item->getSuggestions($collection, $options);
            $this->response->json($entities);
        }
    }

    /**
     * Returns an array of handler data for the collection type
     * @param array $collection
     * @return array
     */
    protected function getHandlerCollectionItem(array $collection)
    {
        $handlers = $this->collection->getHandlers();

        if (empty($handlers[$collection['type']])) {
            $this->outputError(403);
        }

        return $handlers[$collection['type']];
    }

    /**
     * Sets title on the collection items page
     */
    protected function setTitleEditCollectionItem(array $collection)
    {
        $title = $this->text('Add item to collection %name', array(
            '%name' => $collection['title']));
        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the collection items page
     */
    protected function setBreadcrumbEditCollectionItem(array $collection)
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard'));

        $breadcrumbs[] = array(
            'url' => $this->url('admin/content/collection'),
            'text' => $this->text('Collections'));

        $breadcrumbs[] = array(
            'url' => $this->url("admin/content/collection-item/{$collection['collection_id']}"),
            'text' => $this->text('Items of collection %name', array('%name' => $collection['title'])));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the collection items page
     */
    protected function outputEditCollectionItem()
    {
        $this->output('content/collection/item/edit');
    }

}
