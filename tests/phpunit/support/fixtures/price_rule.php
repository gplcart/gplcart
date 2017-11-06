<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'price_rule_id' => 1,
        'value' => 9999,
        'status' => 1,
        'weight' => -9,
        'used' => 1,
        'name' => 'Test price rule',
        'trigger_id' => 1,
        'code' => 'CODE',
        'value_type' => 'percent',
        'currency' => 'USD',
        'created' => 1234567890,
        'modified' => 1234567891
    ),
    array(
        'price_rule_id' => 2,
        'value' => 1111,
        'status' => 0,
        'weight' => 9,
        'used' => 10,
        'name' => 'Test price rule 2',
        'trigger_id' => 2,
        'code' => 'CODE2',
        'value_type' => 'fixed',
        'currency' => 'USD',
        'created' => 1234567890,
        'modified' => 1234567891
    )
);
