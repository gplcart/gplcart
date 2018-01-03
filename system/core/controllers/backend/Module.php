<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\helpers\Graph as GraphHelper;
use gplcart\core\models\Module as ModuleModel;
use gplcart\core\traits\Listing as ListingTrait,
    gplcart\core\traits\Dependency as DependencyTrait;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to modules
 */
class Module extends BackendController
{

    use ListingTrait,
        DependencyTrait;

    /**
     * Module model instance
     * @var \gplcart\core\models\Module $module
     */
    protected $module_model;

    /**
     * Graph class instance
     * @var \gplcart\core\helpers\Graph $graph
     */
    protected $graph;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * The current module
     * @var array
     */
    protected $data_module = array();

    /**
     * @param ModuleModel $module
     * @param GraphHelper $graph
     */
    public function __construct(ModuleModel $module, GraphHelper $graph)
    {
        parent::__construct();

        $this->graph = $graph;
        $this->module_model = $module;
    }

    /**
     * Displays the module admin overview page
     */
    public function listModule()
    {
        $this->actionListModule();
        $this->clearCacheModule();

        $this->setTitleListModule();
        $this->setBreadcrumbListModule();
        $this->setFilterListModule();
        $this->setPagerListModule();

        $this->setData('types', $this->getTypesModule());
        $this->setData('modules', (array) $this->getListModule());
        $this->setData('available_modules', $this->module->getList());
        $this->setData('cached', is_file(GC_FILE_CONFIG_COMPILED_MODULE));

        $this->outputListModule();
    }

    /**
     * Clear module cache
     */
    protected function clearCacheModule()
    {
        $this->controlToken('refresh');

        if ($this->isQuery('refresh') && $this->module->clearCache()) {
            $this->redirect('', $this->text('Cache has been deleted'), 'success');
        }
    }

    /**
     * Sets the filter on the module overview page
     */
    protected function setFilterListModule()
    {
        $this->setFilter($this->getAllowedFiltersModule());
    }

    /**
     * Returns an array of allowed sorters/filters
     * @return array
     */
    protected function getAllowedFiltersModule()
    {
        return array('type', 'name', 'version', 'id', 'has_dependencies', 'created', 'modified');
    }

    /**
     * Applies an action to a module
     */
    protected function actionListModule()
    {
        $this->controlToken('action');

        $action = $this->getQuery('action');
        $module_id = $this->getQuery('module_id');

        if (!empty($action) && !empty($module_id)) {
            $this->setModule($module_id);
            $result = $this->startActionModule($action);
            $this->finishActionModule($result);
        }
    }

    /**
     * Set a module data
     * @param string $module_id
     */
    protected function setModule($module_id)
    {
        $this->data_module = $this->module->get($module_id);

        if (empty($this->data_module)) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Finishes module action
     * @param mixed $result
     */
    protected function finishActionModule($result)
    {
        if ($result === true) {
            $this->redirect('', $this->text('Module has been updated'), 'success');
        }

        if (is_string($result)) {
            $message = $result ? $result : $this->text('Operation unavailable');
            $this->redirect('', $message, 'danger');
        }

        foreach ((array) $result as $error) {
            $this->setMessage((string) $error, 'danger', true);
        }

        $this->redirect();
    }

    /**
     * Performs an action against a module
     * @param string $action
     * @return mixed
     */
    protected function startActionModule($action)
    {
        $this->controlAccess("module_$action");

        switch ($action) {
            case 'enable':
                return $this->module_model->enable($this->data_module['id']);
            case 'disable':
                return $this->module_model->disable($this->data_module['id']);
            case 'install':
                return $this->module_model->install($this->data_module['id']);
            case 'uninstall':
                return $this->module_model->uninstall($this->data_module['id']);
        }

        $this->outputHttpStatus(403);
        return null;
    }

    /**
     * Returns an array of modules
     * @param bool $count
     * @return array|int
     */
    protected function getListModule($count = false)
    {
        $modules = $this->module->getList();
        $this->checkDependenciesListModule($modules);

        foreach ($modules as &$module) {
            // Add key to sort and filter by dependencies
            $module['has_dependencies'] = !empty($module['requires']) || !empty($module['required_by']);
        }

        $allowed = $this->getAllowedFiltersModule();
        $this->filterList($modules, $allowed, $this->query_filter);
        $this->sortList($modules, $allowed, $this->query_filter, array('id' => 'asc'));


        if ($count) {
            return count($modules);
        }

        $this->limitList($modules, $this->data_limit);
        return $modules;
    }

    /**
     * Validates module dependencies
     * @param array $modules
     */
    protected function checkDependenciesListModule(array &$modules)
    {
        $this->validateDependencies($modules);
        $modules = $this->graph->build($modules);
    }

    /**
     * Returns an array of module types
     * @return array
     */
    protected function getTypesModule()
    {
        $types = array();
        foreach ($this->module->getList() as $module) {
            $types[$module['type']] = $this->text(ucfirst($module['type']));
        }

        return $types;
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListModule()
    {
        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->getListModule(true)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Sets titles on the module overview page
     */
    protected function setTitleListModule()
    {
        $this->setTitle($this->text('Modules'));
    }

    /**
     * Sets breadcrumbs on the module overview page
     */
    protected function setBreadcrumbListModule()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the module overview page
     */
    protected function outputListModule()
    {
        $this->output('module/list');
    }

}
