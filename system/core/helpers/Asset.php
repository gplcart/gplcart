<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

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
     * An array of added assets
     * @var array
     */
    protected $assets = array();

    /**
     * Adds a JS file
     * @param string $script
     * @param array $data
     * @return bool|array
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

        return $this->set($data);
    }

    /**
     * Adds a CSS file
     * @param string $css
     * @param array $data
     * @return bool|array
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

        return $this->set($data);
    }

    /**
     * Returns an array of added JS asset
     * @param string $pos Either "top" or "bottom"
     * @return array
     */
    public function getJs($pos)
    {
        return $this->get('js', $pos);
    }

    /**
     * Returns an array of added CSS assets
     * @return array
     */
    public function getCss()
    {
        return $this->get('css', 'top');
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
        return $count * self::WEIGHT_STEP + self::WEIGHT_STEP;
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
     * @param array $asset
     * @return bool|array
     */
    protected function set(array $asset)
    {
        $build = $this->build($asset);

        if (empty($build['asset'])) {
            return false;
        }

        if (isset($this->assets[$build['type']][$build['position']][$build['key']])) {
            return false;
        }

        $this->assets[$build['type']][$build['position']][$build['key']] = $build;
        return $this->assets[$build['type']];
    }

    /**
     * Builds asset data
     * @param array $data
     * @return array
     */
    public function build(array $data)
    {
        if (strpos($data['asset'], 'http') === 0) {
            $type = 'external';
        } else {
            $type = pathinfo($data['asset'], PATHINFO_EXTENSION);
        }

        $data += array(
            'type' => $type,
            'position' => 'top',
            'condition' => '',
            'version' => '',
            'text' => false,
            'file' => '',
            'aggregate' => $type !== 'external'
        );

        if (!in_array($data['type'], array('css', 'js'))) {
            $data['text'] = true;
        }

        if ($type !== 'external' && $type != $data['type']) {
            $data['text'] = true;
        }

        if ($data['text']) {
            $data['key'] = 'text.' . md5($data['asset']);
            return $data;
        }

        if (gplcart_is_absolute_path($data['asset'])) {
            $data['file'] = $data['asset'];
            $data['asset'] = gplcart_relative_path($data['asset']);
        } else if ($type !== 'external') {
            $data['file'] = gplcart_absolute_path($data['asset']);
        }

        if (!empty($data['file'])) {

            if (!file_exists($data['file'])) {
                return array();
            }

            $data['version'] = filemtime($data['file']);
        }

        $data['key'] = $data['asset'] = str_replace('\\', '/', $data['asset']);
        return $data;
    }

}
