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
     * 
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
     * @param integer $weight
     * @return array
     */
    public function js($script = null, $position = 'top', $weight = null)
    {
        static $scripts = array();

        if (empty($script)) {

            if (!empty($position)) {
                return empty($scripts[$position]) ? array() : $scripts[$position];
            }

            return $scripts;
        }

        if (pathinfo($script, PATHINFO_EXTENSION) === 'js') {

            $file = GC_ROOT_DIR . '/' . $script;

            if (!file_exists($file)) {
                return false;
            }

            $key = $this->request->base(true) . $script . '?v=' . filemtime($file);
        } else {
            $key = md5($script);
            $text = true;
        }

        if (!isset($weight)) {
            $weight = empty($scripts[$position]) ? 0 : count($scripts[$position]) + 1;
        }

        $scripts[$position][$key] = array(
            'weight' => (int) $weight,
            'text' => isset($text) ? $script : false,
            'path' => isset($text) ? false : $script
        );

        return $scripts;
    }

    /**
     * Adds a CSS style to the page
     * @staticvar array $styles
     * @param string $css
     * @param type $weight
     * @return array
     */
    public function css($css = null, $weight = null)
    {
        static $styles = array();

        if (!isset($css)) {
            return $styles;
        }

        if (pathinfo($css, PATHINFO_EXTENSION) == 'css') {

            $file = GC_ROOT_DIR . '/' . $css;

            if (!file_exists($file)) {
                return false;
            }

            $key = $this->request->base(true) . $css . '?v=' . filemtime($file);
        } else {
            $key = md5($css);
            $text = true;
        }

        if (!isset($weight)) {
            $weight = !empty($styles) ? count($styles) : 0;
            $weight++;
        }

        $styles[$key] = array(
            'weight' => (int) $weight,
            'text' => isset($text) ? $css : false,
            'path' => isset($text) ? false : $css
        );

        return $styles;
    }

    /**
     * Adds a meta-tag to the page
     * @staticvar array $meta
     * @param array $data
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
     * @param array $breadcrumb
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
    public function title($string = null, $both = true)
    {
        static $title = '';

        if (!isset($string)) {
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
     * @param type $string
     * @return string
     */
    public function ptitle($string = null)
    {
        static $title = '';

        if (!isset($string)) {
            return $title;
        }

        $title = $string;
        return $title;
    }

    /**
     * Adds a page description
     * @staticvar string $description
     * @param type $string
     * @return string
     */
    public function pdescription($string = null)
    {
        static $description = '';

        if (!isset($string)) {
            return $description;
        }

        $description = $string;
        return $description;
    }

}
