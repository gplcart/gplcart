<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

/**
 * Base controller class
 */
class Controller
{

    /**
     * Whether we're installing the system
     * @var boolean
     */
    protected $is_installing = false;

    /**
     * Whether the site in maintenance mode
     * @var boolean
     */
    protected $is_maintenance = false;

    /**
     * Whether the current view is backend
     * @var boolean
     */
    protected $is_backend;

    /**
     * Whether the current theme supports TWIG templates
     * @var boolean
     */
    protected $is_twig = false;

    /**
     * Weight of JS settings
     * @var integer
     */
    protected $js_settings_weight = 0;

    /**
     * The current HTTP status code
     * @var string
     */
    protected $http_status;

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
     * @see \gplcart\modules\example\Example::info()
     */
    protected $theme_settings = array();

    /**
     * Array of enabled languages
     * @var array
     */
    protected $languages = array();

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
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Store model instance
     * @var \gplcart\core\models\Store $store
     */
    protected $store;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Validator model instance
     * @var \gplcart\core\models\Validator $validator
     */
    protected $validator;

    /**
     * Library instance
     * @var \gplcart\core\Library $library
     */
    protected $library;

    /**
     * Current language code
     * @var string
     */
    protected $langcode;

    /**
     * Url class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Asset class instance
     * @var \gplcart\core\helpers\Asset $asset
     */
    protected $asset;

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * Response class instance
     * @var \gplcart\core\helpers\Response $response
     */
    protected $response;

    /**
     * Route class instance
     * @var \gplcart\core\Route $route
     */
    protected $route;

    /**
     * Session class instance
     * @var \gplcart\core\helpers\Session $session
     */
    protected $session;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Twig class instance
     * @var \gplcart\core\helpers\Twig $twig
     */
    protected $twig;

    /**
     * Logger class instance
     * @var \gplcart\core\Logger $logger
     */
    protected $logger;

    /**
     * Filter model instance
     * @var \gplcart\core\models\Filter $filter
     */
    protected $filter;

    /**
     * Device class instance
     * @var \gplcart\core\Device $device
     */
    protected $device;

    /**
     * Pager class instance
     * @var \gplcart\core\helpers\Pager $pager
     */
    protected $pager;

    /**
     * Compressor class instance
     * @var \gplcart\core\helpers\Compressor $compressor
     */
    protected $compressor;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
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

        $this->setDefaultJsAssets();
        $this->setThemeProperties();
        $this->setLanguageProperties();

        $this->setDefaultData();
        $this->setAccessProperties();
        $this->controlMaintenanceMode();

        $this->hook->fire('init', $this);
    }

    /**
     * Returns a property
     * @param string $name
     * @return object
     */
    public function prop($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new \InvalidArgumentException("Property $name does not exist in class " . __CLASS__);
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
     * Returns an array of existing stores
     * @return array
     */
    public function stores()
    {
        return $this->store->getList();
    }

    /**
     * Returns a data of the current user
     * @param mixed $item
     * @return mixed
     */
    public function user($item = null)
    {
        if (isset($item)) {
            return gplcart_array_get_value($this->current_user, $item);
        }

        return $this->current_user;
    }

    /**
     * Returns an array of attached styles
     * @return array
     */
    public function css()
    {
        $stylesheets = $this->asset->getCss();
        $this->compressAssets($stylesheets, 'css');
        return $stylesheets;
    }

    /**
     * Returns an array of attached scripts
     * @param string $position
     * @return array
     */
    public function js($position)
    {
        $scripts = $this->asset->getJs($position);
        $this->compressAssets($scripts, 'js');
        return $scripts;
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
    public function settings($key = null, $default = null)
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
     * Clean up HTML string using HTML Purifier
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
     * Returns a string from a text before the summary delimiter
     * @param string $text
     * @param boolean $xss
     * @param mixed $filter
     * @return string
     */
    public function summary($text, $xss = true, $filter = null)
    {
        $summary = '';

        if ($text !== '') {
            $parts = $this->explodeText($text);
            $summary = trim(reset($parts));
        }

        if ($summary !== '' && $xss) {
            $summary = $this->xss($summary, $filter);
        }

        return $summary;
    }

    /**
     * Whether the system is installing
     * @return bool
     */
    public function isInstalling()
    {
        return $this->is_installing;
    }

    /**
     * If $path isset - returns TRUE if the path pattern mathes the current URL path
     * If $path is not set or NULL - returns the current URL path
     * @param null|string $pattern
     * @return string|bool
     */
    public function path($pattern = null)
    {
        if (isset($pattern)) {
            $result = gplcart_parse_pattern($this->path, $pattern);
            return $result !== false;
        }

        return $this->path;
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

        if (empty($template)) {
            return $this->text('Could not load template %path', array('%path' => $template));
        }

        if (pathinfo($template, PATHINFO_EXTENSION) === 'twig') {
            $settings = empty($this->theme_settings['twig']) ? array() : $this->theme_settings['twig'];
            return $this->renderTwig($template, $data, $settings);
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
        $module = $this->current_theme['id'];
        $is_twig = $this->is_twig;

        if (strpos($file, '|') !== false) {
            $fullpath = false;
            list($module, $file) = explode('|', $file, 2);

            if ($module !== $this->current_theme['id']) {
                $settings = $this->config->module($module, 'twig');
                $is_twig = !empty($settings['status']);
            }
        }

        $path = $fullpath ? $file : GC_MODULE_DIR . "/$module/templates/$file";

        $extensions = array('php');

        if ($is_twig) {
            if (!isset($this->twig)) {
                $this->twig = Container::get('gplcart\\core\\helpers\\Twig');
            }

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
     * Whether the user is superadmin
     * @param null|integer $user_id
     * @return boolean
     */
    public function isSuperadmin($user_id = null)
    {
        return $this->user->isSuperadmin($user_id);
    }

    /**
     * Sets the current HTTP status code
     * @param string $code
     * @return \gplcart\core\Controller
     */
    public function setHttpStatus($code)
    {
        $this->http_status = $code;
        return $this;
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
     * Sets instance properties
     */
    protected function setInstanceProperties()
    {
        $this->user = Container::get('gplcart\\core\\models\\User');
        $this->store = Container::get('gplcart\\core\\models\\Store');
        $this->language = Container::get('gplcart\\core\\models\\Language');
        $this->validator = Container::get('gplcart\\core\\models\\Validator');
        $this->filter = Container::get('gplcart\\core\\models\\Filter');

        $this->url = Container::get('gplcart\\core\\helpers\\Url');
        $this->request = Container::get('gplcart\\core\\helpers\\Request');
        $this->response = Container::get('gplcart\\core\\helpers\\Response');
        $this->asset = Container::get('gplcart\\core\\helpers\\Asset');
        $this->session = Container::get('gplcart\\core\\helpers\\Session');
        $this->pager = Container::get('gplcart\\core\\helpers\\Pager');
        $this->compressor = Container::get('gplcart\\core\\helpers\\Compressor');

        $this->hook = Container::get('gplcart\\core\\Hook');
        $this->route = Container::get('gplcart\\core\\Route');
        $this->config = Container::get('gplcart\\core\\Config');
        $this->logger = Container::get('gplcart\\core\\Logger');
        $this->library = Container::get('gplcart\\core\\Library');
    }

    /**
     * Sets the current route data
     */
    protected function setRouteProperties()
    {
        $this->is_backend = $this->url->isBackend();
        $this->is_installing = $this->url->isInstall();
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
        $this->device = Container::get('Mobile_Detect');

        if ($this->device->isMobile()) {
            $this->current_device = $this->device->isTablet() ? 'tablet' : 'mobile';
        }

        $this->session->set('device', $this->current_device);
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

        $theme = null;

        if ($this->is_backend) {
            $theme = $this->theme_backend;
        } elseif ($this->is_installing) {
            $theme = $this->theme_frontend;
        } elseif (!empty($this->current_store)) {
            $this->theme_frontend = $theme = $this->store->config('theme');

            if ($this->current_device === 'mobile') {
                $this->theme_frontend = $theme = $this->store->config('theme_mobile');
            }

            if ($this->current_device === 'tablet') {
                $this->theme_frontend = $theme = $this->store->config('theme_tablet');
            }
        }

        if (empty($theme)) {
            $this->response->error404();
        }

        $this->current_theme = $this->config->getModuleData($theme);

        if (empty($this->current_theme['info'])) {
            $this->response->error404();
        }

        $this->theme_settings = (array) $this->config->module($theme, null, array());
        $this->is_twig = !empty($this->theme_settings['twig']['status']);

        if (empty($this->theme_settings['templates'])) {
            $this->templates = $this->getDefaultTemplates();
        } else {
            $this->templates = $this->theme_settings['templates'];
        }

        $this->hook->fire('theme', $this);
    }

    /**
     * Whether a theme ID matches the current theme ID
     * @param string $name
     * @return boolean
     */
    public function isCurrentTheme($name)
    {
        return $this->current_theme['id'] === $name;
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
     * @param null|string|array $key
     * @param mixed $value
     */
    public function setError($key, $value)
    {
        if (isset($key)) {
            gplcart_array_set_value($this->errors, $key, $value);
        } else {
            $this->errors = (array) $value;
        }
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
        if ($this->is_installing) {
            return null;
        }

        $this->controlToken(false);

        $this->uid = (int) $this->user->getSession('user_id');

        if (!empty($this->uid)) {
            $this->current_user = $this->user->get($this->uid);
            $this->controlAccessCredentials();
        }

        $this->controlCsrf();
        $this->controlAccessUpload();
        $this->controlAccessRestrictedArea();
        $this->controlAccessAdmin();
        $this->controlAccessAccount();
    }

    /**
     * Output status page if status is set, e.g other than 200
     */
    protected function controlHttpStatus()
    {
        if (isset($this->http_status)) {
            $this->outputHttpStatus($this->http_status);
        }
    }

    /**
     * "Honey pot" submission protection
     */
    public function controlSpam()
    {
        if ($this->request->request('url', '') !== '') {
            $this->response->error403(false);
        }
    }

    /**
     * Controls the current user credentials, such as status, role, password hash...
     * @return boolean
     */
    protected function controlAccessCredentials()
    {
        if (!isset($this->current_user['hash']) || empty($this->current_user['status'])) {
            $this->session->delete();
            $this->url->redirect('login');
            return false;
        }

        $session_hash = $this->user->getSession('hash');
        $session_role_id = $this->user->getSession('role_id');

        if (!gplcart_string_equals($this->current_user['hash'], $session_hash)) {
            $this->session->delete();
            $this->url->redirect('login');
            return false;
        }

        if ($this->current_user['role_id'] != $session_role_id) {
            $this->session->delete();
            $this->url->redirect('login');
            return false;
        }

        return true;
    }

    /**
     * Controls access to upload a file
     */
    protected function controlAccessUpload()
    {
        $file = $this->request->file();
        if (!empty($file) && !$this->access('file_upload')) {
            $this->response->error403();
        }
    }

    /**
     * Controls access to retricted areas
     */
    protected function controlAccessRestrictedArea()
    {
        if (($this->is_backend || (bool) $this->url->isAccount()) && empty($this->uid)) {
            $this->url->redirect('login', array('target' => $this->path));
        }
    }

    /**
     * Prevent Cross-Site Request Forgery (CSRF)
     * @return null|boolean
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
            return true;
        }

        $this->response->error403();
        return false;
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
     * @return boolean|null
     */
    protected function controlAccessAdmin()
    {
        if (!$this->is_backend || $this->isSuperadmin()) {
            return null;
        }

        if (empty($this->current_user['role_status']) || !$this->access('admin')) {
            $this->redirect('/', $this->text('No access'), 'warning');
            return false;
        }

        // Check route specific access
        if (empty($this->access) || $this->access($this->access)) {
            return true;
        }

        $this->setHttpStatus(403);
        return false;
    }

    /**
     * Contols access to account pages
     * @return boolean|null
     */
    protected function controlAccessAccount()
    {
        $account_id = $this->url->isAccount();

        if (empty($account_id)) {
            return null;
        }

        if ($this->uid === $account_id) {
            return true;
        }

        if ($this->isSuperadmin($account_id) && !$this->isSuperadmin()) {
            $this->setHttpStatus(403);
            return false;
        }

        if ($this->access('user')) {
            return true;
        }

        $this->setHttpStatus(403);
        return false;
    }

    /**
     * Switches the site to maintenance mode
     */
    protected function controlMaintenanceMode()
    {
        if (!$this->is_installing && !$this->is_backend//
                && empty($this->current_store['status'])) {
            $this->is_maintenance = true;
            $this->outputMaintenance();
        }
    }

    /**
     * Displays 403 access denied to unwanted users
     * @param string $permission
     */
    public function controlAccess($permission)
    {
        if (!$this->access($permission)) {
            $this->outputHttpStatus(403);
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

        if (!empty($this->http_status)) {

            $title = (string) $this->response->statuses($this->http_status);
            $this->setTitle($title, false);
            $templates = "common/status/{$this->http_status}";
            $options['headers'] = $this->http_status;
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
    final public function outputHttpStatus($code)
    {
        $this->setHttpStatus($code)->output();
    }

    /**
     * Displays site maintenance page
     */
    public function outputMaintenance()
    {
        $this->setTitle('Site maintenance', false);
        $this->output(array('layout' => 'layout/maintenance'), array('headers' => 503));
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

        if (trim($content) !== '') {
            $weight = isset($this->data[$region]) ? count($this->data[$region]) : 0;
            $this->data[$region][] = array('content' => $content, 'weight' => $weight++);
        }
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
     * Compresses and aggregates assets
     * @param array $assets
     * @param string $type
     * @return array
     */
    protected function compressAssets(array &$assets, $type)
    {
        if (!$this->config("compress_$type", 0)) {
            return $assets;
        }

        $directory = GC_COMPRESSED_ASSET_DIR . "/$type";

        $group = 0;
        $groups = $results = array();

        // Split assets by groups
        // "text" assets (e.g JS settings) and those who have "aggregate=>false"
        // are excluded from aggregation
        foreach ($assets as $key => $asset) {

            $exclude = (isset($asset['aggregate']) && empty($asset['aggregate']));

            if (!empty($asset['text']) || $exclude) {
                // Add underscrore to make the key not numeric
                // We check it later to define which assets should be aggregated
                $groups["_$group"] = $asset;
                $group++;
                continue;
            }

            if (!empty($asset['asset'])) {
                $groups[$group][$key] = $asset['asset'];
            }
        }

        foreach ($groups as $group => $content) {

            if (!is_numeric($group)) {
                $results[$group] = $content;
                continue;
            }

            $aggregated = '';

            if ($type == 'js') {
                $aggregated = $this->compressor->compressJs($content, $directory);
            } else if ($type == 'css') {
                $aggregated = $this->compressor->compressCss($content, $directory);
            }

            if (empty($aggregated)) {
                continue;
            }

            $asset = $this->asset->build(array('asset' => $aggregated, 'version' => false));
            $results[$asset['key']] = $asset;
        }

        $assets = $results;
        return $assets;
    }

    /**
     * Modifies data variables before passing them to templates
     */
    protected function prepareOutput()
    {
        $this->data['meta'] = $this->meta;
        $this->data['head_title'] = $this->title;
        $this->data['page_title'] = $this->ptitle;
        $this->data['breadcrumb'] = $this->breadcrumbs;

        $this->data['css'] = $this->css();
        $this->data['js_top'] = $this->js('top');
        $this->data['js_bottom'] = $this->js('bottom');

        $this->setPhpErrors();
    }

    /**
     * Sets php errors recorded by logger
     * @return null
     */
    protected function setPhpErrors()
    {
        $this->setPhpErrorsLive();

        $errors = $this->logger->getErrors();

        if (empty($errors)) {
            return null;
        }

        foreach ($errors as $severity => $messages) {
            foreach ($messages as $message) {
                $this->data['messages'][$severity][] = $message;
            }

            unset($errors[$severity]);
        }
    }

    /**
     * Set up live error reporting
     */
    protected function setPhpErrorsLive()
    {
        if ($this->path('admin/report/events')) {
            return null; // Don't display on the event reporting page
        }

        $access = $this->config('error_live_report', 0);

        if (!$access) {
            return null; // Disabled
        }

        if ($access == 1 && !$this->access('report_events')) {
            return null; // No  access to see the report
        }

        $count = $this->logger->countPhpErrors();

        if (!empty($count)) {
            $options = array('@count' => $count, '@url' => $this->url('admin/report/events'));
            $message = $this->text('Logged PHP errors: <a href="@url">@count</a>', $options);
            $this->data['messages']['warning'][] = $message;
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

        // Make global $this->data available in every .twig template
        $merged = gplcart_array_merge($this->data, $data);

        return $this->twig->render($file, $merged);
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
     * Adds required javascripts
     */
    protected function setDefaultJs()
    {
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
        if (!$this->is_backend || $this->is_installing) {
            return null;
        }

        $key = $this->config('cron_key', '');
        $last_run = (int) $this->config('cron_last_run', 0);
        $interval = (int) $this->config('cron_interval', 86400);

        if (!empty($interval) && (GC_TIME - $last_run) > $interval) {
            $url = $this->url('cron', array('key' => $key));
            $js = "\$(function(){\$.get('$url', function(data){});});";
            $this->setJs($js, array('position' => 'bottom'));
        }
    }

    /**
     * Adds context translation JS files
     */
    protected function setDefaultJsTranslation()
    {
        $classes = array(
            'gplcart\\core\\models\\Language', // text() called in modules
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

        // Track weight of JS settings to keep them together
        if (isset($weight)) {
            $this->js_settings_weight += (int) $weight;
        } else {
            $this->js_settings_weight++;
            $weight = $this->js_settings_weight;
        }

        $this->setJs("$var = $json;", array('weight' => $weight));
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
        $this->data['messages'] = $this->session->getMessage();

        $controller = strtolower(str_replace('\\', '-', $this->current_route['handlers']['controller'][0]));
        $this->data['body_classes'] = array_slice(explode('-', $controller, 3), -1);

        $this->setDefaultJs();
    }

    /**
     * Adds a JS on the page
     * @param string $script
     * @param array $data
     */
    public function setJs($script, array $data = array())
    {
        $this->asset->setJs($script, $data);
    }

    /**
     * Adds a CSS on the page
     * @param string $css
     * @param array $data
     */
    public function setCss($css, array $data = array())
    {
        $this->asset->setCss($css, $data);
    }

    /**
     * Adds single or multiple asset libraries
     * @param string|array $library_id
     * @param array $data
     */
    public function addAssetLibrary($library_id, array $data = array())
    {
        foreach ($this->library->getFiles($library_id) as $file) {
            switch (pathinfo($file, PATHINFO_EXTENSION)) {
                case 'js':
                    $this->setJs($file, $data);
                    break;
                case 'css':
                    $this->setCss($file, $data);
                    break;
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
     * Set meta tags on entity page
     * @param array $data
     */
    protected function setMetaEntity(array $data)
    {
        if ($data['meta_title'] !== '') {
            $this->setTitle($data['meta_title'], false);
        }
        if ($data['meta_description'] !== '') {
            $this->setMeta(array('name' => 'description', 'content' => $data['meta_description']));
        }
        $this->setMeta(array('rel' => 'canonical', 'href' => $this->path));
    }

    /**
     * Returns rendered menu
     * @param array $options
     * @return string
     */
    protected function renderMenu(array $options = array())
    {
        if (empty($options['items'])) {
            return '';
        }
        $options += array('depth' => 0, 'template' => 'common/menu');
        return $this->render($options['template'], $options);
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
        settype($messages, 'array');

        foreach (gplcart_array_flatten($messages) as $message) {
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

            $sort = array(
                'sort' => $filter,
                'order' => ($order == 'desc') ? 'asc' : 'desc');

            $this->data["sort_$filter"] = $this->url('', $sort + $query);
        }

        if (isset($query['sort']) && isset($query['order'])) {
            $this->data['sort'] = "{$query['sort']}-{$query['order']}";
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

            settype($value, 'string');

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

        $query['p'] = '%num';
        $page = isset($query['p']) ? (int) $query['p'] : 1;

        $this->pager->setPage($page)
                ->setPerPage($limit)
                ->setTotal($total)
                ->setUrlPattern('?' . urldecode(http_build_query($query)))
                ->setPreviousText($this->text('Back'))
                ->setNextText($this->text('Next'));

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
