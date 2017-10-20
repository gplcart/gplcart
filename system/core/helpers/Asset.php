<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

use gplcart\core\helpers\Compressor as CompressorHelper;

/**
 * Helpers to work with CSS/JS files
 */
class Asset
{

    /**
     * Compressor helper class instance
     * @var \gplcart\core\helpers\Compressor $compressor
     */
    protected $compressor;

    /**
     * An array of added assets
     * @var array
     */
    protected $assets = array();

    /**
     * @param CompressorHelper $compressor
     */
    public function __construct(CompressorHelper $compressor)
    {
        $this->compressor = $compressor;
    }

    /**
     * Compresses and aggregates assets
     * @param array $assets
     * @param string $type
     * @param string $directory
     * @return array
     */
    public function compress(array $assets, $type, $directory)
    {
        $group = 0;
        $groups = $results = array();
        foreach ($assets as $key => $asset) {

            $exclude = isset($asset['aggregate']) && empty($asset['aggregate']);

            if (!empty($asset['text']) || $exclude) {
                $groups["__$group"] = $asset;
                $group++;
                continue;
            }

            if (!empty($asset['asset'])) {
                $groups[$group][$key] = $asset['asset'];
            }
        }

        foreach ($groups as $group => $contents) {

            if (strpos($group, '__') === 0) {
                $results[$group] = $contents;
                continue;
            }

            if ($type === 'js') {
                $aggregated = $this->compressor->compressJs($contents, $directory);
            } else if ($type === 'css') {
                $aggregated = $this->compressor->compressCss($contents, $directory);
            }

            if (!empty($aggregated)) {
                $asset = $this->build(array('asset' => $aggregated, 'version' => false));
                $results[$asset['key']] = $asset;
            }
        }

        return $results;
    }

    /**
     * Returns a weight for the next asset
     * @param string $type
     * @param string $pos
     * @return integer
     */
    public function getNextWeight($type, $pos)
    {
        $step = 20;
        $count = $this->getLastWeight($type, $pos);
        return $count * $step + $step;
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
    public function get($type, $position)
    {
        if (empty($this->assets[$type][$position])) {
            return array();
        }

        return $this->assets[$type][$position];
    }

    /**
     * Sets an asset
     * @param array $data
     * @return bool|array
     */
    public function set(array $data)
    {
        $build = $this->build($data);

        if (empty($build['asset'])) {
            return false;
        }

        if (!empty($build['merge']) && is_array($build['asset'])) {
            if (isset($this->assets[$build['type']][$build['position']][$build['merge']])) {
                $existing = $this->assets[$build['type']][$build['position']][$build['merge']]['asset'];
                $this->assets[$build['type']][$build['position']][$build['merge']]['asset'] = array_merge($existing, $build['asset']);
            }
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
    protected function build(array $data)
    {
        if (is_array($data['asset'])) {
            $type = 'js';
        } else if (strpos($data['asset'], 'http') === 0) {
            $type = 'external';
        } else {
            $type = pathinfo($data['asset'], PATHINFO_EXTENSION);
        }

        $data += array(
            'file' => '',
            'key' => null,
            'merge' => '',
            'version' => '',
            'type' => $type,
            'text' => false,
            'condition' => '',
            'position' => 'top',
            'aggregate' => ($type !== 'external')
        );

        if (!isset($data['weight'])) {
            $data['weight'] = $this->getNextWeight($data['type'], $data['position']);
        }

        if (!in_array($data['type'], array('css', 'js'))) {
            $data['text'] = true;
        }

        if (($type !== 'external' && $type != $data['type']) || is_array($data['asset'])) {
            $data['text'] = true; // Arrays will be converted to JSON
        }

        if ($data['text']) {
            if (!isset($data['key'])) {
                $data['key'] = 'text.' . md5(json_encode($data['asset']));
            }
            return $data;
        }

        if (gplcart_path_is_absolute($data['asset'])) {
            $data['file'] = $data['asset'];
            $data['asset'] = gplcart_path_relative($data['asset']);
        } else if ($type !== 'external') {
            $data['file'] = gplcart_path_absolute($data['asset']);
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
