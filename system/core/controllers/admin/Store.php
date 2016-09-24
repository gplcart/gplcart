<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\models\Image as ModelsImage;
use core\models\Module as ModelsModule;
use core\models\Collection as ModelsCollection;
use core\controllers\admin\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to multistore functionality
 */
class Store extends BackendController
{

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Module model instance
     * @var \core\models\Module $module
     */
    protected $module;
    
    /**
     * Collection model instance
     * @var \core\models\Collection $collection
     */
    protected $collection;
    
    /**
     * Constructor
     * @param ModelsImage $image
     * @param ModelsModule $module
     * @param ModelsCollection $collection
     */
    public function __construct(ModelsImage $image, ModelsModule $module,
            ModelsCollection $collection)
    {
        parent::__construct();

        $this->image = $image;
        $this->module = $module;
        $this->collection = $collection;
    }

    /**
     * Displays the store overview page
     */
    public function listStore()
    {
        $this->actionStore();

        $query = $this->getFilterQuery();
        $total = $this->getTotalStore($query);
        $limit = $this->setPager($total, $query);
        $stores = $this->getListStore($limit, $query);

        $this->setData('stores', $stores);

        $filters = array('name', 'domain', 'basepath', 'status');
        $this->setFilter($filters, $query);

        $this->setTitleListStore();
        $this->setBreadcrumbListStore();
        $this->outputListStore();
    }

    /**
     * Displays the store settings form
     * @param integer|null $store_id
     */
    public function editStore($store_id = null)
    {
        $store = $this->getStore($store_id);
        $themes = $this->getListThemeStore();
        $collections = $this->getListCollectionStore($store_id);

        $is_default = (isset($store['store_id']) && $this->store->isDefault($store['store_id']));
        $can_delete = (isset($store['store_id']) && $this->store->canDelete($store['store_id']));

        $this->setData('store', $store);
        $this->setData('themes', $themes);
        $this->setData('is_default', $is_default);
        $this->setData('can_delete', $can_delete);
        $this->setData('collections', $collections);

        $this->submitStore($store);
        $this->setDataEditStore();

        $this->setJsEditStore($store);

        $this->seTitleEditStore($store);
        $this->setBreadcrumbEditStore();
        $this->outputEditStore();
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
     * Returns an array of enabled collection for the current store
     * keyed by entity name
     * @param integer $store_id
     * @return array
     */
    protected function getListCollectionStore($store_id)
    {
        $conditions = array('status' => 1, 'store_id' => $store_id);
        $collections = $this->collection->getList($conditions);

        $list = array();
        foreach ($collections as $collection) {
            $list[$collection['type']][$collection['collection_id']] = $collection;
        }

        return $list;
    }

    /**
     * Sets JS on the store edit page
     * @param array $store
     */
    protected function setJsEditStore(array $store)
    {
        if (!empty($store['data']['map'])) {
            $this->setJsSettings('map', $store['data']['map']);
        }
    }

    /**
     * Returns total number of stores
     * @param array $query
     * @return integer
     */
    protected function getTotalStore(array $query)
    {
        $query['count'] = true;
        return $this->store->getList($query);
    }

    /**
     * Renders the store overview page
     */
    protected function outputListStore()
    {
        $this->output('settings/store/list');
    }

    /**
     * Sets breadcrumbs on the stores overview page
     */
    protected function setBreadcrumbListStore()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));
    }

    /**
     * Sets titles on the stores overview page
     */
    protected function setTitleListStore()
    {
        $this->setTitle($this->text('Stores'));
    }

    /**
     * Returns an array of stores
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListStore(array $limit, array $query)
    {
        $query['limit'] = $limit;
        return $this->store->getList($query);
    }

    /**
     * Renders the store edit page templates
     */
    protected function outputEditStore()
    {
        $this->output('settings/store/edit');
    }

    /**
     * Sets breadcrumbs on the store edit page
     */
    protected function setBreadcrumbEditStore()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/settings/store'),
            'text' => $this->text('Stores')));
    }

    /**
     * Sets titles on the store edit page
     * @param array $store
     */
    protected function seTitleEditStore(array $store)
    {
        if (isset($store['store_id'])) {
            $title = $this->text('Edit store %name', array(
                '%name' => $store['name']));
        } else {
            $title = $this->text('Add store');
        }

        $this->setTitle($title);
    }

    /**
     * Prepares store data before sending to templates
     */
    protected function setDataEditStore()
    {
        foreach (array('logo', 'favicon') as $field) {
            $value = $this->getData("store.data.$field");
            if (!empty($value)) {
                $this->setData("store.data.$field", $this->image->urlFromPath($value));
            }
        }

        // Convert arrays to multiline strings
        $multiline_fields = array('email', 'phone', 'fax', 'map');

        foreach ($multiline_fields as $field) {
            $value = $this->getData("store.data.$field");
            if (!empty($value)) {
                $this->setData("store.data.$field", implode("\n", (array) $value));
            }
        }
    }

    /**
     * Returns a store
     * @param integer $store_id
     * @return array
     */
    protected function getStore($store_id)
    {
        if (!is_numeric($store_id)) {
            return array('data' => $this->store->defaultConfig());
        }

        $store = $this->store->get((int) $store_id);

        if (empty($store)) {
            $this->outputError(404);
        }

        return $store;
    }

    /**
     * Deletes a store
     * @param array $store
     * @return null
     */
    protected function deleteStore(array $store)
    {
        $this->controlAccess('store_delete');

        $deleted = (isset($store['store_id']) && $this->store->delete($store['store_id']));

        if ($deleted) {
            $message = $this->text('Store %s has been deleted', array(
                '%s' => $store['name']));
            $this->redirect('admin/settings/store', $message, 'success');
        }

        $message = $this->text('Unable to delete store %name', array(
            '%name' => $store['name']));
        $this->redirect('', $message, 'danger');
    }

    /**
     * Applies an action to the selected stores
     */
    protected function actionStore()
    {
        $action = (string) $this->request->post('action');

        if ($action) {
            return;
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
     * Saves a submitted store data
     * @param array $store
     */
    protected function submitStore(array $store)
    {

        if ($this->isPosted('delete')) {
            $this->deleteStore($store);
        }

        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('store');

        $this->validateStore($store);

        if ($this->hasErrors('store')) {
            return;
        }

        if (isset($store['store_id'])) {
            return $this->updateStore($store);
        }

        $this->addStore();
    }

    /**
     * Updates a store
     * @param array $store
     */
    protected function updateStore(array $store)
    {
        $this->controlAccess('store_edit');

        $submitted = $this->getSubmitted();

        // Prevent editing domain and basepath for default store
        if ($this->store->isDefault($store['store_id'])) {
            unset($submitted['domain'], $submitted['basepath']);
        }
        
        $this->store->update($store['store_id'], $submitted);

        $message = $this->text('Store %name has been updated', array(
            '%name' => $store['name']));
        $this->redirect('admin/settings/store', $message, 'success');
    }

    /**
     * Adds a new store using an array of submitted values
     */
    protected function addStore()
    {
        $this->controlAccess('store_add');

        $submitted = $this->getSubmitted();
        $this->store->add($submitted);

        $message = $this->text('Store has been added');
        $this->redirect('admin/settings/store', $message, 'success');
    }

    /**
     * Validates a store
     * @param array $store
     */
    protected function validateStore(array $store)
    {
        $is_default = (isset($store['store_id']) && $this->store->isDefault($store['store_id']));

        // Delete logo and favicon (if set)
        if ($this->isSubmitted('delete_favicon')) {
            $this->setSubmitted('data.favicon', '');
        }

        if ($this->isSubmitted('delete_logo')) {
            $this->setSubmitted('data.logo', '');
        }

        $domain_pattern = '/^(?!\-)'
                . '(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.)'
                . '{1,126}(?!\d+)[a-zA-Z\d]{1,63}$/';

        $this->addValidator('domain', array(
            'regexp' => array(
                'required' => !$is_default,
                'pattern' => $domain_pattern),
            'store_domain_unique' => array(
                'required' => !$is_default)
        ));

        $this->addValidator('basepath', array(
            'regexp' => array(
                'required' => !$is_default,
                'pattern' => '/^[a-z0-9]{0,50}$/'),
            'store_basepath_unique' => array(
                'domain' => $this->getSubmitted('domain')),
        ));

        $this->addValidator('name', array(
            'length' => array(
                'min' => 1,
                'max' => 255,
                'required' => true,
        )));

        $this->addValidator('data.email', array(
            'email' => array(
                'required' => true,
                'explode' => true)
        ));

        $this->addValidator('data.map', array(
            'numeric' => array('explode' => true)
        ));

        $this->addValidator('data.title', array(
            'length' => array(
                'min' => 1,
                'max' => 255,
                'required' => true
        )));

        $this->addValidator('data.translation', array(
            'translation' => array()
        ));

        $this->addValidator('data.catalog_limit', array(
            'numeric' => array()
        ));

        $this->addValidator('logo', array(
            'upload' => array(
                'control_errors' => true,
                'path' => 'image/upload/store',
                'file' => $this->request->file('logo')
        )));

        $this->addValidator('favicon', array(
            'upload' => array(
                'control_errors' => true,
                'path' => 'image/upload/store',
                'file' => $this->request->file('favicon')
        )));

        $errors = $this->setValidators($store);

        if (empty($errors)) {

            $logo = $this->getValidatorResult('logo');
            $favicon = $this->getValidatorResult('favicon');

            $this->setSubmitted('data.logo', $logo);
            $this->setSubmitted('data.favicon', $favicon);

            $emails = $this->getValidatorResult('data.email');
            $map = $this->getValidatorResult('data.map');

            $this->setSubmitted('data.map', array_slice($map, 0, 2));
            $this->setSubmitted('data.email', $emails);
        }

        $this->setSubmittedArray('data.fax');
        $this->setSubmittedArray('data.phone');

        $this->setSubmittedBool('status');
        $this->setSubmittedBool('data.anonymous_checkout');
    }

}
