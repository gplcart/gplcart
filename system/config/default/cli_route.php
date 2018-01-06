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
            'controller' => array('gplcart\\core\\controllers\\cli\\Install', 'install')
        ),
        'help' => array(
            'description' => /* @text */'Performs full system installation. If no options provided, then you will be guided step by step with the interactive wizard',
            'options' => array(
                '--installer' => /* @text */'Optional. ID of module to be used for this installation process',
                '--db-name' => /* @text */'Required. Database name',
                '--db-user' => /* @text */'Optional. Database user. Defaults to "root"',
                '--db-host' => /* @text */'Optional. Database host. Defaults to "localhost"',
                '--db-pass' => /* @text */'Optional. Database password. Defaults to empty string',
                '--db-type' => /* @text */'Optional. Database type, e.g "mysql" or "sqlite". Defaults to "mysql"',
                '--db-port' => /* @text */'Optional. Database port. Defaults to "3306"',
                '--email' => /* @text */'Required. Admin e-mail',
                '--pass' => /* @text */'Required. Admin password',
                '--title' => /* @text */'Optional. Name of the store. Defaults to "GPL Cart"',
                '--basepath' => /* @text */'Optional. Subfolder name. Defaults to empty string, i.e domain root folder',
                '--timezone' => /* @text */'Optional. Timezone of the store. Defaults to the current timezone',
                '--host' => /* @text */'Optional. Domain name e.g "example.com" without scheme prefix and slashes. Defaults to the current hostname/IP'
            )
        )
    )
);
