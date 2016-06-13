<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
spl_autoload_register(function ($namespace) {
    $path = str_replace('\\', '/', $namespace);
    $file = dirname(__FILE__) . "/../../system/$path.php";

    if (file_exists($file)) {
        require $file;
    }
});
