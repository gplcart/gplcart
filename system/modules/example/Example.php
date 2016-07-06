<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace modules\example;

class Example
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Code
    }

    /**
     * Module information
     * @return array
     */
    public function info()
    {
        return array(
            'name' => 'Example',
            'description' => 'Example module',
            'author' => 'Iurii Makukh',
            'image' => '',
            'core' => '1.0',
            'settings' => array(),
        );
    }
}
