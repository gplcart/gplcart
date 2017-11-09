<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook;

/**
 * Manages basic behaviors and data related to help information
 */
class Help
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * @param Hook $hook
     */
    public function __construct(Hook $hook)
    {
        $this->hook = $hook;
    }

    /**
     * Extracts meta data from a help file
     * @param string $file
     * @return array
     */
    public function getMeta($file)
    {
        $content = file_get_contents($file);

        $matches = array();
        preg_match('~<!--(.*?)-->~msi', $content, $matches);

        if (empty($matches[1])) {
            return array();
        }

        $meta = json_decode(trim($matches[1]), true);

        if (!is_array($meta)) {
            return array();
        }

        $meta += array(
            'title' => '',
            'teaser' => '',
            'weight' => 0
        );

        return $meta;
    }

    /**
     * Returns an array of help data from the file
     * @param string $file
     * @return array
     */
    public function get($file)
    {
        $data = $this->getMeta($file);

        if (empty($data)) {
            return array();
        }

        $hash = gplcart_string_encode(gplcart_path_relative($file));

        $data += array(
            'file' => $file,
            'path' => "admin/help/$hash"
        );

        $this->hook->attach('help.get', $file, $data, $this);
        return $data;
    }

    /**
     * Returns a path to a directory containing help files for the given language
     * @param string $langcode
     * @return string
     */
    public function getDirectory($langcode)
    {
        if (empty($langcode)) {
            $langcode = 'en';
        }

        return GC_DIR_HELP . "/$langcode";
    }

    /**
     * Returns an array of help items
     * @param string
     * @return array
     */
    public function getList($langcode = '')
    {
        if (empty($langcode)) {
            $langcode = 'en';
        }

        $list = &gplcart_static("help.list.$langcode");

        if (isset($list)) {
            return $list;
        }

        $dir = $this->getDirectory($langcode);

        if (!is_dir($dir)) {
            return array();
        }

        $files = glob("$dir/*.php");

        $list = $titles = $weights = array();

        foreach ($files as $file) {
            $data = $this->get($file);
            if (!empty($data['title'])) {
                $list[] = $data;
                $titles[] = $data['title'];
                $weights[] = $data['weight'];
            }
        }

        array_multisort($weights, SORT_ASC, $titles, SORT_ASC, $list);
        return $list;
    }

    /**
     * Returns a help data using a simple path pattern, e.g admin/*
     * @param string $pattern
     * @param string $langcode
     * @return array
     */
    public function getByPattern($pattern, $langcode)
    {
        $prepared = strtr($pattern, array('/' => '-', '*' => '_', '/*' => '_'));

        foreach (array($langcode, 'en') as $code) {
            $dir = $this->getDirectory($code);
            if (is_file("$dir/$prepared.php")) {
                return $this->get("$dir/$prepared.php");
            }
        }

        return array();
    }

}
