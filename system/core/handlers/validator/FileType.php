<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

/**
 * Provides methods to validate different types of files
 */
class FileType
{

    /**
     * Whether the file is an image
     * @param string $file
     * @return boolean
     */
    public function image($file)
    {
        return is_array(getimagesize($file));
    }

    /**
     * Whether the file is a CSV file
     * @param string $file
     * @return boolean
     */
    public function csv($file)
    {
        $mimetype = gplcart_file_mime($file);
        return strtok($mimetype, '/') === 'text';
    }

    /**
     * Whether the file is a ZIP file
     * @param string $file
     * @return boolean
     */
    public function zip($file)
    {
        $zip = zip_open($file);
        return is_resource($zip);
    }

    /**
     * Whether the file is a .json file
     * @param string $file
     * @return boolean
     */
    public function json($file)
    {
        $contents = file_get_contents($file);
        return json_decode($contents) !== null;
    }

}
