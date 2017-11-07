<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'address_id' => 1,
        'state_id' => 1,
        'created' => 1234567890,
        'country' => 'US',
        'city_id' => 1,
        'address_1' => 'First address',
        'address_2' => 'Second address',
        'phone' => '1-213-621-0002',
        'type' => 'shipping',
        'user_id' => 1,
        'middle_name' => 'Middle name 1',
        'last_name' => 'Last name 1',
        'first_name' => 'First name 1',
        'postcode' => 12345,
        'company' => 'Company name 1',
        'fax' => '1-213-621-0002',
        'data' => serialize(array('test' => true)),
    ),
    array(
        'address_id' => 2,
        'state_id' => 2,
        'created' => 1234567810,
        'country' => 'UA',
        'city_id' => 'Kyiv',
        'address_1' => 'First address',
        'address_2' => 'Second address',
        'phone' => '+380961112234',
        'type' => 'payment',
        'user_id' => 2,
        'middle_name' => 'Middle name 2',
        'last_name' => 'Last name 2',
        'first_name' => 'First name 2',
        'postcode' => '11111',
        'company' => 'Company name 2',
        'fax' => '+380961112234',
        'data' => serialize(array('test' => true)),
    )
);
