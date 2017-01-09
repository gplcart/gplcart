<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
/**
 * Array of console routes
 */
$routes['help'] = array(
    'handlers' => array(
        'process' => array('gplcart\core\CliController', 'help')
    ),
    'help' => array(
        'description' => 'Displays all available commands'
    ),
);

$routes['report-event'] = array(
    'handlers' => array(
        'process' => array('gplcart\core\controllers\cli\Report', 'eventReport')
    ),
    'help' => array(
        'description' => 'Reports system events like PHP errors etc',
        'options' => array(
            '--clear' => 'Clear all recorded events',
            '--type' => 'Type of events to be reported, e.g "php". Defaults to all',
            '--severity' => 'Severity of events to be reported, e.g "info", "warning", "danger". Defaults to all',
            '--limit' => 'How many records to display. Defaults to last 20 records'
        )
    ),
);

$routes['report-status'] = array(
    'handlers' => array(
        'process' => array('gplcart\core\controllers\cli\Report', 'statusReport')
    ),
    'help' => array(
        'description' => 'Reports system status'
    ),
);

$routes['install'] = array(
    'handlers' => array(
        'process' => array('gplcart\core\controllers\cli\Install', 'storeInstall')
    ),
    'help' => array(
        'description' => 'Performs full system installation',
        'options' => array(
            '--db-name' => 'Required. Database name',
            '--user-email' => 'Required. Admin e-mail',
            '--store-host' => 'Optional. Domain name e.g "example.com". Do not use "http://" and slashes. Defaults to "localhost"',
            '--db-user' => 'Optional. Database user. Defaults to "root"',
            '--db-host' => 'Optional. Database host. Defaults to "localhost"',
            '--db-password' => 'Optional. Database password. Defaults to empty string',
            '--db-type' => 'Optional. Database type, e.g "mysql" or "sqlite". Defaults to "mysql"',
            '--db-port' => 'Optional. Database port. Defaults to 3306',
            '--user-password' => 'Optional. Admin password. Defaults to randomly generated password',
            '--store-title' => 'Optional. Name of the store. Defaults to "GPL Cart"',
            '--store-basepath' => 'Optional. Subfolder name. Defaults to empty string, i.e domain root folder',
            '--store-timezone' => 'Optional. Timezone of the store. Defaults to "Europe/London"',
            '--installer' => 'Optional. ID of module to be used for this installation process'
        ),
    ),
);

return $routes;
