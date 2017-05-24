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
     * PDO instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Constructor
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
        $this->seekControllerAlias();
        $this->seekControllerRoute();
        $this->callControllerNotFound();
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
     * @return null
     * @todo Do refactoring
     */
    protected function seekControllerAlias()
    {
        if (empty($this->db)) {
            return null;
        }

        $info = $this->getAliasInfo($this->path);

        if (isset($info['id_key'])) {

            // Entity name: product, page, category etc...
            $entityname = str_replace('_id', '', $info['id_key']);

            foreach ($this->getList() as $pattern => $route) {

                if (empty($route['arguments'])) {
                    $route['arguments'] = array();
                }

                if (!isset($route['alias'][0])) {
                    continue; // This route doesn't support aliases
                }

                $pattern_segments = explode('/', $pattern);

                if ($pattern_segments[$route['alias'][0]] !== $entityname) {
                    continue; // Entity name not matching, try the next route
                }

                $route['arguments'] += array($info['id_value']);
                $this->route = $route + array('pattern' => $pattern);

                Handler::call($route, null, 'controller', $route['arguments']);
                throw new RouteException('An error occurred while processing the route');
            }
        }

        // Failed to found the matching controller above
        // The current path can be a system path like product/1
        // so now we'll try to find an appropriate alias in the database and redirect to it
        $this->redirectToAlias();
    }

    /**
     * Finds an alias by the route pattern
     * and redirects to it
     */
    protected function redirectToAlias()
    {
        $path_segments = $this->url->segments();

        foreach ($this->getList() as $pattern => $route) {

            if (empty($route['alias'])) {
                continue;
            }

            if (!isset($path_segments[$route['alias'][0]])) {
                continue;
            }

            if (!isset($path_segments[$route['alias'][1]])) {
                continue;
            }

            $pattern_segments = explode('/', $pattern);

            if (!isset($pattern_segments[$route['alias'][0]])) {
                continue;
            }

            if ($pattern_segments[$route['alias'][0]] !== $path_segments[$route['alias'][0]]) {
                continue;
            }

            $alias = $this->getAliasById($path_segments, $route);

            if (empty($alias)) {
                continue;
            }

            $this->url->redirect($alias);
        }
    }

    /**
     * Selects an alias using entity key ID and numeric value
     * @param array $segments
     * @param array $route
     * @return string
     */
    protected function getAliasById(array $segments, array $route)
    {
        $sql = 'SELECT alias'
                . ' FROM alias'
                . ' WHERE id_key=? AND id_value=?';

        $conditions = array(
            $segments[$route['alias'][0]] . '_id',
            $segments[$route['alias'][1]]
        );

        return (string) $this->db->fetchColumn($sql, $conditions);
    }

    /**
     * Returns alias info (keys) using the current URL path
     * @param string $alias
     * @return array|boolean
     */
    protected function getAliasInfo($alias)
    {
        $sql = 'SELECT id_key, id_value FROM alias WHERE alias=?';
        return $this->db->fetch($sql, array($alias));
    }

    /**
     * Find an appropriate controller for the current URL
     */
    protected function seekControllerRoute()
    {
        foreach ($this->getList() as $pattern => $route) {

            $pattern = trim($pattern, '/');
            $route += array('arguments' => array());

            $arguments = gplcart_parse_pattern($this->path, $pattern);
            if ($arguments !== false) {
                $route['arguments'] += $arguments;
                $this->callControllerRoute($pattern, $route);
                break; // Not really needed, but...
            }
        }
    }

    /**
     * Call a route controller
     * @param string $pattern
     * @param array $route
     * @throws RouteException
     */
    protected function callControllerRoute($pattern, $route)
    {
        $this->route = $route + array('pattern' => $pattern);
        $handler = Handler::get($route, null, 'controller');

        if (!$handler[0] instanceof \gplcart\core\Controller) {
            throw new RouteException('Controller must be instance of \gplcart\core\Controller');
        }

        call_user_func_array($handler, $route['arguments']); // We should stop here
        throw new RouteException('An error occurred while processing the route');
    }

    /**
     * Displays 404 Not Found Page
     */
    protected function callControllerNotFound()
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
