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
        'description' => /* @text */'Displays all available commands',
        'usage' => array(
            'php gplcart help [<command>] [options]'
        ),
        'options' => array(
            '-h, --help' => /* @text */'Show command help'
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\cli\\Help', 'help')
        )
    ),
    'install' => array(
        'alias' => 'i',
        'description' => /* @text */'Performs full system installation. If no options provided, then you will be guided step by step with the interactive wizard',
        'options' => array(
            // Required
            '--email=value' => /* @text */'Admin e-mail',
            '--pass=value' => /* @text */'Admin password',
            '--db-name=value' => /* @text */'Database name',
            // Optional
            '--db-pass=value' => /* @text */'Database password',
            '--db-user=value' => /* @text */'Database user [default: root]',
            '--db-host=value' => /* @text */'Database host [default: localhost]',
            '--db-type=value' => /* @text */'Database type [default: mysql]',
            '--db-port=value' => /* @text */'Database port [default: 3306]',
            '--title=value' => /* @text */'Store name [default: GPL Cart]',
            '--basepath=value' => /* @text */'Installation subfolder name',
            '--timezone=value' => /* @text */'Store timezone',
            '--host=value' => /* @text */'Domain name',
            '--installer=module' => /* @text */'ID of installer module that manages the installation',
            '-h, --help' => /* @text */'Show command help',
        ),
        'usage' => array(
            'php gplcart (install | i) [(--email=<e-mail> --pass=<password> --db-name=<name>)] [options]'
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\cli\\Install', 'install')
        )
    )
);
