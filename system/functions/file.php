<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

/**
 * Scans a directory and deletes its files that match a specific condition
 * @param string $directory
 * @param mixed $pattern Either an array of extensions or a pattern for glob()
 * @param integer $lifespan
 * @return integer
 */
function gplcart_file_delete($directory, $pattern, $lifespan = 0)
{
    $deleted = 0;
    foreach (gplcart_file_scan($directory, $pattern) as $file) {
        if ((filemtime($file) < GC_TIME - $lifespan) && unlink($file)) {
            $deleted++;
        }
    }

    return $deleted;
}

/**
 * Finds all files matching a given pattern in a given directory
 * @param string $path
 * @param string|array $pattern
 * @return array
 */
function gplcart_file_scan($path, $pattern)
{
    if (!is_array($pattern)) {
        return glob("$path/$pattern");
    }

    $extensions = implode(',', $pattern);
    return glob("$path/*.{{$extensions}}", GLOB_BRACE);
}

/**
 * Recursive deletes files and directories
 * @param string $directory
 * @return boolean
 */
function gplcart_file_delete_recursive($directory)
{
    if (!is_dir($directory)) {
        return false;
    }

    $files = gplcart_file_scan_recursive($directory);

    foreach ($files as $file) {
        if (is_dir($file)) {
            gplcart_file_delete_recursive($file);
        } else {
            unlink($file);
        }
    }

    return rmdir($directory);
}

/**
 * Recursive scans files in a directory
 * @param string $directory
 * @param array $results
 * @return array
 */
function gplcart_file_scan_recursive($directory, &$results = array())
{
    foreach (scandir($directory) as $file) {
        $path = $directory . DIRECTORY_SEPARATOR . $file;
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($file != "." && $file != "..") {
            gplcart_file_scan_recursive($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}

/**
 * Returns a file mime type
 * @param string $file
 * @return string
 */
function gplcart_file_mime($file)
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
function gplcart_file_unique($file)
{
    if (!file_exists($file)) { // use file_exists to check dirs and files
        return $file;
    }

    $info = pathinfo($file);
    $extension = isset($info['extension']) ? '.' . $info['extension'] : '';

    $counter = 0;

    do {
        $counter++;
        $modified_filename = $info['filename'] . '-' . $counter . $extension;
        $modified_file = "{$info['dirname']}/$modified_filename";
    } while (is_file($modified_file));

    return $modified_file;
}

/**
 * Writes a CSV file
 * @param string $file
 * @param array $data
 * @param string $del
 * @param string $en
 * @param integer $limit
 * @return boolean
 */
function gplcart_file_csv($file, $data, $del = ",", $en = '"', $limit = 0)
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
 * Converts absolute file path to relative
 * @param string $absolute
 * @return string
 */
function gplcart_file_relative_path($absolute)
{
    $prefix = GC_FILE_DIR . '/';
    if (substr($absolute, 0, strlen($prefix)) === $prefix) {
        return trim(substr($absolute, strlen($prefix)), '/');
    }
    return $absolute;
}
