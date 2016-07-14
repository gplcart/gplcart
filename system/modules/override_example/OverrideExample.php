<?php

/**
 * @package Override module
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace modules\override_example;

class OverrideExample
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
            'name' => 'Override example',
            'description' => 'An example module which demonstrates how to override core methods from a module',
            'author' => 'Iurii Makukh',
            'image' => '',
            'core' => '1.0',
            'settings' => array(),
        );
    }
}
