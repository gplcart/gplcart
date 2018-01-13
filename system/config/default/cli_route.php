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
            'php gplcart help -h',
            'php gplcart (help | h)',
            'php gplcart (help | h) <command>'
        ),
        'options' => array(
            '-h' => /* @text */'Show command help'
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\cli\\Help', 'help')
        )
    ),
    'lang' => array(
        'alias' => 'l',
        'description' => /* @text */'Set UI language',
        'usage' => array(
            'php gplcart lang -h',
            'php gplcart (lang | l)',
            'php gplcart (lang | l) <code>'
        ),
        'options' => array(
            '-h' => /* @text */'Show command help'
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\cli\\Language', 'language')
        )
    ),
    'install' => array(
        'alias' => 'i',
        'description' => /* @text */'Performs full system installation',
        'usage' => array(
            'php gplcart -h',
            'php gplcart (install | i)',
            'php gplcart (install | i) (--email=<e-mail> --pass=<password> --db-name=<name>) [options]'
        ),
        'options' => array(
            // Required
            '--email' => /* @text */'Admin e-mail',
            '--pass' => /* @text */'Admin password',
            '--db-name' => /* @text */'Database name',
            // Optional
            '--db-pass' => /* @text */'Database password',
            '--db-user' => /* @text */'Database user [default: root]',
            '--db-host' => /* @text */'Database host [default: localhost]',
            '--db-type' => /* @text */'Database type [default: mysql]',
            '--db-port' => /* @text */'Database port [default: 3306]',
            '--title' => /* @text */'Store name [default: GPL Cart]',
            '--basepath' => /* @text */'Installation subfolder name',
            '--timezone' => /* @text */'Store timezone',
            '--host' => /* @text */'Domain name',
            '--installer' => /* @text */'ID of installer module that manages the installation',
            '-h' => /* @text */'Show command help',
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\cli\\Install', 'install')
        )
    )
);
