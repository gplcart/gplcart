<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\backend;

use core\helpers\Curl as CurlHelper;
use core\models\Module as ModuleModel;
use core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to modules
 */
class Module extends BackendController
{

    /**
     * Module model instance
     * @var \core\models\Module $module
     */
    protected $module;

    /**
     * Curl class instance
     * @var \core\helpers\Curl $curl
     */
    protected $curl;

    /**
     * Constructor
     * @param ModuleModel $module
     * @param CurlHelper $curl
     */
    public function __construct(ModuleModel $module, CurlHelper $curl)
    {
        parent::__construct();

        $this->curl = $curl;
        $this->module = $module;
    }

    /**
     * Displays the module admin overview page
     */
    public function listModule()
    {
        $this->actionModule();

        $modules = $this->getListModule();
        $this->setData('modules', $modules);

        $this->setTitleListModule();
        $this->setBreadcrumbListModule();
        $this->outputListModule();
    }

    /**
     * Applies an action to the module
     */
    protected function actionModule()
    {
        $action = (string) $this->request->get('action');

        if (empty($action)) {
            return null;
        }

        $this->controlToken();

        $module_id = (string) $this->request->get('module_id');

        if (empty($module_id)) {
            $this->outputError(403);
        }

        $module = $this->module->get($module_id);

        if (empty($module)) {
            $this->outputError(403);
        }

        $allowed = array('enable', 'disable', 'install', 'uninstall', 'delete', 'backup');

        if (!in_array($action, $allowed)) {
            $this->outputError(403);
        }

        $this->controlAccess("module_$action");

        $result = $this->module->{$action}($module_id);

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
     * Returns an array of modules
     * @return array
     */
    protected function getListModule()
    {
        $modules = $this->module->getList();

        foreach ($modules as &$module) {
            $module['always_enabled'] = $this->module->isActiveTheme($module['id']);
            $module['type_name'] = $this->text(ucfirst($module['type']));
        }

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
        $this->controlAccess('module_install');

        $this->submitUploadModule();
        $this->setBreadcrumbUploadModule();
        $this->setTitleUploadModule();
        $this->outputUploadModule();
    }

    /**
     * Installs a uploaded module
     * @return null
     */
    protected function submitUploadModule()
    {
        if (!$this->isPosted('install')) {
            return null;
        }

        $this->validateUploadModule();

        if ($this->hasErrors()) {
            return null;
        }

        $this->installUploadedModule();
        return null;
    }

    /**
     * Install a uploaded module
     */
    protected function installUploadedModule()
    {
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

        $this->setTitleMarketplaceModule();
        $this->setBreadcrumbMarketplaceModule();
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
