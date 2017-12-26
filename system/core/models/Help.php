<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook;
use gplcart\core\helpers\Markdown as MarkdownHelper;

/**
 * Manages basic behaviors and data related to help information
 */
class Help
{

    /**
     * Markdown helper class
     * @var \gplcart\core\helpers\Markdown $markdown
     */
    protected $markdown;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * @param Hook $hook
     * @param MarkdownHelper $markdown
     */
    public function __construct(Hook $hook, MarkdownHelper $markdown)
    {
        $this->hook = $hook;
        $this->markdown = $markdown;
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
        $result = null;
        $this->hook->attach('help.get.before', $file, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        $result = $this->getMeta($file);

        if (empty($result)) {
            return array();
        }

        $hash = gplcart_string_encode(gplcart_path_relative($file));

        $result += array(
            'file' => $file,
            'path' => "admin/help/$hash"
        );

        $this->hook->attach('help.get.after', $file, $result, $this);
        return (array) $result;
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

        $result = &gplcart_static("help.list.$langcode");

        if (isset($result)) {
            return $result;
        }

        $this->hook->attach('help.list.before', $langcode, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $dir = $this->getDirectory($langcode);

        if (!is_dir($dir)) {
            return $result = array();
        }

        $result = $titles = $weights = array();
        foreach (gplcart_file_scan($dir, array('md')) as $file) {
            $data = $this->get($file);
            if (!empty($data['title'])) {
                $result[] = $data;
                $titles[] = $data['title'];
                $weights[] = $data['weight'];
            }
        }

        array_multisort($weights, SORT_ASC, $titles, SORT_ASC, $result);
        $this->hook->attach('help.list.after', $langcode, $result, $this);
        return $result;
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

    /**
     * Render a help file
     * @param string $file
     * @return string
     */
    public function parse($file)
    {
        $result = null;
        $this->hook->attach('help.render.before', $file, $result, $this);

        if (isset($result)) {
            return (string) $result;
        }

        $result = $this->markdown->render(file_get_contents($file));
        $this->hook->attach('help.render.after', $file, $result, $this);
        return (string) $result;
    }

}
