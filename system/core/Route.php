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
    protected $path;

    /**
     * The language code from the current URL
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
        $routes = &gplcart_static(__METHOD__);

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

        if (!empty($languages)) {

            $segments = $this->url->segments(true);
            $default = $this->config->get('language', '');

            $found = !empty($languages[$segments[0]]['status']);
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
     * Finds an alias by the path
     * @return bool
     */
    protected function seekAlias()
    {
        if (!$this->db instanceof Database) {
            return false;
        }

        $info = $this->getAliasByPath($this->path);

        if (!isset($info['id_key'])) {
            $this->seekEntityAlias();
            return false;
        }

        $entity = substr($info['id_key'], 0, -3);

        foreach ($this->getList() as $pattern => $route) {

            if (isset($route['status']) && empty($route['status'])) {
                continue;
            }

            if (!isset($route['alias'][0])) {
                continue;
            }

            $segments = explode('/', $pattern);
            if ($segments[$route['alias'][0]] === $entity) {
                $this->callController($pattern, $route, array($info['id_value']));
                return true;
            }
        }

        return false;
    }

    /**
     * Finds an alias by a route pattern and redirects to it
     */
    protected function seekEntityAlias()
    {
        $segments = $this->url->segments();

        foreach ($this->getList() as $pattern => $route) {

            if (isset($route['status']) && empty($route['status'])) {
                continue;
            }

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
        $route['simple_pattern'] = preg_replace('@\(.*?\)@', '*', $pattern);
        $route['arguments'] += $arguments;

        $this->route = $route;

        $handler = Handler::get($route, null, 'controller');

        if (empty($handler[0]) || !$handler[0] instanceof Controller) {
            throw new RouteException('Controller must be instance of \gplcart\core\Controller');
        }

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
     * @return bool
     */
    protected function seekRoute()
    {
        foreach ($this->getList() as $pattern => $route) {

            if (isset($route['status']) && empty($route['status'])) {
                continue;
            }

            $path = empty($this->path) ? '/' : $this->path;
            $arguments = gplcart_parse_path($path, $pattern);
            if (is_array($arguments)) {
                $this->callController($pattern, $route, $arguments);
                return true;
            }
        }

        return false;
    }

    /**
     * Displays 404 Not Found Page
     */
    protected function outputNotFound()
    {
        $routes = $this->getList();
        $section = $this->url->isBackend() ? 'backend' : 'frontend';
        $this->callController("status-$section", $routes["status-$section"], array(404));
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
