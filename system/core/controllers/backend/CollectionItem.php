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
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * The current collection data
     * @var array
     */
    protected $data_collection = array();

    /**
     * The current collection item data
     * @var array
     */
    protected $data_collection_item = array();

    /**
     * @param CollectionModel $collection
     * @param CollectionItemModel $collection_item
     */
    public function __construct(CollectionModel $collection, CollectionItemModel $collection_item)
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
        $this->setCollectionCollection($collection_id);
        $this->actionListCollectionItem();

        $this->setTitleListCollectionItem();
        $this->setBreadcrumbListCollectionItem();
        $this->setFilterListCollectionItem();
        $this->setPagerListCollectionItem();

        $this->setData('collection', $this->data_collection);
        $this->setData('items', $this->getListCollectionItem());

        $this->outputListCollectionItem();
    }

    /**
     * Set pager
     * @return array
     */
    protected function setPagerListCollectionItem()
    {
        $options = $this->query_filter;
        $options['count'] = true;
        $options['collection_id'] = $this->data_collection['collection_id'];

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->collection_item->getList($options)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Sets filter
     */
    protected function setFilterListCollectionItem()
    {
        $this->setFilter();
    }

    /**
     * Sets a collection data
     * @param integer $collection_id
     */
    protected function setCollectionCollection($collection_id)
    {
        $this->data_collection = $this->collection->get($collection_id);

        if (empty($this->data_collection)) {
            $this->outputHttpStatus(404);
        }
    }

    /**
     * Sets a collection item data
     * @param integer $collection_item_id
     */
    protected function setCollectionCollectionItem($collection_item_id)
    {
        if (is_numeric($collection_item_id)) {
            $collection_item = $this->collection_item->get($collection_item_id);
            if (empty($collection_item)) {
                $this->outputHttpStatus(404);
            }

            $this->data_collection_item = $this->prepareCollectionItem($collection_item);
        }
    }

    /**
     * Prepare an array of collection item data
     * @param array $collection_item
     * @return array
     */
    protected function prepareCollectionItem(array $collection_item)
    {
        $conditions = array(
            'collection_item_id' => $collection_item['collection_item_id']);

        $item = $this->collection_item->getItem($conditions);
        $collection_item['title'] = isset($item['title']) ? $item['title'] : '';
        return $collection_item;
    }

    /**
     * Applies an action to the selected collections
     */
    protected function actionListCollectionItem()
    {
        list($selected, $action, $value) = $this->getPostedAction();

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
            $message = $this->text('Updated %num item(s)', array('%num' => $updated));
            $this->setMessage($message, 'success');
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Returns an array of collection items
     * @return array
     */
    protected function getListCollectionItem()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;
        $conditions['collection_id'] = $this->data_collection['collection_id'];

        return $this->collection_item->getItems($conditions);
    }

    /**
     * Sets title on the collection item overview page
     */
    protected function setTitleListCollectionItem()
    {
        $text = $this->text('Items of collection %name', array('%name' => $this->data_collection['title']));
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the collection item overview page
     */
    protected function setBreadcrumbListCollectionItem()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Collections'),
            'url' => $this->url('admin/content/collection')
        );

        $this->setBreadcrumbs($breadcrumbs);
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
     * @param string|int|null $collection_item_id
     */
    public function editCollectionItem($collection_id, $collection_item_id = null)
    {
        $this->setCollectionCollection($collection_id);
        $this->setCollectionCollectionItem($collection_item_id);
        $this->setTitleEditCollectionItem();
        $this->setBreadcrumbEditCollectionItem();

        $this->setData('collection', $this->data_collection);
        $this->setData('collection_item', $this->data_collection_item);
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
        if ($this->isPosted('delete')) {
            $this->deleteCollectionItem();
        } else if ($this->isPosted('save') && $this->validateEditCollectionItem()) {
            if (isset($this->data_collection_item['collection_item_id'])) {
                $this->updateCollectionItem();
            } else {
                $this->addCollectionItem();
            }
        }
    }

    /**
     * Returns an array of handler data for the collection type
     * @return array
     */
    protected function getHandlerCollectionItem()
    {
        $handlers = $this->collection->getHandlers();

        if (empty($handlers[$this->data_collection['type']])) {
            $this->outputHttpStatus(403);
        }

        return $handlers[$this->data_collection['type']];
    }

    /**
     * Validates a submitted collection item
     * @return bool
     */
    protected function validateEditCollectionItem()
    {
        $this->setSubmitted('collection_item');
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_collection_item);
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
            $this->redirect('', $this->text('Collection item has been added'), 'success');
        }

        $this->redirect('', $this->text('Collection item has not been added'), 'warning');
    }

    /**
     * Update a submitted collection item
     */
    protected function updateCollectionItem()
    {
        $this->controlAccess('collection_item_edit');

        if ($this->collection_item->update($this->data_collection_item['collection_item_id'], $this->getSubmitted())) {
            $url = "admin/content/collection-item/{$this->data_collection['collection_id']}";
            $this->redirect($url, $this->text('Collection item has been updated'), 'success');
        }

        $this->redirect('', $this->text('Collection item has not been updated'), 'warning');
    }

    /**
     * Delete a collection item
     */
    protected function deleteCollectionItem()
    {
        $this->controlAccess('collection_item_delete');

        if ($this->collection_item->delete($this->data_collection_item['collection_item_id'])) {
            $url = "admin/content/collection-item/{$this->data_collection['collection_id']}";
            $this->redirect($url, $this->text('Collection item has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Collection item has not been deleted'), 'warning');
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

        if (empty($this->data_collection_item['collection_item_id'])) {
            $text = $this->text('Add item to %name', $vars);
        } else {
            $text = $this->text('Edit item of %name', $vars);
        }

        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the edit collection item page
     */
    protected function setBreadcrumbEditCollectionItem()
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
