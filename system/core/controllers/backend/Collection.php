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
     * Constructor
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
        $this->actionCollection();

        $this->setTitleListCollection();
        $this->setBreadcrumbListCollection();

        $query = $this->getFilterQuery();

        $allowed = array('type', 'store_id', 'status', 'title', 'collection_id');
        $this->setFilter($allowed, $query);

        $total = $this->getTotalCollection($query);
        $limit = $this->setPager($total, $query);

        $this->setData('stores', $this->store->getNames());
        $this->setData('handlers', $this->collection->getHandlers());
        $this->setData('collections', $this->getListCollection($limit, $query));

        $this->outputListCollection();
    }

    /**
     * Applies an action to the selected collections
     */
    protected function actionCollection()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

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
            $message = $this->text('Collections have been updated');
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Collections have been deleted');
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Returns total number of collections
     * @param array $query
     * @return integer
     */
    protected function getTotalCollection(array $query)
    {
        $query['count'] = true;
        return (int) $this->collection->getList($query);
    }

    /**
     * Returns an array of collections
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListCollection(array $limit, array $query)
    {
        $query['limit'] = $limit;
        return $this->collection->getList($query);
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
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
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

        $this->setData('stores', $this->store->getNames());
        $this->setData('types', $this->collection->getTypes());
        $this->setData('collection', $this->data_collection);
        $this->setData('can_delete', $this->canDeleteCollection());

        $this->submitCollection();
        $this->outputEditCollection();
    }

    /**
     * Whether the current collection can be deleted
     * @return bool
     */
    protected function canDeleteCollection()
    {
        return (isset($this->data_collection['collection_id'])//
                && $this->access('collection_delete')//
                && $this->collection->canDelete($this->data_collection['collection_id']));
    }

    /**
     * Returns an collection
     * @param integer $collection_id
     * @return array
     */
    protected function setCollection($collection_id)
    {
        if (!is_numeric($collection_id)) {
            return array();
        }

        $collection = $this->collection->get($collection_id);

        if (empty($collection)) {
            $this->outputHttpStatus(404);
        }

        $this->data_collection = $collection;
        return $collection;
    }

    /**
     * Saves an array of submitted collection data
     * @return null
     */
    protected function submitCollection()
    {
        if ($this->isPosted('delete')) {
            $this->deleteCollection();
            return null;
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('collection');
        $this->validateCollection();

        if ($this->hasErrors('collection')) {
            return null;
        }

        if (isset($this->data_collection['collection_id'])) {
            $this->updateCollection();
            return null;
        }

        $this->addCollection();
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
     * Validates a submitted collection
     */
    protected function validateCollection()
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_collection);
        $this->validate('collection');
    }

    /**
     * Updates a collection with submitted values
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
     * Adds a new collection using an array of submitted data
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
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Collections'),
            'url' => $this->url('admin/content/collection'),
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the edit collection page
     */
    protected function outputEditCollection()
    {
        $this->output('content/collection/edit');
    }

}
