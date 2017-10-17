<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\Route;
use gplcart\core\models\Language as LanguageModel;

/**
 * Contains methods to validate URL trigger conditions
 */
class Url
{

    /**
     * Route class instance
     * @var \gplcart\core\Route $route
     */
    protected $route;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param Route $route
     * @param LanguageModel $language
     */
    public function __construct(Route $route, LanguageModel $language)
    {
        $this->route = $route;
        $this->language = $language;
    }

    /**
     * Validates the route pattern
     * @param array $values
     * @param string $operator
     * @return boolean|string
     */
    public function route(array $values, $operator)
    {
        if (!in_array($operator, array('=', '!='))) {
            return $this->language->text('Unsupported operator');
        }

        $routes = $this->route->getList();

        foreach ($values as $value) {
            if (empty($routes[$value])) {
                $vars = array('@name' => $this->language->text('Condition'));
                return $this->language->text('@name is unavailable', $vars);
            }
        }

        return true;
    }

    /**
     * Validates the path pattern
     * @param array $values
     * @param string $operator
     * @return boolean|string
     */
    public function path(array $values, $operator)
    {
        if (!in_array($operator, array('=', '!='))) {
            return $this->language->text('Unsupported operator');
        }

        foreach ($values as $value) {
            if (!gplcart_string_is_regexp($value)) {
                $vars = array('@field' => $this->language->text('Condition'));
                return $this->language->text('@field has invalid value', $vars);
            }
        }

        return true;
    }

}
