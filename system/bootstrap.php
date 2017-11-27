<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
require_once 'constants.php';
require_once 'core/functions/common.php';
require_once 'core/functions/setup.php';
require_once 'core/functions/string.php';
require_once 'core/functions/array.php';
require_once 'core/functions/file.php';

gplcart_setup_requirements();

mb_language('uni');
mb_internal_encoding('UTF-8');

gplcart_setup_php();
gplcart_setup_server();
gplcart_setup_vendor();
gplcart_setup_autoload();
