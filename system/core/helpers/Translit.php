<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

/**
 * Wrapper methods for translit library
 */
class Translit
{

    /**
     * Library class instance
     * @var \Translit $lib
     */
    protected $lib;

    /**
     * Constructor
     */
    public function __construct()
    {
        gplcart_require_library('translit/Translit.php');
        $this->lib = new \Translit;
    }

    /**
     * Transliterates a string
     * @param string $string
     * @param string $unknown
     * @param null|string $source_langcode
     * @return string
     */
    public function get($string, $unknown = '?', $source_langcode = null)
    {
        return $this->lib->get($string, $unknown, $source_langcode);
    }

}
