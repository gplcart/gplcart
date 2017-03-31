<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Module as ModuleModel,
    gplcart\core\models\Country as CountryModel,
    gplcart\core\models\Collection as CollectionModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to multistore functionality
 */
class Store extends BackendController
{

    /**
     * Module model instance
     * @var \gplcart\core\models\Module $module
     */
    protected $module;

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * Collection model instance
     * @var \gplcart\core\models\Collection $collection
     */
    protected $collection;

    /**
     * The current store
     * @var array
     */
    protected $data_store = array();

    /**
     * The current filter query
     * @var array
     */
    protected $data_filter = array();

    /**
     * @param ModuleModel $module
     * @param CollectionModel $collection
     * @param CountryModel $country
     */
    public function __construct(ModuleModel $module,
            CollectionModel $collection, CountryModel $country)
    {
        parent::__construct();

        $this->module = $module;
        $this->country = $country;
        $this->collection = $collection;
    }

    /**
     * Displays the store overview page
     */
    public function listStore()
    {
        $this->actionStore();

        $this->setTitleListStore();
        $this->setBreadcrumbListStore();

        $limit = $this->setFilterStore();
        $this->setData('default_store', $this->store->getDefault());
        $this->setData('stores', $this->getListStore($limit));
        $this->outputListStore();
    }

    /**
     * Set the current filter and return max number of items
     * @return integer
     */
    protected function setFilterStore()
    {
        $this->data_filter = $this->getFilterQuery();

        $allowed = array('name', 'domain', 'basepath', 'status');
        $this->setFilter($allowed, $this->data_filter);

        return $this->setPager($this->getTotalStore(), $this->data_filter);
    }

    /**
     * Applies an action to the selected stores
     * @return null
     */
    protected function actionStore()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        $updated = $deleted = 0;
        foreach ($selected as $id) {

            if ($action == 'status' && $this->access('store_edit')) {
                $updated += (int) $this->store->update($id, array('status' => (int) $value));
            }

            if ($action == 'delete' && $this->access('store_delete') && !$this->store->isDefault($id)) {
                $deleted += (int) $this->store->delete($id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Stores have been updated');
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Stores have been deleted');
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Returns total number of stores
     * @return integer
     */
    protected function getTotalStore()
    {
        $query = $this->data_filter;
        $query['count'] = true;
        return (int) $this->store->getList($query);
    }

    /**
     * Returns an array of stores
     * @param array $limit
     * @return array
     */
    protected function getListStore(array $limit)
    {
        $query = $this->data_filter;
        $query['limit'] = $limit;
        return $this->store->getList($query);
    }

    /**
     * Sets titles on the stores overview page
     */
    protected function setTitleListStore()
    {
        $this->setTitle($this->text('Stores'));
    }

    /**
     * Sets breadcrumbs on the stores overview page
     */
    protected function setBreadcrumbListStore()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the store overview page
     */
    protected function outputListStore()
    {
        $this->output('settings/store/list');
    }

    /**
     * Displays the store settings form
     * @param integer|null $store_id
     */
    public function editStore($store_id = null)
    {
        $this->setStore($store_id);

        $this->seTitleEditStore();
        $this->setBreadcrumbEditStore();

        $this->setData('store', $this->data_store);
        $this->setData('themes', $this->getListThemeStore());
        $this->setData('is_default', $this->isDefaultStore());
        $this->setData('can_delete', $this->canDeleteStore());
        $this->setData('countries', $this->getCountriesStore());
        $this->setData('collections', $this->getListCollectionStore());

        $this->submitStore();
        $this->setDataEditStore();

        $this->setJsEditStore();
        $this->outputEditStore();
    }

    /**
     * Returns an array of country names
     * @return array
     */
    protected function getCountriesStore()
    {
        return $this->country->getIso();
    }

    /**
     * Whether the store can be deleted
     * @return boolean
     */
    protected function canDeleteStore()
    {
        return isset($this->data_store['store_id'])//
                && $this->store->canDelete($this->data_store['store_id'])//
                && $this->access('store_delete')//
                && !$this->isDefaultStore();
    }

    /**
     * Whether the store is default
     * @return boolean
     */
    protected function isDefaultStore()
    {
        return isset($this->data_store['store_id'])//
                && $this->store->isDefault($this->data_store['store_id']);
    }

    /**
     * Returns a store
     * @param integer $store_id
     * @return array
     */
    protected function setStore($store_id)
    {
        if (!is_numeric($store_id)) {
            $this->data_store = array('data' => $this->store->defaultConfig());
            return $this->data_store;
        }

        $store = $this->store->get((int) $store_id);

        if (empty($store)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_store = $store;
    }

    /**
     * Returns an array of available theme modules excluding bakend theme
     * @return array
     */
    protected function getListThemeStore()
    {
        $themes = $this->module->getByType('theme', true);
        unset($themes[$this->theme_backend]);
        return $themes;
    }

    /**
     * Returns an array of enabled collection for the current store keyed by entity name
     * @return array
     */
    protected function getListCollectionStore()
    {
        if (empty($this->data_store['store_id'])) {
            return array();
        }

        $conditions = array(
            'status' => 1,
            'store_id' => $this->data_store['store_id']
        );

        $collections = (array) $this->collection->getList($conditions);

        $list = array();
        foreach ($collections as $collection) {
            $list[$collection['type']][$collection['collection_id']] = $collection;
        }

        return $list;
    }

    /**
     * Saves a submitted store data
     * @return null
     */
    protected function submitStore()
    {
        if ($this->isPosted('delete')) {
            $this->deleteStore();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateStore()) {
            return null;
        }

        if (isset($this->data_store['store_id'])) {
            $this->updateStore();
        } else {
            $this->addStore();
        }
    }

    /**
     * Validates a store
     * @return array
     */
    protected function validateStore()
    {
        $this->setSubmitted('store', null, 'raw');

        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_store);
        $this->setSubmittedBool('data.anonymous_checkout');

        foreach (array('email', 'phone', 'fax', 'map') as $field) {
            $this->setSubmittedArray("data.$field");
        }

        $this->validate('store');

        return !$this->hasErrors('store');
    }

    /**
     * Deletes a store
     */
    protected function deleteStore()
    {
        $this->controlAccess('store_delete');

        $deleted = $this->store->delete($this->data_store['store_id']);

        if ($deleted) {
            $message = $this->text('Store has been deleted');
            $this->redirect('admin/settings/store', $message, 'success');
        }

        $message = $this->text('Unable to delete this store');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Updates a store
     */
    protected function updateStore()
    {
        $this->controlAccess('store_edit');

        $submitted = $this->getSubmitted();
        $this->store->update($this->data_store['store_id'], $submitted);

        $message = $this->text('Store has been updated');
        $this->redirect('admin/settings/store', $message, 'success');
    }

    /**
     * Adds a new store using an array of submitted values
     */
    protected function addStore()
    {
        $this->controlAccess('store_add');
        $this->store->add($this->getSubmitted());

        $message = $this->text('Store has been added');
        $this->redirect('admin/settings/store', $message, 'success');
    }

    /**
     * Prepares store data before sending to templates
     */
    protected function setDataEditStore()
    {
        foreach (array('logo', 'favicon') as $field) {
            $value = $this->getData("store.data.$field");
            if (!empty($value)) {
                $this->setData("store.{$field}_thumb", $this->image->urlFromPath($value));
            }
        }

        // Convert arrays to multiline strings
        foreach (array('email', 'phone', 'fax', 'map') as $field) {
            $value = $this->getData("store.data.$field");
            if (isset($value)) {
                $this->setData("store.data.$field", implode("\n", (array) $value));
            }
        }
    }

    /**
     * Sets JS on the store edit page
     */
    protected function setJsEditStore()
    {
        if (!empty($this->data_store['data']['map'])) {
            $this->setJsSettings('map', $this->data_store['data']['map']);
        }
    }

    /**
     * Sets titles on the store edit page
     */
    protected function seTitleEditStore()
    {
        $title = $this->text('Add store');

        if (isset($this->data_store['store_id'])) {
            $vars = array('%name' => $this->data_store['name']);
            $title = $this->text('Edit store %name', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the store edit page
     */
    protected function setBreadcrumbEditStore()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/settings/store'),
            'text' => $this->text('Stores')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the store edit page templates
     */
    protected function outputEditStore()
    {
        $this->output('settings/store/edit');
    }

}
