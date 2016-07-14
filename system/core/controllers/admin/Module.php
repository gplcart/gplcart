<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\File as ModelsFile;
use core\models\Module as ModelsModule;

/**
 * Handles incoming requests and outputs data related to modules
 */
class Module extends Controller
{

    /**
     * Module model instance
     * @var \core\models\Module $module
     */
    protected $module;

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Constructor
     * @param ModelsModule $module
     * @param ModelsFile $file
     */
    public function __construct(ModelsModule $module, ModelsFile $file)
    {
        parent::__construct();

        $this->file = $file;
        $this->module = $module;
    }

    /**
     * Displays the module admin overview page
     * @param null|string $type
     */
    public function modules()
    {
        $type = $this->request->get('type');
        $action = $this->request->get('action');
        $module_id = $this->request->get('module_id');

        if (!empty($module_id) && !empty($action)) {
            $this->action($module_id, $action);
        }

        $this->data['modules'] = $this->getModules($type);
        $this->data['upload_access'] = ($this->access('module_upload'));

        $this->setTitleModules();
        $this->setBreadcrumbModules($type);
        $this->outputModules();
    }

    /**
     * Applies an action to the module
     * @param string $module_id
     * @param string $action
     */
    protected function action($module_id, $action)
    {
        $this->controlToken();

        $module = $this->module->get($module_id);

        if (empty($module)) {
            $this->redirect();
        }

        if (!in_array($action, array('enable', 'disable', 'install', 'uninstall'))) {
            $this->redirect();
        }

        $this->controlAccess("module_$action");

        $result = $this->module->{$action}($module_id);

        if ($result === true) {
            $this->redirect('', $this->text('Modules have been updated'), 'success');
        }

        if (is_string($result)) {
            $message = $result ? $result : $this->text('Operation unavailable');
            $this->redirect('', $message, 'danger');
        }

        foreach ((array) $result as $error) {
            $this->session->setMessage((string) $error, 'danger');
        }

        $this->redirect();
    }

    /**
     * Returns an array of modules
     * @param string|null $type
     * @return array
     */
    protected function getModules($type = null)
    {
        if (empty($type)) {
            $modules = $this->module->getList();
        } else {
            $modules = $this->module->getByType($type);
        }

        foreach ($modules as &$module) {
            if ($this->module->isActiveTheme($module['id'])) {
                $module['always_enabled'] = true;
            }

            $module['type_name'] = $this->text(ucfirst($module['type']));
        }

        return $modules;
    }

    /**
     * Sets titles on the module overview page
     */
    protected function setTitleModules()
    {
        $this->setTitle($this->text('Modules'));
    }

    /**
     * Sets breadcrumbs on the module overview page
     * @param string $type
     */
    protected function setBreadcrumbModules($type)
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));

        if (!empty($type)) {
            $this->setBreadcrumb(array('text' => $this->text('All modules'), 'url' => $this->url('admin/module')));
        }
    }

    /**
     * Renders the module overview page templates
     */
    protected function outputModules()
    {
        $this->output('module/list');
    }

    /**
     * Displays upload module page
     */
    public function upload()
    {
        $this->controlAccess('module_install');

        if ($this->request->post('install')) {
            $this->submitUpload();
        }

        $this->setBreadcrumbUpload();
        $this->setTitleUpload();
        $this->outputUpload();
    }

    /**
     * Installs a uploaded module
     */
    protected function submitUpload()
    {
        $this->validateUpload();
        $errors = $this->formErrors();

        if (!empty($errors)) {
            return false;
        }

        $result = $this->module->installFromZip($this->submitted['destination']);

        if ($result === true) {
            $message = $this->text('The module has been <a href="!href">uploaded and installed</a>. You have to enable it manually', array(
                '!href' => $this->url('admin/module')));

            $this->redirect('', $message, 'success');
        }

        $this->redirect('', $result, 'warning');
    }

    /**
     * Validates and uploads a zip archive
     * @return boolean
     */
    protected function validateUpload()
    {
        $this->submitted = $this->request->file('file');
        $this->file->setHandler('zip')->setUploadPath('private/modules');
        $result = $this->file->upload($this->submitted);

        if ($result !== true) {
            $this->data['form_errors']['file'] = $this->text('Unable to upload the file');
            return false;
        }

        $this->submitted['destination'] = $this->file->getUploadedFile();
    }

    /**
     * Sets breadcrumbs on the module upload page
     */
    protected function setBreadcrumbUpload()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
        $this->setBreadcrumb(array('text' => $this->text('Modules'), 'url' => $this->url('admin/module')));
    }

    /**
     * Sets titles on the module upload page
     */
    protected function setTitleUpload()
    {
        $this->setTitle($this->text('Upload module'));
    }

    /**
     * Renders the module upload page
     */
    protected function outputUpload()
    {
        $this->output('module/upload');
    }

}
