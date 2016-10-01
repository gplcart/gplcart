<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\controllers\admin\Controller as BackendController;
use core\models\Collection as ModelsCollection;

/**
 * Handles incoming requests and outputs data related to collections
 */
class Collection extends BackendController
{

    /**
     * Collection model instance
     * @var \core\models\Collection $collection
     */
    protected $collection;

    /**
     * Constructor
     * @param ModelsCollection $collection
     */
    public function __construct(ModelsCollection $collection)
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

        $query = $this->getFilterQuery();
        $total = $this->getTotalCollection($query);
        $limit = $this->setPager($total, $query);

        $stores = $this->store->getNames();
        $handlers = $this->collection->getHandlers();
        $collections = $this->getListCollection($limit, $query);

        $this->setData('stores', $stores);
        $this->setData('handlers', $handlers);
        $this->setData('collections', $collections);

        $allowed = array('type', 'store_id', 'status', 'title');
        $this->setFilter($allowed, $query);

        $this->setTitleListCollection();
        $this->setBreadcrumbListCollection();
        $this->outputListCollection();
    }

    /**
     * Applies an action to the selected collections
     */
    protected function actionCollection()
    {
        $action = (string)$this->request->post('action');

        if (empty($action)) {
            return;
        }

        $value = (int)$this->request->post('value');
        $selected = (array)$this->request->post('selected', array());

        $deleted = $updated = 0;

        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('collection_edit')) {
                $updated += (int)$this->collection->update($id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('collection_delete')) {
                $deleted += (int)$this->collection->delete($id);
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
        return $this->collection->getList($query);
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
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the collections overview page
     */
    protected function outputListCollection()
    {
        $this->output('content/collection/list');
    }

    public function editCollection($collection_id = null)
    {
        $collection = $this->getCollection($collection_id);

        $stores = $this->store->getNames();
        $handlers = $this->collection->getHandlers();

        $can_delete = (isset($collection['collection_id'])
            && $this->access('collection_delete')
            && $this->collection->canDelete($collection['collection_id']));

        $this->setData('stores', $stores);
        $this->setData('handlers', $handlers);
        $this->setData('collection', $collection);
        $this->setData('can_delete', $can_delete);

        $this->submitCollection($collection);

        $this->setTitleEditCollection($collection);
        $this->setBreadcrumbEditCollection();
        $this->outputEditCollection();
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
     * Saves an array of submitted collection data
     * @param array $collection
     * @return mixed
     */
    protected function submitCollection(array $collection)
    {
        if ($this->isPosted('delete')) {
            return $this->deleteCollection($collection);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('collection');
        $this->validateCollection($collection);

        if ($this->hasErrors('collection')) {
            return null;
        }

        if (isset($collection['collection_id'])) {
            return $this->updateCollection($collection);
        }

        return $this->addCollection();
    }

    /**
     * Deletes a collection
     * @param array $collection
     */
    protected function deleteCollection(array $collection)
    {
        $this->controlAccess('collection_delete');

        $result = $this->collection->delete($collection['collection_id']);

        if (empty($result)) {
            $message = $this->text('Failed to delete this collection. Is it empty?');
            $this->redirect('', $message, 'danger');
        }

        $message = $this->text('Collection has been deleted');
        $this->redirect('admin/content/collection', $message, 'success');
    }

    /**
     * Validates a submitted collection
     * @param array $collection
     */
    protected function validateCollection(array $collection)
    {
        $this->setSubmittedBool('status');

        $this->addValidator('title', array(
            'length' => array('min' => 1, 'max' => 255)
        ));

        $this->addValidator('translation', array(
            'translation' => array()
        ));

        if (empty($collection['collection_id'])) {
            $this->addValidator('type', array(
                'required' => array()
            ));
        }

        $this->addValidator('store_id', array(
            'required' => array()
        ));

        $this->setValidators($collection);
    }

    /**
     * Updates a collection with submitted values
     * @param array $collection
     */
    protected function updateCollection(array $collection)
    {
        $this->controlAccess('collection_edit');
        $submitted = $this->getSubmitted();

        $result = $this->collection->update($collection['collection_id'], $submitted);

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
     * @param array $collection
     */
    protected function setTitleEditCollection(array $collection)
    {
        if (isset($collection['title'])) {
            $title = $this->text('Edit collection %name', array(
                '%name' => $collection['title']
            ));
        } else {
            $title = $this->text('Add collection');
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
            'url' => $this->url('admin/content/collection'),
            'text' => $this->text('Collections')
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
