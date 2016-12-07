<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

use core\helpers\Arr as ArrayHelper;

/**
 * Provides methods to work with HTML document
 */
class Document
{

    /**
     * Request class instance
     * @var \core\helpers\Request $request
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
     * @param string $script
     * @param string $position
     * @param integer|null $weight
     * @param boolean $compress
     * @return mixed
     */
    public function js($script = '', $position = 'top', $weight = null,
            $compress = true)
    {
        static $scripts = array();

        if (empty($script)) {
            return empty($scripts[$position]) ? array() : $scripts[$position];
        }

        $data = array(
            'type' => 'js',
            'asset' => $script,
            'weight' => $weight,
            'position' => $position,
            'compress' => $compress
        );

        $scripts = $this->setAsset($data, (array) $scripts);
        return $scripts;
    }

    /**
     * Adds an asset
     * @param array $data
     * @param array $assets
     * @return array
     */
    public function setAsset(array $data, array $assets)
    {
        $key = $this->getAssetKey($data['asset'], $data['type']);

        if (empty($key)) {
            return $assets;
        }

        $position = empty($data['position']) ? array() : array($data['position']);

        if (!isset($data['weight'])) {
            $elements = ArrayHelper::getValue($assets, $position);
            $data['weight'] = empty($elements) ? 0 : count($elements) + 1;
        }

        $is_text = (0 === strpos($key, 'text.'));

        $asset = array(
            'weight' => (int) $data['weight'],
            'compress' => !empty($data['compress']),
            'text' => $is_text ? $data['asset'] : false,
            'path' => $is_text ? false : $data['asset']
        );

        $position[] = $key;
        ArrayHelper::setValue($assets, $position, $asset);
        return $assets;
    }

    /**
     * Returns a string containing either asset URL or MD5 hash of its content
     * @param string $string
     * @return string
     */
    public function getAssetKey($string, $type)
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

    /**
     * Adds a CSS style to the page
     * @param string $css
     * @param integer|null $weight
     * @param boolean $compress
     * @return array
     */
    public function css($css = '', $weight = null, $compress = true)
    {
        static $styles = array();

        if (empty($css)) {
            return (array) $styles;
        }

        $data = array(
            'asset' => $css,
            'type' => 'css',
            'weight' => $weight,
            'compress' => $compress
        );

        $styles = $this->setAsset($data, (array) $styles);
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

}
