<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
if (version_compare(PHP_VERSION, '5.6.0') < 0) {
    throw new \Exception('PHPUnit 5.7 requires at least PHP 5.6.0');
}

require_once __DIR__ . '/../../../system/bootstrap.php';

if (!class_exists('PHPUnit_Extensions_Database_TestCase')) {
    throw new \Exception('Looks like DBUnit extension not installed. See https://github.com/sebastianbergmann/dbunit');
}

ini_set('memory_limit', '-1');
ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
