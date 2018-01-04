<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'help' => array(
        'alias' => 'h',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\cli\\Help', 'help')
        ),
        'help' => array(
            'description' => /* @text */'Displays all available commands'
        )
    ),
    'install' => array(
        'alias' => 'i',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\cli\\Install', 'wizardInstall')
        ),
        'help' => array(
            'description' => /* @text */'Performs full system installation. Simple step-by-step wizard without options'
        )
    ),
    'install-fast' => array(
        'alias' => 'if',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\cli\\Install', 'fastInstall')
        ),
        'help' => array(
            'description' => /* @text */'Allows to perform full system installation at once',
            'options' => array(
                '--installer' => /* @text */'Optional. ID of module to be used for this installation process',
                '--db-name' => /* @text */'Required. Database name',
                '--db-user' => /* @text */'Optional. Database user. Defaults to "root"',
                '--db-host' => /* @text */'Optional. Database host. Defaults to "localhost"',
                '--db-password' => /* @text */'Optional. Database password. Defaults to empty string',
                '--db-type' => /* @text */'Optional. Database type, e.g "mysql" or "sqlite". Defaults to "mysql"',
                '--db-port' => /* @text */'Optional. Database port. Defaults to "3306"',
                '--user-email' => /* @text */'Required. Admin e-mail',
                '--user-password' => /* @text */'Required. Admin password',
                '--store-title' => /* @text */'Optional. Name of the store. Defaults to "GPL Cart"',
                '--store-basepath' => /* @text */'Optional. Subfolder name. Defaults to empty string, i.e domain root folder',
                '--store-timezone' => /* @text */'Optional. Timezone of the store. Defaults to the current timezone',
                '--store-host' => /* @text */'Optional. Domain name e.g "example.com" without scheme prefix and slashes. Defaults to the current hostname/IP'
            )
        )
    )
);
