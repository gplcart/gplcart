<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

/**
 * Base controller class
 */
class Controller
{

    /**
     * Whether we're installing the system
     * @var boolean
     */
    protected $installing = false;
    
    /**
     * Page meta title
     * @var string
     */
    protected $title = '';

    /**
     * Page header title
     * @var string
     */
    protected $ptitle = '';

    /**
     * Array of meta tags
     * @var array
     */
    protected $meta = array();

    /**
     * Array of breadcrumbs
     * @var array
     */
    protected $breadcrumbs = array();

    /**
     * Whether the current view is backend
     * @var boolean
     */
    protected $backend;

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
     * Current host
     * @var string
     */
    protected $host;

    /**
     * Current HTTP scheme
     * @var string
     */
    protected $scheme;

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
     * Array of the current theme
     * @var array
     */
    protected $current_theme = array();

    /**
     * The current HTML filter
     * @var array|false FALSE means disabled XSS filter, i.e raw output
     */
    protected $current_filter;

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
     * Validator model instance
     * @var \core\models\Validator $validator
     */
    protected $validator;
    
    /**
     * Library instance
     * @var \core\Library $library
     */
    protected $library;

    /**
     * Current language code
     * @var string
     */
    protected $langcode;

    /**
     * Url class instance
     * @var \core\helpers\Url $url
     */
    protected $url;
    
    /**
     * Asset class instance
     * @var \core\helpers\Asset $asset
     */
    protected $asset;

    /**
     * Request class instance
     * @var \core\helpers\Request $request
     */
    protected $request;

    /**
     * Response class instance
     * @var \core\helpers\Response $response
     */
    protected $response;

    /**
     * Route class instance
     * @var \core\Route $route
     */
    protected $route;

    /**
     * Session class instance
     * @var \core\helpers\Session $session
     */
    protected $session;

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Twig class instance
     * @var \core\helpers\Twig $twig
     */
    protected $twig;

    /**
     * Whether the current theme supports TWIG templates
     * @var boolean
     */
    protected $twig_enabled = false;

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Filter model instance
     * @var \core\models\Filter $filter
     */
    protected $filter;

    /**
     * Device class instance
     * @var \core\Device $device
     */
    protected $device;

    /**
     * Pager class instance
     * @var \core\helpers\Pager $pager
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
    public function __construct()
    {
        $this->setInstanceProperties();

        $this->token = $this->config->token();

        $this->setRouteProperties();
        $this->setDeviceProperties();
        $this->setStoreProperties();
        $this->setThemeProperties();
        $this->setLanguageProperties();
        $this->setCronProperties();
        $this->setDefaultData();
        $this->setDefaultJs();
        $this->setAccessProperties();
        $this->controlMaintenanceMode();

        $this->hook->fire('init', $this);
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
        if (empty($file)) {
            return $this->text('No template file provided');
        }

        $template = $this->getTemplateFile($file, $fullpath);

        $this->hook->fire('render', $template, $data, $this);

        $this->setPhpErrors($data);

        if (empty($template)) {
            return $this->text('Could not load template %path', array('%path' => $template));
        }

        if (pathinfo($template, PATHINFO_EXTENSION) === 'twig') {
            return $this->renderTwig($template, $data, (array) $this->theme_settings['twig']);
        }

        return $this->renderPhp($template, $data);
    }

    /**
     * Returns a full path to a module template
     * @param string $file
     * @param boolean $fullpath
     * @return string
     */
    protected function getTemplateFile($file, $fullpath)
    {
        $module = $this->theme;

        if (strpos($file, '|') !== false) {
            $fullpath = false;
            $parts = explode('|', $file, 2);
            $module = $parts[0];
            $file = $parts[1];
        }

        $path = $fullpath ? $file : GC_MODULE_DIR . "/$module/templates/$file";

        $extensions = array('php');
        if ($this->twig_enabled) {
            array_unshift($extensions, 'twig');
        }

        foreach ($extensions as $extension) {
            $template = "$path.$extension";
            if (is_readable($template)) {
                return $template;
            }
        }

        return '';
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
        $class = $this->current_route['handlers']['controller'][0];
        return $this->language->text($string, $arguments, $class);
    }

    /**
     * Whether the user is superadmin
     * @param null|integer $user_id
     * @return boolean
     */
    public function isSuperadmin($user_id = null)
    {
        return $this->user->isSuperadmin($user_id);
    }

    /**
     * Returns a value on a error
     * @param string|array $key
     * @param mixed $has_error A value to be returned when error(s) found
     * @param mixed $no_error A value to be returned when no error(s) found
     * @return mixed
     */
    public function error($key = null, $has_error = null, $no_error = '')
    {
        if (isset($key)) {
            $result = gplcart_array_get_value($this->errors, $key);
        } else {
            $result = empty($this->errors) ? null : $this->errors;
        }

        if (isset($result)) {
            return isset($has_error) ? $has_error : $result;
        }

        return $no_error;
    }

    /**
     * Returns a data of the current store
     * @param mixed $item
     * @return mixed
     */
    public function store($item = null)
    {
        if (isset($item)) {
            return gplcart_array_get_value($this->current_store, $item);
        }

        return $this->current_store;
    }

    /**
     * Returns a token
     * @return string
     */
    public function token()
    {
        return $this->token;
    }

    /**
     * Returns the current user ID
     * @return integer
     */
    public function uid()
    {
        return $this->uid;
    }
    
    /**
     * 
     * @return string
     */
    public function path(){
        return $this->path;
    }

    /**
     * Whether a key is presented in the POST query
     * If no key is set it returns TRUE if the request is POST type
     * @param string|null $key
     * @return boolean
     */
    public function isPosted($key = null)
    {
        if (isset($key)) {
            $value = $this->request->post($key, null);
            return isset($value);
        }

        return ($this->request->method() === 'POST');
    }

    /**
     * Whether a key is presented in the GET query
     * @param string|null $key
     * @return boolean
     */
    public function isQuery($key = null)
    {
        $value = $this->request->get($key);
        return !empty($value);
    }

    /**
     * Whether a key is presented in the submitted values array
     * @param string|array $key
     * @return boolean
     */
    public function isSubmitted($key)
    {
        $result = $this->getSubmitted($key);
        return isset($result);
    }

    /**
     * Whether an error(s) exist
     * @param string|array|null $key
     * @return boolean
     */
    public function isError($key = null)
    {
        return $this->error($key, true, false);
    }

    /**
     * Formats a local time/date
     * @param null|integer $timestamp
     * @param bool $full
     * @return string
     */
    public function date($timestamp = null, $full = true)
    {
        if (!isset($timestamp)) {
            $timestamp = GC_TIME;
        }

        if (empty($timestamp)) {
            return '';
        }

        $format = $this->config('date_prefix', 'd.m.y');

        if ($full) {
            $format .= $this->config('date_suffix', ' H:i');
        }

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
     * Sets instance properties
     */
    protected function setInstanceProperties()
    {
        /* @var $user \core\models\User */
        $this->user = Container::instance('core\\models\\User');

        /* @var $store \core\models\Store */
        $this->store = Container::instance('core\\models\\Store');

        /* @var $language \core\models\Language */
        $this->language = Container::instance('core\\models\\Language');

        /* @var $validator \core\models\Validator */
        $this->validator = Container::instance('core\\models\\Validator');

        /* @var $filter \core\models\Filter */
        $this->filter = Container::instance('core\\models\\Filter');

        /* @var $url \core\helpers\Url */
        $this->url = Container::instance('core\\helpers\\Url');

        /* @var $request \core\helpers\Request */
        $this->request = Container::instance('core\\helpers\\Request');

        /* @var $response \core\helpers\Response */
        $this->response = Container::instance('core\\helpers\\Response');
        
        /* @var $asset \core\helpers\Asset */
        $this->asset = Container::instance('core\\helpers\\Asset');

        /* @var $session \core\helpers\Session */
        $this->session = Container::instance('core\\helpers\\Session');
        
        /* @var $library \core\Library */
        $this->library = Container::instance('core\\Library');

        /* @var $hook \core\Hook */
        $this->hook = Container::instance('core\\Hook');

        /* @var $route \core\Route */
        $this->route = Container::instance('core\\Route');

        /* @var $config \core\Config */
        $this->config = Container::instance('core\\Config');

        /* @var $logger \core\helpers\Logger */
        $this->logger = Container::instance('core\\Logger');

        /* @var $pager \core\helpers\Pager */
        $this->pager = Container::instance('core\\helpers\\Pager');
    }

    /**
     * Sets the current route data
     */
    protected function setRouteProperties()
    {
        $this->backend = $this->url->isBackend();
        $this->installing = $this->url->isInstall();

        // Set access for the route
        $this->current_route = $this->route->getCurrent();

        if (isset($this->current_route['access'])) {
            $this->access = $this->current_route['access'];
        }

        $this->urn = $this->request->urn();
        $this->host = $this->request->host();
        $this->scheme = $this->request->scheme();

        $this->path = $this->url->path();
        $this->base = $this->request->base();
        $this->query = (array) $this->request->get();
        $this->langcode = $this->route->getLangcode();
        $this->uri = $this->scheme . $this->host . $this->urn;
    }

    /**
     * Defines the current user device
     * @return null
     */
    protected function setDeviceProperties()
    {
        $device = $this->session->get('device');

        if (!empty($device)) {
            $this->current_device = $device;
            return null;
        }

        $this->current_device = 'desktop';
        
        $this->library->load('mobile_detect');
        $this->device = Container::instance('Mobile_Detect');

        if ($this->device->isMobile()) {
            $this->current_device = $this->device->isTablet() ? 'tablet' : 'mobile';
        }

        $this->session->set('device', null, $this->current_device);
        return null;
    }

    /**
     * Sets the current store data
     */
    protected function setStoreProperties()
    {
        $this->current_store = $this->store->current();
        if (isset($this->current_store['store_id'])) {
            $this->store_id = $this->current_store['store_id'];
        }
    }

    /**
     * Sets theme data
     */
    protected function setThemeProperties()
    {
        $this->theme_frontend = $this->config('theme', 'frontend');
        $this->theme_backend = $this->config('theme_backend', 'backend');

        if ($this->backend) {
            $this->theme = $this->theme_backend;
        } elseif ($this->installing) {
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

        if (empty($this->theme)) {
            $this->response->error404();
        }

        $this->current_theme = $this->config->getModuleData($this->theme);

        if (empty($this->current_theme['info'])) {
            $this->response->error404();
        }

        $this->theme_settings = (array) $this->config->module($this->theme, null, array());

        if (!empty($this->theme_settings['twig']['status'])) {

            $this->twig_enabled = true;

            /* @var $twig \core\helpers\Twig */
            $this->twig = Container::instance('core\\helpers\\Twig');
        }

        if (empty($this->theme_settings['templates'])) {
            $this->templates = $this->getDefaultTemplates();
        } else {
            $this->templates = $this->theme_settings['templates'];
        }
    }

    /**
     * Sets the current working theme
     * @param string $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * Returns a module ID of the current theme
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Returns an array of the current theme data
     * @return array
     */
    public function theme()
    {
        return $this->current_theme;
    }

    /**
     * Sets a template variable
     * @param string|array $key
     * @param mixed $value
     */
    public function setData($key, $value)
    {
        gplcart_array_set_value($this->data, $key, $value);
    }

    /**
     * Removes a value by a key from an array of template data
     * @param string|array $key
     */
    public function unsetData($key)
    {
        gplcart_array_unset_value($this->data, $key);
    }

    /**
     * Sets an error
     * @param string|array $key
     * @param mixed $value
     */
    public function setError($key, $value)
    {
        gplcart_array_set_value($this->errors, $key, $value);
    }

    /**
     * Removes an error by a key from an array of errors
     * @param string|array $key
     */
    public function unsetError($key)
    {
        gplcart_array_unset_value($this->errors, $key);
    }

    /**
     * Sets an array of submitted data
     * @param string|array|null $key
     * @param mixed $value
     * @param boolean|string $filter
     * @return array
     */
    public function setSubmitted($key = null, $value = null, $filter = true)
    {
        if (!isset($key)) {
            $this->submitted = (array) $this->request->post(null, array(), $filter);
            return $this->submitted;
        }

        if (!isset($value) && empty($this->submitted)) {
            $this->submitted = (array) $this->request->post($key, array(), $filter);
            return $this->submitted;
        }

        gplcart_array_set_value($this->submitted, $key, $value);
        return $this->submitted;
    }

    /**
     * Removes a value(s) from an array of submitted data
     * @param string|array $key
     */
    public function unsetSubmitted($key)
    {
        gplcart_array_unset_value($this->submitted, $key);
    }

    /**
     * Converts a submitted value to boolean
     * If no value is set, it becomes FALSE
     * @param string|array $key
     */
    public function setSubmittedBool($key)
    {
        $original = $this->getSubmitted($key);
        $this->setSubmitted($key, (bool) $original);
    }

    /**
     * Converts a submitted value to array using multiline delimiter
     * @param string|array $key
     */
    public function setSubmittedArray($key)
    {
        $value = $this->getSubmitted($key);

        if (isset($value) && is_string($value)) {
            $this->setSubmitted($key, gplcart_string_array($value));
        }
    }

    /**
     * Returns a submitted value
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     */
    public function getSubmitted($key = null, $default = null)
    {
        if (isset($key)) {
            $result = gplcart_array_get_value($this->submitted, $key);
            return isset($result) ? $result : $default;
        }

        return $this->submitted;
    }

    /**
     * Returns a value from an array of template variables
     * @param string|array|null $key
     * @param mixed
     * @return mixed
     */
    public function getData($key = null, $default = null)
    {
        if (!isset($key)) {
            return $this->data;
        }

        $result = gplcart_array_get_value($this->data, $key);
        return isset($result) ? $result : $default;
    }

    /**
     * Loads translations, available languages etc
     */
    protected function setLanguageProperties()
    {
        $this->language->load();
        $this->languages = $this->language->getList(true);
    }

    /**
     * Sets access to the current page
     * @return boolean
     */
    protected function setAccessProperties()
    {
        if ($this->installing) {
            return null;
        }

        $this->controlToken(false);

        $this->uid = $this->user->id();

        if (!empty($this->uid)) {
            $this->current_user = $this->user->get($this->uid);

            if (empty($this->current_user['status'])//
                    || $this->current_user['role_id'] != $this->user->roleId()) {
                $this->session->delete();
                $this->url->redirect('login');
            }
        }

        $this->controlCsrf();

        // Check access to upload a file
        $file = $this->request->file();
        if (!empty($file) && !$this->access('file_upload')) {
            $this->response->error403();
        }

        // Check access only on restricted areas
        if (!$this->backend && $this->url->isAccount() === false) {
            return true;
        }

        // Redirect anonymous to login form
        if (empty($this->uid)) {
            $this->url->redirect('login', array('target' => $this->path));
        }

        $this->controlAccessAdmin();
        $this->controlAccessAccount();
        return null;
    }

    /**
     * Prevent Cross-Site Request Forgery (CSRF)
     * @return null
     */
    protected function controlCsrf()
    {
        if (!$this->isPosted()) {
            return null;
        }

        if (isset($this->current_route['token'])//
                && empty($this->current_route['token'])) {
            return null;
        }

        if (gplcart_string_equals($this->request->post('token'), $this->token)) {
            return null;
        }

        $this->response->error403();
        return null;
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
     * Controls access to admin pages
     * @return null
     */
    protected function controlAccessAdmin()
    {
        // Check only admin pages
        if (!$this->backend) {
            return null;
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

        return null;
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
     * Contols access to account pages
     * @return null
     */
    protected function controlAccessAccount()
    {
        $account_id = $this->url->isAccount();

        if (empty($account_id)) {
            return null; // This is not an account, exit
        }

        // Allow customers to see their accounts
        if ($this->uid === $account_id) {
            return null;
        }

        if ($this->access('user')) {
            return null;
        }

        $this->outputError(403);
        return null;
    }

    /**
     * Switches the site to maintenance mode
     */
    protected function controlMaintenanceMode()
    {
        if (!$this->installing && !$this->backend//
                && empty($this->current_store['status'])) {
            $this->maintenance = true;
            $this->outputMaintenance();
        }
    }

    /**
     * Displays 403 access denied to unwanted users
     * @param string $permission
     * @param string $redirect
     */
    public function controlAccess($permission, $redirect = '')
    {
        if (!$this->access($permission)) {
            $this->redirect($redirect, $this->text('No access'), 'danger');
        }
    }

    /**
     * Redirects to a new location
     * @param string $url
     * @param string $message
     * @param string $severity
     */
    final public function redirect($url = '', $message = '', $severity = 'info')
    {
        if ($message !== '') {
            $this->setMessage($message, $severity, true);
        }

        $this->url->redirect($url);
    }

    /**
     * Sets page <title> tag
     * @param string $title
     * @param boolean $both
     * @return string
     */
    public function setTitle($title, $both = true)
    {
        $this->title = strip_tags($title);

        if ($both && $this->ptitle === '') {
            return $this->setPageTitle($title);
        }

        return $this->title;
        
    }

    /**
     * Outputs rendered page
     * @param null|array|string $templates
     * @param array $options
     */
    final public function output($templates = null, array $options = array())
    {
        if (empty($templates)) {
            $templates = $this->templates;
        }

        $html = $this->renderOutput($templates);
        $this->response->html($html, $options);
    }

    /**
     * Renders all templates before sending them to a browser
     * @param array|string $templates
     * @return string
     */
    protected function renderOutput($templates)
    {
        if (is_string($templates)) {
            $templates = array('region_content' => $templates);
        }

        $this->hook->fire('output', $this->data, $this);

        $this->prepareOutput();

        $templates += $this->templates;
        $layout_template = $templates['layout'];
        unset($templates['layout']);

        $body_data = $this->data;
        $layout_data = $this->data;

        foreach ($templates as $region => $template) {
            if (!in_array($region, array('region_head', 'region_body'))) {
                $body_data[$region] = $this->renderRegion($region, $template);
            }
        }

        $layout_data['region_head'] = $this->render($templates['region_head'], $this->data);
        $layout_data['region_body'] = $this->render($templates['region_body'], $body_data);

        return $this->render($layout_template, $layout_data);
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
        gplcart_array_sort($this->data[$region]);

        $items = array();
        foreach ($this->data[$region] as $item) {
            $items[] = isset($item['content']) ? (string) $item['content'] : (string) $item;
        }

        $this->data[$region] = $this->render($template, array($region => $items));
        return $this->data[$region];
    }

    /**
     * Displays an error page
     * @param integer $code
     */
    final public function outputError($code)
    {
        $this->setTitleError($code);

        // TODO: rethink this.
        // The problem: theme styles are added
        // in hook init.* which is called in the child controller class
        // and not available here, so we call the hook manually
        $hook = $this->backend ? 'init.backend' : 'init.frontend';
        $this->hook->fireModule($hook, $this->theme, $this);

        $this->output("common/error/$code", array('headers' => $code));
    }

    /**
     * Sets HTTP error page title
     * @param integer $code
     */
    protected function setTitleError($code)
    {
        $title = (string) $this->response->statuses($code);

        if ($title !== '') {
            $this->setTitle($title, false);
        }
    }

    /**
     * Displays site maintenance page
     */
    public function outputMaintenance()
    {
        $this->setTitle('Site maintenance', false);
        $this->output(array('layout' => 'common/maintenance'), array('headers' => 503));
    }

    /**
     * Adds an item to a region
     * @param string $region
     * @param string|array $item Expected array format:
     * first item - template, second - variables for $this->render()
     */
    public function setRegion($region, $item)
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
     * Returns a string containing <title></title>
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns a string containing H title
     * @return string
     */
    public function getPageTitle()
    {
        return $this->ptitle;
    }

    /**
     * Returns an array with page breadcrumbs
     * @return array
     */
    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    /**
     * Returns an array of meta data
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Returns an array of attached styles
     * @return array
     */
    public function getCss()
    {
        $css = $this->asset->getCss();
        gplcart_array_sort($css);
        return $css;
    }

    /**
     * Returns an array of attached Java scripts
     * @param string $position
     * @return array
     */
    public function getJs($position)
    {
        $scripts = $this->asset->getJs($position);
        gplcart_array_sort($scripts);
        return $scripts;
    }

    /**
     * Modifies data variables before passing them to templates
     */
    protected function prepareOutput()
    {
        $this->data['meta'] = $this->getMeta();
        $this->data['head_title'] = $this->getTitle();
        $this->data['page_title'] = $this->getPageTitle();
        $this->data['breadcrumb'] = $this->getBreadcrumbs();

        $this->data['css'] = $this->getCss();
        $this->data['js_top'] = $this->getJs('top');
        $this->data['js_bottom'] = $this->getJs('bottom');
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
            return null;
        }

        foreach ($errors as $severity => $messages) {
            foreach ($messages as $message) {
                $data['messages'][$severity][] = $message;
            }

            unset($errors[$severity]);
        }

        return null;
    }

    /**
     * Renders TWIG templates
     * @param string $template
     * @param array $data
     * @param array $options
     * @return string
     */
    public function renderTwig($template, array $data, array $options = array())
    {
        $parts = explode('/', $template);
        $file = array_pop($parts);
        $directory = implode('/', $parts);

        $this->twig->set($directory, $this, $options);

        // Make global $this->data available in every .twig template
        $data = gplcart_array_merge($this->data, $data);

        return $this->twig->render($file, $data);
    }

    /**
     * Renders PHP templates
     * @param string $template
     * @param array $data
     * @return string
     */
    public function renderPhp($template, array $data)
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include $template;
        return ob_get_clean();
    }

    /**
     * Sets cron properties
     */
    protected function setCronProperties()
    {
        if ($this->installing) {
            return null;
        }

        $this->cron_interval = (int) $this->config('cron_interval', 86400);
        $this->cron_last_run = (int) $this->config('cron_last_run', 0);
        $this->cron_key = $this->config('cron_key', '');

        if (empty($this->cron_key)) {
            $this->cron_key = gplcart_string_random();
            $this->config->set('cron_key', $this->cron_key);
        }

        return null;
    }

    /**
     * Adds required javascripts
     */
    protected function setDefaultJs()
    {
        $this->setDefaultJsAssets();
        $this->setDefaultJsSettings();
        $this->setDefaultJsCron();
        $this->setDefaultJsTranslation();
    }

    /**
     * Sets global system JS files
     */
    protected function setDefaultJsAssets()
    {
        $this->addAssetLibrary('jquery');
        $this->setJs('files/assets/system/js/common.js');
    }

    /**
     * Sets default JS settings
     */
    protected function setDefaultJsSettings()
    {
        $allowed = array(
            'token', 'base', 'lang',
            'lang_region', 'urn', 'uri', 'path', 'query');

        $settings = array_intersect_key($this->data, array_flip($allowed));
        $this->setJsSettings('', $settings, 60);
    }

    /**
     * Adds JS code to call cron URL
     */
    protected function setDefaultJsCron()
    {
        $add = ($this->backend && !empty($this->cron_interval)//
                && (GC_TIME - $this->cron_last_run) > $this->cron_interval);

        if ($add) {
            $url = $this->url('cron', array('key' => $this->cron_key));
            $js = "\$(function(){\$.get('$url', function(data){});});";
            $this->setJs($js, 'bottom');
        }
    }

    /**
     * Adds context translation JS files
     */
    protected function setDefaultJsTranslation()
    {
        $classes = array(
            'core\\models\\Language', // text() called in modules
            $this->current_route['handlers']['controller'][0]
        );

        foreach ($classes as $class) {
            $filename = strtolower(str_replace('\\', '-', $class));
            $file = GC_LOCALE_JS_DIR . "/{$this->langcode}/$filename.js";
            $this->setJs(str_replace(GC_ROOT_DIR . '/', '', $file));
        }
    }

    /**
     * Adds JSON string with JS settings
     * @param string $key
     * @param array $data
     * @param integer|null $weight
     */
    public function setJsSettings($key, array $data, $weight = null)
    {
        $json = json_encode($data);
        $var = rtrim("GplCart.settings.$key", '.');

        if (!isset($weight)) {
            $weight = -75;
        }
        
        $this->setJs("$var = $json;", 'top', $weight);
    }

    /**
     * Sets default template variables
     */
    protected function setDefaultData()
    {
        $this->data['urn'] = $this->urn;
        $this->data['uri'] = $this->uri;
        $this->data['path'] = $this->path;
        $this->data['base'] = $this->base;
        $this->data['token'] = $this->token;
        $this->data['query'] = $this->query;
        $this->data['lang'] = empty($this->langcode) ? 'en' : $this->langcode;

        if (!empty($this->langcode) && strpos($this->langcode, '_') === false) {
            $this->data['lang_region'] = $this->langcode . '-' . strtoupper($this->langcode);
        } else {
            $this->data['lang_region'] = $this->langcode;
        }

        $this->data['languages'] = $this->languages;
        $this->data['current_store'] = $this->current_store;
        $this->data['messages'] = $this->session->getMessage();

        $controller = strtolower(str_replace('\\', '-', $this->current_route['handlers']['controller'][0]));
        $this->data['body_classes'] = array_slice(explode('-', $controller, 3), -1);
    }

    /**
     * Adds a JS on the page
     * @param string $script
     * @param string $pos
     * @param integer $weight
     * @return array
     */
    public function setJs($script, $pos = 'top', $weight = null)
    {
        return $this->asset->setJs($script, $pos, $weight);
    }
    
    /**
     * Sets a JS depending on the current URL path
     * @param string $directory A directory to scan
     * @param string $pos Either "top" or "bottom"
     */
    public function setJsContext($directory, $pos)
    {
        $file = gplcart_file_contex($directory, 'js', $this->path);

        if (isset($file['filename'])) {
            $this->setJs("system/modules/backend/js/{$file['filename']}.js", $pos);
        }
    }

    /**
     * Adds a CSS on the page
     * @param string $css
     * @param integer $weight
     * @return array
     */
    public function setCss($css, $weight = null)
    {
        return $this->asset->setCss($css, $weight);
    }
    
    /**
     * Adds single or multiple asset libraries
     * @param string|array $library_id
     * @param string $position
     */
    public function addAssetLibrary($library_id, $position = 'top')
    {
        $files = $this->library->getFiles($library_id);

        foreach ($files as $file) {

            $type = pathinfo($file, PATHINFO_EXTENSION);

            switch ($type) {
                case 'js':
                    $this->setJs($file, $position);
                    break;
                case 'css':
                    $this->setCss($file);
            }
        }
    }

    /**
     * Sets a meta tag to on the page
     * @param array $content
     * @return array
     */
    public function setMeta($content)
    {
        $this->meta[] = $content;
        return $this->meta;
    }

    /**
     * Sets a single page breadcrumb
     * @param array $breadcrumb
     * @return array
     */
    public function setBreadcrumb(array $breadcrumb)
    {
        $this->breadcrumbs[] = $breadcrumb;
        return $this->breadcrumbs;
    }

    /**
     * Sets an array of page breadcrumbs
     * @param array $breadcrumbs
     */
    public function setBreadcrumbs(array $breadcrumbs)
    {
        foreach ($breadcrumbs as $breadcrumb) {
            $this->setBreadcrumb($breadcrumb);
        }
    }

    /**
     * Sets page titles (H tag)
     * @param string $title
     * @return string
     */
    public function setPageTitle($title)
    {
        $this->ptitle = $title;
        return $this->ptitle;
    }

    /**
     * Converts special characters to HTML entities
     * @param string $string
     * @return string
     */
    public function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Strips or encodes unwanted characters
     * @param string $string
     * @return string
     */
    public function filter($string)
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
    public function truncate($string, $length = 100, $trimmarker = '...')
    {
        return mb_strimwidth($string, 0, $length, $trimmarker, 'UTF-8');
    }

    /**
     * Returns a config item
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function config($key = null, $default = null)
    {
        return $this->config->get($key, $default);
    }

    /**
     * Returns a setting from the current theme settings
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function setting($key = null, $default = null)
    {
        if (!isset($key)) {
            return $this->theme_settings;
        }

        if (array_key_exists($key, $this->theme_settings)) {
            return $this->theme_settings[$key];
        }

        return $default;
    }

    /**
     * Cleans up HTML string using HTML Purifier
     * @param string $string
     * @param mixed $filter
     * @return string
     */
    public function xss($string, $filter = null)
    {
        if (!isset($filter)) {
            $filter = $this->current_filter;
        }

        if ($filter === false) {
            return $string; // Superadmin output
        }

        return $this->filter->filter($string, $filter);
    }

    /**
     * Sets HTML filter globally
     * @param array $data
     * @return array|boolean
     */
    public function setHtmlFilter($data)
    {
        if (isset($data['user_id']) && $this->isSuperadmin($data['user_id'])) {

            $filter_id = $this->config('filter_superadmin');

            if (empty($filter_id)) {
                $this->current_filter = false; // Disable filtering at all
            } else {
                $this->current_filter = $this->filter->get($filter_id);
            }

            return $this->current_filter;
        }

        $role_id = isset($data['role_id']) ? $data['role_id'] : 0;
        $this->current_filter = $this->filter->getByRole($role_id);
        return $this->current_filter;
    }

    /**
     * Returns the current HTML filter
     * @return array
     */
    public function getHtmlFilter()
    {
        return $this->current_filter;
    }

    /**
     * Returns true if an error occurred
     * and passes back to template the submitted data
     * @param string $key
     * @param boolean $message
     * @return boolean
     */
    public function hasErrors($key = null, $message = true)
    {
        if (empty($this->errors)) {
            return false;
        }

        if ($message) {
            $this->setMessage($this->text('One or more errors occurred'), 'danger');
        }

        if (isset($key)) {
            $this->setData($key, $this->submitted);
        }

        return true;
    }

    /**
     * Validates a submitted data
     * @param string $handler_id
     * @param array $options
     * @return array
     */
    protected function validate($handler_id, array $options = array())
    {
        $result = $this->validator->run($handler_id, $this->submitted, $options);

        if ($result === true) {
            return array();
        }

        $this->errors = (array) $result;
        return $this->errors;
    }

    /**
     * Sets a message or an array of messages
     * @param string|array $messages
     * @param string $severity
     * @param bool $once
     */
    public function setMessage($messages, $severity = 'info', $once = false)
    {
        foreach ((array) $messages as $message) {
            if ($once) {
                $this->session->setMessage($message, $severity);
                continue;
            }

            $this->data['messages'][$severity][] = $message;
        }
    }

    /**
     * Explodes a text by summary and full text
     * @param string $text
     * @return array
     */
    protected function explodeText($text)
    {
        $delimiter = $this->config('summary_delimiter', '<!--summary-->');
        return array_filter(array_map('trim', explode($delimiter, $text, 2)));
    }

    /**
     * Returns a string from a text before the summary delimiter
     * @param string $text
     * @param boolean $xss
     * @param array|null $tags
     * @param array|null $protocols
     * @return string
     */
    public function summary($text, $xss = false, $tags = null, $protocols = null)
    {
        $summary = '';

        if ($text !== '') {
            $parts = $this->explodeText($text);
            $summary = trim(reset($parts));
        }

        if ($summary !== '' && $xss) {
            $summary = $this->xss($summary, $tags, $protocols);
        }

        return $summary;
    }

    /**
     * Sets filter variables to the data array
     * @param array $allowed_filters
     * @param array $query
     */
    public function setFilter(array $allowed_filters, $query = null)
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

        if (isset($query['sort']) && isset($query['order'])) {
            $this->data['sort'] = $query['sort'] . '-' . $query['order'];
        }
    }

    /**
     * Returns an array of prepared GET values used for filtering and sorting
     * @param array $default
     * @return array
     */
    public function getFilterQuery(array $default = array())
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
    public function setPager($total, $query = null, $limit = null)
    {
        if (!isset($limit)) {
            $limit = $this->config('admin_list_limit', 20);
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
    public function getPager()
    {
        return isset($this->data['pager']) ? $this->data['pager'] : '';
    }

}
