<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Collection as CollectionModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to collections
 */
class Collection extends BackendController
{

    /**
     * Collection model instance
     * @var \gplcart\core\models\Collection $collection
     */
    protected $collection;

    /**
     * The current collection
     * @var array
     */
    protected $data_collection = array();

    /**
     * @param CollectionModel $collection
     */
    public function __construct(CollectionModel $collection)
    {
        parent::__construct();

        $this->collection = $collection;
    }

    /**
     * Displays the collection overview page
     */
    public function listCollection()
    {
        $this->actionListCollection();

        $this->setTitleListCollection();
        $this->setBreadcrumbListCollection();

        $this->setFilterListCollection();
        $this->setTotalListCollection();
        $this->setPagerLimit();

        $this->setData('collections', $this->getListCollection());
        $this->setData('handlers', $this->collection->getHandlers());

        $this->outputListCollection();
    }

    /**
     * Set the current filter
     */
    protected function setFilterListCollection()
    {
        $allowed = array('type', 'store_id', 'status', 'title', 'collection_id');
        $this->setFilter($allowed);
    }

    /**
     * Applies an action to the selected collections
     */
    protected function actionListCollection()
    {
        $value = $this->getPosted('value', '', true, 'string');
        $action = $this->getPosted('action', '', true, 'string');
        $selected = $this->getPosted('selected', array(), true, 'array');

        if (empty($action)) {
            return null;
        }

        $deleted = $updated = 0;

        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('collection_edit')) {
                $updated += (int) $this->collection->update($id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('collection_delete')) {
                $deleted += (int) $this->collection->delete($id);
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
     * Sets a total number of collections
     */
    protected function setTotalListCollection()
    {
        $query = $this->query_filter;
        $query['count'] = true;
        $this->total = (int) $this->collection->getList($query);
    }

    /**
     * Returns an array of collections
     * @return array
     */
    protected function getListCollection()
    {
        $query = $this->query_filter;
        $query['limit'] = $this->limit;
        return (array) $this->collection->getList($query);
    }

    /**
     * Sets title on the collections overview page
     */
    protected function setTitleListCollection()
    {
        $this->setTitle($this->text('Collections'));
    }

    /**
     * Sets breadcrumbs on the collections overview page
     */
    protected function setBreadcrumbListCollection()
    {
        $this->setBreadcrumbBackend();
    }

    /**
     * Renders the collections overview page
     */
    protected function outputListCollection()
    {
        $this->output('content/collection/list');
    }

    /**
     * Displays the edit collection page
     * @param null|integer $collection_id
     */
    public function editCollection($collection_id = null)
    {
        $this->setCollection($collection_id);

        $this->setTitleEditCollection();
        $this->setBreadcrumbEditCollection();

        $this->setData('types', $this->collection->getTypes());
        $this->setData('collection', $this->data_collection);
        $this->setData('can_delete', $this->canDeleteCollection());

        $this->submitEditCollection();
        $this->outputEditCollection();
    }

    /**
     * Saves an array of submitted collection data
     */
    protected function submitEditCollection()
    {
        if ($this->isPosted('delete')) {
            $this->deleteCollection();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateEditCollection()) {
            return null;
        }

        if (isset($this->data_collection['collection_id'])) {
            $this->updateCollection();
        } else {
            $this->addCollection();
        }
    }

    /**
     * Validates a submitted collection
     * @return bool
     */
    protected function validateEditCollection()
    {
        $this->setSubmitted('collection');
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_collection);

        $this->validateComponent('collection');

        return !$this->hasErrors();
    }

    /**
     * Whether the current collection can be deleted
     * @return bool
     */
    protected function canDeleteCollection()
    {
        return isset($this->data_collection['collection_id'])//
                && $this->access('collection_delete')//
                && $this->collection->canDelete($this->data_collection['collection_id']);
    }

    /**
     * Sets a collection data
     * @param integer $collection_id
     */
    protected function setCollection($collection_id)
    {
        if (is_numeric($collection_id)) {
            $this->data_collection = $this->collection->get($collection_id);
            if (empty($this->data_collection)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Deletes a collection
     */
    protected function deleteCollection()
    {
        $this->controlAccess('collection_delete');
        $result = $this->collection->delete($this->data_collection['collection_id']);

        if (empty($result)) {
            $message = $this->text('Failed to delete this collection. Is it empty?');
            $this->redirect('', $message, 'danger');
        }

        $message = $this->text('Collection has been deleted');
        $this->redirect('admin/content/collection', $message, 'success');
    }

    /**
     * Updates a collection
     */
    protected function updateCollection()
    {
        $this->controlAccess('collection_edit');

        $submitted = $this->getSubmitted();
        $result = $this->collection->update($this->data_collection['collection_id'], $submitted);

        if (empty($result)) {
            $message = $this->text('Collection has not been updated');
            $this->redirect('', $message, 'danger');
        }

        $message = $this->text('Collection has been updated');
        $this->redirect('admin/content/collection', $message, 'success');
    }

    /**
     * Adds a new collection
     */
    protected function addCollection()
    {
        $this->controlAccess('collection_add');

        $submitted = $this->getSubmitted();
        $result = $this->collection->add($submitted);

        if (empty($result)) {
            $message = $this->text('Collection has not been added');
            $this->redirect('', $message, 'danger');
        }

        $message = $this->text('Collection has been added');
        $this->redirect('admin/content/collection', $message, 'success');
    }

    /**
     * Sets title on the edit collection page
     */
    protected function setTitleEditCollection()
    {
        $title = $this->text('Add collection');

        if (isset($this->data_collection['title'])) {
            $vars = array('%name' => $this->data_collection['title']);
            $title = $this->text('Edit collection %name', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit collection page
     */
    protected function setBreadcrumbEditCollection()
    {
        $this->setBreadcrumbBackend();

        $breadcrumb = array(
            'text' => $this->text('Collections'),
            'url' => $this->url('admin/content/collection'),
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the edit collection page
     */
    protected function outputEditCollection()
    {
        $this->output('content/collection/edit');
    }

}
