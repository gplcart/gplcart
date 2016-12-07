<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

use ZipArchive;
use InvalidArgumentException;

/**
 * Extends ZipArchive class
 */
class Zip extends ZipArchive
{

    /**
     * Sets a file path
     * @param string $path
     * @throws InvalidArgumentException
     */
    public function set($path, $create = true)
    {
        $flag = $create ? parent::CREATE : null;

        if ($this->open($path, $flag) !== true) {
            throw new InvalidArgumentException("Cannot open ZIP file $path");
        }

        return $this;
    }

    /**
     * Adds a file to a ZIP archive from the given path
     * @return boolean
     */
    public function add($file)
    {
        return $this->addFile($file);
    }

    /**
     * Zip a whole folder
     * @param string $source
     * @param string $destination
     * @param string $wrapper Wrapping local directory in the archive
     * @return boolean
     */
    public function folder($source, $destination, $wrapper = '')
    {
        $files = File::scanRecursive($source);

        if (empty($files)) {
            return false;
        }

        // Remove last slash and pattern characters after
        $source_patternless = substr($source, 0, strrpos($source, '/'));

        $this->set($destination);

        $added = 0;
        foreach ($files as $file) {

            if (is_dir($file)) {
                $added ++;
                continue;
            }

            $prefix = $wrapper === "" ? "" : "$wrapper/";
            $relative = $prefix . substr($file, strlen($source_patternless) + 1);
            $added += (int) $this->addFile($file, $relative);
        }

        if (count($files) == $added) {
            return $this->close();
        }

        return false;
    }

    /**
     * Removes a file from the archive
     * @param string $file
     * @return boolean
     */
    public function remove($file)
    {
        return $this->deleteName($file);
    }

    /**
     * Extract the complete archive or the given files to the specified destination
     * @param string $path
     * @param array $files
     * @return boolean
     */
    public function extract($path, $files = array())
    {
        if (empty($files)) {
            return $this->extractTo($path);
        }

        return $this->extractTo($path, $files);
    }

    /**
     * Returns an array of files in the archive
     * @return array
     */
    public function getList()
    {
        $files = array();
        for ($i = 0; $i < $this->numFiles; $i++) {
            $files[] = $this->getNameIndex($i);
        }
        return $files;
    }

}
