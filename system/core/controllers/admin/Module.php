<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
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
     * Constructor
     * @param ModelsModule $module
     */
    public function __construct(ModelsModule $module)
    {
        parent::__construct();

        $this->module = $module;
    }

    /**
     * Displays the module admin overview page
     * @param null|string $type
     */
    public function modules($type = null)
    {
        $module_id = $this->request->get('module_id');
        $action = $this->request->get('action');

        if (!empty($module_id) && !empty($action)) {
            $this->action($module_id, $action);
        }

        $this->data['modules'] = $this->getModules($type);

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

}
