<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'order_created_admin' => array(
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\mail\\Order', 'createdToAdmin')
        ),
    ),
    'order_created_customer' => array(
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\mail\\Order', 'createdToCustomer'),
        ),
    ),
    'order_updated_customer' => array(
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\mail\\Order', 'updatedToCustomer'),
        ),
    ),
    'user_registered_admin' => array(
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\mail\\Account', 'registeredToAdmin'),
        ),
    ),
    'user_registered_customer' => array(
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\mail\\Account', 'registeredToCustomer'),
        ),
    ),
    'user_reset_password' => array(
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\mail\\Account', 'resetPassword'),
        ),
    ),
    'user_changed_password' => array(
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\mail\\Account', 'changedPassword'),
        ),
    )
);
