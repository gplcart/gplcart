<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Image;
use core\models\File;
use core\models\Module;
use core\classes\Tool;

class Store extends Controller
{

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Module model instance
     * @var \core\models\Module $module
     */
    protected $module;

    /**
     * Constructor
     * @param Image $image
     * @param File $file
     * @param Module $module
     */
    public function __construct(Image $image, File $file, Module $module)
    {
        parent::__construct();

        $this->image = $image;
        $this->file = $file;
        $this->module = $module;
    }

    /**
     * Displays the store overview page
     */
    public function stores()
    {
        $value = $this->request->post('value');
        $action = $this->request->post('action');
        $selected = $this->request->post('selected', array());

        if ($action) {
            $this->action($selected, $action, $value);
        }

        $query = $this->getFilterQuery();
        $total = $this->setPager($this->getTotalStores($query), $query);

        $this->data['stores'] = $this->getStores($total, $query);

        $filters = array('name', 'domain', 'basepath', 'status', 'scheme');
        $this->setFilter($filters, $query);

        $this->setTitleStores();
        $this->setBreadcrumbStores();
        $this->outputStores();
    }

    /**
     * Returns total number of stores
     * @param array $query
     * @return integer
     */
    protected function getTotalStores($query)
    {
        return $this->store->getList(array('count' => true) + $query);
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
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
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
    protected function getStores($limit, $query)
    {
        return $this->store->getList(array('limit' => $limit) + $query);
    }

    /**
     * Displays the store settings form
     * @param integer|null $store_id
     */
    public function edit($store_id = null)
    {
        $store = $this->get($store_id);

        if ($this->request->post('delete')) {
            $this->delete($store);
        }

        $this->data['store'] = $store;
        $this->data['themes'] = $this->getThemes();
        $this->data['is_default'] = (isset($store['store_id']) && $this->store->isDefault($store['store_id']));
        $this->data['can_delete'] = (isset($store['store_id']) && $this->store->canDelete($store['store_id']));

        if ($this->request->post('save')) {
            $this->submit($store);
        }

        $this->prepareStore();

        $this->seTitleEdit($store);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
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
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->setBreadcrumb(array('url' => $this->url('admin/settings/store'), 'text' => $this->text('Stores')));
    }

    /**
     * Sets titles on the store edit page
     */
    protected function seTitleEdit($store)
    {
        $title = $this->text('Add store');

        if (isset($store['store_id'])) {
            $title = $this->text('Edit store %name', array('%name' => $store['name']));
        }

        $this->setTitle($title);
    }

    /**
     * Prepares store data before output
     */
    protected function prepareStore()
    {
        if (!empty($this->data['store']['data']['logo'])) {
            $this->data['store']['logo_thumb'] = $this->file->url($this->data['store']['data']['logo']);
        }

        if (!empty($this->data['store']['data']['favicon'])) {
            $this->data['store']['favicon_thumb'] = $this->file->url($this->data['store']['data']['favicon']);
        }

        if (!empty($this->data['store']['data']['email'])) {
            $this->data['store']['data']['email'] = implode("\n", (array) $this->data['store']['data']['email']);
        }

        if (!empty($this->data['store']['data']['map'])) {
            $this->addJsSettings('map', $this->data['store']['data']['map']);
            $this->data['store']['data']['map'] = implode("\n", (array) $this->data['store']['data']['map']);
        }

        if (!empty($this->data['store']['data']['phone'])) {
            $this->data['store']['data']['phone'] = implode("\n", (array) $this->data['store']['data']['phone']);
        }

        if (!empty($this->data['store']['data']['fax'])) {
            $this->data['store']['data']['fax'] = implode("\n", (array) $this->data['store']['data']['fax']);
        }

        if (!empty($this->data['store']['data']['social'])) {
            $this->data['store']['data']['social'] = implode("\n", (array) $this->data['store']['data']['social']);
        }

        if (!empty($this->data['store']['data']['hours'])) {
            $opening_hours = '';
            foreach ((array) $this->data['store']['data']['hours'] as $hours) {
                if (is_array($hours)) {
                    list($start, $end) = each($hours);
                    $opening_hours .= "$start - $end\n";
                } else {
                    $opening_hours .= "$hours\n";
                }
            }
            $this->data['store']['data']['hours'] = trim($opening_hours);
        }
    }

    /**
     * Returns an array of theme modules
     * @return type
     */
    protected function getThemes()
    {
        $themes = $this->module->getByType('theme', true);
        $backend_theme = $this->config->get('theme_backend', 'backend');
        unset($themes[$backend_theme]);
        return $themes;
    }

    /**
     * Saves a store
     * @param array $store
     * @return null
     */
    protected function submit($store)
    {
        $this->submitted = $this->request->post('store');

        $this->validate($store);

        if ($this->formErrors()) {
            $this->data['store'] = $this->submitted;
            return;
        }

        if (isset($store['store_id'])) {
            $this->controlAccess('store_edit');
            $this->store->update($store['store_id'], $this->submitted);
            $this->redirect('admin/settings/store', $this->text('Store %name has been updated', array('%name' => $store['name'])), 'success');
        }

        $this->controlAccess('store_add');
        $this->store->add($this->submitted);
        $this->redirect('admin/settings/store', $this->text('Store has been added'), 'success');
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

        if ($store) {
            return $store;
        }

        $this->outputError(404);
    }

    /**
     * Deletes a store
     * @param array $store
     * @return null
     */
    protected function delete($store)
    {
        if (empty($store['store_id'])) {
            return;
        }

        $this->controlAccess('store_delete');

        if ($this->store->delete($store['store_id'])) {
            $this->redirect('admin/settings/store', $this->text('Store %s has been deleted', array(
                        '%s' => $store['name'])), 'success');
        }

        $this->redirect('', $this->text('Unable to delete store %name. The most probable reason - it is used in products, users, orders etc.', array('%name' => $store['name'])), 'danger');
    }

    /**
     * Applies an action to the selected stores
     * @param array $selected
     * @param string $action
     * @param string $value
     * @return boolean
     */
    protected function action($selected, $action, $value)
    {
        $updated = $deleted = 0;
        foreach ($selected as $id) {
            if ($action == 'status' && $this->access('store_edit')) {
                $updated += (int) $this->store->update($id, array('status' => (int) $value));
            }

            if ($action == 'delete' && $this->access('store_delete') && !$this->store->isDefault($id)) {
                $deleted += (int) $this->store->delete($id);
            }
        }

        if ($updated) {
            $this->session->setMessage($this->text('Stores have been updated'), 'success');
            return true;
        }

        if ($deleted) {
            $this->session->setMessage($this->text('Stores have been deleted'), 'success');
            return true;
        }

        return false;
    }

    /**
     * Validates a store
     */
    protected function validate($store)
    {
        $this->validateDomain();
        $this->validateName();
        $this->validateBasepath($store);

        $this->validateEmail();
        $this->validateMap();
        $this->validateHours();
        $this->validateSocial();

        $this->validateTitle();
        $this->validateTranslation();
        $this->validateUpload();

        $this->validateNumeric('catalog_limit');
        $this->validateNumeric('catalog_front_limit');

        $this->submitted['data']['anonymous_checkout'] = !empty($this->submitted['data']['anonymous_checkout']);
        $this->submitted['status'] = !empty($this->submitted['status']);
        $this->submitted['data']['phone'] = Tool::stringToArray($this->submitted['data']['phone']);
        $this->submitted['data']['fax'] = Tool::stringToArray($this->submitted['data']['fax']);
    }

    /**
     * Validates store e-mails
     * @return boolean
     */
    protected function validateEmail()
    {
        if (empty($this->submitted['data']['email'])) {
            $this->data['form_errors']['email'] = $this->text('Required field');
            return false;
        }

        $this->submitted['data']['email'] = Tool::stringToArray($this->submitted['data']['email']);

        $emails = array_filter($this->submitted['data']['email'], function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if (count($emails) != count($this->submitted['data']['email'])) {
            $this->data['form_errors']['email'] = $this->text('Invalid E-mail');
            return false;
        }

        return true;
    }

    /**
     * Validates gmap coordinates
     * @return boolean
     */
    protected function validateMap()
    {
        if (empty($this->submitted['data']['map'])) {
            return true;
        }

        $this->submitted['data']['map'] = Tool::stringToArray($this->submitted['data']['map']);

        $count = count(array_filter($this->submitted['data']['map'], 'is_numeric'));

        if (($count != count($this->submitted['data']['map'])) || $count != 2) {
            $this->data['form_errors']['data']['map'] = $this->text('Invalid map coordinates');
            return false;
        }

        return true;
    }

    /**
     * Validates opening hours
     * @return boolean
     */
    protected function validateHours()
    {
        if (empty($this->submitted['data']['hours'])) {
            return true;
        }

        $this->submitted['data']['hours'] = $days = Tool::stringToArray($this->submitted['data']['hours']);

        if (count($days) != 7) {
            $this->data['form_errors']['data']['hours'] = $this->text('Must be 7 lines, one line per day');
            return false;
        }

        $i = 0;
        foreach ($days as &$hours) {
            $hours = array_filter(str_replace(' ', '', explode('-', $hours)));

            if (!$hours) {
                continue;
            }

            $timestamps = array_filter($hours, function ($hour) {
                return strtotime($hour);
            });

            if (count($timestamps) != 2) {
                $this->data['form_errors']['data']['hours'] = $this->text('Error on line %s. Please use valid time formats: http://php.net/manual/en/datetime.formats.time.php', array('%s' => $i + 1));
                break;
            }

            $hours = array($hours[0] => $hours[1]);
            $i++;
        }

        if (empty($this->data['form_errors']['data']['hours'])) {
            $this->submitted['data']['hours'] = $days;
            return true;
        }

        return false;
    }

    /**
     * Validates social network URLs
     * @return boolean
     */
    protected function validateSocial()
    {
        if (empty($this->submitted['data']['social'])) {
            return true;
        }

        $this->submitted['data']['social'] = Tool::stringToArray($this->submitted['data']['social']);

        $reindexed = array();
        foreach ($this->submitted['data']['social'] as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $this->data['form_errors']['data']['social'] = $this->text('Invalid URL');
                return false;
            }

            $names = explode(".", parse_url($url, PHP_URL_HOST));
            $name = $names[count($names) - 2];
            $reindexed[$name] = $url;
        }

        $this->submitted['data']['social'] = $reindexed;
        return true;
    }

    /**
     * Validates a domain
     * @return boolean
     */
    protected function validateDomain()
    {
        if (!isset($this->submitted['domain'])) {
            return true;
        }

        if (!preg_match('/^[a-z0-9.:-]+$/i', $this->submitted['domain'])) {
            $this->data['form_errors']['domain'] = $this->text('Invalid domain. Example: domain.com or domain.com:8080');
            return false;
        }

        return true;
    }

    /**
     * Validates a store base path
     * @return boolean
     */
    protected function validateBasepath($store)
    {
        if (isset($this->submitted['basepath']) && mb_strlen($this->submitted['basepath']) > 50) {
            $this->data['form_errors']['basepath'] = $this->text('Content must not exceed %s characters', array('%s' => 50));
            return false;
        }

        $domain = $this->submitted['domain'];
        $basepath = $this->submitted['basepath'];
        $stores = $this->store->getList(array('domain' => $domain, 'basepath' => $basepath));

        $existing = false;
        foreach ($stores as $store_id => $data) {
            if (isset($store['store_id']) && $store['store_id'] == $store_id) {
                continue;
            }

            if ($domain === $data['domain'] && $basepath === $data['basepath']) {
                $existing = true;
            }
        }

        if ($existing) {
            $this->data['form_errors']['basepath'] = $this->text('Basepath %basepath already taken for this domain', array('%basepath' => $basepath));
            return false;
        }

        $this->submitted['basepath'] = trim($this->submitted['basepath'], '/');
        return true;
    }

    /**
     * Validates a name
     * @return boolean
     */
    protected function validateName()
    {
        if (empty($this->submitted['name']) || mb_strlen($this->submitted['name']) > 255) {
            $this->data['form_errors']['name'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates numeric values
     * @param string $key
     * @return boolean
     */
    protected function validateNumeric($key)
    {
        if (isset($this->submitted['data'][$key]) && !is_numeric($this->submitted['data'][$key])) {
            $this->data['form_errors']['data'][$key] = $this->text('Only numeric values allowed');
            return false;
        }

        return true;
    }

    /**
     * Validates a store title
     * @return boolean
     */
    protected function validateTitle()
    {
        if (empty($this->submitted['data']['title']) || mb_strlen($this->submitted['data']['title']) > 255) {
            $this->data['form_errors']['data']['title'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates translations
     * @return boolean
     */
    protected function validateTranslation()
    {
        if (empty($this->submitted['data']['translation'])) {
            return true;
        }

        $has_errors = false;
        foreach ($this->submitted['data']['translation'] as $code => $translation) {
            if (isset($translation['title']) && mb_strlen($translation['title']) > 255) {
                $this->data['form_errors']['data']['translation'][$code]['title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $has_errors = true;
            }
        }

        return !$has_errors;
    }

    /**
     * Validates file uploads
     * @return boolean
     */
    protected function validateUpload()
    {
        // Delete old logo
        if (!empty($this->submitted['delete_favicon']) && !empty($this->submitted['data']['favicon'])) {
            $this->submitted['data']['favicon'] = '';
        }

        // Delete old favicon
        if (!empty($this->submitted['delete_logo']) && !empty($this->submitted['data']['logo'])) {
            $this->submitted['data']['logo'] = '';
        }

        $this->file->setUploadPath('image/upload/store')->setHandler('image');

        $logo = $this->request->file('logo');
        $favicon = $this->request->file('favicon');

        if ($logo) {
            if ($this->file->upload($logo) !== true) {
                $this->data['form_errors']['logo'] = $this->text('Unable to upload the file');
                return false;
            }

            $this->submitted['data']['logo'] = $this->file->path($this->file->getUploadedFile());
        }

        if ($favicon) {
            if ($this->file->upload($favicon) !== true) {
                $this->data['form_errors']['favicon'] = $this->text('Unable to upload the file');
                return false;
            }

            $this->submitted['data']['favicon'] = $this->file->path($this->file->getUploadedFile());
        }

        return true;
    }
}
