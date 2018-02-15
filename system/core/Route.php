<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use Exception;
use gplcart\core\helpers\Request;
use gplcart\core\helpers\Response;
use gplcart\core\helpers\Url;
use LogicException;
use OutOfBoundsException;
use UnexpectedValueException;

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
     * Request class instance
     * @var \gplcart\core\helpers\Response $response
     */
    protected $response;

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
     * @param Url $url
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Config $config, Hook $hook, Url $url, Request $request, Response $response)
    {
        $this->url = $url;
        $this->hook = $hook;
        $this->config = $config;
        $this->request = $request;
        $this->response = $response;
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
        try {
            $this->processAlias();
            $this->processRoute();
            $this->output404();
        } catch (Exception $ex) {
            trigger_error($ex->getMessage());
            $this->response->outputError500();
        }
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
                $sql = 'SELECT alias FROM alias WHERE entity=? AND entity_id=?';
                $alias_path = $this->db->fetchColumn($sql, $conditions);
            }

            if (!empty($alias_path)) {
                $this->url->redirect($alias_path);
            }
        }
    }

    /**
     * Call a controller for the route
     * @param string|array $route
     * @param array $arguments
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    public function callController($route, array $arguments = array())
    {
        $route = $this->set($route, $arguments);
        $callback = Handler::get($route, null, 'controller');

        if (!$callback[0] instanceof Controller) {
            throw new UnexpectedValueException('Controller must be instance of \gplcart\core\Controller');
        }

        call_user_func_array($callback, $route['arguments']);

        // We should never get here as the page callback must abort the script execution
        throw new LogicException('An error occurred while processing the route');
    }

    /**
     * Calls an alias controller for the route
     * @param string $pattern
     * @param string $path
     * @param array|null $alias
     */
    public function callAliasController($pattern, $path, $alias)
    {
        $route = $this->set($pattern, array($pattern, $path, $alias));
        Handler::call($route, null, 'alias', $route['arguments']);
    }

    /**
     * Sets a route
     * @param array|string $route
     * @param array $arguments
     * @return array
     * @throws OutOfBoundsException
     */
    public function set($route, array $arguments = array())
    {
        if (!is_array($route)) {

            $pattern = $route;
            $list = $this->getList();

            if (empty($list[$pattern])) {
                throw new OutOfBoundsException("Unknown route pattern $pattern");
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
