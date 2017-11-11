<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'order' => array(
        'title' => /* @text */'Recent orders',
        'status' => true,
        'weight' => 0,
        'template' => 'dashboard/panels/orders',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\dashboard\\Dashboard', 'order'),
        )
    ),
    'cart' => array(
        'title' => /* @text */'Recent cart items',
        'status' => true,
        'weight' => 1,
        'template' => 'dashboard/panels/cart',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\dashboard\\Dashboard', 'cart'),
        )
    ),
    'transaction' => array(
        'title' => /* @text */'Recent transactions',
        'status' => true,
        'weight' => 2,
        'template' => 'dashboard/panels/transactions',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\dashboard\\Dashboard', 'transaction'),
        )
    ),
    'pricerule' => array(
        'title' => /* @text */'Active price rules',
        'status' => true,
        'weight' => 3,
        'template' => 'dashboard/panels/pricerules',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\dashboard\\Dashboard', 'pricerule'),
        )
    ),
    'summary' => array(
        'title' => /* @text */'Summary',
        'status' => true,
        'weight' => 4,
        'template' => 'dashboard/panels/summary',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\dashboard\\Dashboard', 'summary'),
        )
    ),
    'review' => array(
        'title' => /* @text */'Recent reviews',
        'status' => true,
        'weight' => 5,
        'template' => 'dashboard/panels/reviews',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\dashboard\\Dashboard', 'review'),
        )
    ),
    'user' => array(
        'title' => /* @text */'Recent users',
        'status' => true,
        'weight' => 6,
        'template' => 'dashboard/panels/users',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\dashboard\\Dashboard', 'user'),
        )
    ),
    'event' => array(
        'title' => /* @text */'Recent events',
        'status' => true,
        'weight' => 7,
        'template' => 'dashboard/panels/events',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\dashboard\\Dashboard', 'event'),
        )
    )
);
