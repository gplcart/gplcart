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
        preg_match_all('~<!--(.*?)-->~msi', $content, $matches);

        $meta = array('title' => '', 'teaser' => '');

        if (isset($matches[1][0])) {
            $meta['title'] = trim($matches[1][0]);
        }

        if (isset($matches[1][1])) {
            $meta['teaser'] = trim($matches[1][1]);
        }

        return $meta;
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

        $dir = GC_DIR_HELP . "/$langcode";

        if (!is_dir($dir) || !is_readable($dir)) {
            return array();
        }

        $files = glob("$dir/*.php");

        $list = array();
        foreach ($files as $file) {
            $meta = $this->getMeta($file);
            if (!empty($meta['title'])) {
                $list[] = array(
                    'file' => $file,
                    'title' => $meta['title'],
                    'teaser' => $meta['teaser'],
                    'hash' => gplcart_string_encode(gplcart_path_relative($file)));
            }
        }

        gplcart_array_sort($list, 'title');

        $this->hook->attach('help.list', $list, $this);
        return $list;
    }

}
