<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use Exception;
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
    public function __construct(Config $config, Hook $hook, UrlHelper $url,
            RequestHelper $request)
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
        $routes = &gplcart_static('route.list');

        if (isset($routes)) {
            return $routes;
        }

        $routes = require GC_CONFIG_ROUTE;
        $this->hook->attach('route.list', $routes, $this);
        return $routes;
    }

    /**
     * Processes the current route
     */
    public function process()
    {
        $this->outputAlias();
        $this->outputRoute();
        $this->output404();
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

        if (!empty($languages)) {

            $segments = $this->url->segments(true);
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
    public function outputAlias($path = null)
    {
        if (empty($this->db)) {
            return null;
        }

        if (!isset($path)) {
            $path = $this->path;
        }

        if (!$this->isAliasablePath($path)) {
            return null;
        }

        $alias = $this->db->fetch('SELECT id_key, id_value FROM alias WHERE alias=?', array($path));

        $routes = $this->getList();
        foreach ($routes as $pattern => $route) {

            if (isset($route['status']) && empty($route['status'])) {
                unset($routes[$pattern]);
                continue;
            }

            if (empty($route['handlers']['alias'])) {
                unset($routes[$pattern]);
                continue;
            }

            $this->call($pattern, array($path, $pattern, $alias), 'alias');
        }

        foreach ($routes as $pattern => $route) {
            $this->call($pattern, array($path, $pattern, null), 'alias');
        }
    }

    /**
     * Whether the path 100% cannot have an alias
     * Allows to avoid unneeded database queries
     * @param string $path
     * @return boolean
     */
    public function isAliasablePath($path)
    {
        if (empty($path)) {
            return false;
        }

        $excluded = array('admin', 'account', 'review', 'checkout', 'compare', 'files');

        foreach ($excluded as $prefix) {
            if (strpos($path, $prefix) === 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Try to find an entity alias
     * @param string $path
     * @param string $pattern
     * @param array|null $alias
     * @throws RouteException
     */
    public function findEntityAlias($path, $pattern, $alias)
    {

        if (!empty($alias['id_key']) && !empty($alias['id_value'])) {
            $entity = substr($alias['id_key'], 0, -3);
            if (strpos($pattern, "$entity/") === 0) {
                $this->call($pattern, array($alias['id_value']));
                throw new RouteException('An error occurred while processing the route');
            }
        }

        if (!isset($alias)) {
            $arguments = gplcart_parse_path($path, $pattern);
            if (is_array($arguments)) {
                $entity_id = reset($arguments);
                $entity = strtok($pattern, '/') . '_id';
                $alias_path = $this->db->fetchColumn('SELECT alias FROM alias WHERE id_key=? AND id_value=?', array($entity, $entity_id));
            }

            if (!empty($alias_path)) {
                $this->url->redirect($alias_path);
            }
        }
    }

    /**
     * Call a route controller
     * @param string $pattern
     * @param array $arguments
     * @param string $method
     * @return mixed
     */
    public function call($pattern, $arguments = array(), $method = 'controller')
    {
        $list = $this->getList();
        $route = $list[$pattern];

        $route += array('arguments' => array(), 'pattern' => $pattern);
        $route['simple_pattern'] = preg_replace('@\(.*?\)@', '*', $pattern);
        $route['arguments'] = array_merge($arguments, $route['arguments']);

        $this->route = $route;
        $handler = $this->getHandler($route, $method);
        return call_user_func_array($handler, $this->route['arguments']);
    }

    /**
     * Returns route handler
     * @param array $route
     * @param string $method
     * @return \gplcart\core\Controller
     * @throws RouteException
     */
    public function getHandler(array $route, $method = 'controller')
    {
        try {
            $handler = Handler::get($route, null, $method);
        } catch (Exception $ex) {
            throw new RouteException($ex->getMessage());
        }

        return $handler;
    }

    /**
     * Find an appropriate controller for the current URL
     * @return bool
     */
    public function outputRoute()
    {
        foreach ($this->getList() as $pattern => $route) {

            if (isset($route['status']) && empty($route['status'])) {
                continue;
            }

            $path = empty($this->path) ? '/' : $this->path;
            $arguments = gplcart_parse_path($path, $pattern);

            if (is_array($arguments)) {
                $this->call($pattern, $arguments);
                throw new RouteException('An error occurred while processing the route');
            }
        }
    }

    /**
     * Displays 404 Not Found Page
     */
    public function output404()
    {
        $pattern = $this->url->isBackend() ? 'status-backend' : 'status-frontend';
        $this->call($pattern, array(404));
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
