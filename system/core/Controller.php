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
abstract class Controller
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
     * Whether the current request is AJAX
     * @var boolean
     */
    protected $is_ajax;

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
     * Current theme name
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
     * Current user cart ID
     * @var integer|string
     */
    protected $cart_uid;

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
     * An array of filter parameters
     * @var array
     */
    protected $query_filter = array();

    /**
     * Pager limits
     * @var array
     */
    protected $limit;

    /**
     * A total number of items found for the filter conditions
     * @var integer
     */
    protected $total;

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
     * Submitted form values
     * @var array
     */
    protected $submitted = array();

    /**
     * A key to get submitted data for $this->submitted
     * @var string
     */
    protected $form_source;

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
     * Cart model instance
     * @var \gplcart\core\models\Cart $cart
     */
    protected $cart;

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
     * Filter model instance
     * @var \gplcart\core\models\Filter $filter
     */
    protected $filter;

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
        $this->setAccessProperties();

        $this->setRouteProperties();
        $this->setStoreProperties();

        $this->setDefaultJsAssets();
        $this->setThemeProperties();

        $this->language->load();

        $this->setDefaultData();
        $this->controlCommonAccess();
        $this->controlMaintenanceMode();

        $this->hook->fire('construct.controller', $this);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->hook->fire('destruct.controller', $this);
    }

    /**
     * Sets user/access properties
     */
    protected function setAccessProperties()
    {
        $this->token = $this->config->token();
        $this->cart_uid = $this->cart->uid();
        $this->uid = (int) $this->user->getSession('user_id');

        if (!empty($this->uid)) {
            $this->current_user = $this->user->get($this->uid);
        }
    }

    /**
     * Returns a property
     * @param string $name
     * @return object
     */
    public function getProperty($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new \InvalidArgumentException("Property $name does not exist");
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
     * @param boolean $exclude_langcode
     * @return string
     */
    public function url($path = '', array $query = array(), $absolute = false,
            $exclude_langcode = false)
    {
        return $this->url->get($path, $query, $absolute, $exclude_langcode);
    }

    /**
     * Returns a formatted URL with a language code
     * @param string $langcode
     * @param string $path
     * @param array $query
     * @return string
     */
    public function urll($langcode, $path = '', array $query = array())
    {
        if ($langcode === $this->language->getDefault()) {
            $langcode = '';
        }
        return $this->url->language($langcode, $path, $query);
    }

    /**
     * Download a file
     * @param string $file
     * @param string $filename
     * @param array $options
     */
    public function download($file, $filename = '', $options = array())
    {
        $this->response->download($file, $filename, $options);
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
     * @param mixed $has_error A value to be returned when a error(s) found
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
    public function getStore($item = null)
    {
        if (isset($item)) {
            return gplcart_array_get_value($this->current_store, $item);
        }
        return $this->current_store;
    }

    /**
     * Returns a data of the current user
     * @param mixed $item
     * @return mixed
     */
    public function getUser($item = null)
    {
        if (isset($item)) {
            return gplcart_array_get_value($this->current_user, $item);
        }
        return $this->current_user;
    }

    /**
     * Returns an array of CSS styles
     * @return array
     */
    public function getCss()
    {
        $stylesheets = $this->asset->getCss();
        $this->compressAssets($stylesheets, 'css');
        return $stylesheets;
    }

    /**
     * Returns an array of JS scripts
     * @param string $position
     * @return array
     */
    public function getJs($position)
    {
        $scripts = $this->asset->getJs($position);
        $this->compressAssets($scripts, 'js');
        return $scripts;
    }

    /**
     * Formats a local time/date
     * @param null|integer $timestamp
     * @param bool $full
     * @param string $format
     * @return string
     */
    public function date($timestamp = null, $full = true, $format = '')
    {
        if (empty($timestamp)) {
            $timestamp = GC_TIME;
        }

        if (!empty($format)) {
            $timestamp = \DateTime::createFromFormat($format, $timestamp)->getTimestamp();
        }

        $dateformat = $this->config('date_prefix', 'd.m.y');

        if ($full) {
            $dateformat .= $this->config('date_suffix', ' H:i');
        }

        return date($dateformat, (int) $timestamp);
    }

    /**
     * Converts special characters to HTML entities
     * @param string $string
     * @return string
     */
    public function e($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Returns JSON escaped string
     * @param mixed $data
     * @return string
     */
    public function json($data)
    {
        return htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Returns a truncated string
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
     * Lower a string
     * @param string $string
     * @return string
     */
    public function lower($string)
    {
        return mb_strtolower($string, 'UTF-8');
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
     * Clean up HTML string using defined HTML filters
     * @param string $string
     * @param mixed $filter
     * @return string
     */
    public function filter($string, $filter = null)
    {
        if (!isset($filter)) {
            $filter = $this->current_filter;
        }

        return $this->filter->run($string, $filter);
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
            $summary = reset($parts);
        }
        if ($summary !== '' && $xss) {
            $summary = $this->filter($summary, $filter);
        }
        return $summary;
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
            return preg_match("~$pattern~i", $this->path) === 1;
        }
        return $this->path;
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
     * Whether the current URL is backend area (i.e /admin)
     * @return bool
     */
    public function isBackend()
    {
        return $this->url->isBackend();
    }

    /**
     * Whether the site in maintenance mode
     * @return boolean
     */
    public function isMaintenance()
    {
        return $this->is_maintenance;
    }

    /**
     * Renders a template
     * @param string $file
     * @param array $data
     * @param boolean $merge
     * @return string
     */
    public function render($file, array $data = array(), $merge = true)
    {
        $template = $this->getTemplateFile($file);

        if ($merge) {
            $data = array_merge($data, $this->getDefaultData());
        }

        $this->hook->fire('template', $template, $data, $this);

        $rendered = null;
        $this->hook->fire('template.render', $template, $data, $rendered, $this);

        if (isset($rendered)) {
            return trim($rendered);
        }

        $template .= '.php';

        if (is_file($template)) {
            $rendered = $this->renderTemplate($template, $data);
            return trim($rendered);
        }

        return $this->text('Could not load template %path', array('%path' => $template));
    }

    /**
     * Returns a full path to a module template WITHOUT extension
     * @param string $file
     * @return string
     */
    protected function getTemplateFile($file)
    {
        $module = $this->theme;

        $fullpath = false;
        if (strpos($file, '|') === false) {
            $fullpath = strpos($file, GC_ROOT_DIR) === 0;
        } else {
            list($module, $file) = explode('|', $file, 2);
        }

        return $fullpath ? $file : GC_MODULE_DIR . "/$module/templates/$file";
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
     */
    public function setHttpStatus($code)
    {
        $this->http_status = $code;
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

        return $this->request->method() === 'POST';
    }

    /**
     * Returns a data from POST query
     * @param string $name
     * @param mixed $default
     * @param bool|string $filter
     * @return mixed
     */
    public function getPosted($name = null, $default = null, $filter = true)
    {
        return $this->request->post($name, $default, $filter);
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
     * Returns a GET query
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function getQuery($key = null, $default = null)
    {
        return $this->request->get($key, $default);
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
        $this->cart = Container::get('gplcart\\core\\models\\Cart');
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
        $this->library = Container::get('gplcart\\core\\Library');
    }

    /**
     * Sets the current route data
     */
    protected function setRouteProperties()
    {
        $this->is_backend = $this->isBackend();
        $this->is_installing = $this->url->isInstall();
        $this->current_route = $this->route->getCurrent();

        $this->urn = $this->request->urn();
        $this->host = $this->request->host();
        $this->scheme = $this->request->scheme();
        $this->is_ajax = $this->request->isAjax();

        $this->path = $this->url->path();
        $this->base = $this->request->base();
        $this->query = (array) $this->getQuery();
        $this->langcode = $this->route->getLangcode();
        $this->uri = $this->scheme . $this->host . $this->urn;
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
     * Sets theme properties
     */
    protected function setThemeProperties()
    {
        $this->theme_frontend = $this->config('theme', 'frontend');
        $this->theme_backend = $this->config('theme_backend', 'backend');

        if ($this->is_backend) {
            $this->theme = $this->theme_backend;
        } elseif ($this->is_installing) {
            $this->theme = $this->theme_frontend;
        } elseif (!empty($this->current_store)) {
            $this->theme_frontend = $this->theme = $this->store->config('theme');
        }

        $this->hook->fire('theme', $this);

        if (empty($this->theme)) {
            $this->response->error404();
        }

        $this->current_theme = $this->config->getModuleInfo($this->theme);

        if (empty($this->current_theme)) {
            $this->response->error404();
        }

        $this->theme_settings = (array) $this->config->module($this->theme, null, array());

        if (empty($this->theme_settings['templates'])) {
            $this->templates = $this->getDefaultTemplates();
        } else {
            $this->templates = $this->theme_settings['templates'];
        }
    }

    /**
     * Set the current theme
     * @param string $name
     */
    public function setCurrentTheme($name)
    {
        $this->theme = $name;
    }

    /**
     * Whether a theme ID matches the current theme ID
     * @param string $name
     * @return boolean
     */
    public function isCurrentTheme($name)
    {
        return $this->theme === $name;
    }

    /**
     * Whether the current request is AJAX
     * @return boolean
     */
    public function isAjax()
    {
        return $this->is_ajax;
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
            $this->form_source = (string) $key;
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
     * Controll user access to the current page
     */
    protected function controlCommonAccess()
    {
        if (!$this->isInstalling()) {

            $this->controlToken(false);

            if (!empty($this->uid)) {
                $this->controlAccessCredentials();
            }

            $this->controlCsrf();
            $this->controlAccessUpload();
            $this->controlAccessRestrictedArea();
            $this->controlAccessAdmin();
            $this->controlAccessAccount();
        }
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
     */
    protected function controlAccessCredentials()
    {
        if (!isset($this->current_user['hash']) || empty($this->current_user['status'])) {
            $this->session->delete();
            $this->url->redirect('login');
        }

        $session_hash = $this->user->getSession('hash');
        $session_role_id = (int) $this->user->getSession('role_id');

        if (!gplcart_string_equals($this->current_user['hash'], $session_hash)) {
            $this->session->delete();
            $this->url->redirect('login');
        }

        if ((int) $this->current_user['role_id'] !== $session_role_id) {
            $this->session->delete();
            $this->url->redirect('login');
        }
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
        if (($this->is_backend || $this->url->isAccount()) && empty($this->uid)) {
            $this->url->redirect('login', array('target' => $this->path));
        }
    }

    /**
     * Prevent Cross-Site Request Forgery (CSRF)
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

        if (!gplcart_string_equals($this->request->post('token'), $this->token)) {
            $this->response->error403();
        }
    }

    /**
     * Controls token in the URL query
     * @param boolean $required
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
     */
    protected function controlAccessAdmin()
    {
        if (!$this->is_backend || $this->isSuperadmin()) {
            return null;
        }

        if (empty($this->current_user['role_status']) || !$this->access('admin')) {
            $this->redirect('/', $this->text('No access'), 'warning');
        }

        // Check route specific access
        if (isset($this->current_route['access']) && !$this->access($this->current_route['access'])) {
            $this->setHttpStatus(403);
        }
    }

    /**
     * Restrict access to only superadmin (UID 1)
     */
    protected function controlAccessSuperAdmin()
    {
        if (!$this->isSuperadmin()) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Contols access to account pages
     */
    protected function controlAccessAccount()
    {
        $account_id = $this->url->getAccountId();

        if ($account_id === false || $this->uid === $account_id) {
            return null;
        }

        if ($this->isSuperadmin($account_id) && !$this->isSuperadmin()) {
            $this->setHttpStatus(403);
            return null;
        }

        if (!$this->access('user')) {
            $this->setHttpStatus(403);
        }
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

        $this->prepareOutput();

        $templates += $this->templates;
        $layout = $templates['layout'];
        unset($templates['layout']);

        $body = $data = $this->data;
        foreach ($templates as $region => $template) {
            if (!in_array($region, array('region_head', 'region_body'))) {
                $body[$region] = $this->renderRegion($region, $template);
            }
        }

        $data['region_head'] = $this->render($templates['region_head'], $this->data);
        $data['region_body'] = $this->render($templates['region_body'], $body);

        $html = $this->render($layout, $data, false);
        $this->hook->fire('template.output', $html, $this);
        return $html;
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
            $items[] = isset($item['rendered']) ? (string) $item['rendered'] : (string) $item;
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
        $this->setHttpStatus($code);
        $this->output();
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
            $content = trim($item);
        }

        if ($content !== '') {
            $weight = isset($this->data["region_$region"]) ? count($this->data["region_$region"]) : 0;
            $this->data["region_$region"][] = array('rendered' => $content, 'weight' => $weight++);
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
        $this->data['_meta_tags'] = $this->meta;
        $this->data['_head_title'] = $this->title;
        $this->data['_page_title'] = $this->ptitle;
        $this->data['_breadcrumbs'] = $this->breadcrumbs;

        $this->data['_styles'] = $this->getCss();
        $this->data['_scripts_top'] = $this->getJs('top');
        $this->data['_scripts_bottom'] = $this->getJs('bottom');
    }

    /**
     * Renders PHP templates
     * @param string $template
     * @param array $data
     * @return string
     */
    public function renderTemplate($template, array $data)
    {
        extract($data, EXTR_SKIP);

        unset($data); // Kill duplicate

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
        $this->setDefaultJsStore();
    }

    /**
     * Set per-store JS (Google Analytics etc)
     */
    protected function setDefaultJsStore()
    {
        if (!empty($this->current_store['data']['js'])//
                && !$this->is_backend //
                && empty($this->current_route['internal'])) {
            $this->setJs($this->current_store['data']['js'], array('position' => 'bottom', 'aggregate' => false));
        }
    }

    /**
     * Sets global system JS files
     */
    protected function setDefaultJsAssets()
    {
        $this->addAssetLibrary('jquery');
        $this->setJs('files/assets/system/js/common.js');

        $this->addAssetLibrary('bootstrap');
        $this->addAssetLibrary(array('html5shiv', 'respond'), array('aggregate' => false, 'condition' => 'if lt IE 9'));
    }

    /**
     * Sets default JS settings
     */
    protected function setDefaultJsSettings()
    {
        $settings = array();
        foreach ($this->getDefaultData() as $key => $value) {
            $settings[ltrim($key, '_')] = $value;
        }
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
     * Returns superglobal template variables
     * @return array
     */
    protected function getDefaultData()
    {
        $data = array();

        $data['_uid'] = $this->uid;
        $data['_urn'] = $this->urn;
        $data['_uri'] = $this->uri;
        $data['_path'] = $this->path;
        $data['_base'] = $this->base;
        $data['_host'] = $this->host;
        $data['_token'] = $this->token;
        $data['_query'] = $this->query;
        $data['_scheme'] = $this->scheme;
        $data['_cart_uid'] = $this->cart_uid;
        $data['_is_front'] = $this->url->isFront();
        $data['_is_logged_in'] = !empty($this->uid);
        $data['_is_admin'] = $this->access('admin');
        $data['_is_superadmin'] = $this->isSuperadmin();
        $data['_langcode'] = empty($this->langcode) ? 'en' : $this->langcode;

        return $data;
    }

    /**
     * Sets default template variables
     */
    protected function setDefaultData()
    {
        $this->data = $this->getDefaultData();

        $this->data['_cart'] = $this->getCart();
        $this->data['_captcha'] = $this->renderCaptcha();
        $this->data['_languages'] = $this->language->getList();
        $this->data['_messages'] = $this->session->getMessage();
        $this->data['_user'] = $this->current_user;
        $this->data['_store'] = $this->current_store;

        $controller = strtolower(str_replace('\\', '-', $this->current_route['handlers']['controller'][0]));
        $this->data['_classes'] = array_slice(explode('-', $controller, 3), -1);

        $this->setDefaultJs();
    }

    /**
     * Returns the current cart data
     * @return array
     */
    public function getCart()
    {
        $conditions = array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );
        return $this->cart->getContent($conditions);
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
        if (!empty($data['meta_title'])) {
            $this->setTitle($data['meta_title'], false);
        }
        if (!empty($data['meta_description'])) {
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
     * Returns rendered honeypot input
     * @return string
     */
    public function renderCaptcha()
    {
        return $this->render('common/honeypot');
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
     */
    public function setHtmlFilter($data)
    {
        $role_id = isset($data['role_id']) ? $data['role_id'] : 0;
        $this->current_filter = $this->filter->getByRole($role_id);
    }

    /**
     * Returns true if an error occurred and pass back to template the submitted data
     * @param boolean $message
     * @return boolean
     */
    public function hasErrors($message = true)
    {
        if (empty($this->errors)) {
            return false;
        }

        if ($message) {
            $this->setMessage($this->text('One or more errors occurred'), 'danger');
        }

        if (isset($this->form_source)) {
            $this->setData($this->form_source, $this->submitted);
        }
        return true;
    }

    /**
     * Validates a submitted set of elements
     * @param string $handler_id
     * @param array $options
     * @return array
     */
    protected function validateComponent($handler_id, array $options = array())
    {
        $result = $this->validator->run($handler_id, $this->submitted, $options);

        if ($result === true) {
            return array();
        }
        $this->errors = (array) $result;
        return $this->errors;
    }

    /**
     * Validates a single element
     * @param string|array $field
     * @param string $handler_id
     * @param string|array $args
     * @return boolean
     */
    protected function validateElement($field, $handler_id, $args = array())
    {
        if (is_array($field)) {
            $label = reset($field);
            $field = key($field);
        }

        $options = array(
            'field' => $field,
            'arguments' => (array) $args,
            'label' => empty($label) ? $this->text('Field') : $label
        );

        $result = $this->validator->run($handler_id, $this->submitted, $options);

        if ($result === true) {
            return true;
        }

        settype($result, 'array');
        $this->errors = gplcart_array_merge($this->errors, $result);
        return false;
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
            $this->data['_messages'][$severity][] = $message;
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
        $parts = array_filter(array_map('trim', explode($delimiter, $text, 2)));
        return array_pad($parts, 2, '');
    }

    /**
     * Sets filter variables to the data array
     * @param array $allowed
     * @param array $query
     */
    public function setFilter(array $allowed = array(), $query = null)
    {
        if (!isset($query)) {
            $query = $this->getFilterQuery();
        }

        $this->query_filter = $query;
        $this->data['_filtering'] = false;
        $order = isset($this->query['order']) ? $this->query['order'] : '';

        foreach ($allowed as $filter) {

            $this->data["filter_$filter"] = '';
            if (isset($this->query[$filter])) {
                $this->data['filtering'] = true;
                $this->data["filter_$filter"] = (string) $this->query[$filter];
            }

            $sort = array(
                'sort' => $filter,
                'order' => $order == 'desc' ? 'asc' : 'desc') + $this->query_filter;

            $this->data["sort_$filter"] = $this->url('', $sort);
        }

        if (isset($this->query_filter['sort']) && isset($this->query_filter['order'])) {
            $this->data['_sort'] = "{$this->query_filter['sort']}-{$this->query_filter['order']}";
        }
    }

    /**
     * Returns an array of prepared GET values used for filtering and sorting
     * @param array $default
     * @param array $allowed An array of allowed keys in the GET query
     * @return array
     */
    public function getFilterQuery(array $default = array(), $allowed = array())
    {
        $query = $this->query;

        foreach ($query as $key => $value) {
            settype($value, 'string');
            if ($key === 'sort' && strpos($value, '-') !== false) {
                list($sort, $order) = explode('-', $value, 2);
                $query['sort'] = $sort;
                $query['order'] = $order;
            }

            if ($value === 'any') {
                unset($query[$key]);
            }
        }

        $query += $default;

        if (empty($allowed)) {
            return $query;
        }

        return array_intersect_key($query, array_flip($allowed));
    }

    /**
     * Sets the pager
     * @param integer $total
     * @param null|array $query
     * @param null|integer $limit
     * @return array
     */
    public function setPager($total, $query = null, $limit = null)
    {
        $this->data['_pager'] = $this->getPager($total, $query, $limit);
        return $this->getPagerLimit();
    }

    /**
     * Returns pager limits
     * @return array
     */
    public function getPagerLimit()
    {
        return $this->pager->getLimit();
    }

    /**
     * Set pager limits
     * @return string
     */
    public function setPagerLimit($limit = null)
    {
        $this->limit = $this->setPager($this->total, $this->query_filter, $limit);
    }

    /**
     * Returns a rendered pager
     * @param integer $total
     * @param null|array $query
     * @param null|integer $limit
     * @param string $key
     * @return string
     */
    public function getPager($total = null, $query = null, $limit = null,
            $key = 'p')
    {
        if (!isset($total)) {
            $total = (int) $this->total;
        }

        if (!isset($limit)) {
            $limit = $this->config('list_limit', 20);
        }

        if (!isset($query)) {
            $query = $this->getFilterQuery();
        }

        return $this->renderPager($total, $query, $limit, $key);
    }

    /**
     * Returns a rendered pager
     * @param integer $total
     * @param array $query
     * @param integer $limit
     * @param string $key
     * @return string
     */
    public function renderPager($total, $query, $limit, $key = 'p')
    {
        $page = 1;
        if (isset($query[$key])) {
            $page = (int) $query[$key];
        }

        $query[$key] = '%num';

        return $this->pager->setPage($page)
                        ->setPerPage($limit)
                        ->setTotal($total)
                        ->setUrlPattern('?' . urldecode(http_build_query($query)))
                        ->setPreviousText($this->text('Back'))
                        ->setNextText($this->text('Next'))->render();
    }

}
