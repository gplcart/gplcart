<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use InvalidArgumentException;

/**
 * Base controller class
 */
abstract class Controller
{

    /**
     * Whether the current path is an installation area
     * @var boolean
     */
    protected $is_install = false;

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
     * The current HTTP status code
     * @var string
     */
    protected $http_status;

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
     * The request URI from $_SERVER['REQUEST_URI'] variable
     * @var string
     */
    protected $uri_path;

    /**
     * Full current URI including query and schema
     * @var string
     */
    protected $uri;

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
     * @var array|false
     */
    protected $current_filter;

    /**
     * Current language data
     * @var array
     */
    protected $current_language;

    /**
     * Array of the current theme module info
     * @var array
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
    protected $post_key;

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
     * Translation model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Validator model instance
     * @var \gplcart\core\models\Validator $validator
     */
    protected $validator;

    /**
     * Image model instance
     * @var \gplcart\core\models\Image $image
     */
    protected $image;

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
     * URL class instance
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
     * Server class instance
     * @var \gplcart\core\helpers\Server $server
     */
    protected $server;

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
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Module class instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setInstanceProperties();

        $this->setToken();
        $this->setRouteProperties();
        $this->setLanguageProperties();
        $this->setUserProperties();
        $this->setStoreProperties();
        $this->setDefaultAssets();
        $this->setThemeProperties();
        $this->setDefaultData();
        $this->controlCommonAccess();
        $this->controlMaintenanceMode();

        $this->hook->attach('construct.controller', $this);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->hook->attach('destruct.controller', $this);
    }

    /**
     * Returns a property
     * @param string $name
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getProperty($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new InvalidArgumentException("Property $name does not exist");
    }

    /**
     * Set a property
     * @param string $property
     * @param object $value
     */
    public function setProperty($property, $value)
    {
        $this->{$property} = $value;
    }

    /**
     * Sets a token
     * @param string|null $token
     * @return string
     */
    public function setToken($token = null)
    {
        if (isset($token)) {
            return $this->token = $token;
        }

        return $this->token = gplcart_string_encode(crypt(session_id(), $this->config->getKey()));
    }

    /**
     * Returns an object instance
     * @param string $class
     * @return object
     */
    public function getInstance($class)
    {
        return Container::get($class);
    }

    /**
     * Sets instance properties
     */
    protected function setInstanceProperties()
    {
        $this->hook = $this->getInstance('gplcart\\core\\Hook');
        $this->route = $this->getInstance('gplcart\\core\\Route');
        $this->module = $this->getInstance('gplcart\\core\\Module');
        $this->config = $this->getInstance('gplcart\\core\\Config');
        $this->library = $this->getInstance('gplcart\\core\\Library');

        $this->cart = $this->getInstance('gplcart\\core\\models\\Cart');
        $this->user = $this->getInstance('gplcart\\core\\models\\User');
        $this->store = $this->getInstance('gplcart\\core\\models\\Store');
        $this->image = $this->getInstance('gplcart\\core\\models\\Image');
        $this->filter = $this->getInstance('gplcart\\core\\models\\Filter');
        $this->language = $this->getInstance('gplcart\\core\\models\\Language');
        $this->validator = $this->getInstance('gplcart\\core\\models\\Validator');
        $this->translation = $this->getInstance('gplcart\\core\\models\\Translation');

        $this->url = $this->getInstance('gplcart\\core\\helpers\\Url');
        $this->asset = $this->getInstance('gplcart\\core\\helpers\\Asset');
        $this->pager = $this->getInstance('gplcart\\core\\helpers\\Pager');
        $this->server = $this->getInstance('gplcart\\core\\helpers\\Server');
        $this->session = $this->getInstance('gplcart\\core\\helpers\\Session');
        $this->request = $this->getInstance('gplcart\\core\\helpers\\Request');
        $this->response = $this->getInstance('gplcart\\core\\helpers\\Response');
    }

    /**
     * Sets the current route data
     */
    protected function setRouteProperties()
    {
        $this->current_route = $this->route->get();

        $this->path = $this->url->path();
        $this->is_backend = $this->url->isBackend();
        $this->is_install = $this->url->isInstall();

        $this->host = $this->server->httpHost();
        $this->scheme = $this->server->httpScheme();
        $this->uri_path = $this->server->requestUri();
        $this->is_ajax = $this->server->isAjaxRequest();
        $this->uri = $this->scheme . $this->host . $this->uri_path;

        $this->base = $this->request->base();
        $this->query = (array) $this->request->get(null, array(), 'array');
    }

    /**
     * Sets the current language data
     */
    protected function setLanguageProperties()
    {
        if (!$this->isInternalRoute()) {
            $langcode = $this->route->getLangcode();
            if ($langcode) {
                $this->langcode = $langcode;
                $this->translation->set($langcode, $this->current_route['simple_pattern']);
                $this->current_language = $this->language->get($this->langcode);
            }
        }
    }

    /**
     * Returns the current route data
     * @return array
     */
    public function getRoute()
    {
        return $this->current_route;
    }

    /**
     * Sets user/access properties
     */
    protected function setUserProperties()
    {
        if (!$this->isInstall()) {
            $this->cart_uid = $this->cart->getUid();
            $this->uid = $this->user->getId();
            if (!empty($this->uid)) {
                $this->current_user = $this->user->get($this->uid);
            }
        }

        if (!empty($this->current_user['timezone'])) {
            date_default_timezone_set($this->current_user['timezone']);
        }
    }

    /**
     * Sets the current store data
     */
    protected function setStoreProperties()
    {
        $this->current_store = $this->store->get();

        if (isset($this->current_store['store_id'])) {
            $this->store_id = $this->current_store['store_id'];
        }
    }

    /**
     * Sets global system asset files
     */
    protected function setDefaultAssets()
    {
        if (!$this->isInternalRoute()) {
            $this->addAssetLibrary('jquery', array('aggregate' => false));
            $this->setJs('files/assets/system/js/common.js', array('aggregate' => false));
            $this->setCss('files/assets/system/css/common.css', array('aggregate' => false));
        }
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
     * @param boolean $exclude_lang
     * @return string
     */
    public function url($path = '', array $query = array(), $absolute = false, $exclude_lang = false)
    {
        return $this->url->get($path, $query, $absolute, $exclude_lang);
    }

    /**
     * Returns a formatted URL with a language code
     * @param string $langcode
     * @param string $path
     * @param array $query
     * @return string
     */
    public function lurl($langcode, $path = '', array $query = array())
    {
        if ($langcode === $this->language->getDefault()) {
            $langcode = '';
        }

        return $this->url->language($langcode, $path, $query);
    }

    /**
     * Returns an image URL
     * @param string $path
     * @param integer|null $imagestyle_id
     * @param boolean $absolute
     * @return string
     */
    public function image($path, $imagestyle_id = null, $absolute = false)
    {
        return $this->image->getUrl($path, $imagestyle_id, $absolute);
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
    public function text($string, array $arguments = array())
    {
        return $this->translation->text($string, $arguments);
    }

    /**
     * Prints and/or returns JS array with translations
     * @param string|array $strings
     * @param bool $print
     * @return string
     */
    public function jstext($strings, $print = true)
    {
        $code = '';
        foreach ((array) $strings as $string) {
            $key = gplcart_json_encode($string);
            $text = $this->translation->text($string);
            $translation = gplcart_json_encode(array($text));
            $code .= "Gplcart.translations[$key]=$translation;\n";
        }

        if ($print && $code) {
            echo "<script>$code</script>";
        }

        return $code;
    }

    /**
     * Returns a value if an error occurred
     * @param string|array $key
     * @param mixed $return_error
     * @param mixed $return_no_error
     * @return mixed
     */
    public function error($key = null, $return_error = null, $return_no_error = '')
    {
        if (isset($key)) {
            $result = gplcart_array_get($this->errors, $key);
        } else {
            $result = empty($this->errors) ? null : $this->errors;
        }

        if (isset($result)) {
            return isset($return_error) ? $return_error : $result;
        }

        return $return_no_error;
    }

    /**
     * Returns a data of the current store
     * @param mixed $item
     * @return mixed
     */
    public function getStore($item = null)
    {
        if (isset($item)) {
            return gplcart_array_get($this->current_store, $item);
        }

        return $this->current_store;
    }

    /**
     * Returns the current store ID
     * @return int
     */
    public function getStoreId()
    {
        return $this->store_id;
    }

    /**
     * Returns the current cart user ID
     * @return int|string
     */
    public function getCartUid()
    {
        return $this->cart_uid;
    }

    /**
     * Returns base path
     * @return string
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Returns the request IP
     * @return string
     */
    public function getIp()
    {
        return $this->server->remoteAddr();
    }

    /**
     * Returns a data of the current user
     * @param mixed $item
     * @return mixed
     */
    public function getUser($item = null)
    {
        if (isset($item)) {
            return gplcart_array_get($this->current_user, $item);
        }

        return $this->current_user;
    }

    /**
     * Returns an array of CSS styles
     * @return array
     */
    public function getCss()
    {
        return $this->asset->get('css', 'top');
    }

    /**
     * Returns an array of JS scripts
     * @param string $position
     * @return array
     */
    public function getJs($position)
    {
        $js = $this->asset->get('js', $position);

        if (isset($js['js_settings']['asset'])) {
            $json = gplcart_json_encode($js['js_settings']['asset']);
            $js['js_settings']['asset'] = "Gplcart.settings=$json;";
        }

        return $js;
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

        if ($full) {
            $format = $this->config->get('date_full_format', 'd.m.Y H:i');
        } else {
            $format = $this->config->get('date_short_format', 'd.m.y');
        }

        return date($format, $timestamp);
    }

    /**
     * Return a formatted string
     * @param string|array $format
     * @param array $arguments
     * @param string $glue
     * @return string
     */
    public function format($format, array $arguments = array(), $glue = '<br>')
    {
        if (is_array($format)) {
            $format = implode($glue, gplcart_array_flatten($format));
        }

        return vsprintf($format, $arguments);
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
        return htmlspecialchars(gplcart_json_encode($data), ENT_QUOTES, 'UTF-8');
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
    public function configTheme($key = null, $default = null)
    {
        if (!isset($key)) {
            return $this->theme_settings;
        }

        $value = gplcart_array_get($this->theme_settings, $key);
        return isset($value) ? $value : $default;
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

        $delimiter = $this->config('teaser_delimiter', '<!--teaser-->');
        $string = str_replace($delimiter, htmlspecialchars($delimiter), $string);
        return $this->filter->run($string, $filter);
    }

    /**
     * Explodes a text by teaser and full text
     * @param string $text
     * @return array
     */
    protected function explodeText($text)
    {
        $delimiter = $this->config('teaser_delimiter', '<!--teaser-->');
        $text = str_replace(htmlspecialchars($delimiter), $delimiter, $text);
        $parts = array_filter(array_map('trim', explode($delimiter, $text, 2)));
        return array_pad($parts, 2, '');
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
     * Returns a string from a text before the teaser delimiter
     * @param string $text
     * @param boolean $enable_filter
     * @param mixed $filter_id
     * @return string
     */
    public function teaser($text, $enable_filter = true, $filter_id = null)
    {
        $summary = '';
        if ($text !== '') {
            $parts = $this->explodeText($text);
            $summary = reset($parts);
        }

        if ($summary !== '' && $enable_filter) {
            $summary = $this->filter($summary, $filter_id);
        }

        return $summary;
    }

    /**
     * If $path is set - returns TRUE if the path pattern mathes the current URL path
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
     * Whether the current URL is an installing area
     * @return bool
     */
    public function isInstall()
    {
        return $this->is_install;
    }

    /**
     * Whether the current URL is an admin area
     * @return bool
     */
    public function isBackend()
    {
        return $this->is_backend;
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
     * Whether the route is internal
     * @return bool
     */
    public function isInternalRoute()
    {
        return !empty($this->current_route['internal']);
    }

    /**
     * Renders a template
     * @param string $file
     * @param array $data
     * @param boolean $merge
     * @param string $default
     * @return string
     */
    public function render($file, $data = array(), $merge = true, $default = '')
    {
        settype($data, 'array');
        $templates = $this->getTemplateFiles($file);

        if ($merge) {
            $data = array_merge($data, $this->getDefaultData());
        }

        $rendered = null;
        $this->hook->attach('template.render', $templates, $data, $rendered, $this);

        if (isset($rendered)) {
            return trim($rendered);
        }

        list($original, $overridden) = $templates;

        if (is_file("$overridden.php")) {
            $template = "$overridden.php";
        } else if (is_file("$original.php")) {
            $template = "$original.php";
        } else {
            return $default;
        }

        $rendered = $this->renderPhpTemplate($template, $data);
        return trim($rendered);
    }

    /**
     * Returns an array of full template paths without file extension
     * @param string $file
     * @return array
     */
    protected function getTemplateFiles($file)
    {
        $module_id = $this->theme;

        if (strpos($file, '|') !== false) {
            list($module_id, $file) = explode('|', $file, 2);
        } else if (gplcart_path_is_absolute($file)) {
            $template = substr($file, 0, (strrpos($file, '.')));
            return array($template, $template);
        }

        return array(
            GC_DIR_MODULE . "/$module_id/templates/$file",
            GC_DIR_MODULE . "/{$this->theme}/override/templates/$module_id/$file"
        );
    }

    /**
     * Whether the user is super admin
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
     * Whether the key exists in POST query or current query is POST type
     * @param string|null $key
     * @return boolean
     */
    public function isPosted($key = null)
    {
        if (isset($key)) {
            $value = $this->request->post($key, null);
            return isset($value);
        }

        return $this->server->requestMethod() === 'POST';
    }

    /**
     * Returns a data from POST query
     * @param string $name
     * @param mixed $default
     * @param bool|string $filter
     * @param string $type
     * @return mixed
     */
    public function getPosted($name, $default = null, $filter = true, $type = 'string')
    {
        return $this->request->post($name, $default, $filter, $type);
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
     * @param string|null $type
     * @return mixed
     */
    public function getQuery($key = null, $default = null, $type = 'string')
    {
        return $this->request->get($key, $default, $type);
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
     * Sets theme properties
     */
    protected function setThemeProperties()
    {
        $this->theme_frontend = $this->config('theme', 'frontend');
        $this->theme_backend = $this->config('theme_backend', 'backend');

        if ($this->is_backend) {
            $this->theme = $this->theme_backend;
        } elseif ($this->is_install) {
            $this->theme = $this->theme_frontend;
        } elseif (!empty($this->current_store)) {
            $this->theme_frontend = $this->store->getConfig('theme');
            $this->theme = $this->theme_frontend;
        }

        $this->hook->attach('theme', $this);

        if (empty($this->theme)) {
            $this->response->outputError404();
        }

        $this->current_theme = $this->module->getInfo($this->theme);

        if (empty($this->current_theme)) {
            $this->response->outputError404();
        }

        $this->theme_settings = (array) $this->getModuleSettings($this->theme, null, array());

        $this->templates = $this->getDefaultTemplates();
        if (!empty($this->current_theme['data']['templates'])) {
            $this->templates = array_merge($this->templates, $this->current_theme['data']['templates']);
        }
    }

    /**
     * Returns all or a single module setting
     * @param string $module_id
     * @param null|string $key
     * @param mixed $default
     * @return mixed
     */
    public function getModuleSettings($module_id, $key = null, $default = null)
    {
        return $this->module->getSettings($module_id, $key, $default);
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
     * Returns the current theme module ID
     * @return string
     */
    public function getCurrentTheme()
    {
        return $this->theme;
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
     * @return array
     */
    public function setData($key, $value)
    {
        gplcart_array_set($this->data, $key, $value);

        return $this->data;
    }

    /**
     * Removes a value by a key from an array of template data
     * @param string|array $key
     * @return array
     */
    public function unsetData($key)
    {
        gplcart_array_unset($this->data, $key);

        return $this->data;
    }

    /**
     * Sets an error
     * @param null|string|array $key
     * @param mixed $value
     * @return array
     */
    public function setError($key, $value)
    {
        if (isset($key)) {
            gplcart_array_set($this->errors, $key, $value);
        } else {
            $this->errors = (array) $value;
        }

        return $this->errors;
    }

    /**
     * Removes an error by a key from an array of errors
     * @param string|array $key
     * @return array
     */
    public function unsetError($key)
    {
        gplcart_array_unset($this->errors, $key);
        return $this->errors;
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

            if (isset($value)) {
                return $this->submitted = (array) $value;
            }

            $this->submitted = (array) $this->request->post(null, array(), $filter, 'array');
            return $this->submitted;
        }

        if (!isset($value) && empty($this->submitted)) {
            $this->post_key = (string) $key;
            $this->submitted = (array) $this->request->post($key, array(), $filter, 'array');
            return $this->submitted;
        }

        gplcart_array_set($this->submitted, $key, $value);
        return $this->submitted;
    }

    /**
     * Removes a value(s) from an array of submitted data
     * @param string|array $key
     * @return array
     */
    public function unsetSubmitted($key)
    {
        gplcart_array_unset($this->submitted, $key);
        return $this->submitted;
    }

    /**
     * Limit an array of submitted data to the allowed keys
     * @param array $allowed
     * @return array
     */
    public function filterSubmitted(array $allowed)
    {
        $this->submitted = array_intersect_key($this->submitted, array_flip($allowed));
        return $this->submitted;
    }

    /**
     * Converts a submitted value to boolean
     * @param string|array $key
     * @return boolean
     */
    public function setSubmittedBool($key)
    {
        $bool = (bool) $this->getSubmitted($key);
        $this->setSubmitted($key, $bool);
        return $bool;
    }

    /**
     * Converts a submitted value to array using multiline delimiter
     * @param string|array $key
     * @return array
     */
    public function setSubmittedArray($key)
    {
        $value = $this->getSubmitted($key);

        if (isset($value) && is_string($value)) {
            $array = gplcart_string_explode_multiline($value);
            $this->setSubmitted($key, $array);
            return $array;
        }

        return array();
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
            $result = gplcart_array_get($this->submitted, $key);
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

        $result = gplcart_array_get($this->data, $key);
        return isset($result) ? $result : $default;
    }

    /**
     * Controls user access to the current page
     */
    protected function controlCommonAccess()
    {
        if (!$this->isInstall()) {

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
        if ($this->request->post('url', '', false, 'string') !== '') {
            $this->response->outputError403(false);
        }
    }

    /**
     * Controls the current user credentials, such as status, role, password hash...
     */
    protected function controlAccessCredentials()
    {
        if (empty($this->current_user['hash']) || empty($this->current_user['status'])) {
            $this->session->delete();
            $this->url->redirect('login');
        }

        if (!gplcart_string_equals($this->current_user['hash'], $this->user->getSession('hash'))) {
            $this->session->delete();
            $this->url->redirect('login');
        }

        if ($this->current_user['role_id'] != $this->user->getRoleId()) {
            $this->session->delete();
            $this->url->redirect('login');
        }
    }

    /**
     * Controls access to upload a file
     */
    protected function controlAccessUpload()
    {
        if ($this->request->file() && !$this->access('file_upload')) {
            $this->response->outputError403();
        }
    }

    /**
     * Controls access to restricted areas
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
        if ($this->isPosted() && (!isset($this->current_route['token']) || !empty($this->current_route['token']))) {
            $token = $this->request->post('token', '', false, 'string');
            if (!gplcart_string_equals($token, $this->token)) {
                $this->response->outputError403();
            }
        }
    }

    /**
     * Controls token in the URL query
     * @param null|string $key
     */
    protected function controlToken($key = null)
    {
        $control = isset($key) ? isset($this->query[$key]) : !empty($this->query);
        if ($control && (empty($this->query['token']) || !gplcart_string_equals($this->token, $this->query['token']))) {
            $this->response->outputError403();
        }
    }

    /**
     * Controls access to admin pages
     */
    protected function controlAccessAdmin()
    {
        if ($this->is_backend && !$this->isSuperadmin()) {

            if (empty($this->current_user['role_status']) || !$this->access('admin')) {
                $this->redirect('/', $this->text('No access'), 'warning');
            }

            if (isset($this->current_route['access']) && !$this->access($this->current_route['access'])) {
                $this->setHttpStatus(403);
            }
        }
    }

    /**
     * Restrict access to only super-admin (UID 1)
     */
    protected function controlAccessSuperAdmin()
    {
        if (!$this->isSuperadmin()) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Controls access to account pages
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
        $allowed_path = $this->is_install || $this->is_backend//
            || in_array($this->path, array('login', 'logout', 'forgot'));

        $this->is_maintenance = empty($this->current_store['status']) && !$allowed_path;

        if ($this->is_maintenance && !$this->access('maintenance')) {
            if (!$this->isFront()) {
                $this->redirect('/');
            }
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
     * @param boolean $exclude_lang
     */
    public function redirect($url = '', $message = '', $severity = 'info', $exclude_lang = false)
    {
        $this->setMessage($message, $severity, true);

        $parsed = parse_url($url);

        $query = array();
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
        }

        $full = strpos($url, $this->base) === 0;
        $this->url->redirect($url, (array) $query, $full, $exclude_lang);
    }

    /**
     * Sets page <title> tag
     * @param string $title
     * @param boolean $both
     */
    public function setTitle($title, $both = true)
    {
        $this->data['_head_title'] = strip_tags($title);

        if ($both && !isset($this->data['_page_title'])) {
            $this->setPageTitle($title);
        }
    }

    /**
     * Sets page titles (H tag)
     * @param string $title
     */
    public function setPageTitle($title)
    {
        $this->data['_page_title'] = $title;
    }

    /**
     * Extracts translatable strings from JS files and creates translation
     * @param array $scripts
     */
    protected function setJsTranslation(array $scripts)
    {
        if (!empty($this->langcode) && !is_file($this->translation->getContextJsFile())) {
            foreach ($scripts as $key => $script) {
                if (strpos($key, 'system/modules/') === 0
                    && strpos($key, '/vendor/') === false
                    && !empty($script['file'])) {
                    $string = file_get_contents($script['file']);
                    $this->translation->createJsTranslation($string);
                }
            }
        }
    }

    /**
     * Prepare output
     * @param mixed $templates
     * @param array $options
     */
    protected function prepareOutput(&$templates, array &$options)
    {
        if (!empty($this->http_status)) {
            $title = (string) $this->response->getStatus($this->http_status);
            $this->setTitle($title, false);
            $templates = "common/status/{$this->http_status}";
            $options['headers'] = $this->http_status;
        }
    }

    /**
     * Prepare template data variables before output
     */
    protected function prepareDataOutput()
    {
        $this->data['_css'] = $this->getCss();

        foreach (array('top', 'bottom') as $position) {
            $this->data["_js_$position"] = $this->getJs($position);
            $this->setJsTranslation($this->data["_js_$position"]);
        }

        $this->hook->attach('template.data', $this->data, $this);

        gplcart_array_sort($this->data['_css']);
        gplcart_array_sort($this->data['_js_top']);
        gplcart_array_sort($this->data['_js_bottom']);

        $this->data['_css'] = $this->compressAssets($this->data['_css'], 'css');

        foreach (array('top', 'bottom') as $position) {
            $this->data["_js_$position"] = $this->compressAssets($this->data["_js_$position"], 'js');
        }
    }

    /**
     * Renders all templates before sending them to a browser
     * @param array|string $templates
     * @return string
     */
    protected function renderOutput($templates)
    {
        $html = '';

        if (!$this->isInternalRoute()) {

            if (!is_array($templates)) {
                $templates = array('region_content' => (string) $templates);
            }

            $templates = array_merge($this->templates, $templates);

            $layout = $templates['layout'];
            unset($templates['layout']);

            $body = $data = $this->data;
            foreach ($templates as $id => $template) {
                if (strpos($id, 'region_') === 0) {
                    $body[$id] = $this->renderRegion($id, $template);
                }
            }

            $data['_head'] = $data['_body'] = '';

            if (!empty($templates['head'])) {
                $data['_head'] = $this->render($templates['head'], $this->data);
            }

            if (!empty($templates['body'])) {
                $data['_body'] = $this->render($templates['body'], $body);
            }

            $html = $this->render($layout, $data, false);
        }

        $this->hook->attach('template.output', $html, $this);
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
     * Outputs rendered page
     * @param null|array|string $templates
     * @param array $options
     */
    final public function output($templates = null, array $options = array())
    {
        if (empty($templates)) {
            $templates = $this->templates;
        }

        $this->prepareDataOutput();
        $this->prepareOutput($templates, $options);
        $this->response->outputHtml($this->renderOutput($templates), $options);
    }

    /**
     * Output JSON string
     * @param mixed $data
     * @param array $options
     */
    public function outputJson($data, array $options = array())
    {
        $this->response->outputJson($data, $options);
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
        $this->output(array('body' => 'layout/maintenance'), array('headers' => 503));
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

        if ($content !== '' && isset($this->templates["region_$region"])) {
            $weight = 1;
            if (isset($this->data["region_$region"])) {
                $weight = count($this->data["region_$region"]) + 1;
            }
            $this->data["region_$region"][] = array('rendered' => $content, 'weight' => $weight);
        }
    }

    /**
     * Returns an array of default templates
     * @return array
     */
    protected function getDefaultTemplates()
    {
        return array(
            'head' => 'layout/head',
            'body' => 'layout/body',
            'layout' => 'layout/layout',
            'region_content' => 'layout/region_content',
            'region_bottom' => 'layout/region_bottom'
        );
    }

    /**
     * Compresses and aggregates assets
     * @param array $assets
     * @param string $type
     * @return array
     */
    protected function compressAssets(array $assets, $type)
    {
        if ($this->config("compress_$type", 0)) {
            return $this->asset->compress($assets, $type, GC_DIR_ASSET_COMPILED . "/$type");
        }

        return $assets;
    }

    /**
     * Renders PHP templates
     * @param string $template
     * @param array $data
     * @return string
     */
    public function renderPhpTemplate($template, array $data)
    {
        extract($data, EXTR_SKIP);

        unset($data); // Kill duplicates

        ob_start();
        include $template;
        return ob_get_clean();
    }

    /**
     * Adds default JS
     */
    protected function setDefaultJs()
    {
        foreach ($this->getDefaultData() as $key => $value) {
            $this->setJsSettings(ltrim($key, '_'), $value, 60);
        }

        $json = gplcart_json_encode($this->translation->loadJsTranslation());
        $this->setJs("Gplcart.translations=$json;");
    }

    /**
     * Adds JSON string with JS settings
     * @param string $key
     * @param mixed $data
     * @param integer|null $weight
     */
    public function setJsSettings($key, $data, $weight = null)
    {
        $asset = array(
            'type' => 'js',
            'weight' => $weight,
            'aggregate' => false,
            'key' => 'js_settings',
            'merge' => 'js_settings',
            'asset' => array($key => $data)
        );

        $this->asset->set($asset);
    }

    /**
     * Returns global template variables
     * @return array
     */
    protected function getDefaultData()
    {
        $data = array();

        $data['_uid'] = $this->uid;
        $data['_uri'] = $this->uri;
        $data['_path'] = $this->path;
        $data['_base'] = $this->base;
        $data['_host'] = $this->host;
        $data['_token'] = $this->token;
        $data['_query'] = $this->query;
        $data['_scheme'] = $this->scheme;
        $data['_cart_uid'] = $this->cart_uid;
        $data['_is_front'] = $this->isFront();
        $data['_is_logged_in'] = !empty($this->uid);
        $data['_is_admin'] = $this->access('admin');
        $data['_is_superadmin'] = $this->isSuperadmin();
        $data['_langcode'] = empty($this->langcode) ? 'en' : $this->langcode;
        $data['_url'] = $this->scheme . $this->host . $this->base . $this->path;

        return $data;
    }

    /**
     * Whether the current path is home page
     * @return bool
     */
    public function isFront()
    {
        return $this->url->isFront();
    }

    /**
     * Sets default template variables
     */
    protected function setDefaultData()
    {
        $this->data = array_merge($this->data, $this->getDefaultData());

        $this->data['_version'] = gplcart_version();
        $this->data['_user'] = $this->current_user;
        $this->data['_store'] = $this->current_store;
        $this->data['_language'] = $this->current_language;
        $this->data['_messages'] = $this->session->getMessage();
        $this->data['_languages'] = $this->language->getList(array('enabled' => true));
        $this->data['_store_title'] = $this->store->getTranslation('title', $this->langcode);

        if (!empty($this->current_store['data']['logo'])) {
            $this->data['_store_logo'] = $this->image($this->current_store['data']['logo']);
        }

        if (!empty($this->current_store['data']['favicon'])) {
            $this->data['_store_favicon'] = $this->image($this->current_store['data']['favicon']);
        }

        $this->setClasses();
        $this->setDefaultJs();
    }

    /**
     * Sets an array of body CSS classes
     */
    protected function setClasses()
    {
        $classes = array();
        if (isset($this->current_route['pattern'])) {
            $pattern = $this->current_route['pattern'] ? $this->current_route['pattern'] : 'front';
            foreach (explode('/', $pattern) as $part) {
                if (ctype_alpha($part)) {
                    $classes[] = "gc-$part";
                }
            }
        }

        $this->data['_classes'] = $classes;
    }

    /**
     * Adds a JS on the page
     * @param string $script
     * @param array $data
     * @return bool|array
     */
    public function setJs($script, array $data = array())
    {
        $data['type'] = 'js';
        $data['asset'] = $script;

        return $this->asset->set($data);
    }

    /**
     * Adds a CSS on the page
     * @param string $css
     * @param array $data
     * @return bool|array
     */
    public function setCss($css, array $data = array())
    {
        $data['asset'] = $css;
        $data['type'] = 'css';

        return $this->asset->set($data);
    }

    /**
     * Adds single or multiple asset libraries
     * @param string|array $library_id
     * @param array $data
     * @return array
     */
    public function addAssetLibrary($library_id, array $data = array())
    {
        $added = array();
        foreach ($this->library->getFiles($library_id) as $file) {

            $extension = pathinfo($file, PATHINFO_EXTENSION);

            if ($extension === 'js') {
                $result = $this->setJs($file, $data);
            } else if ($extension === 'css') {
                $result = $this->setCss($file, $data);
            }

            if (!empty($result)) {
                $added[] = $file;
            }
        }

        return $added;
    }

    /**
     * Sets a meta tag on the page
     * @param array $content
     */
    public function setMeta($content)
    {
        $key = '_meta_tags';

        if (!isset($this->data[$key])) {
            $this->data[$key] = array();
        }

        $this->data[$key][] = $content;
    }

    /**
     * Sets a single page breadcrumb
     * @param array $breadcrumb
     */
    public function setBreadcrumb(array $breadcrumb)
    {
        $key = '_breadcrumbs';

        if (!isset($this->data[$key])) {
            $this->data[$key] = array();
        }

        $this->data[$key][] = $breadcrumb;
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

        if (isset($this->post_key)) {
            $this->setData($this->post_key, $this->submitted);
        }

        return true;
    }

    /**
     * Validates a submitted set of elements
     * @param string $handler_id
     * @param array $options
     * @return array
     */
    public function validateComponent($handler_id, array $options = array())
    {
        $result = $this->validator->run($handler_id, $this->submitted, $options);

        if ($result === true) {
            return array();
        }

        return $this->errors = (array) $result;
    }

    /**
     * Validates a single element
     * @param string|array $field
     * @param string $handler_id
     * @param string|array $arguments
     * @return boolean
     */
    protected function validateElement($field, $handler_id, $arguments = array())
    {
        if (is_array($field)) {
            $label = reset($field);
            $field = key($field);
        }

        $options = array(
            'field' => $field,
            'arguments' => (array) $arguments,
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
        if (!empty($messages)) {
            foreach (gplcart_array_flatten((array) $messages) as $message) {
                if ($once) {
                    $this->session->setMessage($message, $severity);
                } else {
                    $this->data['_messages'][$severity][] = $message;
                }
            }
        }
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

        $this->setFilterData($allowed, $query);
        $this->query_filter = array_filter($query, 'is_string');
    }

    /**
     * @param array $allowed
     * @param array $query
     */
    protected function setFilterData(array $allowed, array $query)
    {
        $this->data['_filtering'] = false;
        $order = isset($this->query['order']) ? $this->query['order'] : '';

        foreach ($allowed as $filter) {

            $this->data["filter_$filter"] = '';

            if (isset($this->query[$filter]) && is_string($this->query[$filter])) {
                $this->data['_filtering'] = true;
                $this->data["filter_$filter"] = $this->query[$filter];
            }

            $sort = array('sort' => $filter, 'order' => $order == 'desc' ? 'asc' : 'desc');
            $sort += $query;
            $this->data["sort_$filter"] = $this->url('', $sort);
        }
    }

    /**
     * Returns an array of prepared GET values used for filtering and sorting
     * @param array $default
     * @param array $allowed
     * @return array
     */
    public function getFilterQuery(array $default = array(), $allowed = array())
    {
        $query = $this->query;
        foreach ($query as $key => $value) {

            if (!is_string($value)) {
                continue;
            }

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
     * @param array $options
     * @return array
     */
    public function setPager(array $options)
    {
        $pager = $this->getPager($options);
        $this->data['_pager'] = $pager['rendered'];
        return $pager['limit'];
    }

    /**
     * Returns a rendered pager
     * @param array $options
     * @return array
     */
    public function getPager(array $options = array())
    {
        $options += array(
            'query' => $this->getFilterQuery(),
            'limit' => $this->config('list_limit', 20)
        );

        return array(
            'rendered' => $this->getWidgetPager($options),
            'limit' => $this->pager->getLimit()
        );
    }

    /**
     * Returns the rendered pager
     * @param array $options
     * @return string
     */
    public function getWidgetPager(array $options)
    {
        $options += array(
            'key' => 'p',
            'page' => 1,
            'template' => 'common/pager',
            'query' => $this->getQuery(null, array(), 'array')
        );

        if (isset($options['query'][$options['key']])) {
            $options['page'] = (int) $options['query'][$options['key']];
        }

        $options['query'][$options['key']] = '%num';

        $data = array(
            'options' => $options,
            'pager' => $this->pager->build($options)->get()
        );

        return $this->render($options['template'], $data);
    }

}
