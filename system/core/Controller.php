<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\Container;
use core\classes\Tool;

/**
 * Base controller class. Provides methods to be used in the child classes and
 * some basic system functions such as access control etc.
 */
class Controller
{

    /**
     * Name of the current theme
     * @var string
     */
    protected $theme;

    /**
     * Frontend theme module name
     * @var string
     */
    protected $theme_frontend;

    /**
     * Backend theme module name
     * @var string
     */
    protected $theme_backend;

    /**
     * An numeric ID of the user currently visiting the site
     * @var integer
     */
    protected $uid;

    /**
     * A random string generated from the session
     * @var string
     */
    protected $token;

    /**
     * Base URL
     * @var string
     */
    protected $base;

    /**
     * Current URL path without query
     * @var string
     */
    protected $path;

    /**
     * Full current URI including query and schema
     * @var string
     */
    protected $uri;

    /**
     * Current URN
     * @var string
     */
    protected $urn;

    /**
     * Current query
     * @var array
     */
    protected $query = array();

    /**
     * Access for the current route
     * @var string
     */
    protected $access = '';

    /**
     * Whether the site in maintenance mode
     * @var boolean
     */
    protected $maintenance = false;

    /**
     * UNIX timestamp of the last request stored in the session
     * @var integer
     */
    protected $last_activity = 0;

    /**
     * Array of template variables
     * @var array
     */
    protected $data = array();

    /**
     * Array of templates keyed by region for the current theme
     * @var array
     */
    protected $templates = array();

    /**
     * Current store ID
     * @var integer
     */
    protected $store_id;

    /**
     * Current route data
     * @var array
     */
    protected $current_route = array();

    /**
     * Current user data
     * @var array
     */
    protected $current_user = array();

    /**
     * Array of the current store
     * @var array
     */
    protected $current_store = array();

    /**
     * Device type
     * @var string
     */
    protected $current_device;

    /**
     * Current job
     * @var array
     */
    protected $current_job = array();

    /**
     * Array of the current theme module info
     * @var array
     * @see \modules\example\Example::info()
     */
    protected $theme_settings = array();

    /**
     * Array of enabled languages
     * @var array
     */
    protected $languages = array();

    /**
     * Interval in seconds between cron calls
     * @var integer
     */
    protected $cron_interval;

    /**
     * UNIX-timestamp when cron was lastly started
     * @var integer
     */
    protected $cron_last_run;

    /**
     * Cron secret key to launch from outside
     * @var string
     */
    protected $cron_key;

    /**
     * Submitted form values
     * @var array
     */
    protected $submitted = array();

    /**
     * Array of validation errors
     * @var array
     */
    protected $errors = array();

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * Store model instance
     * @var \core\models\Store $store
     */
    protected $store;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Current language code
     * @var string
     */
    protected $langcode;

    /**
     * Notification model instance
     * @var \core\models\Notification $notification
     */
    protected $notification;

    /**
     * Url class instance
     * @var \core\classes\Url $url
     */
    protected $url;

    /**
     * Request class instance
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * Response class instance
     * @var \core\classes\Response $response
     */
    protected $response;

    /**
     * Route class instance
     * @var \core\Route $route
     */
    protected $route;

    /**
     * Session class instance
     * @var \core\classes\Session $session
     */
    protected $session;

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Twig class instance
     * @var \core\classes\Twig $twig
     */
    protected $twig;

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Document class instance
     * @var \core\Document $document
     */
    protected $document;

    /**
     * Filter class instance
     * @var \core\Filter $filter
     */
    protected $filter;

    /**
     * Device class instance
     * @var \core\Device $device
     */
    protected $device;

    /**
     * Pager class instance
     * @var \core\classes\Pager $pager
     */
    protected $pager;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Constructor
     */
    protected function __construct()
    {
        /* @var $user \core\models\User */
        $this->user = Container::instance('core\\models\\User');

        /* @var $store \core\models\Store */
        $this->store = Container::instance('core\\models\\Store');

        /* @var $language \core\models\Language */
        $this->language = Container::instance('core\\models\\Language');

        /* @var $notification \core\models\Notification */
        $this->notification = Container::instance('core\\models\\Notification');

        /* @var $url \core\classes\Url */
        $this->url = Container::instance('core\\classes\\Url');

        /* @var $request \core\classes\Request */
        $this->request = Container::instance('core\\classes\\Request');

        /* @var $response \core\classes\Response */
        $this->response = Container::instance('core\\classes\\Response');

        /* @var $session \core\classes\Session */
        $this->session = Container::instance('core\\classes\\Session');

        /* @var $hook \core\Hook */
        $this->hook = Container::instance('core\\Hook');

        /* @var $route \core\Route */
        $this->route = Container::instance('core\\Route');

        /* @var $config \core\Config */
        $this->config = Container::instance('core\\Config');

        /* @var $logger \core\classes\Logger */
        $this->logger = Container::instance('core\\Logger');

        /* @var $document \core\classes\Document */
        $this->document = Container::instance('core\\classes\\Document');

        /* @var $filter \core\classes\Filter */
        $this->filter = Container::instance('core\\classes\\Filter');

        /* @var $device \core\classes\Device */
        $this->device = Container::instance('core\\classes\\Device');

        /* @var $pager \core\classes\Pager */
        $this->pager = Container::instance('core\\classes\\Pager');

        $this->token = $this->config->token();

        $this->setJob();
        $this->setRoute();
        $this->setDevice();
        $this->setStore();
        $this->setTheme();
        $this->setLanguage();
        $this->setAccess();
        $this->setCron();
        $this->setData();
        $this->setScripts();
        $this->setNotifications();
        $this->setMaintenance();
    }

    /**
     * Catches end of PHP processing
     */
    public function __destruct()
    {
        $this->text();
    }

    /**
     * Whether the user has a given access
     * @param string $permission
     * @return boolean
     */
    public function access($permission)
    {
        return $this->user->access($permission);
    }

    /**
     * Renders a template
     * @param string $file
     * @param array $data
     * @param boolean $fullpath
     * @return string
     */
    public function render($file, array $data = array(), $fullpath = false)
    {
        $module = $this->theme;

        if (strpos($file, ':') !== false) {
            $fullpath = false;
            $parts = explode(':', $file, 2);
            $property = $parts[0];
            $module = isset($this->{"theme_$property"}) ? $this->{"theme_$property"} : $this->theme;
            $file = $parts[1];
        }

        $template = $fullpath ? $file : GC_MODULE_DIR . "/$module/templates/$file";
        $extension = isset($this->theme_settings['twig']) ? '.twig' : '.php';

        if ((substr($template, -strlen($extension)) !== $extension)) {
            $template .= $extension;
        }

        $this->hook->fire('render', $template, $data);

        $this->setPhpErrors($data);

        if (!file_exists($template)) {
            return "Could not load template $template";
        }

        if ($extension === '.twig') {
            return $this->renderTwig($template, $data, (array) $this->theme_settings['twig']);
        }

        return $this->renderPhp($template, $data);
    }

    /**
     * Returns a formatted URL
     * @param string $path
     * @param array $query
     * @param boolean $absolute
     * @return string
     */
    public function url($path = '', array $query = array(), $absolute = false)
    {
        return $this->url->get($path, $query, $absolute);
    }

    /**
     * Translates a text
     * @param string $string
     * @param array $arguments
     * @return string
     */
    public function text($string = null, array $arguments = array())
    {
        return $this->language->text($string, $arguments);
    }

    /**
     * Whether the current user is superadmin
     * @return boolean
     */
    public function isSuperadmin()
    {
        return $this->user->isSuperadmin();
    }

    /**
     * Formats a local time/date
     * @param integer|null $timestamp
     * @return string
     */
    public function date($timestamp = null)
    {
        if (!isset($timestamp)) {
            $timestamp = GC_TIME;
        }

        $format = $this->config->get('date_format', 'd.m.Y H:i');
        return date($format, (int) $timestamp);
    }

    /**
     * Formats tag attributes
     * @param array $attributes
     * @return string
     */
    public function attributes(array $attributes)
    {
        foreach ($attributes as $attribute => &$data) {
            $data = implode(' ', (array) $data);
            $data = $attribute . '="' . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') . '"';
        }

        return empty($attributes) ? '' : ' ' . implode(' ', $attributes);
    }

    /**
     * Sets the current route data
     */
    protected function setRoute()
    {
        // Set access for the route
        $this->current_route = $this->route->getCurrent();

        if (isset($this->current_route['access'])) {
            $this->access = $this->current_route['access'];
        }

        $this->base = $this->request->base();
        $this->path = $this->url->path();
        $this->uri = $this->request->scheme() . $this->request->host() . $this->request->urn();
        $this->urn = $this->request->urn();
        $this->query = $this->request->get();
        $this->langcode = $this->route->getLangcode();
    }

    /**
     * Defines the current user device
     * @return null
     */
    protected function setDevice()
    {
        $device = $this->session->get('device');

        if (!empty($device)) {
            $this->current_device = $device;
            return;
        }

        $this->current_device = 'desktop';

        if ($this->device->isMobile()) {
            $this->current_device = $this->device->isTablet() ? 'tablet' : 'mobile';
        }

        $this->session->set('device', null, $this->current_device);
        return;
    }

    /**
     * Sets the current store data
     */
    protected function setStore()
    {
        $this->current_store = $this->store->current();
        if (isset($this->current_store['store_id'])) {
            $this->store_id = $this->current_store['store_id'];
        }
    }

    /**
     * Sets theme data
     * @param null|string $theme
     */
    protected function setTheme($theme = null)
    {
        $this->theme_frontend = $this->config->get('theme', 'frontend');
        $this->theme_backend = $this->config->get('theme_backend', 'backend');

        if (isset($theme)) {
            $this->theme = $theme;
        } else {
            if ($this->url->isBackend()) {
                $this->theme = $this->theme_backend;
            } elseif ($this->url->isInstall()) {
                $this->theme = $this->theme_frontend;
            } elseif (!empty($this->current_store)) {
                $this->theme_frontend = $this->theme = $this->store->config('theme');

                if ($this->current_device === 'mobile') {
                    $this->theme_frontend = $this->theme = $this->store->config('theme_mobile');
                }

                if ($this->current_device === 'tablet') {
                    $this->theme_frontend = $this->theme = $this->store->config('theme_tablet');
                }
            }
        }

        if (empty($this->theme)) {
            $this->response->error404();
        }

        $theme_class = $this->config->getModuleClass($this->theme);
        $theme_data = $this->config->getModuleData($theme_class);

        if (empty($theme_data['info'])) {
            $this->response->error404();
        }

        if (!empty($theme_data['info']['settings'])) {
            $this->theme_settings = $theme_data['info']['settings'];
        }

        if (isset($this->theme_settings['twig'])) {
            /* @var $twig \core\classes\Twig */
            $this->twig = Container::instance('core\\classes\\Twig');
        }

        if (empty($this->theme_settings['templates'])) {
            $this->templates = $this->getDefaultTemplates();
        } else {
            $this->templates = $this->theme_settings['templates'];
        }
    }

    /**
     * Loads translations, available languages etc
     */
    protected function setLanguage()
    {
        $this->language->load();
        $this->languages = $this->language->getList(true);
    }

    /**
     * Sets access to the current page
     * @return boolean
     */
    protected function setAccess()
    {
        if ($this->url->isInstall()) {
            return;
        }

        $this->controlToken(false);

        $this->uid = $this->user->id();

        if (!empty($this->uid)) {
            $this->current_user = $this->user->get($this->uid);
            if (empty($this->current_user['status']) || $this->current_user['role_id'] != $this->user->roleId()) {
                $this->session->delete();
                $this->url->redirect('login');
            }
        }

        // Prevent Cross-Site Request Forgery (CSRF)
        if ($this->request->method() === 'POST') {
            if (!Tool::hashEquals($this->request->post('token'), $this->token)) {
                $this->response->error403();
            }

            $file = $this->request->file();

            // Check access to upload a file
            if (!empty($file) && !$this->access('file_upload')) {
                $this->response->error403();
            }
        }

        // Check access only on restricted areas
        if (!$this->url->isBackend() && $this->url->isAccount() === false) {
            return true;
        }

        // Redirect anonymous to login form
        if (empty($this->uid)) {
            $this->url->redirect('login', array('target' => $this->path));
        }

        // Control session timeout
        $this->controlSessionTimeout();
        $this->controlAccessAdmin();
        $this->controlAccessAccount();
    }

    /**
     * Controls token in the URL query
     * @param boolean $required Whether the token must be presented in the URL
     */
    protected function controlToken($required = true)
    {
        $token = $this->request->get('token', null);

        if ($required && !$this->config->tokenValid($token)) {
            $this->response->error403();
        }

        if (isset($token) && !$this->config->tokenValid($token)) {
            $this->response->error403();
        }
    }

    /**
     * Controls session timeout
     * @return integer Timestamp of the last activity
     */
    protected function controlSessionTimeout()
    {
        $time = GC_TIME;

        $this->last_activity = (int) $this->session->get('last_activity');

        if (!empty($this->last_activity) && ($time - $this->last_activity > GC_SESSION_TIMEOUT)) {
            $this->session->delete();
            $this->url->redirect('login', array('target' => $this->path));
        }

        $this->last_activity = $time;
        $this->session->set('last_activity', null, $time);
        return $time;
    }

    /**
     * Controls access to admin pages
     * @return null
     */
    protected function controlAccessAdmin()
    {
        // Check only admin pages
        if (!$this->url->isBackend()) {
            return;
        }

        if (!$this->isSuperadmin() && empty($this->current_user['role_status'])) {
            $this->outputError(403);
        }

        // Admin must have "admin" access plus route specific access
        if (!$this->access('admin')) {
            $this->redirect('/');
        }

        if (!$this->access($this->access)) {
            $this->outputError(403);
        }
    }

    /**
     * Displays 403 error when the current user is not superadmin
     */
    protected function controlAccessSuperAdmin()
    {
        if (!$this->isSuperadmin()) {
            $this->outputError(403);
        }
    }

    /**
     * Redirects to a new location
     * @param string $url
     * @param string $message
     * @param string $severity
     */
    protected function redirect($url = '', $message = '', $severity = 'info')
    {
        if ($message !== '') {
            $this->session->setMessage($message, $severity);
        }

        $this->url->redirect($url);
    }

    /**
     * Displsys an error page
     * @param integer $code
     */
    protected function outputError($code = 403)
    {
        $title = (string) $this->response->statuses($code);

        if ($title !== '') {
            $this->setTitle($title, false);
        }

        $this->output("common/error/$code", array('headers' => $code));
    }

    /**
     * Sets page <title> tag
     * @param string $title
     * @param boolean $both
     * @return string
     */
    protected function setTitle($title, $both = true)
    {
        return $this->document->title($title, $both);
    }

    /**
     * Outputs rendered page
     * @param array|string $templates
     * @param array $options
     */
    protected function output($templates, array $options = array())
    {
        if (is_string($templates)) {
            $templates = array('region_content' => $templates);
        }

        $this->prepareOutput();
        $this->hook->fire('data', $this->data);

        $templates += $this->templates;
        $layout_template = $templates['layout'];
        unset($templates['layout']);

        $layout_data = $body_data = $this->data;

        foreach ($templates as $region => $template) {
            if (!in_array($region, array('region_head', 'region_body'))) {
                $body_data[$region] = $this->renderRegion($region, $template);
            }
        }

        $layout_data['region_head'] = $this->render($templates['region_head'], $this->data);
        $layout_data['region_body'] = $this->render($templates['region_body'], $body_data);

        $this->response->html($this->render($layout_template, $layout_data), $options);
    }

    /**
     * Renders a region
     * @param string $region
     * @param string $template
     * @return string
     */
    protected function renderRegion($region, $template)
    {
        if (!isset($this->data[$region])) {
            return $this->render($template, $this->data);
        }

        $this->data[$region] = (array) $this->data[$region];
        Tool::sortWeight($this->data[$region]);

        $items = array();
        foreach ($this->data[$region] as $item) {
            $items[] = isset($item['content']) ? $item['content'] : $item;
        }

        $this->data[$region] = $items;

        return $this->render($template, $this->data);
    }

    /**
     * Adds an item to a region
     * @param string $region
     * @param string|array $item Expected array format:
     * first item - template, second - variables for $this->render()
     */
    protected function addRegionItem($region, $item)
    {
        if (is_array($item)) {
            $template = array_shift($item);
            $data = $item ? reset($item) : array();
            $content = $this->render($template, $data);
        } else {
            $content = $item;
        }

        $weight = isset($this->data[$region]) ? count($this->data[$region]) : 0;
        $this->data[$region][] = array('content' => $content, 'weight' => $weight++);
    }

    /**
     * Returns an array of default templates keyed by region
     * @return array
     */
    protected function getDefaultTemplates()
    {
        return array(
            'layout' => 'layout/layout',
            'region_head' => 'layout/head',
            'region_body' => 'layout/body',
            'region_left' => 'layout/left',
            'region_right' => 'layout/right',
            'region_content' => 'layout/content',
            'region_top' => 'layout/top',
            'region_bottom' => 'layout/bottom',
        );
    }

    /**
     * Modifies data variables before passing them to templates
     */
    protected function prepareOutput()
    {
        $this->data['head_title'] = $this->document->title();
        $this->data['page_title'] = $this->document->ptitle();
        $this->data['page_description'] = $this->document->pdescription();
        $this->data['breadcrumb'] = $this->document->breadcrumb();
        $this->data['meta'] = $this->document->meta();

        // Sort and add styles and javascripts
        $this->data['css'] = $this->document->css();
        Tool::sortWeight($this->data['css']);

        $this->data['js_top'] = $this->document->js(null, 'top');
        Tool::sortWeight($this->data['js_top']);

        $this->data['js_bottom'] = $this->document->js(null, 'bottom');
        Tool::sortWeight($this->data['js_bottom']);
    }

    /**
     * Sets php errors recorded by logger
     * @param array $data
     * @return null
     */
    protected function setPhpErrors(array &$data)
    {
        $errors = $this->logger->getErrors();

        if (empty($errors)) {
            return;
        }

        foreach ($errors as $severity => $messages) {
            foreach ($messages as $message) {
                $data['messages'][$severity][] = $message;
            }

            unset($errors[$severity]);
        }

        return;
    }

    /**
     * Renders TWIG templates
     * @param string $template_path
     * @param array $data
     * @param array $options
     * @return string
     */
    protected function renderTwig($template_path, array $data,
            array $options = array())
    {
        $parts = explode('/', $template_path);
        $template_file = array_pop($parts);
        $template_dir = implode('/', $parts);

        $this->twig->set($template_dir, $this, $options);
        return $this->twig->render($template_file, $data);
    }

    /**
     * Renders PHP templates
     * @param string $template
     * @param array $data
     * @return string
     */
    protected function renderPhp($template, array $data)
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include $template;
        return ob_get_clean();
    }

    /**
     * Contols access to account pages
     * @return null
     */
    protected function controlAccessAccount()
    {
        $account_id = $this->url->isAccount();

        if (empty($account_id)) {
            return; // This is not an account, exit
        }

        // Allow customers to see their accounts
        if ($this->uid === $account_id) {
            return;
        }

        if ($this->access('user')) {
            return;
        }

        $this->outputError(403);
    }

    /**
     * Sets cron properties
     */
    protected function setCron()
    {
        $this->cron_interval = (int) $this->config->get('cron_interval', 86400);
        $this->cron_last_run = (int) $this->config->get('cron_last_run', 0);
        $this->cron_key = $this->config->get('cron_key', '');

        if (empty($this->cron_key)) {
            $this->cron_key = Tool::randomString();
            $this->config->set('cron_key', $this->cron_key);
        }
    }

    /**
     * Sets a batch job from the current URL
     * @return null
     */
    protected function setJob()
    {
        $job_id = (string) $this->request->get('job_id');

        if (empty($job_id)) {
            return;
        }

        /* @var $job \core\models\Job */
        $job = Container::instance('core\\models\\Job');

        $this->current_job = $job->get($job_id);

        if (empty($this->current_job['status'])) {
            return;
        }

        $process_job_id = (string) $this->request->get('process_job');

        if ($this->request->isAjax() && $process_job_id === $job_id) {
            $this->response->json($job->process($this->current_job));
        }
    }

    /**
     * Adds required javascripts
     */
    protected function setScripts()
    {
        // Libraries
        $this->document->js('files/assets/jquery/jquery/jquery-1.11.3.js', 'top', -100);
        $this->document->js('files/assets/system/js/common.js', 'top', -90);

        // Settings
        $allowed = array('token', 'base', 'lang', 'lang_region', 'urn', 'uri', 'path', 'last_activity', 'session_limit');
        $settings = array_intersect_key($this->data, array_flip($allowed));
        $this->setJsSettings('', $settings, -80);

        // Js translation
        $this->document->js($this->language->getCompiledJs(), 'top', -70);

        // Batch job
        if (!empty($this->current_job['status'])) {
            $this->setJsSettings('job', $this->current_job, -60);
        }

        $is_backend = $this->url->isBackend();

        // Call cron
        if ($is_backend && !empty($this->cron_interval) && (GC_TIME - $this->cron_last_run) > $this->cron_interval) {
            $url = $this->url('cron', array('key' => $this->cron_key));
            $js = "\$(function(){\$.get('$url', function(data){});});";
            $this->document->js($js, 'bottom');
        }

        // Automatic logout
        if ($is_backend) {
            $session_limit = GC_SESSION_TIMEOUT * 1000;
            $this->document->js("GplCart.logout($session_limit);", 'bottom');
        }
    }

    /**
     * Adds JSON string with JS settings
     * @param string $key
     * @param array $data
     * @param integer|null $weight
     */
    protected function setJsSettings($key, array $data, $weight = null)
    {
        $json = json_encode($data);
        $var = rtrim("GplCart.settings.$key", '.');

        if (!isset($weight)) {
            $weight = -75;
        }

        $this->document->js("$var = $json;", 'top', $weight);
    }

    /**
     * Sets default template variables
     */
    protected function setData()
    {
        $this->data['token'] = $this->token;
        $this->data['base'] = $this->base;
        $this->data['lang'] = $this->langcode;
        $this->data['urn'] = $this->urn;
        $this->data['uri'] = $this->uri;
        $this->data['path'] = $this->path;
        $this->data['last_activity'] = $this->last_activity;
        $this->data['session_limit'] = ($this->last_activity + GC_SESSION_TIMEOUT) * 1000;

        $this->data['lang_region'] = $this->langcode;
        if (strpos($this->langcode, '_') === false) {
            $this->data['lang_region'] = $this->langcode . '-' . strtoupper($this->langcode);
        }

        $this->data['notifications'] = array();
        $this->data['messages'] = $this->session->getMessage();
        $this->data['languages'] = $this->languages;
        $route_class = str_replace('/', '-', preg_replace("/[^A-Za-z0-9\/]/", '', $this->current_route['pattern']));
        $this->data['body_classes'] = array($route_class);
        $this->data['current_store'] = $this->current_store;

        if ($this->url->isBackend()) {
            $this->data['help'] = $this->getHelp();
        }
    }

    /**
     * Sets an array of notification messages
     * @return null
     */
    protected function setNotifications()
    {
        if (!$this->url->isBackend()) {
            return;
        }

        $notifications = $this->notification->getList($this->config->get('notification_summary_limit', 10));

        foreach ($notifications as $notification_id => $notification) {
            if (!$this->access($notification['access'])) {
                continue;
            }

            if (empty($notification['summary']['message'])) {
                continue;
            }

            $severity = 'info';
            $text = $notification['summary']['message'];

            if (isset($notification['summary']['severity'])) {
                $severity = $notification['summary']['severity'];
            }

            $this->data['notifications'][$notification_id] = array(
                'text' => $this->text($text),
                'severity' => $severity
            );
        }
    }

    /**
     * Switches the site to maintenance mode
     */
    protected function setMaintenance()
    {
        if (!$this->url->isInstall() && !$this->url->isBackend() && empty($this->current_store['status'])) {
            $this->maintenance = true;
            $this->outputMaintenance();
        }
    }

    /**
     * Displays site maintenance page
     */
    protected function outputMaintenance()
    {
        $this->setTitle('Site maintenance', false);
        $this->output(array('layout' => 'common/maintenance'), array('headers' => 503));
    }

    /**
     * Adds a JS on the page
     * @param string $script
     * @param string $position
     * @param integer $weight
     * @return array
     */
    protected function setJs($script, $position, $weight = null)
    {
        return $this->document->js($script, $position, $weight);
    }

    /**
     * Adds a CSS on the page
     * @param string $css
     * @param integer $weight
     * @return array
     */
    protected function setCss($css, $weight = null)
    {
        return $this->document->css($css, $weight);
    }

    /**
     * Sets a meta tag to on the page
     * @param array $content
     * @return array
     */
    protected function setMeta($content)
    {
        return $this->document->meta($content);
    }

    /**
     * Sets page breadcrumb
     * @param array $breadcrumb
     * @return array
     */
    protected function setBreadcrumb(array $breadcrumb)
    {
        return $this->document->breadcrumb($breadcrumb);
    }

    /**
     * Sets page titles (H tag)
     * @param string $title
     * @return string
     */
    protected function setPageTitle($title)
    {
        return $this->document->ptitle($title);
    }

    /**
     * Sets page description (not metatag)
     * @param string $description
     * @return string
     */
    protected function setPageDescription($description)
    {
        return $this->document->pdescription($description);
    }

    /**
     * Converts special characters to HTML entities
     * @param string $string
     * @return string
     */
    protected function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Strips or encodes unwanted characters
     * @param string $string
     * @return string
     */
    protected function filter($string)
    {
        return filter_var($string, FILTER_SANITIZE_STRING);
    }

    /**
     * Returns truncated string with specified width
     * @param string $string
     * @param integer $length
     * @param string $trimmarker
     * @return string
     */
    protected function truncate($string, $length = 100, $trimmarker = '...')
    {
        return mb_strimwidth($string, 0, $length, $trimmarker, 'UTF-8');
    }

    /**
     * Removes dangerous stuff from a string
     * @param string $string
     * @param array|null $tags
     * @param array|null $protocols
     * @return string
     */
    protected function xss($string, $tags = null, $protocols = null)
    {
        return $this->filter->xss($string, $tags, $protocols);
    }

    /**
     * Returns text of an error, or array of errors or custom string (second param)
     * @param null|string $key
     * @param null|string $return_on_error
     * @return mixed
     */
    public function error($key = null, $return_on_error = null)
    {
        if (isset($key)) {
            $result = isset($this->errors[$key]) ? $this->errors[$key] : null;
        } else {
            $result = $this->errors;
        }
        
        if(empty($result)){
            $result = '';
        }

        return (isset($return_on_error) && !empty($result)) ? $return_on_error : $result;
    }

    /**
     * Whether one or more errors have occurred
     * @param bool $message
     * @param mixed $return_on_error
     * @return mixed
     */
    protected function hasError($message = true, $return_on_error = true)
    {
        $result = $this->error(null, $return_on_error);

        if (empty($result)) {
            return false;
        }

        if ($message) {
            $this->setMessage($this->text('One or more errors have occured'), 'warning');
        }

        return $result;
    }

    /**
     * Displays 403 access denied to unwanted users
     * @param string $permission
     * @param string $redirect
     */
    protected function controlAccess($permission, $redirect = '')
    {
        if (!$this->access($permission)) {
            $this->redirect($redirect, $this->text('You are not permitted to perform this operation'), 'danger');
        }
    }

    /**
     * "Honey pot" submission protection
     * @param string $type
     * @return null
     */
    protected function controlSpam($type)
    {
        $honeypot = $this->request->request('url', '');

        if ($honeypot === '') {
            return;
        }

        $ip = $this->request->ip();

        $message = array(
            'ip' => $ip,
            'message' => 'Spam submit from IP %address',
            'variables' => array('%address' => $ip)
        );

        $this->logger->log($type, $message, 'warning');
        $this->response->error403(false);
    }

    /**
     * Sets an array of messages
     * @param array|string $messages
     * @param string $severity
     */
    protected function setMessage($messages, $severity = 'info')
    {
        foreach ((array) $messages as $message) {
            $this->data['messages'][$severity][] = $message;
        }
    }

    /**
     * Returns a rendered job widget
     * @return string
     */
    protected function getJob()
    {
        if (empty($this->current_job['status'])) {
            return '';
        }

        if (!empty($this->current_job['widget'])) {
            return $this->render($this->current_job['widget'], array('job' => $this->current_job));
        }

        return $this->render('common/job/widget', array('job' => $this->current_job));
    }

    /**
     * Returns a rendered help link depending on the current URL
     * @return string
     */
    protected function getHelp()
    {
        $expected_filename = implode('_', $this->url->segments());
        $folder = $this->langcode ? $this->langcode : 'en';
        $directory = GC_HELP_DIR . "/$folder";

        if (!is_readable($directory)) {
            return '';
        }

        $found = array();
        foreach (Tool::scanFiles($directory, array('php')) as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            if (0 === strpos($expected_filename, $filename)) {
                $found[strlen($filename)] = array('path' => $file, 'filename' => $filename);
            }
        }

        if (empty($found)) {
            return '';
        }

        ksort($found);
        $help = end($found);
        $parts = $this->explodeText($this->render($help['path'], array(), true));

        if (!empty($parts)) {
            return $this->render('help/summary', array('content' => array_map('trim', $parts), 'file' => $help));
        }

        return '';
    }

    /**
     * Explodes a text by summary and full text
     * @param string $text
     * @return array
     */
    protected function explodeText($text)
    {
        return array_filter(explode($this->getSummaryDelimiter(), $text, 2));
    }

    /**
     * Returns a string used to separate summary and rest of text
     * @return string
     */
    protected function getSummaryDelimiter()
    {
        return $this->config->get('summary_delimiter', '<!--summary-->');
    }

    /**
     * Returns a string from a text before the summary delimiter
     * @param string $text
     * @param boolean $filter_xss
     * @param array|null $allowed_tags
     * @param array|null $allowed_protocols
     * @return string
     */
    protected function summary($text, $filter_xss = false, $allowed_tags = null,
            $allowed_protocols = null)
    {
        $summary = '';

        if ($text !== '') {
            $parts = $this->explodeText($text);
            $summary = trim(reset($parts));
        }

        if ($summary !== '' && $filter_xss) {
            $summary = $this->xss($summary, $allowed_tags, $allowed_protocols);
        }

        return $summary;
    }

    /**
     * Sets filter variables to the data array
     * @param array $allowed_filters
     * @param array $query
     */
    protected function setFilter(array $allowed_filters, $query = null)
    {
        if (!isset($query)) {
            $query = $this->getFilterQuery();
        }

        $order = (string) $this->request->get('order');

        $this->data['filtering'] = false;

        foreach ($allowed_filters as $filter) {

            $current_filter = $this->request->get($filter, null);

            if (isset($current_filter)) {
                $this->data['filtering'] = true;
            }

            $this->data["filter_$filter"] = (string) $current_filter;

            $this->data["sort_$filter"] = $this->url('', array(
                'sort' => $filter,
                'order' => ($order === 'desc') ? 'asc' : 'desc') + $query);
        }
    }

    /**
     * Returns an array of prepared GET values used for filtering and sorting
     * @param array $default
     * @return array
     */
    protected function getFilterQuery(array $default = array())
    {
        $query = $this->query;

        foreach ($query as $key => $value) {

            $value = (string) $value;

            if ($key === 'sort' && strpos($value, '-') !== false) {
                $parts = explode('-', $value, 2);
                $query['sort'] = reset($parts);
                $query['order'] = end($parts);
            }

            if ($value === 'any') {
                unset($query[$key]);
            }
        }

        return $query + $default;
    }

    /**
     * Sets the pager
     * @param integer $total
     * @param null|array $query
     * @param null|integer $limit
     * @return array Array of SQL limit values
     */
    protected function setPager($total, $query = null, $limit = null)
    {
        if (!isset($limit)) {
            $limit = $this->config->get('admin_list_limit', 20);
        }

        if (!isset($query)) {
            $query = $this->getFilterQuery();
        }

        $page = isset($query['p']) ? (int) $query['p'] : 1;

        $query['p'] = '%num';

        $this->pager->setPage($page);
        $this->pager->setPerPage($limit);
        $this->pager->setTotal($total);
        $this->pager->setUrlPattern('?' . urldecode(http_build_query($query)));

        $this->pager->setPreviousText($this->text('Back'));
        $this->pager->setNextText($this->text('Next'));

        $this->data['pager'] = $this->pager->render();
        return $this->pager->getLimit();
    }

    /**
     * Returns a rendered pager from data array
     * @return string
     */
    protected function getPager()
    {
        return isset($this->data['pager']) ? $this->data['pager'] : '';
    }

    /**
     * Returns current theme settings
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    protected function getSettings($key = null, $default = null)
    {
        if (isset($key)) {
            return array_key_exists($key, $this->theme_settings) ? $this->theme_settings[$key] : $default;
        }

        return $this->theme_settings;
    }

}
