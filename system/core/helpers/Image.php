<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

use InvalidArgumentException;

/**
 * Methods for image manipulation using GD PHP library
 */
class Image
{

    /**
     * Image resource
     * @var resource
     */
    protected $image;

    /**
     * Image path
     * @var string
     */
    protected $filename;

    /**
     * Image format
     * @var string
     */
    protected $format;

    /**
     * Image width
     * @var int
     */
    protected $width;

    /**
     * Image height
     * @var int
     */
    protected $height;

    /**
     * Destroy image resource
     */
    public function __destruct()
    {
        if (is_resource($this->image) && get_resource_type($this->image) === 'gd') {
            imagedestroy($this->image);
        }
    }

    /**
     * Load an image
     * @param string $filename
     * @return $this
     * @throws InvalidArgumentException
     */
    public function set($filename)
    {
        $this->image = null;
        $this->filename = $filename;

        $info = getimagesize($this->filename);

        if (empty($info)) {
            throw new InvalidArgumentException("Invalid image: {$this->filename}");
        }

        $this->width = $info[0];
        $this->height = $info[1];
        $this->format = preg_replace('/^image\//', '', $info['mime']);
        $this->image = $this->callFunction('imagecreatefrom', $this->format, array($this->filename));

        if (!is_resource($this->image)) {
            throw new InvalidArgumentException("Failed to create image resource for {$this->filename}");
        }

        imagesavealpha($this->image, true);
        imagealphablending($this->image, true);

        return $this;
    }

    /**
     * Returns an array of supported image formats
     * @return array
     */
    public function getSupportedFormats()
    {
        static $formats = null;

        if (isset($formats)) {
            return $formats;
        }

        $formats = array();
        foreach (gd_info() as $name => $status) {
            if (strpos($name, 'Support') !== false && $status) {
                $formats[] = strtolower(strtok($name, ' '));
            }
        }

        return $formats = array_unique($formats);
    }

    /**
     * Call a function which name is constructed from prefix and image format
     * @param string $prefix
     * @param string $format
     * @param array $arguments
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function callFunction($prefix, $format, array $arguments)
    {
        $function = "$prefix$format";
        if (in_array($format, $this->getSupportedFormats()) && function_exists($function)) {
            return call_user_func_array($function, $arguments);
        }

        throw new InvalidArgumentException('Unsupported function prefix/image format');
    }

    /**
     * Create image thumbnail keeping aspect ratio
     * @param int $width
     * @param int|null $height
     * @return $this
     */
    public function thumbnail($width, $height = null)
    {
        if (!isset($height)) {
            $height = $width;
        }

        if (($height / $width) > ($this->height / $this->width)) {
            $this->fitToHeight($height);
        } else {
            $this->fitToWidth($width);
        }

        $left = floor(($this->width / 2) - ($width / 2));
        $top = floor(($this->height / 2) - ($height / 2));

        return $this->crop($left, $top, $width + $left, $height + $top);
    }

    /**
     * Proportionally resize to the specified height
     * @param int $height
     * @return $this
     */
    public function fitToHeight($height)
    {
        $width = $height / ($this->height / $this->width);
        return $this->resize($width, $height);
    }

    /**
     * Proportionally resize to the specified width
     * @param int $width
     * @return $this
     */
    public function fitToWidth($width)
    {
        $height = $width * ($this->height / $this->width);
        return $this->resize($width, $height);
    }

    /**
     * Crop an image
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @return $this
     */
    public function crop($x1, $y1, $x2, $y2)
    {
        if ($x2 < $x1) {
            list($x1, $x2) = array($x2, $x1);
        }
        if ($y2 < $y1) {
            list($y1, $y2) = array($y2, $y1);
        }

        $crop_width = $x2 - $x1;
        $crop_height = $y2 - $y1;

        $new = imagecreatetruecolor($crop_width, $crop_height);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        imagecopyresampled($new, $this->image, 0, 0, $x1, $y1, $crop_width, $crop_height, $crop_width, $crop_height);

        $this->image = $new;
        $this->width = $crop_width;
        $this->height = $crop_height;

        return $this;
    }

    /**
     * Resize an image to the specified dimensions
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function resize($width, $height)
    {
        $new = imagecreatetruecolor($width, $height);

        if ($this->format === 'gif') {

            $transparent_index = imagecolortransparent($this->image);
            $palletsize = imagecolorstotal($this->image);

            if ($transparent_index >= 0 && $transparent_index < $palletsize) {
                $transparent_color = imagecolorsforindex($this->image, $transparent_index);
                $transparent_index = imagecolorallocate($new, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagefill($new, 0, 0, $transparent_index);
                imagecolortransparent($new, $transparent_index);
            }
        } else {
            imagealphablending($new, false);
            imagesavealpha($new, true);
        }

        imagecopyresampled($new, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

        $this->image = $new;
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Save an image
     * @param string $filename
     * @param int|null $quality
     * @param string $format
     * @return $this
     * @throws InvalidArgumentException
     */
    public function save($filename = '', $quality = 100, $format = '')
    {
        if (empty($filename)) {
            $filename = $this->filename;
        }

        if (empty($format)) {
            $format = $this->format;
        }

        $arguments = array($this->image, $filename);

        if (in_array($format, array('jpg', 'jpeg'))) {
            $format = 'jpeg';
            imageinterlace($this->image, true);
            $arguments = array($this->image, $filename, round($quality));
        } else if ($format === 'png') {
            $arguments = array($this->image, $filename, round(9 * $quality / 100));
        }

        $result = $this->callFunction('image', $format, $arguments);

        if (empty($result)) {
            throw new InvalidArgumentException("Unable to save image $filename");
        }

        return $this;
    }

}
