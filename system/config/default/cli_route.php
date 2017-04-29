<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'help' => array(
        'handlers' => array(
            'process' => array('gplcart\core\CliController', 'help')
        ),
        'help' => array(
            'description' => 'Displays all available commands'
        )
    ),
    'install' => array(
        'handlers' => array(
            'process' => array('gplcart\core\controllers\cli\Install', 'storeInstall')
        ),
        'help' => array(
            'description' => 'Performs full system installation. Simple step-by-step wizard, has no options'
        )
    )
);
