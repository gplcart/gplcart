<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
error_reporting(E_ALL);

require 'constants.php';
require 'core/functions/common.php';
require 'core/functions/setup.php';
require 'core/functions/string.php';
require 'core/functions/array.php';
require 'core/functions/file.php';

gplcart_setup_requirements();

mb_language('uni');
mb_internal_encoding('UTF-8');

gplcart_setup_php();
gplcart_setup_server();
gplcart_setup_autoload();
