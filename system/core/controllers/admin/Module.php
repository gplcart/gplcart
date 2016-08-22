<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\classes\Curl;
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
     * Curl class instance
     * @var \core\classes\Curl $curl
     */
    protected $curl;

    /**
     * Constructor
     * @param ModelsModule $module
     * @param ModelsFile $file
     * @param Curl $curl
     */
    public function __construct(ModelsModule $module, ModelsFile $file,
            Curl $curl)
    {
        parent::__construct();

        $this->curl = $curl;
        $this->file = $file;
        $this->module = $module;
    }

    /**
     * Displays the module admin overview page
     * @param null|string $type
     */
    public function modules()
    {
        if ($this->isPosted('action')) {
            $this->action();
        }

        $modules = $this->getModules();
        $this->setData('modules', $modules);

        $this->setTitleModules();
        $this->setBreadcrumbModules();
        $this->outputModules();
    }

    /**
     * Applies an action to the module
     */
    protected function action()
    {
        $this->controlToken();
        $module_id = (string) $this->request->get('module_id');

        if (empty($module_id)) {
            $this->redirect();
        }

        $module = $this->module->get($module_id);

        if (empty($module)) {
            $this->redirect();
        }

        $action = (string) $this->request->get('action');
        $allowed = array('enable', 'disable', 'install', 'uninstall');

        if (!in_array($action, $allowed)) {
            $this->redirect();
        }

        $this->controlAccess("module_$action");

        $result = $this->module->{$action}($module_id);

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
     * Returns an array of modules
     * @return array
     */
    protected function getModules()
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
    protected function setTitleModules()
    {
        $this->setTitle($this->text('Modules'));
    }

    /**
     * Sets breadcrumbs on the module overview page
     */
    protected function setBreadcrumbModules()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));
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

        if ($this->isPosted('install')) {
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

        if ($this->hasErrors()) {
            return;
        }

        $uploaded = $this->getSubmitted('destination');
        $result = $this->module->installFromZip($uploaded);

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
        $options = array(
            'handler' => 'zip',
            'path' => 'private/modules',
            'file' => $this->request->file('file'),
        );

        $this->addValidator('file', array('upload' => $options));

        $this->setValidators();
        $path = $this->getValidatorResult('file');

        $this->setSubmitted('destination', GC_FILE_DIR . "/$path");
    }

    /**
     * Sets breadcrumbs on the module upload page
     */
    protected function setBreadcrumbUpload()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));

        $this->setBreadcrumb(array(
            'text' => $this->text('Modules'),
            'url' => $this->url('admin/module')));
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

    /**
     * Displays the marketplace overview page
     */
    public function marketplace()
    {
        $default = array(
            'sort' => $this->config('marketplace_sort', 'views'),
            'order' => $this->config('marketplace_order', 'desc')
        );

        $query = $this->getFilterQuery($default);
        $total = $this->getMarketplaceTotal($query);

        $query['limit'] = $this->setPager($total, $query);
        $results = $this->getMarketplaceList($query);

        $fields = array('category_id', 'price', 'views',
            'rating', 'title', 'downloads');

        $this->setFilter($fields);
        $this->setData('marketplace', $results);

        $this->setTitleMarketplace();
        $this->setBreadcrumbMarketplace();
        $this->outputMarketplace();
    }

    /**
     * Sets titles on the marketplace overview page
     */
    protected function setTitleMarketplace()
    {
        $this->setTitle($this->text('Marketplace'));
    }

    /**
     * Sets breadcrumbs on the marketplace overview page
     */
    protected function setBreadcrumbMarketplace()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/module'),
            'text' => $this->text('Modules')));
    }

    /**
     * Renders an outputs the marketplace overview page
     */
    protected function outputMarketplace()
    {
        $this->output('module/marketplace');
    }

    /**
     * Returns an array of marketplace items or null on error
     * @param array $options
     * @return array|null
     */
    protected function getMarketplaceList(array $options = array())
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
     * Returns total marketplace items found for the given conditions
     * @param array $options
     * @return integer
     */
    protected function getMarketplaceTotal(array $options = array())
    {
        $options['count'] = true;
        $result = $this->getMarketplaceList($options);

        return empty($result['total']) ? 0 : (int) $result['total'];
    }

}
