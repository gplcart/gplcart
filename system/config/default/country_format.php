<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'country' => array(
        'name' => 'Country', // @text
        'required' => 0,
        'weight' => 0,
        'status' => 1
    ),
    'state_id' => array(
        'name' => 'Country state', // @text
        'required' => 0,
        'weight' => 1,
        'status' => 1
    ),
    'city_id' => array(
        'name' => 'City', // @text
        'required' => 1,
        'weight' => 2,
        'status' => 1
    ),
    'address_1' => array(
        'name' => 'Address', // @text
        'required' => 1,
        'weight' => 3,
        'status' => 1
    ),
    'address_2' => array(
        'name' => 'Additional address', // @text
        'required' => 0,
        'weight' => 4,
        'status' => 0
    ),
    'phone' => array(
        'name' => 'Phone', // @text
        'required' => 1,
        'weight' => 5,
        'status' => 1
    ),
    'postcode' => array(
        'name' => 'Post code/ZIP', // @text
        'required' => 1,
        'weight' => 6,
        'status' => 1
    ),
    'first_name' => array(
        'name' => 'First name', // @text
        'required' => 1,
        'weight' => 7,
        'status' => 1
    ),
    'middle_name' => array(
        'name' => 'Middle name', // @text
        'required' => 0,
        'weight' => 8,
        'status' => 0
    ),
    'last_name' => array(
        'name' => 'Last name', // @text
        'required' => 1,
        'weight' => 9,
        'status' => 1
    ),
    'company' => array(
        'name' => 'Company', // @text
        'required' => 0,
        'weight' => 10,
        'status' => 0
    ),
    'fax' => array(
        'name' => 'Fax', // @text
        'required' => 0,
        'weight' => 11,
        'status' => 0
    )
);
