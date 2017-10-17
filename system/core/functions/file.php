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
function gplcart_file_empty($directory, $pattern, $lifespan = 0)
{
    if (!is_dir($directory)) {
        return 0;
    }

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
 * @param string $file
 * @param int $errors
 * @param int $success
 * @return boolean
 */
function gplcart_file_delete_recursive($file, &$errors = 0, &$success = 0)
{
    if (is_file($file)) {
        if (unlink($file)) {
            $success++;
            return true;
        }
        $errors++;
        return false;
    }

    if (!is_dir($file)) {
        return false;
    }

    foreach (gplcart_file_scan_recursive($file) as $f) {
        gplcart_file_delete_recursive($f, $errors, $success);
    }

    if (rmdir($file)) {
        $success++;
        return true;
    }

    $errors++;
    return false;
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
        $path = "$directory/$file";
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
 * Returns a unique file path using a base path
 * @param string $file
 * @return string
 */
function gplcart_file_unique($file)
{
    if (!file_exists($file)) {
        return $file;
    }

    $info = pathinfo($file);
    $extension = isset($info['extension']) ? '.' . $info['extension'] : '';

    $counter = 0;

    do {
        $counter++;
        $modified_filename = $info['filename'] . '-' . $counter . $extension;
        $modified_file = "{$info['dirname']}/$modified_filename";
    } while (file_exists($modified_file));

    return $modified_file;
}

/**
 * Returns an absolute path to a temporary file in the private directory
 * @param string $filename
 * @param bool $unique
 * @return string
 */
function gplcart_file_private_temp($filename, $unique = false)
{
    $file = GC_PRIVATE_TEMP_DIR . "/$filename";
    return $unique ? gplcart_file_unique($file) : $file;
}

/**
 * Returns an absolute path to either private module directory or file
 * @param string $module_id
 * @param string $filename
 * @param bool $unique
 * @return string
 */
function gplcart_file_private_module($module_id, $filename = '', $unique = false)
{
    $dir = GC_PRIVATE_MODULE_DIR . "/$module_id";

    if (empty($filename)) {
        return $dir;
    }

    $file = "$dir/$filename";
    return $unique ? gplcart_file_unique($file) : $file;
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
    return $result !== false;
}

/**
 * Converts an absolute file path to relative
 * @param string $absolute
 * @return string
 */
function gplcart_file_relative($absolute)
{
    return gplcart_path_relative($absolute, GC_FILE_DIR);
}

/**
 * Converts a relative file path to absolute
 * @param string $path
 * @return string
 */
function gplcart_file_absolute($path)
{
    if (gplcart_path_starts($path, GC_FILE_DIR)) {
        return $path;
    }

    return GC_FILE_DIR . "/$path";
}

/**
 * Converts bytes to a human readable file size
 * @param integer $size
 * @param integer $precision
 * @return string
 */
function gplcart_file_size($size, $precision = 2)
{
    $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

    $i = 0;
    $step = 1024;
    while (($size / $step) > 0.9) {
        $size = $size / $step;
        $i++;
    }

    return round($size, $precision) . $units[$i];
}

/**
 * Formats file permissions
 * @param string $permissions
 * @return string
 */
function gplcart_file_perms($permissions)
{
    return substr(sprintf('%o', $permissions), -4);
}

/**
 * Sanitize file name
 * @param string $name
 * @param string $replace
 * @return string
 */
function gplcart_file_sanitize($name, $replace = '')
{
    // Remove unsafe characters
    $safe = preg_replace('/[^0-9a-zA-Z-.,_]/', $replace, $name);
    // Remove multiple consecutive non-alphabetical characters
    return preg_replace('/(_)_+|(\.)\.+|(\,)\,+|(-)-+/', '\\1\\2\\3\\4', $safe);
}

/**
 * Returns temporary file directory
 * @return string
 */
function gplcart_file_tempdir()
{
    return ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
}

/**
 * Create file with unique file name in the temporary directory
 * @param string $prefix
 * @return string
 */
function gplcart_file_tempname($prefix = 'GC')
{
    return tempnam(gplcart_file_tempdir(), $prefix);
}

/**
 * Reorder the $_FILES array when uploading multiple files
 * @param array $files
 * @return boolean
 */
function gplcart_file_convert_upload(&$files)
{
    if (empty($files['name']) || (count($files['name']) == 1 && empty($files['name'][0]))) {
        return false;
    }

    $converted = array();
    foreach ($files as $key => $all) {
        foreach ($all as $i => $val) {
            $converted[$i][$key] = $val;
        }
    }

    $files = $converted;
    return true;
}
