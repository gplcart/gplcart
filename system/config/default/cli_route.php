<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @link http://docopt.org
 */
return array(
    'help' => array(
        'alias' => 'h',
        'description' => 'Displays all available commands', // @text
        'usage' => array(
            'gplcart (help | h) -h',
            'gplcart (help | h)',
            'gplcart (help | h) <command>'
        ),
        'options' => array(
            '-h' => 'Show command help' // @text
        ),
        'handlers' => array(
            /** @uses \gplcart\core\controllers\cli\Help::help() */
            'controller' => array('gplcart\\core\\controllers\\cli\\Help', 'help')
        )
    ),
    'install' => array(
        'alias' => 'i',
        'description' => 'Performs full system installation', // @text
        'usage' => array(
            'gplcart (install | i) -h',
            'gplcart (install | i)',
            'gplcart (install | i) (--email=<e-mail> --pass=<password> --db-name=<name>) [options]'
        ),
        'options' => array(
            // Required
            '--email' => 'Admin e-mail', // @text
            '--pass' => 'Admin password', // @text
            '--db-name' => 'Database name', // @text
            // Optional
            '--db-pass' => 'Database password', // @text
            '--db-user' => 'Database user [default: root]' . ' ', // @text
            '--db-host' => 'Database host [default: localhost]', // @text
            '--db-type' => 'Database type [default: mysql]', // @text
            '--db-port' => 'Database port [default: 3306]', // @text
            '--title' => 'Store name [default: GPL Cart]', // @text
            '--basepath' => 'Installation subfolder name', // @text
            '--timezone' => 'Store timezone', // @text
            '--host' => 'Domain name', // @text
            '--installer' => 'ID of the module that manages the installation process', // @text
            '-h' => 'Show command help', // @text
        ),
        'handlers' => array(
            /** @uses \gplcart\core\controllers\cli\Install::install() */
            'controller' => array('gplcart\\core\\controllers\\cli\\Install', 'install')
        )
    )
);
