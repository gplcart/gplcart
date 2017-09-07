<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'country' => array(
        'name' => /* @text */'Country',
        'required' => 0,
        'weight' => 0,
        'status' => 1
    ),
    'state_id' => array(
        'name' => /* @text */'State',
        'required' => 0,
        'weight' => 1,
        'status' => 1
    ),
    'city_id' => array(
        'name' => /* @text */'City',
        'required' => 1,
        'weight' => 2,
        'status' => 1
    ),
    'address_1' => array(
        'name' => /* @text */'Address',
        'required' => 1,
        'weight' => 3,
        'status' => 1
    ),
    'address_2' => array(
        'name' => /* @text */'Additional address',
        'required' => 0,
        'weight' => 4,
        'status' => 0
    ),
    'phone' => array(
        'name' => /* @text */'Phone',
        'required' => 1,
        'weight' => 5,
        'status' => 1
    ),
    'postcode' => array(
        'name' => /* @text */'Post code/ZIP',
        'required' => 1,
        'weight' => 6,
        'status' => 1
    ),
    'first_name' => array(
        'name' => /* @text */'First name',
        'required' => 1,
        'weight' => 7,
        'status' => 1
    ),
    'middle_name' => array(
        'name' => /* @text */'Middle name',
        'required' => 0,
        'weight' => 8,
        'status' => 0
    ),
    'last_name' => array(
        'name' => /* @text */'Last name',
        'required' => 1,
        'weight' => 9,
        'status' => 1
    ),
    'company' => array(
        'name' => /* @text */'Company',
        'required' => 0,
        'weight' => 10,
        'status' => 0
    ),
    'fax' => array(
        'name' => /* @text */'Fax',
        'required' => 0,
        'weight' => 11,
        'status' => 0
    )
);
