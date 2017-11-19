<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'log_id' => 'log1',
        'created' => 1234567890,
        'text' => 'Text 1',
        'type' => 'php',
        'severity' => 'warning',
        'translatable' => 1,
        'data' => serialize(array('test' => true))
    ),
    array(
        'log_id' => 'log2',
        'created' => 1234567890,
        'text' => 'Text 2',
        'type' => 'php',
        'severity' => 'danger',
        'translatable' => 1,
        'data' => serialize(array('test' => true))
    )
);
