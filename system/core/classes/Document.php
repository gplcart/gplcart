<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\classes;

use core\classes\Request;

class Document
{

    /**
     * Request class instance
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * Constructor
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Adds a JS file or inline code to the page
     * @staticvar array $scripts
     * @param string $script
     * @param string $position
     * @param integer|null $weight
     * @return mixed
     */
    public function js($script = '', $position = 'top', $weight = null)
    {
        static $scripts = array();

        if (empty($script)) {
            if (!empty($position)) {
                return empty($scripts[$position]) ? array() : $scripts[$position];
            }

            return $scripts;
        }

        $key = $this->getAssetKey($script, 'js');

        if (empty($key)) {
            return array();
        }

        if (!isset($weight)) {
            $weight = empty($scripts[$position]) ? 0 : count($scripts[$position]) + 1;
        }

        $is_text = (0 === strpos($key, 'text.'));

        $scripts[$position][$key] = array(
            'weight' => (int) $weight,
            'text' => $is_text ? $script : false,
            'path' => $is_text ? false : $script
        );

        return $scripts;
    }

    /**
     * Adds a CSS style to the page
     * @staticvar array $styles
     * @param string $css
     * @param integer|null $weight
     * @return array
     */
    public function css($css = '', $weight = null)
    {
        static $styles = array();

        if (empty($css)) {
            return $styles;
        }

        $key = $this->getAssetKey($css, 'css');

        if (empty($key)) {
            return array();
        }

        if (!isset($weight)) {
            $weight = !empty($styles) ? count($styles) : 0;
            $weight++;
        }

        $is_text = (0 === strpos($key, 'text.'));

        $styles[$key] = array(
            'weight' => (int) $weight,
            'text' => $is_text ? $css : false,
            'path' => $is_text ? false : $css
        );

        return $styles;
    }

    /**
     * Adds a meta-tag to the page
     * @staticvar array $meta
     * @param array|null $data
     * @return array
     */
    public function meta($data = null)
    {
        static $meta = array();

        if (!isset($data)) {
            return $meta;
        }

        $meta[] = $data;
        return $meta;
    }

    /**
     * Adds a breadcrumb to the page
     * @staticvar array $breadcrumbs
     * @param array|null $breadcrumb
     * @return array
     */
    public function breadcrumb($breadcrumb = null)
    {
        static $breadcrumbs = array();

        if (!isset($breadcrumb)) {
            return $breadcrumbs;
        }

        $breadcrumbs[] = $breadcrumb;
        return $breadcrumbs;
    }

    /**
     * Adds a title to the page
     * @staticvar string $title
     * @param string $string
     * @param bool $both
     * @return string
     */
    public function title($string = '', $both = true)
    {
        static $title = '';

        if ($string === '') {
            return $title;
        }

        $title = strip_tags($string);

        if ($both && !$this->ptitle()) {
            return $this->ptitle($string);
        }

        return $title;
    }

    /**
     * Adds a page title
     * @staticvar string $title
     * @param string $string
     * @return string
     */
    public function ptitle($string = '')
    {
        static $title = '';

        if ($string === '') {
            return $title;
        }

        $title = $string;
        return $title;
    }

    /**
     * Adds a page description
     * @staticvar string $description
     * @param string $string
     * @return string
     */
    public function pdescription($string = '')
    {
        static $description = '';

        if ($string === '') {
            return $description;
        }

        $description = $string;
        return $description;
    }

    /**
     * Returns a string containing either asset URL or MD5 hash of its content
     * @param string $string
     * @return string
     */
    protected function getAssetKey($string, $type)
    {
        if (pathinfo($string, PATHINFO_EXTENSION) === $type) {
            $file = GC_ROOT_DIR . '/' . $string;
            if (!file_exists($file)) {
                return false;
            }
            return $this->request->base(true) . $string . '?v=' . filemtime($file);
        }

        return 'text.' . md5($string);
    }
}
