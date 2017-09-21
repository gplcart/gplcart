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
     * Default weight to add to the the next asset
     */
    const WEIGHT_STEP = 20;

    /**
     * An array of added assets
     * @var array
     */
    protected $assets = array();

    /**
     * An array of asset groups
     * @var array
     */
    protected $groups = array();

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

        foreach ($groups as $group => $content) {

            if (strpos($group, '__') === 0) {
                $results[$group] = $content;
                continue;
            }

            if ($type === 'js') {
                $aggregated = $this->compressor->compressJs($content, $directory);
            } else if ($type === 'css') {
                $aggregated = $this->compressor->compressCss($content, $directory);
            }

            if (!empty($aggregated)) {
                $asset = $this->build(array('asset' => $aggregated, 'version' => false));
                $results[$asset['key']] = $asset;
            }
        }

        return $results;
    }

    /**
     * Sets groups of assets
     * @param string $key
     * @param array $data
     * @return bool|array
     */
    public function setGroup($key, array $data)
    {
        if (!isset($this->groups[$data['type']][$key])) {
            $this->groups[$data['type']][$key] = 0;
        }

        if (isset($data['weight'])) {
            $this->groups[$data['type']][$key] += (int) $data['weight'];
        } else {
            $this->groups[$data['type']][$key] ++;
            $data['weight'] = $this->groups[$data['type']][$key];
        }

        return $this->set($data);
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
            'aggregate' => ($type !== 'external')
        );

        if (!isset($data['weight'])) {
            $data['weight'] = $this->getNextWeight($data['type'], $data['position']);
        }

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
