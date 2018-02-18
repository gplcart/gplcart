<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use Exception;
use gplcart\core\models\ImageStyle;
use OutOfRangeException;
use RuntimeException;

/**
 * Handles incoming requests and outputs data related to image cache
 */
class Image extends Controller
{

    /**
     * Image style model instance
     * @var \gplcart\core\models\ImageStyle $image_style
     */
    protected $image_style;

    /**
     * A path to the cached image from the current URL
     * @var string
     */
    protected $data_path;

    /**
     * A full server path to the source image file
     * @var string
     */
    protected $data_source_file;

    /**
     * An image style ID from the current URL
     * @var string
     */
    protected $data_imagestyle_id;

    /**
     * A full server path to the cached image
     * @var string
     */
    protected $data_cached_file;

    /**
     * Image style directory
     * @var string
     */
    protected $data_imagestyle_directory;

    /**
     * @param ImageStyle $image_style
     */
    public function __construct(ImageStyle $image_style)
    {
        parent::__construct();

        $this->image_style = $image_style;
    }

    /**
     * Outputs processed images
     */
    public function outputCacheImage()
    {
        try {
            $this->setUrlPathImage();
            $this->setFileImage();
            $this->setDirectoryImage();
            $this->tryOutputImage();
            $this->checkCacheDirectoryImage();
            $this->applyActionsImage();
            $this->tryOutputImage();
        } catch (Exception $ex) {
            $this->response->outputError404(false);
        }
    }

    /**
     * Parse the current URL path
     * @throws OutOfRangeException
     */
    protected function setUrlPathImage()
    {
        $parts = explode('files/image/cache/', urldecode(strtok($this->server->requestUri(), '?')));

        if (empty($parts[1])) {
            throw new OutOfRangeException('Second segment is empty in the image cache path');
        }

        $parts = explode('/', $parts[1]);

        if (empty($parts[1])) {
            throw new OutOfRangeException('Second segment is empty in the image cache path');
        }

        $this->data_imagestyle_id = array_shift($parts);

        if ($parts[0] == 'image') {
            unset($parts[0]);
        }

        $this->data_path = implode('/', $parts);
    }

    /**
     * Set image file path
     * @throws RuntimeException
     */
    protected function setFileImage()
    {
        $this->data_source_file = gplcart_file_absolute("image/{$this->data_path}");

        if (!is_file($this->data_source_file)) {
            throw new RuntimeException('No source image file');
        }
    }

    /**
     * Set the current image style directory
     */
    protected function setDirectoryImage()
    {
        $imagestyle_directory = GC_DIR_IMAGE_CACHE . "/$this->data_imagestyle_id";
        $image_directory = pathinfo($this->data_path, PATHINFO_DIRNAME);

        if (!empty($image_directory)) {
            $imagestyle_directory = GC_DIR_IMAGE_CACHE . "/$this->data_imagestyle_id/$image_directory";
        }

        $this->data_imagestyle_directory = $imagestyle_directory;
        $this->data_cached_file = "$imagestyle_directory/" . basename($this->data_path);
    }

    /**
     * Try to output existing image
     */
    protected function tryOutputImage()
    {
        if (is_file($this->data_cached_file)) {

            $this->response->addHeader('Last-Modified', gmdate('D, d M Y H:i:s T', filemtime($this->data_cached_file)))
                ->addHeader('Content-Length', filesize($this->data_cached_file))
                ->addHeader('Content-type', mime_content_type($this->data_cached_file))
                ->sendHeaders();

            readfile($this->data_cached_file);
            exit;
        }
    }

    /**
     * Check the image style directory
     */
    protected function checkCacheDirectoryImage()
    {
        if (!file_exists($this->data_imagestyle_directory) && !mkdir($this->data_imagestyle_directory, 0775, true)) {
            throw new RuntimeException('Cannot create image style directory');
        }
    }

    /**
     * Apply all defined actions to the source image
     * @throws RuntimeException
     */
    protected function applyActionsImage()
    {
        $actions = $this->image_style->getActions($this->data_imagestyle_id);

        if (empty($actions)) {
            throw new RuntimeException('No image style actions to apply');
        }

        $this->image_style->applyActions($actions, $this->data_source_file, $this->data_cached_file);
    }
}
