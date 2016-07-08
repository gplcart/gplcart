<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\file;

use core\models\File as ModelsFile;

/**
 * Provides methods to validate different types of files
 */
class Validator
{

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Constructor
     * @param ModelsFile $file
     */
    public function __construct(ModelsFile $file)
    {
        $this->file = $file;
    }

    /**
     * Whether the file is an image
     * @param string $file
     * @param array $validator
     * @return boolean
     */
    public function image($file, array $validator)
    {
        $allowed = array('image/jpeg', 'image/gif', 'image/png');

        if (!empty($validator['mime_types'])) {
            $allowed = $validator['mime_types'];
        }

        if (!in_array($this->file->getMimetype($file), $allowed)) {
            return false;
        }

        return (bool) getimagesize($file);
    }

    /**
     * Whether the file is a sertificate
     * @param string $file
     * @param array $validator
     * @return boolean
     */
    public function p12($file, array $validator)
    {
        // TODO: find a way to validate .p12 file.
        return true;
    }

    /**
     * Whether the file is a CSV file
     * @param string $file
     * @param array $validator
     * @return boolean
     */
    public function csv($file, array $validator)
    {
        $allowed = array('text/plain', 'text/csv', 'text/tsv');
        return in_array($this->file->getMimetype($file), $allowed);
    }

}
