<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\classes\Tool;

/**
 * Provides methods to validate different types of files
 */
class File
{

    /**
     * Whether the file is an image
     * @param string $file
     * @param array $options
     * @return boolean
     */
    public function image($file, array $options)
    {
        $allowed = array('image/jpeg', 'image/gif', 'image/png');

        if (in_array(Tool::mimetype($file), $allowed)) {
            return (bool) getimagesize($file);
        }

        return false;
    }

    /**
     * Whether the file is a sertificate
     * @param string $file
     * @param array $options
     * @return boolean
     */
    public function p12($file, array $options)
    {
        $content = file_get_contents($file);
        $secret = isset($options['secret']) ? $options['secret'] : 'notasecret';

        if (empty($content)) {
            return false;
        }
        
        return openssl_pkcs12_read($content, $info, $secret);
    }

    /**
     * Whether the file is a CSV file
     * @param string $file
     * @param array $options
     * @return boolean
     */
    public function csv($file, array $options)
    {
        $allowed = array('text/plain', 'text/csv', 'text/tsv');
        return in_array(Tool::mimetype($file), $allowed);
    }

    /**
     * Whether the file is a ZIP file
     * @param string $file
     * @param array $options
     * @return type
     */
    public function zip($file, array $options)
    {
        $allowed = array('application/zip', 'multipart/x-zip');
        return in_array(Tool::mimetype($file), $allowed);
    }

}
