<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\helpers\Curl as CurlHelper;
use gplcart\core\helpers\Graph as GraphHelper;
use gplcart\core\models\Module as ModuleModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to modules
 */
class Module extends BackendController
{

    use \gplcart\core\traits\Dependency;

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
     * Curl class instance
     * @var \gplcart\core\helpers\Curl $curl
     */
    protected $curl;

    /**
     * The current module
     * @var array
     */
    protected $data_module = array();

    /**
     * Constructor
     * @param ModuleModel $module
     * @param CurlHelper $curl
     * @param GraphHelper $graph
     */
    public function __construct(ModuleModel $module, CurlHelper $curl,
            GraphHelper $graph)
    {
        parent::__construct();

        $this->curl = $curl;
        $this->graph = $graph;
        $this->module = $module;
    }

    /**
     * Displays the module admin overview page
     */
    public function listModule()
    {
        $this->actionModule();

        $this->setTitleListModule();
        $this->setBreadcrumbListModule();

        $query = $this->getFilterQuery();
        $allowed = array('type', 'name', 'version', 'id');
        $this->setFilter($allowed, $query);

        $total = $this->getTotalModule($query);
        $limit = $this->setPager($total, $query);
        $modules = $this->getListModule($query, $limit);

        $this->setData('modules', $modules);
        $this->outputListModule();
    }

    /**
     * Applies an action to the module
     */
    protected function actionModule()
    {
        $action = (string) $this->request->get('action');
        $module_id = (string) $this->request->get('module_id');

        if (empty($action) || empty($module_id)) {
            return null;
        }

        $this->setModule($module_id);

        $result = $this->startActionModule($action);
        $this->finishActionModule($action, $result);
    }

    /**
     * Set module data
     * @param string $module_id
     */
    protected function setModule($module_id)
    {
        $module = $this->module->get($module_id);

        if (empty($module)) {
            $this->outputHttpStatus(403);
        }

        $this->data_module = $module;
    }

    /**
     * Finishes module action
     * @param string $action
     * @param mixed $result
     */
    protected function finishActionModule($action, $result)
    {
        if ($result === true) {

            $message = $this->text('Module has been updated');

            if ($action === 'backup') {
                $vars = array('@url' => $this->url('admin/tool/backup'));
                $message = $this->text('Backup has been <a href="@url">created</a>', $vars);
            }

            $this->redirect('', $message, 'success');
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

        $id = $this->data_module['id'];

        // Don't call methods like $this->module->{$action}
        // to make them visible in IDE
        switch ($action) {
            case 'enable':
                return $this->module->enable($id);
            case 'disable':
                return $this->module->disable($id);
            case 'install':
                return $this->module->install($id);
            case 'uninstall':
                return $this->module->uninstall($id);
            case 'delete':
                return $this->module->delete($id);
            case 'backup':
                return $this->module->backup($id);
        }

        $this->outputHttpStatus(403);
    }

    /**
     * Returns an array of modules
     * @param array $query
     * @param array $limit
     * @return array
     */
    protected function getListModule(array $query, array $limit = array())
    {
        $modules = $this->module->getList();
        $this->sortListModule($modules, $query);
        $this->filterListModule($modules, $query);
        $this->limitListModule($modules, $limit);
        $this->checkDependenciesListModule($modules);
        $this->prepareListModule($modules);

        return $modules;
    }

    /**
     * Validates dependencies and append requires/required by info
     * to every module in the array
     * @param array $modules
     */
    protected function checkDependenciesListModule(array &$modules)
    {
        $this->validateDependenciesTrait($modules);
        $modules = $this->graph->build($modules);
    }

    /**
     * Adds axtra data to every module in the array
     * @param array $modules
     */
    protected function prepareListModule(array &$modules)
    {
        foreach ($modules as &$module) {
            $module['always_enabled'] = $this->module->isActiveTheme($module['id']);
            $module['type_name'] = $this->text(ucfirst($module['type']));
        }
    }

    /**
     * Returns a total number of modules found 
     * @param array $query
     * @return integer
     */
    protected function getTotalModule(array $query)
    {
        $modules = $this->getListModule($query);
        return count($modules);
    }

    /**
     * Filters modules using by a field
     * @param array $modules
     * @param array $query
     * @return array
     */
    protected function filterListModule(array &$modules, array $query)
    {
        $allowed = array('type', 'name', 'version', 'id');

        // Remove all but white-listed fields
        $filter = array_intersect_key($query, array_flip($allowed));

        if (empty($filter)) {
            return $modules;
        }

        // Use only first pair field => search term
        $term = reset($filter);
        $field = key($filter);

        // %LIKE% filter
        $filtered = array_filter($modules, function ($module) use ($field, $term) {
            return stripos($module[$field], $term) !== false;
        });

        $modules = $filtered;
        return $modules;
    }

    /**
     * Slices an array of modules using starting offset and max length
     * @param array $modules
     * @param array $limit
     */
    protected function limitListModule(array &$modules, array $limit)
    {
        if (!empty($limit)) {
            list($from, $to) = $limit;
            $modules = array_slice($modules, $from, $to, true);
        }
    }

    /**
     * Sort modules by a field
     * @param array $modules
     * @param array $query
     * @return array
     */
    protected function sortListModule(array &$modules, array $query)
    {
        if (empty($query['order']) || empty($query['sort'])) {
            return $modules;
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('type', 'name', 'version', 'id');

        if (!in_array($query['order'], $allowed_order)//
                || !in_array($query['sort'], $allowed_sort)) {
            return $modules;
        }

        uasort($modules, function($a, $b) use ($query) {

            $diff = strcmp($a[$query['sort']], $b[$query['sort']]);

            if ($diff === 0) {
                return 0;
            }

            if ($query['order'] == 'asc') {
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
        $breadcrumb = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the module overview page templates
     */
    protected function outputListModule()
    {
        $this->output('module/list');
    }

    /**
     * Displays upload module page
     */
    public function uploadModule()
    {
        $this->setBreadcrumbUploadModule();
        $this->setTitleUploadModule();
        $this->controlAccessUploadModule();
        $this->submitUploadModule();
        $this->outputUploadModule();
    }

    /**
     * Controls access to module upload form
     */
    protected function controlAccessUploadModule()
    {
        $access = $this->access('module_install')//
                && $this->access('file_upload')//
                && $this->access('module_upload');

        if (!$access) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Installs a uploaded module
     */
    protected function submitUploadModule()
    {
        if ($this->isPosted('install') && $this->validateUploadModule()) {
            $this->installUploadedModule();
        }
    }

    /**
     * Install a uploaded module
     */
    protected function installUploadedModule()
    {
        $this->controlAccessUploadModule();

        $uploaded = $this->getSubmitted('destination');
        $result = $this->module->installFromZip($uploaded);

        if ($result !== true) {
            $errors = implode('<br>', array_filter((array) $result));
            $message = empty($errors) ? $this->text('An error occurred') : $errors;
            $this->redirect('', $message, 'warning');
        }

        $vars = array('!href' => $this->url('admin/module/list'));
        $message = $this->text('The module has been <a href="!href">uploaded and installed</a>. You have to enable it manually', $vars);
        $this->redirect('', $message, 'success');
    }

    /**
     * Validates and uploads a zip archive
     * @return boolean
     */
    protected function validateUploadModule()
    {
        $this->validate('module_upload');
        return !$this->hasErrors();
    }

    /**
     * Sets breadcrumbs on the module upload page
     */
    protected function setBreadcrumbUploadModule()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Modules'),
            'url' => $this->url('admin/module/list')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Sets titles on the module upload page
     */
    protected function setTitleUploadModule()
    {
        $this->setTitle($this->text('Upload module'));
    }

    /**
     * Renders the module upload page
     */
    protected function outputUploadModule()
    {
        $this->output('module/upload');
    }

    /**
     * Displays the marketplace overview page
     */
    public function marketplaceModule()
    {
        $this->setTitleMarketplaceModule();
        $this->setBreadcrumbMarketplaceModule();

        $default = array(
            'sort' => $this->config('marketplace_sort', 'views'),
            'order' => $this->config('marketplace_order', 'desc')
        );

        $query = $this->getFilterQuery($default);
        $total = $this->getTotalMarketplaceModule($query);

        $query['limit'] = $this->setPager($total, $query);
        $results = $this->getListMarketplaceModule($query);

        $fields = array('category_id', 'price', 'views',
            'rating', 'title', 'downloads');

        $this->setFilter($fields);
        $this->setData('marketplace', $results);
        $this->outputMarketplaceModule();
    }

    /**
     * Returns total marketplace items found for the given conditions
     * @param array $options
     * @return integer
     */
    protected function getTotalMarketplaceModule(array $options = array())
    {
        $options['count'] = true;
        $result = $this->getListMarketplaceModule($options);

        return empty($result['total']) ? 0 : (int) $result['total'];
    }

    /**
     * Returns an array of marketplace items or null on error
     * @param array $options
     * @return array|null
     */
    protected function getListMarketplaceModule(array $options = array())
    {
        $options += array('core' => strtok(GC_VERSION, '.'));
        $response = $this->curl->post(GC_MARKETPLACE_API_URL, array('fields' => $options));
        $info = $this->curl->getInfo();

        if (empty($info['http_code']) || $info['http_code'] != 200) {
            return array();
        }

        $results = json_decode($response, true);

        if (empty($results['items'])) {
            return $results;
        }

        foreach ($results['items'] as &$item) {
            $item['price'] = floatval($item['price']);
        }
        return $results;
    }

    /**
     * Sets titles on the marketplace overview page
     */
    protected function setTitleMarketplaceModule()
    {
        $this->setTitle($this->text('Marketplace'));
    }

    /**
     * Sets breadcrumbs on the marketplace overview page
     */
    protected function setBreadcrumbMarketplaceModule()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/module/list'),
            'text' => $this->text('Modules')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders an outputs the marketplace overview page
     */
    protected function outputMarketplaceModule()
    {
        $this->output('module/marketplace');
    }

}
