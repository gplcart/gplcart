<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
/**
 * Core version
 * @var string
 */
define('GC_VERSION', '1.0.0');

/**
 * Script start time
 * @var float
 */
define('GC_START', microtime(true));

/**
 * Whether CLI mode is on
 * @var bool
 */
define('GC_CLI', PHP_SAPI === 'cli');

/**
 * Whether the current OS is Windows
 * @var bool
 */
define('GC_WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

/**
 * Server's request time
 * @var int
 */
define('GC_TIME', (int) $_SERVER['REQUEST_TIME']);

/**
 * URL base path
 * @var string
 */
define('GC_BASE', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])));

/**
 * Path to root directory
 * @var string
 */
define('GC_DIR', str_replace('\\', '/', realpath(__DIR__ . '/../')));

/**
 * Path to directory containing system files
 * @var string
 */
define('GC_DIR_SYSTEM', GC_DIR . '/system');

/**
 * Path to directory containing core files
 * @var string
 */
define('GC_DIR_CORE', GC_DIR_SYSTEM . '/core');

/**
 * Path to directory containing modules
 * @var string
 */
define('GC_DIR_MODULE', GC_DIR_SYSTEM . '/modules');

/**
 * Path to directory containing cache files
 * @var string
 */
define('GC_DIR_CACHE', GC_DIR . '/cache');

/**
 * Path to directory containing public and private files
 * @var string
 */
define('GC_DIR_FILE', GC_DIR . '/files');

/**
 * Path to directory containing CSS and JS files
 * @var string
 */
define('GC_DIR_ASSET', GC_DIR_FILE . '/assets');

/**
 * Path to directory containing 3-d party asset libraries
 * @var string
 */
define('GC_DIR_ASSET_VENDOR', GC_DIR_ASSET . '/vendor');

/**
 * Path to directory containing compiled JS and CSS files
 * @var string
 */
define('GC_DIR_ASSET_COMPILED', GC_DIR_ASSET . '/compiled');

/**
 * Path to directory containing default configuration files
 * @var string
 */
define('GC_DIR_CONFIG', GC_DIR_SYSTEM . '/config/default');

/**
 * Path to directory containing compiled configuration files
 * @var string
 */
define('GC_DIR_CONFIG_COMPILED', GC_DIR_SYSTEM . '/config/compiled');

/**
 * Path to directory containing uploaded public files
 * @var string
 */
define('GC_DIR_UPLOAD', GC_DIR_FILE . '/upload');

/**
 * Path to directory containing public files to download
 * @var string
 */
define('GC_DIR_DOWNLOAD', GC_DIR_FILE . '/download');

/**
 * Path to directory containing all private files
 * @var string
 */
define('GC_DIR_PRIVATE', GC_DIR_FILE . '/private');

/**
 * Path to directory containing temporary private files
 * @var string
 */
define('GC_DIR_PRIVATE_TEMP', GC_DIR_PRIVATE . '/temp');

/**
 * Path to directory containing private files provided by third-party module
 * @var string
 */
define('GC_DIR_PRIVATE_MODULE', GC_DIR_PRIVATE . '/modules');

/**
 * Path to directory containing uploaded images
 * @var string
 */
define('GC_DIR_IMAGE', GC_DIR_FILE . '/image/upload');

/**
 * Path to a directory containing images created by image styles
 * @var string
 */
define('GC_DIR_IMAGE_CACHE', GC_DIR_FILE . '/image/cache');

/**
 * Path to a directory containing core translation files
 * @var string
 */
define('GC_DIR_TRANSLATION', GC_DIR_SYSTEM . '/translations');

/**
 * Path to a directory containing help files
 * @var string
 */
define('GC_DIR_HELP', GC_DIR_SYSTEM . '/help');

/**
 * Common configuration file
 * @var string
 */
define('GC_FILE_CONFIG', GC_DIR_CONFIG . '/common.php');

/**
 * Configuration file containing default database scheme
 * @var string
 */
define('GC_FILE_CONFIG_DATABASE', GC_DIR_CONFIG . '/database.php');

/**
 * Configuration file containing language ISO data
 * @var string
 */
define('GC_FILE_CONFIG_LANGUAGE', GC_DIR_CONFIG . '/language.php');

/**
 * Configuration file containing country ISO data
 * @var string
 */
define('GC_FILE_CONFIG_COUNTRY', GC_DIR_CONFIG . '/country.php');

/**
 * Configuration file containing data for converting measurement units
 * @var string
 */
define('GC_FILE_CONFIG_UNIT', GC_DIR_CONFIG . '/unit.php');

/**
 * Configuration file containing URL alias handlers
 * @var string
 */
define('GC_FILE_CONFIG_ALIAS', GC_DIR_CONFIG . '/alias.php');

/**
 * Configuration file containing currency ISO data
 * @var string
 */
define('GC_FILE_CONFIG_CURRENCY', GC_DIR_CONFIG . '/currency.php');

/**
 * Configuration file containing URL routes
 * @var string
 */
define('GC_FILE_CONFIG_ROUTE', GC_DIR_CONFIG . '/route.php');

/**
 * Configuration file containing CLI command routes
 * @var string
 */
define('GC_FILE_CONFIG_ROUTE_CLI', GC_DIR_CONFIG . '/cli_route.php');

/**
 * Configuration file containing system permissions
 * @var string
 */
define('GC_FILE_CONFIG_PERMISSION', GC_DIR_CONFIG . '/permission.php');

/**
 * Configuration file containing price rule type handlers
 * @var string
 */
define('GC_FILE_CONFIG_PRICE_RULE_TYPE', GC_DIR_CONFIG . '/price_rule_type.php');

/**
 * Configuration file containing validators
 * @var string
 */
define('GC_FILE_CONFIG_VALIDATOR', GC_DIR_CONFIG . '/validator.php');

/**
 * Configuration file containing image styles
 * @var string
 */
define('GC_FILE_CONFIG_IMAGE_STYLE', GC_DIR_CONFIG . '/image_style.php');

/**
 * Configuration file containing actions for image styles
 * @var string
 */
define('GC_FILE_CONFIG_IMAGE_ACTION', GC_DIR_CONFIG . '/image_action.php');

/**
 * Configuration file containing core libraries
 * @var string
 */
define('GC_FILE_CONFIG_LIBRARY', GC_DIR_CONFIG . '/library.php');

/**
 * Configuration file containing mail handlers
 * @var string
 */
define('GC_FILE_CONFIG_MAIL', GC_DIR_CONFIG . '/mail.php');

/**
 * Configuration file containing collection handlers
 * @var string
 */
define('GC_FILE_CONFIG_COLLECTION', GC_DIR_CONFIG . '/collection.php');

/**
 * Configuration file containing trigger conditions
 * @var string
 */
define('GC_FILE_CONFIG_CONDITION', GC_DIR_CONFIG . '/condition.php');

/**
 * Configuration file containing default country format
 * @var string
 */
define('GC_FILE_CONFIG_COUNTRY_FORMAT', GC_DIR_CONFIG . '/country_format.php');

/**
 * Configuration file containing dashboard handlers
 * @var string
 */
define('GC_FILE_CONFIG_DASHBOARD', GC_DIR_CONFIG . '/dashboard.php');

/**
 * Configuration file containing system requirements
 * @var string
 */
define('GC_FILE_CONFIG_REQUIREMENT', GC_DIR_CONFIG . '/requirement.php');

/**
 * Configuration file containing file handlers
 * @var string
 */
define('GC_FILE_CONFIG_FILE', GC_DIR_CONFIG . '/file.php');

/**
 * File containing compiled common configuration
 * @var string
 */
define('GC_FILE_CONFIG_COMPILED', GC_DIR_CONFIG_COMPILED . '/common.php');

/**
 * File containing compiled library configuration
 * @var string
 */
define('GC_FILE_CONFIG_COMPILED_LIBRARY', GC_DIR_CONFIG_COMPILED . '/library.php');

/**
 * File containing compiled modules configuration
 * @var string
 */
define('GC_FILE_CONFIG_COMPILED_MODULE', GC_DIR_CONFIG_COMPILED . '/module.php');

/**
 * File containing compiled class override configuration
 * @var string
 */
define('GC_FILE_CONFIG_COMPILED_OVERRIDE', GC_DIR_CONFIG_COMPILED . '/override.php');

/**
 * Permission ID for superadmin (user #1) access
 */
define('GC_PERM_SUPERADMIN', '_superadmin');

/**
 * Permission ID for public access
 */
define('GC_PERM_PUBLIC', '_public');
