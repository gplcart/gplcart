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
        $type = (string) $this->request->get('type');
        $action = (string) $this->request->get('action');
        $module_id = (string) $this->request->get('module_id');

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

        if (!in_array($action, array('enable', 'disable', 'install', 'uninstall'), true)) {
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
        $errors = $this->getErrors();

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
            $this->errors['file'] = $this->text('Unable to upload the file');
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

    /**
     * Displays the marketplace overview page
     */
    public function marketplace()
    {
        $order = $this->config->get('marketplace_order', 'desc');
        $sort = $this->config->get('marketplace_sort', 'views');
        $default = array('sort' => $sort, 'order' => $order);

        $query = $this->getFilterQuery($default);
        $total = $this->getMarketplaceTotal($query);

        $query['limit'] = $this->setPager($total, $query);
        $results = $this->getMarketplaceList($query);

        $this->setFilter(array(
            'category_id',
            'price',
            'views',
            'rating',
            'title',
            'downloads'));

        $this->data['marketplace'] = $results;

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
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->setBreadcrumb(array('url' => $this->url('admin/module'), 'text' => $this->text('Modules')));
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
