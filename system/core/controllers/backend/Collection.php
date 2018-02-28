<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Collection as CollectionModel;
use gplcart\core\models\TranslationEntity as TranslationEntityModel;

/**
 * Handles incoming requests and outputs data related to collections
 */
class Collection extends Controller
{

    /**
     * Collection model instance
     * @var \gplcart\core\models\Collection $collection
     */
    protected $collection;

    /**
     * Entity translation model instance
     * @var \gplcart\core\models\TranslationEntity $translation_entity
     */
    protected $translation_entity;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * The current collection
     * @var array
     */
    protected $data_collection = array();

    /**
     * @param CollectionModel $collection
     * @param TranslationEntityModel $translation_entity
     */
    public function __construct(CollectionModel $collection, TranslationEntityModel $translation_entity)
    {
        parent::__construct();

        $this->collection = $collection;
        $this->translation_entity = $translation_entity;
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
        $this->setPagerListCollection();

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
     * Set pager
     * @return array
     */
    protected function setPagerListCollection()
    {
        $options = $this->query_filter;
        $options['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->collection->getList($options)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Applies an action to the selected collections
     */
    protected function actionListCollection()
    {
        list($selected, $action, $value) = $this->getPostedAction();

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
            $message = $this->text('Updated %num item(s)', array('%num' => $updated));
            $this->setMessage($message, 'success');
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Returns an array of collections
     * @return array
     */
    protected function getListCollection()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;

        return (array) $this->collection->getList($conditions);
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

        $this->setData('types', $this->collection->getTypes());
        $this->setData('collection', $this->data_collection);
        $this->setData('can_delete', $this->canDeleteCollection());
        $this->setData('languages', $this->language->getList(array('enabled' => true)));

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
        } else if ($this->isPosted('save') && $this->validateEditCollection()) {
            if (isset($this->data_collection['collection_id'])) {
                $this->updateCollection();
            } else {
                $this->addCollection();
            }
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
        return isset($this->data_collection['collection_id'])
            && $this->access('collection_delete')
            && $this->collection->canDelete($this->data_collection['collection_id']);
    }

    /**
     * Sets a collection data
     * @param integer $collection_id
     */
    protected function setCollection($collection_id)
    {
        $this->data_collection = array();

        if (is_numeric($collection_id)) {

            $conditions = array(
                'language' => 'und',
                'collection_id' => $collection_id
            );

            $this->data_collection = $this->collection->get($conditions);

            if (empty($this->data_collection)) {
                $this->outputHttpStatus(404);
            }

            $this->prepareCollection($this->data_collection);
        }
    }

    /**
     * Prepare an array of collection data
     * @param array $collection
     */
    protected function prepareCollection(array &$collection)
    {
        $this->setItemTranslation($collection, 'collection', $this->translation_entity);
    }

    /**
     * Deletes a collection
     */
    protected function deleteCollection()
    {
        $this->controlAccess('collection_delete');

        if ($this->collection->delete($this->data_collection['collection_id'])) {
            $this->redirect('admin/content/collection', $this->text('Collection has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Collection has not been deleted'), 'warning');
    }

    /**
     * Updates a collection
     */
    protected function updateCollection()
    {
        $this->controlAccess('collection_edit');

        if ($this->collection->update($this->data_collection['collection_id'], $this->getSubmitted())) {
            $this->redirect('admin/content/collection', $this->text('Collection has been updated'), 'success');
        }

        $this->redirect('', $this->text('Collection has not been updated'), 'danger');
    }

    /**
     * Adds a new collection
     */
    protected function addCollection()
    {
        $this->controlAccess('collection_add');

        if ($this->collection->add($this->getSubmitted())) {
            $this->redirect('admin/content/collection', $this->text('Collection has been added'), 'success');
        }

        $this->redirect('', $this->text('Collection has not been added'), 'danger');
    }

    /**
     * Sets title on the edit collection page
     */
    protected function setTitleEditCollection()
    {
        if (isset($this->data_collection['title'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_collection['title']));
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
            'text' => $this->text('Collections'),
            'url' => $this->url('admin/content/collection'),
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the edit collection page
     */
    protected function outputEditCollection()
    {
        $this->output('content/collection/edit');
    }

}
