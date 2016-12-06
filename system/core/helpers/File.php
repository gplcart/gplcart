<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

/**
 * Collection of helper methods to work with files
 */
class File
{
    /**
     * Scans a directory and deletes its files that match a specific condition
     * @param string $directory
     * @param mixed $pattern Either an array of extensions or a pattern for glob()
     * @param integer $lifespan
     * @return integer
     */
    public static function delete($directory, $pattern, $lifespan = 0)
    {
        $deleted = 0;
        foreach (static::scan($directory, $pattern) as $file) {
            if ((filemtime($file) < GC_TIME - $lifespan) && unlink($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Finds all files matching a given pattern in a given directory
     * @param string $path
     * @param string $pattern Either an array of allowed extensions or a pattern for glob()
     * @return array
     */
    public static function scan($path, $pattern)
    {
        if (is_array($pattern)) {
            $extensions = implode(',', $pattern);
            return glob("$path/*.{{$extensions}}", GLOB_BRACE);
        }

        return glob("$path/$pattern");
    }

    /**
     * Recursive deletes files and directories
     * @param string $directory
     * @return boolean
     */
    public static function deleteRecursive($directory)
    {
        if (!file_exists($directory)) {
            return false;
        }

        if (!is_dir($directory)) {
            return false;
        }

        foreach (scandir($directory) as $object) {
            if ($object == '.' || $object == '..') {
                continue;
            }

            $path = $directory . '/' . $object;
            if (is_dir($path)) {
                static::deleteRecursive($path);
                continue;
            }

            unlink($path);
        }

        return rmdir($directory);
    }

    /**
     * Recursive scans files
     * @param string $pattern
     * @param integer $flags
     * @return array
     */
    public static function scanRecursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, static::scanRecursive($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }

    /**
     * Returns a file mime type
     * @param string $file
     * @return string
     */
    public static function mime($file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file);
        finfo_close($finfo);

        return $mimetype;
    }

    /**
     * Returns a unique file path using a base path
     * @param string $file
     * @return string
     */
    public static function unique($file)
    {
        if (!file_exists($file)) {
            return $file;
        }

        $info = pathinfo($file);
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';

        $counter = 1;

        do {
            $filename = $info['filename'] . '-' . $counter++ . $extension;
            $file = "{$info['dirname']}/$filename";
        } while (file_exists($file));

        return $file;
    }

    /**
     * Writes a CSV file
     * @param string $file An absolute path to the file
     * @param array $data An array of fields to be written
     * @param string $del A field delimiter (one character)
     * @param string $en A field enclosure character (one character)
     * @param integer $limit
     * @return boolean
     */
    public static function csv($file, $data, $del = ',', $en = '"', $limit = 0)
    {
        $handle = fopen($file, 'a+');

        if ($handle === false) {
            return false;
        }

        if (!empty($limit) && filesize($file) > $limit) {
            ftruncate($handle, 0);
            rewind($handle);
        }

        $result = fputcsv($handle, $data, $del, $en);
        fclose($handle);

        return ($result !== false);
    }

    /**
     * Finds a best matched file for the current URL
     * @param string $dir
     * @param string $ext
     * @param array|string $url URL or an array of URL segments
     * @param string $glue
     * @return array
     */
    public static function contex($dir, $ext, $url, $glue = '-')
    {
        if (!is_readable($dir)) {
            return array();
        }

        $expected = trim(str_replace('/', $glue, (string) $url), $glue);

        $candidates = array();
        $files = static::scan($dir, (array) $ext);

        foreach ($files as $file) {

            $filename = pathinfo($file, PATHINFO_FILENAME);

            if (0 === strpos($expected, $filename)) {
                $candidates[strlen($filename)] = array(
                    'path' => $file,
                    'filename' => $filename
                );
            }
        }

        if (empty($candidates)) {
            return array();
        }

        ksort($candidates);
        return end($candidates);
    }

}
