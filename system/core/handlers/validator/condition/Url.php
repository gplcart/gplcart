<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\models\Translation as TranslationModel;
use gplcart\core\Route;

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
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * @param Route $route
     * @param TranslationModel $translation
     */
    public function __construct(Route $route, TranslationModel $translation)
    {
        $this->route = $route;
        $this->translation = $translation;
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
            return $this->translation->text('Unsupported operator');
        }

        $existing = $this->route->getList();

        foreach ($values as $pattern) {
            if (empty($existing[$pattern])) {
                return $this->translation->text('@name is unavailable', array(
                    '@name' => $this->translation->text('Condition')));
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
            return $this->translation->text('Unsupported operator');
        }

        foreach ($values as $pattern) {
            if (!gplcart_string_is_regexp($pattern)) {
                return $this->translation->text('@field has invalid value', array(
                    '@field' => $this->translation->text('Condition')));
            }
        }

        return true;
    }

}
