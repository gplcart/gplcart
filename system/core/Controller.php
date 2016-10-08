<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\classes\Tool;

/**
 * Base controller class. Contents methods to be used in the child classes and
 * some basic system functions such as access control etc.
 */
class Controller
{

    /**
     * Whether we're installing the system
     * @var boolean
     */
    protected $installing = false;

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
     * Current language code
     * @var string
     */
    protected $langcode;

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
    public function __construct()
    {

        $this->setInstanceProperties();

        $this->token = $this->config->token();

        $this->setRouteProperties();
        $this->setDeviceProperties();
        $this->setStoreProperties();
        $this->setThemeProperties();
        $this->setLanguageProperties();
        $this->setAccessProperties();
        $this->setCronProperties();

        $this->setDefaultData();
        $this->setDefaultJs();
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

        $template = $this->getModuleTemplatePath($file, $fullpath);
        $extension = isset($this->theme_settings['twig']) ? '.twig' : '.php';

        if ((substr($template, -strlen($extension)) !== $extension)) {
            $template .= $extension;
        }

        $this->hook->fire('render', $template, $data, $this);

        $this->setPhpErrors($data);

        if (!file_exists($template)) {
            return $this->text('Could not load template %path', array('%path' => $template));
        }

        if ($extension === '.twig') {
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
    protected function getModuleTemplatePath($file, $fullpath)
    {
        $module = $this->theme;

        if (strpos($file, '|') !== false) {
            $fullpath = false;
            $parts = explode('|', $file, 2);
            $module = $parts[0];
            $file = $parts[1];
        }

        return $fullpath ? $file : GC_MODULE_DIR . "/$module/templates/$file";
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
        $result = $this->getError($key);
        return !empty($result);
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
        $this->query = $this->request->get();
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

        $theme_data = $this->config->getModuleData($this->theme);

        if (empty($theme_data['info'])) {
            $this->response->error404();
        }

        $this->theme_settings = $this->config->module($this->theme, null, array());

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
     * Sets the current working theme
     * @param string $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * Sets a template variable
     * @param string|array $key
     * @param mixed $value
     */
    public function setData($key, $value)
    {
        Tool::setArrayValue($this->data, $key, $value);
    }

    /**
     * Removes a value by a key from an array of template data
     * @param string|array $key
     */
    public function unsetData($key)
    {
        Tool::unsetArrayValue($this->data, $key);
    }

    /**
     * Sets an error
     * @param string|array $key
     * @param mixed $value
     */
    public function setError($key, $value)
    {
        Tool::setArrayValue($this->errors, $key, $value);
    }

    /**
     * Removes an error by a key from an array of errors
     * @param string|array $key
     */
    public function unsetError($key)
    {
        Tool::unsetArrayValue($this->errors, $key);
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

        Tool::setArrayValue($this->submitted, $key, $value);
        return $this->submitted;
    }

    /**
     * Removes a value(s) from an array of submitted data
     * @param string|array $key
     */
    public function unsetSubmitted($key)
    {
        Tool::unsetArrayValue($this->submitted, $key);
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
            $this->setSubmitted($key, Tool::stringToArray($value));
        }
    }

    /**
     * Returns an error
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function getError($key = null, $default = null)
    {
        if (isset($key)) {
            $result = Tool::getArrayValue($this->errors, $key);
            return isset($result) ? $result : $default;
        }

        return $this->errors;
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
            $result = Tool::getArrayValue($this->submitted, $key);
            return isset($result) ? $result : $default;
        }

        return $this->submitted;
    }

    /**
     * Returns a value from an array of template variables
     * @param string|array $key
     * @param mixed
     * @return mixed
     */
    public function getData($key, $default = null)
    {
        $result = Tool::getArrayValue($this->data, $key);
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
        if ($this->isPosted()) {

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
        if (!$this->backend && $this->url->isAccount() === false) {
            return true;
        }

        // Redirect anonymous to login form
        if (empty($this->uid)) {
            $this->url->redirect('login', array('target' => $this->path));
        }

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
     * Controls access to admin pages
     * @return null
     */
    protected function controlAccessAdmin()
    {
        // Check only admin pages
        if (!$this->backend) {
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
     * Switches the site to maintenance mode
     */
    protected function controlMaintenanceMode()
    {
        if (!$this->installing && !$this->backend && empty($this->current_store['status'])) {
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
            $this->redirect($redirect, $this->text('You are not permitted to perform this operation'), 'danger');
        }
    }

    /**
     * "Honey pot" submission protection
     * @param string $type
     * @return null
     */
    public function controlSpam($type)
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
     * Redirects to a new location
     * @param string $url
     * @param string $message
     * @param string $severity
     */
    public function redirect($url = '', $message = '', $severity = 'info')
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
        return $this->document->title($title, $both);
    }

    /**
     * Outputs rendered page
     * @param array|string $templates
     * @param array $options
     */
    public function output($templates, array $options = array())
    {
        if (is_string($templates)) {
            $templates = array('region_content' => $templates);
        }

        $this->hook->fire('output', $this->data, $this);

        $this->prepareOutput();

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
     * Displays an error page
     * @param integer $code
     */
    public function outputError($code = 403)
    {
        $title = (string) $this->response->statuses($code);

        if ($title !== '') {
            $this->setTitle($title, false);
        }

        $this->output("common/error/$code", array('headers' => $code));
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
     * Adds validators for a submitted field
     * @param string $field
     * @param array $validators
     */
    protected function addValidator($field, array $validators = array())
    {
        $this->validator->add($field, $validators);
    }

    /**
     * Starts validation and sets validation errors (if any)
     * @param array $data
     * @return array
     */
    protected function setValidators(array $data = array())
    {
        $this->errors = $this->validator->set($this->submitted, $data)->getError();
        return $this->errors;
    }

    /**
     * Returns validation result(s)
     * @param string $field
     * @return mixed
     */
    protected function getValidatorResult($field = null)
    {
        return $this->validator->getResult($field);
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
        return $this->document->title();
    }

    /**
     * Returns a string containing H title
     * @return string
     */
    public function getPageTitle()
    {
        return $this->document->ptitle();
    }

    /**
     * Returns an array with page breadcrumbs
     * @return array
     */
    public function getBreadcrumbs()
    {
        return $this->document->breadcrumb();
    }

    /**
     * Returns an array of meta data
     * @return array
     */
    public function getMeta()
    {
        return $this->document->meta();
    }

    /**
     * Returns an array of attached styles
     * @return array
     */
    public function getCss()
    {
        $css = $this->document->css();

        if ($this->config('compress_css', 0)) {
            return $this->getCompressedAssets($css, 'css');
        }

        Tool::sortWeight($css);
        return $css;
    }

    /**
     * Returns an array of attached Java scripts
     * @param string $region
     * @return array
     */
    public function getJs($region)
    {
        $scripts = $this->document->js(null, $region);

        if ($this->config('compress_js', 0)) {
            return $this->getCompressedAssets($scripts, 'js', "-$region");
        }

        Tool::sortWeight($scripts);
        return $scripts;
    }

    /**
     * Returns an array of asset files including the compressed version
     * @param array $assets
     * @param string $type
     * @param string $id
     * @return array
     */
    protected function getCompressedAssets(array $assets, $type, $id = '')
    {
        if (empty($assets)) {
            return array();
        }

        $file = "files/assets/compressed/$type/{$this->theme}$id.$type";

        if (!file_exists(GC_ROOT_DIR . "/$file")) {
            $compressor = Container::instance('core\\classes\\Compressor');
        }

        $weights = array();
        foreach ($assets as $path => $asset) {

            if (empty($asset['compress'])) {
                continue;
            }

            $weights[] = $asset['weight'];

            if (!isset($compressor)) {
                unset($assets[$path]);
                continue;
            }

            $source = $asset['path'] ? $asset['path'] : $asset['text'];
            $compressor->{$type}('add', $source);
            unset($assets[$path]);
        }

        if (isset($compressor)) {
            $compressor->{$type}('minify', GC_ROOT_DIR . "/$file");
        }

        $data = array(
            'type' => $type,
            'asset' => $file,
            'compress' => false,
            'weight' => (min($weights) - 10)
        );

        $results = $this->document->setAsset($data, $assets);
        Tool::sortWeight($results);
        return $results;
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
            return;
        }

        $this->cron_interval = (int) $this->config('cron_interval', 86400);
        $this->cron_last_run = (int) $this->config('cron_last_run', 0);
        $this->cron_key = $this->config('cron_key', '');

        if (empty($this->cron_key)) {
            $this->cron_key = Tool::randomString();
            $this->config->set('cron_key', $this->cron_key);
        }
    }

    /**
     * Adds required javascripts
     */
    protected function setDefaultJs()
    {
        // Libraries
        $this->document->js('files/assets/jquery/jquery/jquery-1.11.3.js', 'top', -999);
        $this->document->js('files/assets/system/js/common.js', 'top', -900);

        // Settings
        $allowed = array(
            'token', 'base', 'lang',
            'lang_region', 'urn', 'uri', 'path');

        $settings = array_intersect_key($this->data, array_flip($allowed));
        $this->setJsSettings('', $settings, -800);

        $this->setJsTranslation();

        // Call cron
        if ($this->backend && !empty($this->cron_interval) && (GC_TIME - $this->cron_last_run) > $this->cron_interval) {
            $url = $this->url('cron', array('key' => $this->cron_key));
            $js = "\$(function(){\$.get('$url', function(data){});});";
            $this->document->js($js, 'bottom');
        }
    }

    /**
     * Adds context translation JS files
     */
    protected function setJsTranslation()
    {
        $classes[] = 'core\\models\\Language'; // text() called in modules
        $classes[] = $this->current_route['handlers']['controller'][0];

        foreach ($classes as $class) {
            $filename = strtolower(str_replace('\\', '-', $class));
            $file = GC_LOCALE_JS_DIR . "/{$this->langcode}/$filename.js";
            $this->document->js(str_replace(GC_ROOT_DIR, '', $file), 'top', -70);
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

        $this->document->js("$var = $json;", 'top', $weight);
    }

    /**
     * Sets default template variables
     */
    protected function setDefaultData()
    {
        $this->data['token'] = $this->token;
        $this->data['base'] = $this->base;
        $this->data['lang'] = empty($this->langcode) ? 'en' : $this->langcode;
        $this->data['urn'] = $this->urn;
        $this->data['uri'] = $this->uri;
        $this->data['path'] = $this->path;

        if (!empty($this->langcode) && strpos($this->langcode, '_') === false) {
            $this->data['lang_region'] = $this->langcode . '-' . strtoupper($this->langcode);
        } else {
            $this->data['lang_region'] = $this->langcode;
        }

        $this->data['messages'] = $this->session->getMessage();
        $this->data['languages'] = $this->languages;

        $controller = strtolower(str_replace('\\', '-', $this->current_route['handlers']['controller'][0]));
        $this->data['body_classes'] = array_slice(explode('-', $controller, 3), -1);
        $this->data['current_store'] = $this->current_store;
    }

    /**
     * Adds a JS on the page
     * @param string $script
     * @param string $position
     * @param integer $weight
     * @return array
     */
    public function setJs($script, $position, $weight = null)
    {
        return $this->document->js($script, $position, $weight);
    }

    /**
     * Adds a CSS on the page
     * @param string $css
     * @param integer $weight
     * @return array
     */
    public function setCss($css, $weight = null)
    {
        return $this->document->css($css, $weight);
    }

    /**
     * Sets a meta tag to on the page
     * @param array $content
     * @return array
     */
    public function setMeta($content)
    {
        return $this->document->meta($content);
    }

    /**
     * Sets a single page breadcrumb
     * @param array $breadcrumb
     * @return array
     */
    public function setBreadcrumb(array $breadcrumb)
    {
        return $this->document->breadcrumb($breadcrumb);
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
        return $this->document->ptitle($title);
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
        if (isset($key)) {
            return array_key_exists($key, $this->theme_settings) ? $this->theme_settings[$key] : $default;
        }

        return $this->theme_settings;
    }

    /**
     * Removes dangerous stuff from a string
     * @param string $string
     * @param array|null $tags
     * @param array|null $protocols
     * @return string
     */
    public function xss($string, $tags = null, $protocols = null)
    {
        return $this->filter->xss($string, $tags, $protocols);
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
        $errors = $this->getError();

        if (empty($errors)) {
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
            } else {
                $this->data['messages'][$severity][] = $message;
            }
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
