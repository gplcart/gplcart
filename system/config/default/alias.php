<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'page' => array(
        'pattern' => '%t.html',
        'mapping' => array('%t' => 'title'),
        'handlers' => array(
            'data' => array('gplcart\\core\\models\\Page', 'get')
        )
    ),
    'product' => array(
        'pattern' => '%t.html',
        'mapping' => array('%t' => 'title'),
        'handlers' => array(
            'data' => array('gplcart\\core\\models\\Product', 'get')
        )
    ),
    'category' => array(
        'pattern' => '%t.html',
        'mapping' => array('%t' => 'title'),
        'handlers' => array(
            'data' => array('gplcart\\core\\models\\Category', 'get')
        )
    )
);
