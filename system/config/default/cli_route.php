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
            'process' => array('gplcart\\core\\CliController', 'help')
        ),
        'help' => array(
            'description' => 'Displays all available commands'
        )
    ),
    'install' => array(
        'handlers' => array(
            'process' => array('gplcart\\core\\controllers\\cli\\Install', 'wizardInstall')
        ),
        'help' => array(
            'description' => 'Performs full system installation. Simple step-by-step wizard, has no options'
        )
    ),
    'install-fast' => array(
        'handlers' => array(
            'process' => array('gplcart\\core\\controllers\\cli\\Install', 'fastInstall')
        ),
        'help' => array(
            'description' => 'Allows to perform full system installation at once',
            'options' => array(
                '--installer' => 'Optional. ID of module to be used for this installation process',
                '--db-name' => 'Required. Database name',
                '--db-user' => 'Optional. Database user. Defaults to "root"',
                '--db-host' => 'Optional. Database host. Defaults to "localhost"',
                '--db-password' => 'Optional. Database password. Defaults to empty string',
                '--db-type' => 'Optional. Database type, e.g "mysql" or "sqlite". Defaults to "mysql"',
                '--db-port' => 'Optional. Database port. Defaults to "3306"',
                '--user-email' => 'Required. Admin e-mail',
                '--user-password' => 'Required. Admin password',
                '--store-title' => 'Optional. Name of the store. Defaults to "GPL Cart"',
                '--store-basepath' => 'Optional. Subfolder name. Defaults to empty string, i.e domain root folder',
                '--store-timezone' => 'Optional. Timezone of the store. Defaults to the current timezone',
                '--store-host' => 'Optional. Domain name e.g "example.com" without scheme prefix and slashes. Defaults to the current hostname/IP'
            )
        )
    )
);
