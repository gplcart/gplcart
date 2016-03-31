<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Cory LaViska, http://www.abeautifulsite.net
 * @author Iurii Makukh <gplcart.software@gmail.com>
 */

namespace core\classes;

class Image
{

    /**
     * Image EXIF header
     * @var mixed
     */
    protected $exif;

    /**
     * Image quality (percent)
     * @var integer
     */
    public $quality = 80;

    /**
     * Image resource
     * @var resource
     */
    protected $image;

    /**
     * Image filename
     * @var string
     */
    protected $filename;

    /**
     * Image info
     * @var array
     */
    protected $original_info;

    /**
     * Image width
     * @var integer
     */
    protected $width;

    /**
     * Image height
     * @var integer
     */
    protected $height;

    /**
     * Sets an image
     * @param string|null $filename
     * @param string|null $width
     * @param string|null $height
     * @param string|null $color
     * @return \core\classes\Image
     */
    public function setFile($filename = null, $width = null, $height = null,
                            $color = null)
    {
        if ($filename) {
            $this->load($filename);
        } elseif ($width) {
            $this->create($width, $height, $color);
        }

        return $this;
    }

    /**
     * Load an image
     * @param string $filename
     * @return \core\classes\Image
     * @throws \RuntimeException
     */
    public function load($filename)
    {
        // Require GD library
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('Required extension GD is not loaded.');
        }

        $this->filename = $filename;

        return $this->get_meta_data();
    }

    /**
     * Get meta data of image or base64 string
     * @return \core\classes\Image
     * @throws \InvalidArgumentException
     */
    protected function get_meta_data()
    {
        //gather meta data
        $info = getimagesize($this->filename);

        switch ($info['mime']) {
            case 'image/gif':
                $this->image = imagecreatefromgif($this->filename);
                break;
            case 'image/jpeg':
                $this->image = imagecreatefromjpeg($this->filename);
                break;
            case 'image/png':
                $this->image = imagecreatefrompng($this->filename);
                break;
            default:
                throw new \InvalidArgumentException('Invalid image: ' . $this->filename);
        }

        $this->original_info = array(
            'width' => $info[0],
            'height' => $info[1],
            'orientation' => $this->get_orientation(),
            'exif' => (function_exists('exif_read_data') && $info['mime'] === 'image/jpeg') ? ($this->exif = @exif_read_data($this->filename)) : null,
            'format' => preg_replace('/^image\//', '', $info['mime']),
            'mime' => $info['mime']
        );

        $this->width = $info[0];
        $this->height = $info[1];

        imagesavealpha($this->image, true);
        imagealphablending($this->image, true);

        return $this;
    }

    /**
     * Get the current orientation
     * @return string
     */
    public function get_orientation()
    {
        if (imagesx($this->image) > imagesy($this->image)) {
            return 'landscape';
        }

        if (imagesx($this->image) < imagesy($this->image)) {
            return 'portrait';
        }

        return 'square';
    }

    /**
     * Create an image from scratch
     * @param integer $width
     * @param integer|null $height
     * @param string|null $color
     * @return \core\classes\Image
     */
    public function create($width, $height = null, $color = null)
    {
        $height = $height ? : $width;
        $this->width = $width;
        $this->height = $height;
        $this->image = imagecreatetruecolor($width, $height);
        $this->original_info = array(
            'width' => $width,
            'height' => $height,
            'orientation' => $this->get_orientation(),
            'exif' => null,
            'format' => 'png',
            'mime' => 'image/png'
        );

        if ($color) {
            $this->fill($color);
        }

        return $this;
    }

    /**
     * Fill image with color
     * @param string $color
     * @return \core\classes\Image
     */
    public function fill($color = '#000000')
    {
        $rgba = $this->normalize_color($color);
        $fill_color = imagecolorallocatealpha($this->image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);

        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);
        imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $fill_color);

        return $this;
    }

    /**
     * Converts a hex color value to its RGB equivalent
     * @param string $color
     * @return boolean|array
     */
    protected function normalize_color($color)
    {
        if (is_string($color)) {
            $color = trim($color, '#');

            if (strlen($color) == 6) {
                list($r, $g, $b) = array(
                    $color[0] . $color[1],
                    $color[2] . $color[3],
                    $color[4] . $color[5]
                );
            } elseif (strlen($color) == 3) {
                list($r, $g, $b) = array(
                    $color[0] . $color[0],
                    $color[1] . $color[1],
                    $color[2] . $color[2]
                );
            } else {
                return false;
            }

            return array('r' => hexdec($r), 'g' => hexdec($g), 'b' => hexdec($b), 'a' => 0);
        } elseif (is_array($color) && (count($color) == 3 || count($color) == 4)) {
            if (isset($color['r'], $color['g'], $color['b'])) {
                return array(
                    'r' => $this->keep_within($color['r'], 0, 255),
                    'g' => $this->keep_within($color['g'], 0, 255),
                    'b' => $this->keep_within($color['b'], 0, 255),
                    'a' => $this->keep_within(isset($color['a']) ? $color['a'] : 0, 0, 127)
                );
            } elseif (isset($color[0], $color[1], $color[2])) {
                return array(
                    'r' => $this->keep_within($color[0], 0, 255),
                    'g' => $this->keep_within($color[1], 0, 255),
                    'b' => $this->keep_within($color[2], 0, 255),
                    'a' => $this->keep_within(isset($color[3]) ? $color[3] : 0, 0, 127)
                );
            }
        }

        return false;
    }

    /**
     * Ensures $value is always within $min and $max range.
     * @param integer $value
     * @param integer $min
     * @param integer $max
     * @return integer
     */
    protected function keep_within($value, $min, $max)
    {
        if ($value < $min) {
            return $min;
        }

        if ($value > $max) {
            return $max;
        }

        return $value;
    }

    /**
     * Destroy image resource
     */
    public function __destruct()
    {
        if (isset($this->image) && get_resource_type($this->image) === 'gd') {
            imagedestroy($this->image);
        }
    }

    /**
     * Rotates and/or flips an image automatically so the orientation will be correct (based on exif 'Orientation')
     * @return \core\classes\Image
     */
    public function auto_orient()
    {
        switch ($this->original_info['exif']['Orientation']) {
            case 1:
                // Do nothing
                break;
            case 2:
                // Flip horizontal
                $this->flip('x');
                break;
            case 3:
                // Rotate 180 counterclockwise
                $this->rotate(-180);
                break;
            case 4:
                // vertical flip
                $this->flip('y');
                break;
            case 5:
                // Rotate 90 clockwise and flip vertically
                $this->flip('y');
                $this->rotate(90);
                break;
            case 6:
                // Rotate 90 clockwise
                $this->rotate(90);
                break;
            case 7:
                // Rotate 90 clockwise and flip horizontally
                $this->flip('x');
                $this->rotate(90);
                break;
            case 8:
                // Rotate 90 counterclockwise
                $this->rotate(-90);
                break;
        }

        return $this;
    }

    /**
     * Flip an image horizontally or vertically
     * @param string $direction
     * @return \core\classes\Image
     */
    public function flip($direction)
    {
        $new = imagecreatetruecolor($this->width, $this->height);

        imagealphablending($new, false);
        imagesavealpha($new, true);

        switch (strtolower($direction)) {
            case 'y':
                for ($y = 0; $y < $this->height; $y++) {
                    imagecopy($new, $this->image, 0, $y, 0, $this->height - $y - 1, $this->width, 1);
                }
                break;
            default:
                for ($x = 0; $x < $this->width; $x++) {
                    imagecopy($new, $this->image, $x, 0, $this->width - $x - 1, 0, 1, $this->height);
                }
                break;
        }

        $this->image = $new;

        return $this;
    }

    /**
     * Rotate an image
     * @param integer $angle
     * @param string $bg_color
     * @return \core\classes\Image
     */
    public function rotate($angle, $bg_color = '#000000')
    {
        // Perform the rotation
        $rgba = $this->normalize_color($bg_color);
        $bg_color = imagecolorallocatealpha($this->image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);

        $new = imagerotate($this->image, -($this->keep_within($angle, -360, 360)), $bg_color);

        imagesavealpha($new, true);
        imagealphablending($new, true);

        // Update meta data
        $this->width = imagesx($new);
        $this->height = imagesy($new);
        $this->image = $new;

        return $this;
    }

    /**
     * Best fit (proportionally resize to fit in specified width/height)
     * @param integer $max_width
     * @param integer $max_height
     * @return \core\classes\Image
     */
    public function best_fit($max_width, $max_height)
    {

        // If it already fits, there's nothing to do
        if ($this->width <= $max_width && $this->height <= $max_height) {
            return $this;
        }

        // Determine aspect ratio
        $aspect_ratio = $this->height / $this->width;

        // Make width fit into new dimensions
        if ($this->width > $max_width) {
            $width = $max_width;
            $height = $width * $aspect_ratio;
        } else {
            $width = $this->width;
            $height = $this->height;
        }

        // Make height fit into new dimensions
        if ($height > $max_height) {
            $height = $max_height;
            $width = $height / $aspect_ratio;
        }

        return $this->resize($width, $height);
    }

    /**
     * Resize an image to the specified dimensions
     * @param integer $width
     * @param integer $height
     * @return \core\classes\Image
     */
    public function resize($width, $height)
    {
        // Generate new GD image
        $new = imagecreatetruecolor($width, $height);

        if ($this->original_info['format'] === 'gif') {

            // Preserve transparency in GIFs
            $transparent_index = imagecolortransparent($this->image);
            $palletsize = imagecolorstotal($this->image);

            if ($transparent_index >= 0 && $transparent_index < $palletsize) {
                $transparent_color = imagecolorsforindex($this->image, $transparent_index);
                $transparent_index = imagecolorallocate($new, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagefill($new, 0, 0, $transparent_index);
                imagecolortransparent($new, $transparent_index);
            }
        } else {
            // Preserve transparency in PNGs (benign for JPEGs)
            imagealphablending($new, false);
            imagesavealpha($new, true);
        }

        // Resize
        imagecopyresampled($new, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

        // Update meta data
        $this->width = $width;
        $this->height = $height;
        $this->image = $new;

        return $this;
    }

    /**
     * Blur
     * @param string $type
     * @param integer $passes
     * @return \core\classes\Image
     */
    public function blur($type = 'selective', $passes = 1)
    {
        switch (strtolower($type)) {
            case 'gaussian':
                $type = IMG_FILTER_GAUSSIAN_BLUR;
                break;
            default:
                $type = IMG_FILTER_SELECTIVE_BLUR;
                break;
        }
        for ($i = 0; $i < $passes; $i++) {
            imagefilter($this->image, $type);
        }

        return $this;
    }

    /**
     * Brightness
     * @param integer $level
     * @return \core\classes\Image
     */
    public function brightness($level)
    {
        imagefilter($this->image, IMG_FILTER_BRIGHTNESS, $this->keep_within($level, -255, 255));

        return $this;
    }

    /**
     * Contrast
     * @param integer $level
     * @return \core\classes\Image
     */
    public function contrast($level)
    {
        imagefilter($this->image, IMG_FILTER_CONTRAST, $this->keep_within($level, -100, 100));

        return $this;
    }

    /**
     * Colorize
     * @param string $color
     * @param float $opacity
     * @return \core\classes\Image
     */
    public function colorize($color, $opacity)
    {
        $rgba = $this->normalize_color($color);
        $alpha = $this->keep_within(127 - (127 * $opacity), 0, 127);
        imagefilter($this->image, IMG_FILTER_COLORIZE, $this->keep_within($rgba['r'], 0, 255), $this->keep_within($rgba['g'], 0, 255), $this->keep_within($rgba['b'], 0, 255), $alpha);

        return $this;
    }

    /**
     * Desaturate
     * @param integer $percentage
     * @return \core\classes\Image
     */
    public function desaturate($percentage = 100)
    {
        // Determine percentage
        $percentage = $this->keep_within($percentage, 0, 100);

        if ($percentage === 100) {
            imagefilter($this->image, IMG_FILTER_GRAYSCALE);
        } else {
            // Make a desaturated copy of the image
            $new = imagecreatetruecolor($this->width, $this->height);

            imagealphablending($new, false);
            imagesavealpha($new, true);
            imagecopy($new, $this->image, 0, 0, 0, 0, $this->width, $this->height);
            imagefilter($new, IMG_FILTER_GRAYSCALE);

            // Merge with specified percentage
            $this->imagecopymerge_alpha($this->image, $new, 0, 0, 0, 0, $this->width, $this->height, $percentage);
            imagedestroy($new);
        }

        return $this;
    }

    /**
     * Same as PHP's imagecopymerge() function, except preserves alpha-transparency in 24-bit PNGs
     * @param resource $dst_im
     * @param resource $src_im
     * @param integer $dst_x
     * @param integer $dst_y
     * @param integer $src_x
     * @param integer $src_y
     * @param integer $src_w
     * @param integer $src_h
     * @param integer $pct
     * @return null
     */
    protected function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y,
                                            $src_x, $src_y, $src_w, $src_h, $pct)
    {
        // Get image width and height and percentage
        $pct /= 100;
        $w = imagesx($src_im);
        $h = imagesy($src_im);

        // Turn alpha blending off
        imagealphablending($src_im, false);

        // Find the most opaque pixel in the image (the one with the smallest alpha value)
        $minalpha = 127;

        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $alpha = (imagecolorat($src_im, $x, $y) >> 24) & 0xFF;
                if ($alpha < $minalpha) {
                    $minalpha = $alpha;
                }
            }
        }

        // Loop through image pixels and modify alpha for each
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                // Get current alpha value (represents the TANSPARENCY!)
                $colorxy = imagecolorat($src_im, $x, $y);
                $alpha = ($colorxy >> 24) & 0xFF;
                // Calculate new alpha
                if ($minalpha !== 127) {
                    $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
                } else {
                    $alpha += 127 * $pct;
                }
                // Get the color index with new alpha
                $alphacolorxy = imagecolorallocatealpha($src_im, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
                // Set pixel with the new color + opacity
                if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
                    return;
                }
            }
        }

        // Copy it
        imagesavealpha($dst_im, true);
        imagealphablending($dst_im, true);
        imagesavealpha($src_im, true);
        imagealphablending($src_im, true);
        imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);

        return;
    }

    /**
     * Edge detect
     * @return \core\classes\Image
     */
    public function edges()
    {
        imagefilter($this->image, IMG_FILTER_EDGEDETECT);

        return $this;
    }

    /**
     * Emboss
     * @return \core\classes\Image
     */
    public function emboss()
    {
        imagefilter($this->image, IMG_FILTER_EMBOSS);

        return $this;
    }

    /**
     * Get the current height
     * @return integer
     */
    public function get_height()
    {
        return $this->height;
    }

    /**
     * Get info about the original image
     * @return array
     */
    public function get_original_info()
    {
        return $this->original_info;
    }

    /**
     * Get the current width
     * @return integer
     */
    public function get_width()
    {
        return $this->width;
    }

    /**
     * Invert
     * @return \core\classes\Image
     */
    public function invert()
    {
        imagefilter($this->image, IMG_FILTER_NEGATE);

        return $this;
    }

    /**
     * Mean remove
     * @return \core\classes\Image
     */
    public function mean_remove()
    {
        imagefilter($this->image, IMG_FILTER_MEAN_REMOVAL);

        return $this;
    }

    /**
     * Changes the opacity level of the image
     * @param float $opacity
     * @return \core\classes\Image
     */
    public function opacity($opacity)
    {
        // Determine opacity
        $opacity = $this->keep_within($opacity, 0, 1) * 100;

        // Make a copy of the image
        $copy = imagecreatetruecolor($this->width, $this->height);

        imagealphablending($copy, false);
        imagesavealpha($copy, true);
        imagecopy($copy, $this->image, 0, 0, 0, 0, $this->width, $this->height);

        // Create transparent layer
        $this->create($this->width, $this->height, array(0, 0, 0, 127));

        // Merge with specified opacity
        $this->imagecopymerge_alpha($this->image, $copy, 0, 0, 0, 0, $this->width, $this->height, $opacity);
        imagedestroy($copy);

        return $this;
    }

    /**
     * Overlay
     * @param object $overlay
     * @param string $position
     * @param float $opacity
     * @param integer $x_offset
     * @param integer $y_offset
     * @return \core\classes\Image
     */
    public function overlay($overlay, $position = 'center', $opacity = 1,
                            $x_offset = 0, $y_offset = 0)
    {
        // Load overlay image
        if (!($overlay instanceof Image)) {
            $overlay = new Image($overlay);
        }

        // Convert opacity
        $opacity = $opacity * 100;

        // Determine position
        switch (strtolower($position)) {
            case 'top left':
                $x = 0 + $x_offset;
                $y = 0 + $y_offset;
                break;
            case 'top right':
                $x = $this->width - $overlay->width + $x_offset;
                $y = 0 + $y_offset;
                break;
            case 'top':
                $x = ($this->width / 2) - ($overlay->width / 2) + $x_offset;
                $y = 0 + $y_offset;
                break;
            case 'bottom left':
                $x = 0 + $x_offset;
                $y = $this->height - $overlay->height + $y_offset;
                break;
            case 'bottom right':
                $x = $this->width - $overlay->width + $x_offset;
                $y = $this->height - $overlay->height + $y_offset;
                break;
            case 'bottom':
                $x = ($this->width / 2) - ($overlay->width / 2) + $x_offset;
                $y = $this->height - $overlay->height + $y_offset;
                break;
            case 'left':
                $x = 0 + $x_offset;
                $y = ($this->height / 2) - ($overlay->height / 2) + $y_offset;
                break;
            case 'right':
                $x = $this->width - $overlay->width + $x_offset;
                $y = ($this->height / 2) - ($overlay->height / 2) + $y_offset;
                break;
            case 'center':
            default:
                $x = ($this->width / 2) - ($overlay->width / 2) + $x_offset;
                $y = ($this->height / 2) - ($overlay->height / 2) + $y_offset;
                break;
        }

        // Perform the overlay
        $this->imagecopymerge_alpha($this->image, $overlay->image, $x, $y, 0, 0, $overlay->width, $overlay->height, $opacity);

        return $this;
    }

    /**
     * Pixelate
     * @param integer $block_size
     * @return \core\classes\Image
     */
    public function pixelate($block_size = 10)
    {
        imagefilter($this->image, IMG_FILTER_PIXELATE, $block_size, true);

        return $this;
    }

    /**
     * Save an image
     * @param string|null $filename
     * @param string|null $quality
     * @param string|null $format
     * @return \core\classes\Image
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function save($filename = null, $quality = null, $format = null)
    {
        // Determine quality, filename, and format
        $quality = $quality ? : $this->quality;
        $filename = $filename ? : $this->filename;

        if (empty($format)) {
            $format = $this->file_ext($filename) ? : $this->original_info['format'];
        }

        // Create the image
        switch (strtolower($format)) {
            case 'gif':
                $result = imagegif($this->image, $filename);
                break;
            case 'jpg':
            case 'jpeg':
                imageinterlace($this->image, true);
                $result = imagejpeg($this->image, $filename, round($quality));
                break;
            case 'png':
                $result = imagepng($this->image, $filename, round(9 * $quality / 100));
                break;
            default:
                throw new \InvalidArgumentException('Unsupported format');
        }

        if (empty($result)) {
            throw new \RuntimeException('Unable to save image: ' . $filename);
        }

        return $this;
    }

    /**
     * Returns the file extension of the specified file
     * @param string $filename
     * @return string
     */
    protected function file_ext($filename)
    {
        if (!preg_match('/\./', $filename)) {
            return '';
        }

        return preg_replace('/^.*\./', '', $filename);
    }

    /**
     * Sepia
     * @return \core\classes\Image
     */
    public function sepia()
    {
        imagefilter($this->image, IMG_FILTER_GRAYSCALE);
        imagefilter($this->image, IMG_FILTER_COLORIZE, 100, 50, 0);

        return $this;
    }

    /**
     * Sketch
     * @return \core\classes\Image
     */
    public function sketch()
    {
        imagefilter($this->image, IMG_FILTER_MEAN_REMOVAL);

        return $this;
    }

    /**
     * Smooth
     * @param integer $level
     * @return \core\classes\Image
     */
    public function smooth($level)
    {
        imagefilter($this->image, IMG_FILTER_SMOOTH, $this->keep_within($level, -10, 10));

        return $this;
    }

    /**
     * Add text to an image
     * @param string $text
     * @param string $font_file
     * @param integer $font_size
     * @param string $color
     * @param string $position
     * @param integer $x_offset
     * @param integer $y_offset
     * @return \core\classes\Image
     * @throws \RuntimeException
     */
    public function text($text, $font_file, $font_size = 12, $color = '#000000',
                         $position = 'center', $x_offset = 0, $y_offset = 0)
    {
        // todo - this method could be improved to support the text angle
        $angle = 0;

        // Determine text color
        $rgba = $this->normalize_color($color);
        $color = imagecolorallocatealpha($this->image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);

        // Determine textbox size
        $box = imagettfbbox($font_size, $angle, $font_file, $text);

        if (empty($box)) {
            throw new \RuntimeException('Unable to load font: ' . $font_file);
        }

        $box_width = abs($box[6] - $box[2]);
        $box_height = abs($box[7] - $box[1]);

        // Determine position
        switch (strtolower($position)) {
            case 'top left':
                $x = 0 + $x_offset;
                $y = 0 + $y_offset + $box_height;
                break;
            case 'top right':
                $x = $this->width - $box_width + $x_offset;
                $y = 0 + $y_offset + $box_height;
                break;
            case 'top':
                $x = ($this->width / 2) - ($box_width / 2) + $x_offset;
                $y = 0 + $y_offset + $box_height;
                break;
            case 'bottom left':
                $x = 0 + $x_offset;
                $y = $this->height - $box_height + $y_offset + $box_height;
                break;
            case 'bottom right':
                $x = $this->width - $box_width + $x_offset;
                $y = $this->height - $box_height + $y_offset + $box_height;
                break;
            case 'bottom':
                $x = ($this->width / 2) - ($box_width / 2) + $x_offset;
                $y = $this->height - $box_height + $y_offset + $box_height;
                break;
            case 'left':
                $x = 0 + $x_offset;
                $y = ($this->height / 2) - (($box_height / 2) - $box_height) + $y_offset;
                break;
            case 'right':
                $x = $this->width - $box_width + $x_offset;
                $y = ($this->height / 2) - (($box_height / 2) - $box_height) + $y_offset;
                break;
            case 'center':
            default:
                $x = ($this->width / 2) - ($box_width / 2) + $x_offset;
                $y = ($this->height / 2) - (($box_height / 2) - $box_height) + $y_offset;
                break;
        }

        // Add the text
        imagesavealpha($this->image, true);
        imagealphablending($this->image, true);
        imagettftext($this->image, $font_size, $angle, $x, $y, $color, $font_file, $text);

        return $this;
    }

    /**
     * Thumbnail
     * @param integer $width
     * @param integer|null $height
     * @return \core\classes\Image
     */
    public function thumbnail($width, $height = null)
    {
        // Determine height
        $height = $height ? : $width;

        // Determine aspect ratios
        $current_aspect_ratio = $this->height / $this->width;
        $new_aspect_ratio = $height / $width;

        // Fit to height/width
        if ($new_aspect_ratio > $current_aspect_ratio) {
            $this->fit_to_height($height);
        } else {
            $this->fit_to_width($width);
        }

        $left = floor(($this->width / 2) - ($width / 2));
        $top = floor(($this->height / 2) - ($height / 2));

        // Return trimmed image
        return $this->crop($left, $top, $width + $left, $height + $top);
    }

    /**
     * Fit to height (proportionally resize to specified height)
     * @param integer $height
     * @return \core\classes\Image
     */
    public function fit_to_height($height)
    {
        $aspect_ratio = $this->height / $this->width;
        $width = $height / $aspect_ratio;

        return $this->resize($width, $height);
    }

    /**
     * Fit to width (proportionally resize to specified width)
     * @param integer $width
     * @return \core\classes\Image
     */
    public function fit_to_width($width)
    {
        $aspect_ratio = $this->height / $this->width;
        $height = $width * $aspect_ratio;

        return $this->resize($width, $height);
    }

    /**
     * Crop an image
     * @param integer $x1
     * @param integer $y1
     * @param integer $x2
     * @param integer $y2
     * @return \core\classes\Image
     */
    public function crop($x1, $y1, $x2, $y2)
    {
        // Determine crop size
        if ($x2 < $x1) {
            list($x1, $x2) = array($x2, $x1);
        }
        if ($y2 < $y1) {
            list($y1, $y2) = array($y2, $y1);
        }
        $crop_width = $x2 - $x1;
        $crop_height = $y2 - $y1;

        // Perform crop
        $new = imagecreatetruecolor($crop_width, $crop_height);

        imagealphablending($new, false);
        imagesavealpha($new, true);
        imagecopyresampled($new, $this->image, 0, 0, $x1, $y1, $crop_width, $crop_height, $crop_width, $crop_height);

        // Update meta data
        $this->width = $crop_width;
        $this->height = $crop_height;
        $this->image = $new;

        return $this;
    }
}
