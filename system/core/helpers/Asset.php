<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

use gplcart\core\helpers\Request as RequestHelper;

/**
 * Helpers to work with CSS/JS files
 */
class Asset
{

    /**
     * Default weight to add to the the next asset
     */
    const WEIGHT_STEP = 20;

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * An array of added assests
     * @var array
     */
    protected $assets = array();

    /**
     * Constructor
     * @param RequestHelper $request
     */
    public function __construct(RequestHelper $request)
    {
        $this->request = $request;
    }

    /**
     * Adds a JS file
     * @param string $script
     * @param array $data
     */
    public function setJs($script, $data = array())
    {
        $data += array(
            'type' => 'js',
            'asset' => $script,
            'position' => 'top'
        );

        if (!isset($data['weight'])) {
            $data['weight'] = $this->getNextWeight('js', $data['position']);
        }

        $this->set($data);
    }

    /**
     * Adds a CSS file
     * @param string $css
     * @param array $data
     */
    public function setCss($css, $data = array())
    {
        $data += array(
            'asset' => $css,
            'type' => 'css',
        );

        if (!isset($data['weight'])) {
            $data['weight'] = $this->getNextWeight('css', 'top');
        }

        $this->set($data);
    }

    /**
     * Returns an array of added JS assest
     * @param string $pos Top or Bottom
     * @return array
     */
    public function getJs($pos)
    {
        $js = $this->get('js', $pos);
        gplcart_array_sort($js);
        return $js;
    }

    /**
     * Returns an array of added CSS assets
     * @return array
     */
    public function getCss()
    {
        $css = $this->get('css', 'top');
        gplcart_array_sort($css);
        return $css;
    }

    /**
     * Returns a weight for the next asset
     * @param string $type
     * @param string $pos
     * @return integer
     */
    public function getNextWeight($type, $pos)
    {
        $count = $this->getLastWeight($type, $pos);
        $weight = $count * self::WEIGHT_STEP + self::WEIGHT_STEP;
        return $weight;
    }

    /**
     * Returns a weight of the last added asset
     * @param string $type Either "css" or "js"
     * @param string $pos Either "top" or "bottom"
     * @return integer
     */
    public function getLastWeight($type, $pos)
    {
        return empty($this->assets[$type][$pos]) ? 0 : count($this->assets[$type][$pos]);
    }

    /**
     * Returns an array of asset items
     * @param string $type
     * @param string $position
     * @return array
     */
    protected function get($type, $position)
    {
        if (empty($this->assets[$type][$position])) {
            return array();
        }

        return $this->assets[$type][$position];
    }

    /**
     * Sets an asset
     * @param array $data
     * @return bool
     */
    protected function set(array $data)
    {
        $data = $this->build($data);

        if (empty($data['asset'])) {
            return false;
        }

        if (isset($this->assets[$data['type']][$data['position']][$data['key']])) {
            return false;
        }

        $this->assets[$data['type']][$data['position']][$data['key']] = $data;
        return true;
    }

    /**
     * Builds asset data
     * @param array $data
     * @return array
     */
    public function build(array $data)
    {
        $type = pathinfo($data['asset'], PATHINFO_EXTENSION);

        $data += array(
            'type' => $type,
            'position' => 'top',
            'aggregate' => true,
            'condition' => '',
            'version' => 'v'
        );

        $data['text'] = (!in_array($data['type'], array('css', 'js')) || $type != $data['type']);

        if ($data['text']) {
            $data['key'] = 'text.' . md5($data['asset']);
            return $data;
        }

        $is_absolute = (strpos($data['asset'], GC_ROOT_DIR) === 0);

        if ($is_absolute) {
            $data['file'] = $data['asset'];
            $data['asset'] = str_replace(GC_ROOT_DIR . '/', '', $data['asset']);
        } else {
            $data['file'] = gplcart_absolute_path($data['asset']);
        }

        if (!file_exists($data['file'])) {
            return array();
        }

        $data['key'] = $this->request->base(true) . $data['asset'];

        if (!empty($data['version'])) {
            $data['key'] .= "?{$data['version']}=" . filemtime($data['file']);
        }

        return $data;
    }

}
