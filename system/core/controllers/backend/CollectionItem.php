<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Collection as CollectionModel,
    gplcart\core\models\CollectionItem as CollectionItemModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to collection items
 */
class CollectionItem extends BackendController
{

    /**
     * Collection model instance
     * @var \gplcart\core\models\Collection $collection
     */
    protected $collection;

    /**
     * Collection item model instance
     * @var \gplcart\core\models\CollectionItem $collection
     */
    protected $collection_item;

    /**
     * The current collection data
     * @var array
     */
    protected $data_collection = array();

    /**
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
     * Displays the collection item overview page
     * @param integer $collection_id
     */
    public function listCollectionItem($collection_id)
    {
        $this->setCollectionCollectionItem($collection_id);
        $this->actionListCollectionItem();

        $this->setTitleListCollectionItem();
        $this->setBreadcrumbListCollectionItem();

        $this->setData('collection', $this->data_collection);
        $this->setData('items', $this->getListCollectionItem());

        $this->outputListCollectionItem();
    }

    /**
     * Sets a collection data
     * @param integer $collection_id
     */
    protected function setCollectionCollectionItem($collection_id)
    {
        if (is_numeric($collection_id)) {
            $this->data_collection = $this->collection->get($collection_id);
            if (empty($this->data_collection)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Applies an action to the selected collections
     */
    protected function actionListCollectionItem()
    {
        list($selected, $action, $value) = $this->getPostedAction();

        if (empty($action)) {
            return null;
        }

        if ($action === 'weight' && $this->access('collection_item_edit')) {
            $this->updateWeightCollectionItem($selected);
            return null;
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
            $message = $this->text('Updated %num items', array('%num' => $updated));
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num items', array('%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Updates weight of collection items
     * @param array $items
     */
    protected function updateWeightCollectionItem(array $items)
    {
        foreach ($items as $id => $weight) {
            $this->collection_item->update($id, array('weight' => $weight));
        }

        $response = array('success' => $this->text('Items have been reordered'));
        $this->response->json($response);
    }

    /**
     * Returns an array of collection items
     * @return array
     */
    protected function getListCollectionItem()
    {
        $conditions = array(
            'type' => $this->data_collection['type'],
            'collection_id' => $this->data_collection['collection_id']
        );

        return $this->collection_item->getItems($conditions);
    }

    /**
     * Sets title on the collection item overview page
     */
    protected function setTitleListCollectionItem()
    {
        $vars = array('%name' => $this->data_collection['title']);
        $this->setTitle($this->text('Items of collection %name', $vars));
    }

    /**
     * Sets breadcrumbs on the collection item overview page
     */
    protected function setBreadcrumbListCollectionItem()
    {
        $this->setBreadcrumbHome();

        $breadcrumb = array(
            'text' => $this->text('Collections'),
            'url' => $this->url('admin/content/collection')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render an output the collection item overview page
     */
    protected function outputListCollectionItem()
    {
        $this->output('content/collection/item/list');
    }

    /**
     * Displays the edit collection item form
     * @param integer $collection_id
     */
    public function editCollectionItem($collection_id)
    {
        $this->setCollectionCollectionItem($collection_id);

        $this->setTitleEditCollectionItem();
        $this->setBreadcrumbEditCollectionItem();

        $this->setData('collection', $this->data_collection);
        $this->setData('handler', $this->getHandlerCollectionItem());
        $this->setData('weight', $this->collection_item->getNextWeight($collection_id));

        $this->submitEditCollectionItem();

        $this->setJsEditCollectionItem();
        $this->outputEditCollectionItem();
    }

    /**
     * Saves a submitted collection item
     */
    protected function submitEditCollectionItem()
    {
        if ($this->isPosted('save') && $this->validateEditCollectionItem()) {
            $this->addCollectionItem();
        }
    }

    /**
     * Returns an array of handler data for the collection type
     * @return array
     */
    protected function getHandlerCollectionItem()
    {
        $handlers = $this->collection->getHandlers();
        $type = $this->data_collection['type'];

        if (empty($handlers[$type])) {
            $this->outputHttpStatus(403);
        }

        return $handlers[$type];
    }

    /**
     * Validates a submitted collection item
     * @return bool
     */
    protected function validateEditCollectionItem()
    {
        $this->setSubmitted('collection_item');
        $this->setSubmittedBool('status');
        $this->setSubmitted('collection_id', $this->data_collection['collection_id']);

        $this->validateComponent('collection_item');

        return !$this->hasErrors();
    }

    /**
     * Adds a new collection item
     */
    protected function addCollectionItem()
    {
        $this->controlAccess('collection_item_add');

        if ($this->collection_item->add($this->getSubmitted())) {
            $url = "admin/content/collection-item/{$this->data_collection['collection_id']}";
            $this->redirect($url, $this->text('Collection item has been added'), 'success');
        }

        $this->redirect('', $this->text('Collection item has not been added'), 'warning');
    }

    /**
     * Sets JS on the edit collection item page
     */
    protected function setJsEditCollectionItem()
    {
        $this->setJsSettings('collection', $this->data_collection);
    }

    /**
     * Sets title on the edit collection item page
     */
    protected function setTitleEditCollectionItem()
    {
        $vars = array('%name' => $this->data_collection['title']);
        $this->setTitle($this->text('Add item to collection %name', $vars));
    }

    /**
     * Sets breadcrumbs on the edit collection item page
     */
    protected function setBreadcrumbEditCollectionItem()
    {
        $this->setBreadcrumbHome();

        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin/content/collection'),
            'text' => $this->text('Collections')
        );

        $breadcrumbs[] = array(
            'url' => $this->url("admin/content/collection-item/{$this->data_collection['collection_id']}"),
            'text' => $this->text('Items of collection %name', array('%name' => $this->data_collection['title']))
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the edit collection item page
     */
    protected function outputEditCollectionItem()
    {
        $this->output('content/collection/item/edit');
    }

}
