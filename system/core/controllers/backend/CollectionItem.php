<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\backend;

use core\models\Collection as CollectionModel;
use core\models\CollectionItem as CollectionItemModel;
use core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to collection items
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
     * @param CollectionModel $collection
     * @param CollectionItemModel $collection_item
     */
    public function __construct(CollectionModel $collection,
            CollectionItemModel $collection_item)
    {
        parent::__construct();

        $this->collection = $collection;
        $this->collection_item = $collection_item;
    }

    /**
     * Displays the collection items overview page
     * @param integer $collection_id
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
     * Applies an action to the selected collections
     * @return null
     */
    protected function actionCollectionItem()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        // Update weight and returm JSON responce
        if ($action === 'weight' && $this->access('collection_item_edit')) {
            return $this->updateWeight($selected);
        }

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

        return null;
    }

    /**
     * Updates weight of collection items
     * @param array $items
     */
    protected function updateWeight(array $items)
    {
        foreach ($items as $id => $weight) {
            $this->collection_item->update($id, array('weight' => $weight));
        }

        $response = array(
            'success' => $this->text('Collection items have been reordered'));

        $this->response->json($response);
    }

    /**
     * Returns an array of collection items
     * @param array $collection
     * @return array
     */
    protected function getListCollectionItem(array $collection)
    {
        $conditions = array(
            'type' => $collection['type'],
            'collection_id' => $collection['collection_id']
        );

        return $this->collection_item->getItems($conditions);
    }

    /**
     * Sets title on the collection items page
     * @param array $collection
     */
    protected function setTitleListCollectionItem(array $collection)
    {
        $title = $this->text('Items of collection %name', array(
            '%name' => $collection['title']
        ));

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
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/content/collection'),
            'text' => $this->text('Collections')
        );

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
        $handler = $this->getHandlerCollectionItem($collection);
        $weight = $this->collection_item->getNextWeight($collection_id);

        $this->setData('weight', $weight);
        $this->setData('handler', $handler);
        $this->setData('collection', $collection);

        $this->submitCollectionItem($collection);

        $this->setJsEditCollectionItem($collection);
        $this->setTitleEditCollectionItem($collection);
        $this->setBreadcrumbEditCollectionItem($collection);
        $this->outputEditCollectionItem();
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
     * Saves a submitted collection item
     * @param array $collection
     * @return null
     */
    protected function submitCollectionItem(array $collection)
    {
        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('collection_item');
        $this->validateCollectionItem($collection);

        if (!$this->hasErrors('collection_item')) {
            $this->addCollectionItem($collection);
        }

        return null;
    }

    /**
     * Validates a submitted collection item
     * @param array $collection
     */
    protected function validateCollectionItem(array $collection)
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('collection_id', $collection['collection_id']);
        $this->validate('collection_item');
    }

    /**
     * Adds a new item to the collection
     * @param array $collection
     */
    protected function addCollectionItem(array $collection)
    {
        $this->controlAccess('collection_item_add');

        $submitted = $this->getSubmitted();
        $added = $this->collection_item->add($submitted);

        if (empty($added)) {
            $message = $this->text('Collection item has not been added');
            $this->redirect('', $message, 'warning');
        }

        $url = "admin/content/collection-item/{$collection['collection_id']}";
        $message = $this->text('Collection item has been added');
        $this->redirect($url, $message, 'success');
    }

    /**
     * Sets JS on the edit collection item page
     * @param array $collection
     */
    protected function setJsEditCollectionItem(array $collection)
    {
        $this->setJsSettings('collection', $collection);
    }

    /**
     * Sets title on the collection items page
     * @param array $collection
     */
    protected function setTitleEditCollectionItem(array $collection)
    {
        $title = $this->text('Add item to collection %name', array(
            '%name' => $collection['title']
        ));

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the collection items page
     * @param array $collection
     */
    protected function setBreadcrumbEditCollectionItem(array $collection)
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/content/collection'),
            'text' => $this->text('Collections')
        );

        $breadcrumbs[] = array(
            'url' => $this->url("admin/content/collection-item/{$collection['collection_id']}"),
            'text' => $this->text('Items of collection %name', array('%name' => $collection['title']))
        );

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
