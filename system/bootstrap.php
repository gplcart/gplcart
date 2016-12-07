<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
error_reporting(E_ALL);

require 'constants.php';
require 'functions/common.php';
require 'functions/setup.php';
require 'functions/string.php';
require 'functions/array.php';
require 'functions/regexp.php';
require 'functions/file.php';
require 'functions/date.php';

gplcart_setup_requirements();

mb_language('uni');
mb_internal_encoding('UTF-8');

gplcart_setup_ini();
gplcart_setup_server();
gplcart_setup_autoload();
