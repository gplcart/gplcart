<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\image;

use gplcart\core\helpers\Image;

/**
 * Methods to process images
 */
class Action
{

    /**
     * Image helper class instance
     * @var \gplcart\core\helpers\Image $image
     */
    protected $image;

    /**
     * @param Image $image
     */
    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    /**
     * Make thumbnail
     * @param string $source
     * @param string $target
     * @param array $action
     * @return bool
     */
    public function thumbnail(&$source, $target, array $action)
    {
        list($width, $height) = $action['value'];

        if ($this->image->set($source)->thumbnail($width, $height)->save($target)) {
            $source = $target;
            return true;
        }

        return false;
    }

    /**
     * Crop
     * @param string $source
     * @param string $target
     * @param array $action
     * @return bool
     */
    public function crop(&$source, $target, array $action)
    {
        list($x1, $y1, $x2, $y2) = $action['value'];

        if ($this->image->set($source)->crop($x1, $y1, $x2, $y2)->save($target)) {
            $source = $target;
            return true;
        }

        return false;
    }

    /**
     * Resize
     * @param string $source
     * @param string $target
     * @param array $action
     * @return bool
     */
    public function resize(&$source, $target, array $action)
    {
        list($width, $height) = $action['value'];

        if ($this->image->set($source)->resize($width, $height)->save($target)) {
            $source = $target;
            return true;
        }

        return false;
    }

    /**
     * Fit to width
     * @param string $source
     * @param string $target
     * @param array $action
     * @return bool
     */
    public function fitWidth(&$source, $target, array $action)
    {
        $width = reset($action['value']);

        if ($this->image->set($source)->fitToWidth($width)->save($target)) {
            $source = $target;
            return true;
        }

        return false;
    }

    /**
     * Fit to height
     * @param string $source
     * @param string $target
     * @param array $action
     * @return bool
     */
    public function fitHeight(&$source, $target, array $action)
    {
        $height = reset($action['value']);

        if ($this->image->set($source)->fitToHeight($height)->save($target)) {
            $source = $target;
            return true;
        }

        return false;
    }

}
