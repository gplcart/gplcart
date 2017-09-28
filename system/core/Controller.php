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
     * @var array|false
     */
    protected $current_filter;

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
     * Current language data
     * @var array
     */
    protected $current_language;

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

        $this->token = $this->config->token();

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
     * Sets instance properties
     */
    protected function setInstanceProperties()
    {
        $this->cart = Container::get('gplcart\\core\\models\\Cart');
        $this->user = Container::get('gplcart\\core\\models\\User');
        $this->store = Container::get('gplcart\\core\\models\\Store');
        $this->image = Container::get('gplcart\\core\\models\\Image');
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
        $this->current_route = $this->route->getCurrent();
        $this->path = $this->url->path();
        $this->is_backend = $this->url->isBackend();
        $this->is_install = $this->url->isInstall();
        $this->urn = $this->request->urn();
        $this->base = $this->request->base();
        $this->host = $this->request->host();
        $this->scheme = $this->request->scheme();
        $this->is_ajax = $this->request->isAjax();
        $this->uri = $this->scheme . $this->host . $this->urn;
        $this->query = (array) $this->request->get(null, array(), 'array');
    }

    /**
     * Sets the current language data
     */
    protected function setLanguageProperties()
    {
        if (empty($this->current_route['internal'])) {
            $this->langcode = $this->route->getLangcode();
            if ($this->langcode) {
                $this->current_language = $this->language->get($this->langcode);
                $this->language->setLangcode($this->langcode);
                $this->language->setContext($this->current_route['simple_pattern']);
            }
        }
    }

    /**
     * Returns the current route data
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function getRoute($key = null, $default = null)
    {
        if (isset($key)) {
            return isset($this->current_route[$key]) ? $this->current_route[$key] : $default;
        }

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
        $this->current_store = $this->store->getCurrent();
        if (isset($this->current_store['store_id'])) {
            $this->store_id = $this->current_store['store_id'];
        }
    }

    /**
     * Sets global system asset files
     */
    protected function setDefaultAssets()
    {
        if (empty($this->current_route['internal'])) {
            $this->addAssetLibrary('jquery', array('aggregate' => false));
            $this->setJs('files/assets/system/js/common.js', array('aggregate' => false));
            $this->setCss('files/assets/system/css/common.css', array('aggregate' => false));
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
        if (!isset($imagestyle_id)) {
            return $this->image->urlFromPath($path);
        }

        return $this->image->url($imagestyle_id, $path, $absolute);
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
        return $this->language->text($string, $arguments);
    }

    /**
     * Prints and/or returns JS array code with translations
     * @param string|array $strings
     * @param bool $print
     * @return string
     */
    public function jstext($strings, $print = true)
    {
        $code = '';
        foreach ((array) $strings as $string) {
            $text = $this->language->text($string, array(), false);
            if (isset($text[0]) && $text[0] !== '') {
                $key = gplcart_json_encode($string);
                $translation = gplcart_json_encode($text);
                $code .= "Gplcart.translations[$key]=$translation;\n";
            }
        }

        if ($print && $code) {
            echo "<script>$code</script>";
        }

        return $code;
    }

    /**
     * Returns a value on a error
     * @param string|array $key
     * @param mixed $return_error
     * @param mixed $return_no_error
     * @return mixed
     */
    public function error($key = null, $return_error = null,
            $return_no_error = '')
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
        $css = $this->asset->get('css', 'top');
        return $this->compressAssets($css, 'css');
    }

    /**
     * Returns an array of JS scripts
     * @param string $position
     * @return array
     */
    public function getJs($position)
    {
        $js = $this->asset->get('js', $position);
        return $this->compressAssets($js, 'js');
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

        return date($dateformat, $timestamp);
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
     * Renders a template
     * @param string $file
     * @param array $data
     * @param boolean $merge
     * @return string
     */
    public function render($file, array $data = array(), $merge = true)
    {
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
        }

        if (empty($template)) {
            return '';
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
        }

        return array(
            GC_MODULE_DIR . "/$module_id/templates/$file",
            GC_MODULE_DIR . "/{$this->theme}/override/templates/$module_id/$file"
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
     * @param string $type
     * @return mixed
     */
    public function getPosted($name, $default = null, $filter = true,
            $type = 'string')
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
            $this->theme_frontend = $this->theme = $this->store->config('theme');
        }

        $this->hook->attach('theme', $this);

        if (empty($this->theme)) {
            $this->response->error404();
        }

        $this->current_theme = $this->config->getModuleInfo($this->theme);

        if (empty($this->current_theme)) {
            $this->response->error404();
        }

        $this->theme_settings = (array) $this->config->module($this->theme, null, array());

        $this->templates = $this->getDefaultTemplates();
        if (!empty($this->current_theme['data']['templates'])) {
            $this->templates = array_merge($this->templates, $this->current_theme['data']['templates']);
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

            $this->submitted = $this->request->post(null, array(), $filter, 'array');
            return $this->submitted;
        }

        if (!isset($value) && empty($this->submitted)) {
            $this->form_source = (string) $key;
            $this->submitted = $this->request->post($key, array(), $filter, 'array');
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
    protected function filterSubmitted(array $allowed)
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
            $array = gplcart_string_array($value);
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
            $this->response->error403(false);
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
            $this->response->error403();
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
                $this->response->error403();
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
        if ($control && (empty($this->query['token']) || !$this->config->tokenValid($this->query['token']))) {
            $this->response->error403();
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
        $this->is_maintenance = empty($this->current_store['status'])//
                && !$this->is_install//
                && !$this->is_backend//
                && !$this->path('^login$')//
                && !$this->path('^logout$');

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
     * @param boolean $exclude_langcode
     */
    final public function redirect($url = '', $message = '', $severity = 'info',
            $exclude_langcode = false)
    {
        if ($message !== '') {
            $this->setMessage($message, $severity, true);
        }

        $this->url->redirect($url, array(), false, $exclude_langcode);
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
        $this->response->html($this->renderOutput($templates), $options);
    }

    /**
     * Extracts translatable strings from JS files and creates translation
     * @param array $scripts
     */
    protected function setJsTranslation(array $scripts)
    {
        if (!empty($this->langcode) && !is_file($this->language->getContextJsFile())) {
            foreach ($scripts as $key => $script) {
                if (strpos($key, 'system/modules/') === 0 && strpos($key, '/vendor/') === false && !empty($script['file'])) {
                    $string = file_get_contents($script['file']);
                    $this->language->createJsTranslation($string);
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
            $title = (string) $this->response->statuses($this->http_status);
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
    }

    /**
     * Renders all templates before sending them to a browser
     * @param array|string $templates
     * @return string
     */
    protected function renderOutput($templates)
    {
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
            $weight = isset($this->data["region_$region"]) ? count($this->data["region_$region"]) : 0;
            $weight++;
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
            $directory = GC_COMPRESSED_ASSET_DIR . "/$type";
            return $this->asset->compress($assets, $type, $directory);
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
     * Adds required JS
     */
    protected function setDefaultJs()
    {
        $this->setDefaultJsSettings();
        $this->setDefaultJsTranslation();
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
     * Adds JS translations
     */
    protected function setDefaultJsTranslation()
    {
        $translations = $this->language->loadJsTranslation();
        $json = gplcart_json_encode($translations);
        $this->setJs("Gplcart.translations=$json");
    }

    /**
     * Adds JSON string with JS settings
     * @param string $key
     * @param array $data
     * @param integer|null $weight
     */
    public function setJsSettings($key, array $data, $weight = null)
    {
        $json = gplcart_json_encode($data);
        $var = rtrim("Gplcart.settings.$key", '.');

        $asset = array(
            'type' => 'js',
            'weight' => $weight,
            'asset' => "$var = $json;"
        );

        $this->asset->setGroup('settings', $asset);
    }

    /**
     * Returns global template variables
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
        $data['_is_front'] = $this->isFront();
        $data['_is_logged_in'] = !empty($this->uid);
        $data['_is_admin'] = $this->access('admin');
        $data['_is_superadmin'] = $this->isSuperadmin();
        $data['_langcode'] = empty($this->langcode) ? 'en' : $this->langcode;

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

        $this->data['_help'] = '';
        $this->data['_version'] = gplcart_version();
        $this->data['_user'] = $this->current_user;
        $this->data['_store'] = $this->current_store;
        $this->data['_language'] = $this->current_language;
        $this->data['_messages'] = $this->session->getMessage();
        $this->data['_languages'] = $this->language->getList(true);
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
                    $classes[] = "gc-$part"; // Add prefix to prevent conflicts
                }
            }
        }

        $this->data['_classes'] = $classes;
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

        return $this->errors = (array) $result;
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
            } else {
                $this->data['_messages'][$severity][] = $message;
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

        $this->setFilterData($allowed, $query);
        $this->query_filter = array_filter($query, 'is_string');
    }

    /**
     *
     * @param array $allowed
     * @param array $query
     */
    protected function setFilterData(array $allowed, array $query)
    {
        $this->data['_filtering'] = false;
        $order = isset($this->query['order']) ? $this->query['order'] : '';

        foreach ($allowed as $filter) {

            $this->data["filter_$filter"] = '';
            if (isset($this->query[$filter])) {
                $this->data['_filtering'] = true;
                $this->data["filter_$filter"] = (string) $this->query[$filter];
            }

            $sort = array(
                'sort' => $filter,
                'order' => $order == 'desc' ? 'asc' : 'desc') + $query;

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
     * @param null|integer
     * @return array
     */
    public function setPagerLimit($limit = null)
    {
        return $this->limit = $this->setPager($this->total, $this->query_filter, $limit);
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
        $data = array(
            'page' => 1,
            'limit' => $limit,
            'total' => $total,
            'query' => $query
        );

        if (isset($query[$key])) {
            $data['page'] = (int) $query[$key];
        }

        $data['query'][$key] = '%num';
        $pager = $this->pager->build($data)->get();

        return $this->render('common/pager', array('pager' => $pager));
    }

    /**
     * Returns the rendered menu
     * @param array $options
     * @return string
     */
    public function renderMenu(array $options)
    {
        $options += array(
            'depth' => 0,
            'template' => 'common/menu'
        );

        return empty($options['items']) ? '' : $this->render($options['template'], $options);
    }

    /**
     * Returns rendered honey pot input
     * @return string
     */
    public function renderCaptcha()
    {
        return $this->render('common/honeypot');
    }

}
