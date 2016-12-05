<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

/**
 * Collection of helper methods based on regular expressions
 */
class Regexp
{

    /**
     * Validates a domain name, e.g domain.com
     * @param string $domain
     * @return boolean
     */
    public static function matchDomain($domain)
    {
        $pattern = '/^(?!\-)'
                . '(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.)'
                . '{1,126}(?!\d+)[a-zA-Z\d]{1,63}$/';

        return (bool) preg_match($pattern, $domain);
    }

    /**
     * Parses and extracts arguments from a string
     * @param string $string
     * @param string $pattern
     * @return boolean|array
     */
    public static function matchPattern($string, $pattern)
    {
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';

        if (preg_match($pattern, $string, $params)) {
            array_shift($params);
            return array_values($params);
        }

        return false;
    }

}
