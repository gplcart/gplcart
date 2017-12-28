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
use gplcart\core\exceptions\Route as RouteException;

/**
 * Routes incoming requests
 */
class Route
{

    /**
     * URL class instance
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
    protected $path = '';

    /**
     * The language code from the current URL
     * @var string
     */
    protected $langcode;

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
     * @param Config $config
     * @param Hook $hook
     * @param UrlHelper $url
     * @param RequestHelper $request
     */
    public function __construct(Config $config, Hook $hook, UrlHelper $url, RequestHelper $request)
    {
        $this->url = $url;
        $this->hook = $hook;
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Initialize route
     */
    public function init()
    {
        $this->setLangcode();
        $this->path = $this->url->path();
        $this->db = $this->config->getDb();
    }

    /**
     * Returns an array of all available routes
     * @return array
     */
    public function getList()
    {
        $routes = &gplcart_static('route.list');

        if (isset($routes)) {
            return $routes;
        }

        $routes = (array) gplcart_config_get(GC_FILE_CONFIG_ROUTE);
        $this->hook->attach('route.list', $routes, $this);
        return $routes;
    }

    /**
     * Processes the route
     */
    public function process()
    {
        $this->processAlias();
        $this->processRoute();
        $this->output404();
    }

    /**
     * Sets language
     */
    protected function setLangcode()
    {
        $languages = $this->config->get('languages', array());

        if (!empty($languages)) {

            $segments = $this->url->getSegments(true);
            $default = $this->config->get('language', 'en');

            $found = !empty($languages[$segments[0]]['status']) || $segments[0] === 'en';
            $this->langcode = $found ? $segments[0] : $default;
            $is_default = ($this->langcode === $default);

            $suffix = $is_default ? '' : $this->langcode;

            $this->request->setLangcode($suffix);

            if ($found && $is_default && $this->config->get('redirect_default_langcode', 1)) {
                unset($segments[0]);
                $path = $this->request->base(true) . implode('/', $segments);
                $this->url->redirect($path, $this->request->get(), true);
            }
        }
    }

    /**
     * Finds an alias for a path
     * @param string|null $path
     */
    public function processAlias($path = null)
    {
        if (!isset($path)) {
            $path = $this->path;
        }

        if ($this->db->isInitialized() && $this->config->get('alias', true)) {
            foreach (array_keys($this->findAlias($path)) as $pattern) {
                $this->callAliasController($pattern, $path, null);
            }
        }
    }

    /**
     * Try to find and call an alias handler using the URL path
     * @param string $path
     * @return array An array of active routes which don't support aliases
     */
    protected function findAlias($path)
    {
        $routes = $this->getList();
        $alias = $this->db->fetch('SELECT entity, entity_id FROM alias WHERE alias=?', array($path));

        foreach ($routes as $pattern => $route) {

            if (isset($route['status']) && empty($route['status'])) {
                unset($routes[$pattern]);
                continue;
            }

            if (empty($route['handlers']['alias'])) {
                unset($routes[$pattern]);
                continue;
            }

            $this->callAliasController($pattern, $path, $alias);
        }

        return $routes;
    }

    /**
     * Find an appropriate controller for the URL
     * @throws RouteException
     */
    public function processRoute()
    {
        foreach ($this->getList() as $pattern => $route) {

            if (isset($route['status']) && empty($route['status'])) {
                continue;
            }

            $arguments = array();
            if (gplcart_path_match($this->path, $pattern, $arguments)) {
                $this->callController($pattern, $arguments);
            }
        }
    }

    /**
     * Displays 404 Not Found Page
     */
    public function output404()
    {
        $pattern = $this->url->isBackend() ? 'status-backend' : 'status-frontend';
        $this->callController($pattern, array(404));
    }

    /**
     * Route alias callback
     * @param string $pattern
     * @param string $path
     * @param array $alias
     */
    public function aliasCallback($pattern, $path, $alias)
    {
        if (!empty($alias['entity']) && !empty($alias['entity_id'])) {
            if (strpos($pattern, "{$alias['entity']}/") === 0) {
                $this->callController($pattern, array($alias['entity_id']));
            }
        }

        if (!isset($alias)) {
            $arguments = array();
            if (gplcart_path_match($path, $pattern, $arguments)) {
                $conditions = array(strtok($pattern, '/'), reset($arguments));
                $alias_path = $this->db->fetchColumn('SELECT alias FROM alias WHERE entity=? AND entity_id=?', $conditions);
            }

            if (!empty($alias_path)) {
                $this->url->redirect($alias_path);
            }
        }
    }

    /**
     * Call a route controller
     * @param string|array $route
     * @param array $arguments
     * @param string $method
     */
    public function callHandler($route, $arguments = array(), $method = 'controller')
    {
        try {
            $route = $this->set($route, $arguments);
            Handler::call($route, null, $method, $route['arguments']);
        } catch (\Exception $ex) {
            throw new RouteException($ex->getMessage());
        }
    }

    /**
     * Call a controller for the route
     * @param string|array $route
     * @param array $arguments
     * @throws RouteException
     */
    public function callController($route, array $arguments = array())
    {
        $this->callHandler($route, $arguments);
        throw new RouteException('An error occurred while processing the route');
    }

    /**
     * Calls an alias controller for the route
     * @param string $pattern
     * @param string $path
     * @param array|null $alias
     */
    public function callAliasController($pattern, $path, $alias)
    {
        $this->callHandler($pattern, array($pattern, $path, $alias), 'alias');
    }

    /**
     * Sets a route
     * @param array|string $route
     * @param array $arguments
     * @return array
     */
    public function set($route, array $arguments = array())
    {
        if (!is_array($route)) {

            $pattern = $route;
            $list = $this->getList();

            if (empty($list[$pattern])) {
                throw new RouteException("Unknown route pattern $pattern");
            }

            $route = $list[$pattern];
            $route += array('arguments' => array(), 'pattern' => $pattern);
            $route['simple_pattern'] = preg_replace('@\(.*?\)@', '*', $pattern);

            if (!empty($arguments)) {
                $route['arguments'] = array_merge($arguments, $route['arguments']);
            }
        }

        return $this->route = $route;
    }

    /**
     * Returns the current route
     * @return array
     */
    public function get()
    {
        return $this->route;
    }

    /**
     * Returns a language from the current URL
     * @return string
     */
    public function getLangcode()
    {
        return $this->langcode;
    }

}
