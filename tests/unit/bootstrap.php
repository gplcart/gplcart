<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
spl_autoload_register(function ($namespace) {
    $path = str_replace('\\', '/', $namespace);
    $file = dirname(__FILE__) . "/../../system/$path.php";

    if (file_exists($file)) {
        require $file;
    }
});
