<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'order_created_admin' => array(
        'name' => /* @text */'To admin: new order',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\mail\\Order', 'createdToAdmin')
        ),
    ),
    'order_created_customer' => array(
        'name' => /* @text */'To customer: new order',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\mail\\Order', 'createdToCustomer'),
        ),
    ),
    'order_updated_customer' => array(
        'name' => /* @text */'To customer: order has been updated',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\mail\\Order', 'updatedToCustomer'),
        ),
    ),
    'user_registered_admin' => array(
        'name' => /* @text */'To admin: new user',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\mail\\Account', 'registeredToAdmin'),
        ),
    ),
    'user_registered_customer' => array(
        'name' => /* @text */'To user: account has been created',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\mail\\Account', 'registeredToCustomer'),
        ),
    ),
    'user_reset_password' => array(
        'name' => /* @text */'To user: reset password',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\mail\\Account', 'resetPassword'),
        ),
    ),
    'user_changed_password' => array(
        'name' => /* @text */'To user: password has been changed',
        'handlers' => array(
            'data' => array('gplcart\\core\\handlers\\mail\\Account', 'changedPassword'),
        ),
    )
);
