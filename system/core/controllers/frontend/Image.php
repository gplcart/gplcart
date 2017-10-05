<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to image cache
 */
class Image extends FrontendController
{

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
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Outputs processed images
     */
    public function cacheImage()
    {
        $this->setPathImage();
        $this->setFileImage();
        $this->setDirectoryImage();
        $this->setCacheImage();

        $this->tryOutputImage();
        $this->checkCacheDirectoryImage();
        $this->applyActionsImage();

        $this->tryOutputImage();
        $this->response->error404(false);
    }

    /**
     * Set the full server path to the cached image
     */
    protected function setCacheImage()
    {
        $this->data_cached_file = "{$this->data_imagestyle_directory}/" . basename($this->data_path);
    }

    /**
     * Check the image style directory
     */
    protected function checkCacheDirectoryImage()
    {
        if (!file_exists($this->data_imagestyle_directory) && !mkdir($this->data_imagestyle_directory, 0775, true)) {
            $this->response->error404(false);
        }
    }

    /**
     * Apply all defined actions to the source image
     */
    protected function applyActionsImage()
    {
        $actions = $this->image->getStyleActions($this->data_imagestyle_id);

        if (empty($actions)) {
            $this->response->error404(false);
        } else {
            $this->image->applyActions($actions, $this->data_source_file, $this->data_cached_file);
        }
    }

    /**
     * Check if the cached image exists and output it
     */
    protected function tryOutputImage()
    {
        if (is_file($this->data_cached_file)) {
            $headers = array('headers' => $this->getHeaders($this->data_cached_file));
            $this->response->file($this->data_cached_file, $headers);
        }
    }

    /**
     * Parse the current URL path and extract an image style ID and expected path to the cached image
     */
    protected function setPathImage()
    {
        $path = urldecode(strtok($this->request->urn(), '?'));
        $parts = explode('files/image/cache/', $path);

        if (empty($parts[1])) {
            $this->response->error404(false);
        }

        $parts = explode('/', $parts[1]);

        if (empty($parts[1])) {
            $this->response->error404(false);
        }

        $this->data_imagestyle_id = array_shift($parts);

        if ($parts[0] == 'image') {
            unset($parts[0]);
        }

        $this->data_path = implode('/', $parts);
    }

    /**
     * Set the current image style directory
     */
    protected function setDirectoryImage()
    {
        $imagestyle_directory = GC_IMAGE_CACHE_DIR . "/{$this->data_imagestyle_id}";
        $image_directory = pathinfo($this->data_path, PATHINFO_DIRNAME);

        if (!empty($image_directory)) {
            $imagestyle_directory = GC_IMAGE_CACHE_DIR . "/{$this->data_imagestyle_id}/$image_directory";
        }

        $this->data_imagestyle_directory = $imagestyle_directory;
    }

    /**
     * Set the full expected server path to the cached image
     */
    protected function setFileImage()
    {
        $file = gplcart_file_absolute("image/{$this->data_path}");

        if (is_file($file)) {
            $this->data_source_file = $file;
        } else {
            $this->response->error404(false);
        }
    }

    /**
     * Returns HTTP headers
     * @param string $file
     * @return array
     */
    protected function getHeaders($file)
    {
        $timestamp = filemtime($file);
        $expires = (int) $this->config('image_cache_lifetime', 365*24*60*60); // 1 year

        $headers = array(
            array('Last-Modified', gmdate('D, d M Y H:i:s T', $timestamp)),
            array('Cache-Control', "public, max-age=$expires")
        );

        return $headers;
    }

}
