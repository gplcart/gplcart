<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\helpers\Url as UrlHelper,
    gplcart\core\helpers\Request as RequestHelper;
use gplcart\core\exceptions\RouteException;

/**
 * Routes incoming requests
 */
class Route
{

    /**
     * Url class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * The current path
     * @var string
     */
    protected $path;

    /**
     * The current language code from the url
     * @var string
     */
    protected $langcode = '';

    /**
     * The current route
     * @var array
     */
    protected $route;

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * @param UrlHelper $url
     * @param RequestHelper $request
     * @param Config $config
     * @param Hook $hook
     */
    public function __construct(UrlHelper $url, RequestHelper $request,
            Config $config, Hook $hook)
    {
        $this->url = $url;
        $this->hook = $hook;
        $this->config = $config;
        $this->request = $request;
        $this->db = $config->getDb();

        $this->setLangcode();
        $this->path = $this->url->path();
    }

    /**
     * Returns an array of all available routes
     * @return array
     */
    public function getList()
    {
        $routes = &Cache::memory(__METHOD__);

        if (isset($routes)) {
            return $routes;
        }

        $routes = require GC_CONFIG_ROUTE;
        $this->hook->fire('route.list', $routes, $this);
        return $routes;
    }

    /**
     * Processes the current route
     */
    public function process()
    {
        $this->seekAlias();
        $this->seekRoute();
        $this->outputNotFound();
    }

    /**
     * Returns a language from the current URL
     * @return string
     */
    public function getLangcode()
    {
        return $this->langcode;
    }

    /**
     * Returns the current route
     * @return array
     */
    public function getCurrent()
    {
        return $this->route;
    }

    /**
     * Sets the current language
     */
    protected function setLangcode()
    {
        $languages = $this->config->get('languages', array());

        if (empty($languages)) {
            return null;
        }

        $segments = $this->url->segments(true);
        $default = $this->config->get('language', '');

        $found = !empty($languages[$segments[0]]['status']);
        $this->langcode = $found ? $segments[0] : $default;
        $is_default = $this->langcode === $default;

        $suffix = $is_default ? '' : $this->langcode;
        $this->request->setLangcode($suffix);

        // Redirect to URL without default language code
        if ($found && $is_default && $this->config->get('redirect_default_langcode', 1)) {
            unset($segments[0]);
            $path = $this->request->base(true) . implode('/', $segments);
            $this->url->redirect($path, $this->request->get(), true);
        }
    }

    /**
     * Finds an alias by the path
     */
    protected function seekAlias()
    {
        // We need database set up to find aliases
        if (empty($this->db)) {
            return null;
        }

        // Assuming we're on some/path.html
        // First check if the path stored in the database as an entity alias
        $info = $this->getAliasByPath($this->path);

        if (!isset($info['id_key'])) {
            // This path not found in the database. Then assume it's an entity path, e.g product/1 or category/1
            // Try to find an alias for the entity and redirect to it
            $this->seekEntityAlias();
            return null;
        }

        // Figure out which route is associated with the alias
        // Get entity name by removing "_id" suffix from the end of the entity key
        $entity = substr($info['id_key'], 0, -3);

        foreach ($this->getList() as $pattern => $route) {

            if (!isset($route['alias'][0])) {
                continue;
            }

            $segments = explode('/', $pattern);
            if ($segments[$route['alias'][0]] === $entity) {
                $this->callController($pattern, $route, array($info['id_value']));
                return null;
            }
        }
    }

    /**
     * Finds an alias by a route pattern and redirects to it
     */
    protected function seekEntityAlias()
    {
        $segments = $this->url->segments();

        foreach ($this->getList() as $pattern => $route) {

            if (empty($route['alias'])) {
                continue;
            }

            if (!isset($segments[$route['alias'][0]])) {
                continue;
            }

            if (!isset($segments[$route['alias'][1]])) {
                continue;
            }

            $pattern_segments = explode('/', $pattern);

            if (!isset($pattern_segments[$route['alias'][0]])) {
                continue;
            }

            if ($pattern_segments[$route['alias'][0]] !== $segments[$route['alias'][0]]) {
                continue;
            }

            $value = $segments[$route['alias'][1]];
            $key = $segments[$route['alias'][0]] . '_id';

            $alias = $this->getAliasByEntity($key, $value);

            if ($alias !== '') {
                $this->url->redirect($alias);
            }
        }
    }

    /**
     * Call a route controller
     * @param string $pattern
     * @param array $route
     * @param array $arguments
     * @throws RouteException
     */
    protected function callController($pattern, $route, $arguments = array())
    {
        $route += array('arguments' => array(), 'pattern' => $pattern);
        $route['arguments'] += $arguments;

        $handler = Handler::get($route, null, 'controller');

        if (empty($handler[0]) || !$handler[0] instanceof \gplcart\core\Controller) {
            throw new RouteException('Controller must be instance of \gplcart\core\Controller');
        }

        $this->route = $route;
        call_user_func_array($handler, $this->route['arguments']); // We should stop here
        throw new RouteException('An error occurred while processing the route');
    }

    /**
     * Selects an alias using entity key and value
     * @param string $key
     * @param integer $value
     * @return string
     */
    protected function getAliasByEntity($key, $value)
    {
        $sql = 'SELECT alias FROM alias WHERE id_key=? AND id_value=?';
        return (string) $this->db->fetchColumn($sql, array($key, $value));
    }

    /**
     * Returns an array of alias info using the current URL path
     * @param string $path
     * @return array
     */
    protected function getAliasByPath($path)
    {
        return $this->db->fetch('SELECT id_key, id_value FROM alias WHERE alias=?', array($path));
    }

    /**
     * Find an appropriate controller for the current URL
     */
    protected function seekRoute()
    {
        foreach ($this->getList() as $pattern => $route) {
            $pattern = trim($pattern, '/');
            $arguments = gplcart_parse_pattern($this->path, $pattern);
            if (is_array($arguments)) {
                $this->callController($pattern, $route, $arguments);
                break;
            }
        }
    }

    /**
     * Displays 404 Not Found Page
     */
    protected function outputNotFound()
    {
        $section = $this->url->isBackend() ? 'backend' : 'frontend';

        $route = array(
            'handlers' => array(
                'controller' => array(
                    "gplcart\\core\\controllers\\$section\\Controller",
                    'outputHttpStatus'))
        );

        Handler::call($route, null, 'controller', array(404));
        throw new RouteException('An error occurred while processing the route');
    }

    /**
     * Returns the current path
     * @return string
     */
    public function path()
    {
        return $this->path;
    }

}
