<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\handlers\condition\Base as BaseHandler;
use gplcart\core\helpers\Url as UrlHelper;
use gplcart\core\Route;

/**
 * Provides methods to check URL conditions
 */
class Url extends BaseHandler
{

    /**
     * Route instance
     * @var \gplcart\core\Route $route
     */
    protected $route;

    /**
     * URL helper class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * @param Route $route
     * @param UrlHelper $url
     */
    public function __construct(Route $route, UrlHelper $url)
    {
        $this->url = $url;
        $this->route = $route;
    }

    /**
     * Returns true if route condition is met
     * @param array $condition
     * @return boolean
     */
    public function route(array $condition)
    {
        if (!in_array($condition['operator'], array('=', '!='))) {
            return false;
        }

        $route = $this->route->get();
        return $this->compare($route['pattern'], $condition['value'], $condition['operator']);
    }

    /**
     * Returns true if path condition is met
     * @param array $condition
     * @return boolean
     */
    public function path(array $condition)
    {
        if (!in_array($condition['operator'], array('=', '!='))) {
            return false;
        }

        $path = $this->url->path();

        $found = false;

        foreach ((array) $condition['value'] as $pattern) {
            if (gplcart_path_match($path, $pattern)) {
                $found = true;
            }
        }

        return $condition['operator'] === '=' ? $found : !$found;
    }

}
