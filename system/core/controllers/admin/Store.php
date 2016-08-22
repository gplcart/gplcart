<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Image as ModelsImage;
use core\models\Module as ModelsModule;

/**
 * Handles incoming requests and outputs data related to multistore functionality
 */
class Store extends Controller
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
     * Constructor
     * @param ModelsImage $image
     * @param ModelsModule $module
     */
    public function __construct(ModelsImage $image, ModelsModule $module)
    {
        parent::__construct();

        $this->image = $image;
        $this->module = $module;
    }

    /**
     * Displays the store overview page
     */
    public function stores()
    {
        if ($this->isPosted('action')) {
            $this->action();
        }

        $query = $this->getFilterQuery();
        $total = $this->getTotalStores($query);
        $limit = $this->setPager($total, $query);
        $stores = $this->getStores($limit, $query);

        $this->setData('stores', $stores);

        $filters = array('name', 'domain', 'basepath', 'status');
        $this->setFilter($filters, $query);

        $this->setTitleStores();
        $this->setBreadcrumbStores();
        $this->outputStores();
    }

    /**
     * Displays the store settings form
     * @param integer|null $store_id
     */
    public function edit($store_id = null)
    {
        $store = $this->get($store_id);

        if ($this->isPosted('delete')) {
            $this->delete($store);
        }

        $themes = $this->getThemes();
        $is_default = (isset($store['store_id']) && $this->store->isDefault($store['store_id']));
        $can_delete = (isset($store['store_id']) && $this->store->canDelete($store['store_id']));

        $this->setData('store', $store);
        $this->setData('themes', $themes);
        $this->setData('is_default', $is_default);
        $this->setData('can_delete', $can_delete);

        if ($this->isPosted('save')) {
            $this->submit($store);
        }

        $this->setDataStore();
        
        $this->setJsEdit($store);

        $this->seTitleEdit($store);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }
    
    /**
     * Sets JS on the store edit page
     * @param array $store
     */
    protected function setJsEdit(array $store)
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
    protected function getTotalStores(array $query)
    {
        $query['count'] = true;
        return $this->store->getList($query);
    }

    /**
     * Renders the store overview page
     */
    protected function outputStores()
    {
        $this->output('settings/store/list');
    }

    /**
     * Sets breadcrumbs on the stores overview page
     */
    protected function setBreadcrumbStores()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));
    }

    /**
     * Sets titles on the stores overview page
     */
    protected function setTitleStores()
    {
        $this->setTitle($this->text('Stores'));
    }

    /**
     * Returns an array of stores
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getStores(array $limit, array $query)
    {
        $query['limit'] = $limit;
        return $this->store->getList($query);
    }

    /**
     * Renders the store edit page templates
     */
    protected function outputEdit()
    {
        $this->output('settings/store/edit');
    }

    /**
     * Sets breadcrumbs on the store edit page
     */
    protected function setBreadcrumbEdit()
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
    protected function seTitleEdit(array $store)
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
    protected function setDataStore()
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
     * Returns an array of theme modules
     * @return array
     */
    protected function getThemes()
    {
        $themes = $this->module->getByType('theme', true);
        $backend_theme = $this->config('theme_backend', 'backend');

        unset($themes[$backend_theme]);
        return $themes;
    }

    /**
     * Returns a store
     * @param integer $store_id
     * @return array
     */
    protected function get($store_id)
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
    protected function delete(array $store)
    {
        $this->controlAccess('store_delete');

        $deleted = (isset($store['store_id']) && $this->store->delete($store['store_id']));

        if ($deleted) {
            $this->redirect('admin/settings/store', $this->text('Store %s has been deleted', array(
                        '%s' => $store['name'])), 'success');
        }

        $this->redirect('', $this->text('Unable to delete store %name', array(
                    '%name' => $store['name'])), 'danger');
    }

    /**
     * Applies an action to the selected stores
     */
    protected function action()
    {
        $value = (int) $this->request->post('value');
        $action = (string) $this->request->post('action');
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
            $this->setMessage($this->text('Stores have been updated'), 'success', true);
        }

        if ($deleted > 0) {
            $this->setMessage($this->text('Stores have been deleted'), 'success', true);
        }
    }

    /**
     * Saves a submitted store data
     * @param array $store
     * @return null
     */
    protected function submit(array $store)
    {
        $this->setSubmitted('store');

        $this->validate($store);

        if ($this->hasErrors('store')) {
            return;
        }

        if (isset($store['store_id'])) {
            $this->controlAccess('store_edit');
            $this->store->update($store['store_id'], $this->getSubmitted());
            $this->redirect('admin/settings/store', $this->text('Store %name has been updated', array('%name' => $store['name'])), 'success');
        }

        $this->controlAccess('store_add');
        $this->store->add($this->getSubmitted());
        $this->redirect('admin/settings/store', $this->text('Store has been added'), 'success');
    }

    /**
     * Validates a store
     * @param array $store
     */
    protected function validate(array $store)
    {
        $is_default = (isset($store['store_id']) && $this->store->isDefault($store['store_id']));

        // Prevent editing domain and basepath for default store
        if ($is_default) {
            $this->setSubmitted('domain', null);
            $this->setSubmitted('basepath', null);
        }

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

        /**
         * The domain regexp pattern ensures that:
         *  - each label/level (splitted by a dot) may contain up to 63 characters.
         *  - the full domain name may have up to 127 levels.
         *  - the full domain name may not exceed the length of 253 characters in its textual representation.
         *  - each label can consist of letters, digits and hyphens.
         *  - labels cannot start or end with a hyphen.
         *  - the top-level domain (extension) cannot be all-numeric.
         */
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
            $map = array_slice($this->getValidatorResult('data.map'), 0, 2);

            $this->setSubmitted('data.map', $map);
            $this->setSubmitted('data.email', $emails);
        }

        $this->setSubmittedArray('data.fax');
        $this->setSubmittedArray('data.phone');

        $this->setSubmittedBool('status');
        $this->setSubmittedBool('data.anonymous_checkout');
    }

}
