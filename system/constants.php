<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
define('GC_VERSION', '1.0.0');
define('GC_START', microtime(true));
define('GC_CLI', PHP_SAPI === 'cli');
define('GC_WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
define('GC_TIME', (int) $_SERVER['REQUEST_TIME']);
define('GC_BASE', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])));
// File paths
define('GC_ROOT_DIR', realpath(__DIR__ . '/../'));
define('GC_SYSTEM_DIR', GC_ROOT_DIR . '/system');
define('GC_CORE_DIR', GC_SYSTEM_DIR . '/core');
define('GC_MODULE_DIR', GC_SYSTEM_DIR . '/modules');
define('GC_CACHE_DIR', GC_ROOT_DIR . '/cache');
define('GC_FILE_DIR', GC_ROOT_DIR . '/files');
define('GC_ASSET_DIR', GC_FILE_DIR . '/assets');
define('GC_ASSET_LIBRARY_DIR', GC_ASSET_DIR . '/libraries');
define('GC_COMPRESSED_ASSET_DIR', GC_ASSET_DIR . '/compressed');
define('GC_CONFIG_DIR', GC_SYSTEM_DIR . '/config');
define('GC_CONFIG_DEFAULT_DIR', GC_CONFIG_DIR . '/default');
define('GC_CONFIG_RUNTIME_DIR', GC_CONFIG_DIR . '/runtime');
define('GC_CONFIG_COMPILED_LIBRARY', GC_CONFIG_RUNTIME_DIR . '/library.php');
define('GC_CONFIG_MODULE', GC_CONFIG_RUNTIME_DIR . '/module.php');
define('GC_CONFIG_OVERRIDE', GC_CONFIG_RUNTIME_DIR . '/override.php');
define('GC_CONFIG_COMMON', GC_CONFIG_RUNTIME_DIR . '/common.php');
define('GC_CONFIG_DATABASE', GC_CONFIG_DEFAULT_DIR . '/database.php');
define('GC_CONFIG_LANGUAGE', GC_CONFIG_DEFAULT_DIR . '/language.php');
define('GC_CONFIG_COUNTRY', GC_CONFIG_DEFAULT_DIR . '/country.php');
define('GC_CONFIG_CURRENCY', GC_CONFIG_DEFAULT_DIR . '/currency.php');
define('GC_CONFIG_ROUTE', GC_CONFIG_DEFAULT_DIR . '/route.php');
define('GC_CONFIG_CLI_ROUTE', GC_CONFIG_DEFAULT_DIR . '/cli_route.php');
define('GC_CONFIG_PERMISSION', GC_CONFIG_DEFAULT_DIR . '/permission.php');
define('GC_CONFIG_COMMON_DEFAULT', GC_CONFIG_DEFAULT_DIR . '/common.php');
define('GC_CONFIG_VALIDATOR', GC_CONFIG_DEFAULT_DIR . '/validator.php');
define('GC_CONFIG_IMAGE_STYLE', GC_CONFIG_DEFAULT_DIR . '/image_style.php');
define('GC_CONFIG_IMAGE_ACTION', GC_CONFIG_DEFAULT_DIR . '/image_action.php');
define('GC_CONFIG_LIBRARY', GC_CONFIG_DEFAULT_DIR . '/library.php');
define('GC_CONFIG_MAIL', GC_CONFIG_DEFAULT_DIR . '/mail.php');
define('GC_CONFIG_COLLECTION', GC_CONFIG_DEFAULT_DIR . '/collection.php');
define('GC_CONFIG_CONDITION', GC_CONFIG_DEFAULT_DIR . '/condition.php');
define('GC_CONFIG_COUNTRY_FORMAT', GC_CONFIG_DEFAULT_DIR . '/country_format.php');
define('GC_CONFIG_DASHBOARD', GC_CONFIG_DEFAULT_DIR . '/dashboard.php');
define('GC_CONFIG_REQUIREMENT', GC_CONFIG_DEFAULT_DIR . '/requirement.php');
define('GC_CONFIG_FILE', GC_CONFIG_DEFAULT_DIR . '/file.php');
define('GC_UPLOAD_DIR', GC_FILE_DIR . '/upload');
define('GC_DOWNLOAD_DIR', GC_FILE_DIR . '/download');
define('GC_PRIVATE_DIR', GC_FILE_DIR . '/private');
define('GC_PRIVATE_DOWNLOAD_DIR', GC_PRIVATE_DIR . '/download');
define('GC_PRIVATE_TEMP_DIR', GC_PRIVATE_DIR . '/temp');
define('GC_PRIVATE_MODULE_DIR', GC_PRIVATE_DIR . '/modules');
define('GC_IMAGE_DIR', GC_FILE_DIR . '/image/upload');
define('GC_IMAGE_CACHE_DIR', GC_FILE_DIR . '/image/cache');
define('GC_TRANSLATION_DIR', GC_SYSTEM_DIR . '/translations');



