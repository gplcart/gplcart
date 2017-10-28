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
use gplcart\core\traits\Dependency as DependencyTrait;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to modules
 */
class Module extends BackendController
{

    use DependencyTrait;

    /**
     * Module model instance
     * @var \gplcart\core\models\Module $module
     */
    protected $module;

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
        $this->module = $module;
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

        $this->setData('modules', $this->getListModule());
        $this->setData('cached', $this->config->hasModuleCache());
        $this->setData('available_modules', $this->module->getList());

        $this->outputListModule();
    }

    /**
     * Clear module cache
     */
    protected function clearCacheModule()
    {
        $this->controlToken('refresh');

        if ($this->isQuery('refresh') && $this->config->clearModuleCache()) {
            $this->redirect('', $this->text('Cache has been deleted'), 'success');
        }
    }

    /**
     * Sets the filter on the module overview page
     */
    protected function setFilterListModule()
    {
        $this->setFilter(array('type', 'name', 'version', 'id'));
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

        // Don't call methods like $this->module->{$action}
        // to make them visible in IDE
        switch ($action) {
            case 'enable':
                return $this->module->enable($this->data_module['id']);
            case 'disable':
                return $this->module->disable($this->data_module['id']);
            case 'install':
                return $this->module->install($this->data_module['id']);
            case 'uninstall':
                return $this->module->uninstall($this->data_module['id']);
        }

        $this->outputHttpStatus(403);
        return null;
    }

    /**
     * Returns an array of modules
     * @return array
     */
    protected function getListModule()
    {
        $modules = $this->module->getList();
        $this->checkDependenciesListModule($modules);

        $this->sortListModule($modules);
        $this->filterListModule($modules);
        $this->limitListModule($modules);

        return $modules;
    }

    /**
     * Validates module dependencies
     * @param array $modules
     */
    protected function checkDependenciesListModule(array &$modules)
    {
        $this->validateDependenciesTrait($modules);
        $modules = $this->graph->build($modules);
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListModule()
    {
        $pager = array(
            'query' => $this->query_filter,
            'total' => count($this->module->getList())
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Filters modules by a field
     * @param array $modules
     * @return array
     */
    protected function filterListModule(array &$modules)
    {
        $query = $this->query_filter;
        $allowed = array('type', 'name', 'version', 'id');
        $filter = array_intersect_key($query, array_flip($allowed));

        if (empty($filter)) {
            return $modules;
        }

        $term = reset($filter);
        $field = key($filter);

        $filtered = array_filter($modules, function ($module) use ($field, $term) {
            return stripos($module[$field], $term) !== false;
        });

        return $modules = $filtered;
    }

    /**
     * Slices an array of modules
     * @param array $modules
     */
    protected function limitListModule(array &$modules)
    {
        list($from, $to) = $this->data_limit;
        $modules = array_slice($modules, $from, $to, true);
    }

    /**
     * Sort modules by a field
     * @param array $modules
     * @return array
     */
    protected function sortListModule(array &$modules)
    {
        $query = $this->query_filter;

        if (empty($query['order'])) {
            $query['order'] = 'asc';
        }

        if (empty($query['sort'])) {
            $query['sort'] = 'id';
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('type', 'name', 'version', 'id');

        if (!in_array($query['order'], $allowed_order) || !in_array($query['sort'], $allowed_sort)) {
            return $modules;
        }

        uasort($modules, function ($a, $b) use ($query) {

            if (empty($a[$query['sort']]) || empty($b[$query['sort']])) {
                return 0;
            }

            $diff = strcmp($a[$query['sort']], $b[$query['sort']]);

            if ($diff === 0) {
                return 0;
            }

            if ($query['order'] === 'asc') {
                return $diff > 0;
            }

            return $diff < 0;
        });

        return $modules;
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
        $this->setBreadcrumbHome();
    }

    /**
     * Render and output the module overview page
     */
    protected function outputListModule()
    {
        $this->output('module/list');
    }

}
