<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

use core\helpers\Request as RequestHelper;

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
     * @var \core\helpers\Request $request
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
     * @param string $pos
     * @param integer $weight
     * @return array
     */
    public function setJs($script, $pos = 'top', $weight = null)
    {
        if (!isset($weight)) {
            $weight = $this->getNextWeight('js', $pos);
        }

        $data = array(
            'type' => 'js',
            'asset' => $script,
            'position' => $pos,
            'weight' => $weight
        );

        return $this->set($data);
    }

    /**
     * Adds a CSS file
     * @param string $css
     * @param integer $weight
     * @return array
     */
    public function setCss($css, $weight = null)
    {
        if (!isset($weight)) {
            $weight = $this->getNextWeight('css', 'top');
        }

        $data = array(
            'asset' => $css,
            'type' => 'css',
            'weight' => $weight
        );

        $result = $this->set($data);
        return $result['top'];
    }

    /**
     * Returns an array of added JS assest
     * @param string $pos Top or Bottom
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
     * @return array
     */
    protected function set(array $data)
    {
        $key = $this->getKey($data);
        $position = empty($data['position']) ? 'top' : $data['position'];

        if (isset($this->assets[$data['type']][$position][$key]) || empty($key)) {
            return $this->assets[$data['type']];
        }
        
        $data['text'] = strpos($key, 'text.') === 0;

        $this->assets[$data['type']][$position][$key] = $data;
        return $this->assets[$data['type']];
    }

    /**
     * Returns a string containing either asset URL or MD5 hash of its content
     * @param array $data
     * @return string
     */
    protected function getKey(array $data)
    {
        if (pathinfo($data['asset'], PATHINFO_EXTENSION) !== $data['type']) {
            return 'text.' . md5($data['asset']);
        }

        $file = GC_ROOT_DIR . '/' . $data['asset'];

        if (!file_exists($file)) {
            return '';
        }

        return $this->getUrl($data['asset']);
    }

    /**
     * Returns a URL of an asset with "version" ID
     * @param string $file
     * @return string
     */
    public function getUrl($file)
    {
        return $this->request->base(true) . "$file?v=" . filemtime($file);
    }

}
