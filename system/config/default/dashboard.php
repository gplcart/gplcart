<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'summary' => array(
        'title' => 'Summary',
        'status' => true,
        'weight' => 0,
        'template' => 'dashboard/panels/summary',
        'handlers' => array(
            'data' => array('gplcart\core\handlers\dashboard\Dashboard', 'summary'),
        )
    ),
    'order' => array(
        'title' => 'Recent orders',
        'status' => true,
        'weight' => 0,
        'template' => 'dashboard/panels/orders',
        'handlers' => array(
            'data' => array('gplcart\core\handlers\dashboard\Dashboard', 'order'),
        )
    ),
    'transaction' => array(
        'title' => 'Recent transactions',
        'status' => true,
        'weight' => 0,
        'template' => 'dashboard/panels/transactions',
        'handlers' => array(
            'data' => array('gplcart\core\handlers\dashboard\Dashboard', 'transaction'),
        )
    ),
    'pricerule' => array(
        'title' => 'Active price rules',
        'status' => true,
        'weight' => 0,
        'template' => 'dashboard/panels/pricerules',
        'handlers' => array(
            'data' => array('gplcart\core\handlers\dashboard\Dashboard', 'pricerule'),
        )
    ),
    'cart' => array(
        'title' => 'Recent cart items',
        'status' => true,
        'weight' => 0,
        'template' => 'dashboard/panels/cart',
        'handlers' => array(
            'data' => array('gplcart\core\handlers\dashboard\Dashboard', 'cart'),
        )
    ),
    'review' => array(
        'title' => 'Recent reviews',
        'status' => true,
        'weight' => 0,
        'template' => 'dashboard/panels/reviews',
        'handlers' => array(
            'data' => array('gplcart\core\handlers\dashboard\Dashboard', 'review'),
        )
    ),
    'event' => array(
        'title' => 'Recent events',
        'status' => true,
        'weight' => 0,
        'template' => 'dashboard/panels/events',
        'handlers' => array(
            'data' => array('gplcart\core\handlers\dashboard\Dashboard', 'event'),
        )
    ),
    'user' => array(
        'title' => 'Recent users',
        'status' => true,
        'weight' => 0,
        'template' => 'dashboard/panels/users',
        'handlers' => array(
            'data' => array('gplcart\core\handlers\dashboard\Dashboard', 'user'),
        )
    )
);
