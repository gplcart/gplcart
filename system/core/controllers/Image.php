<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers;

use core\Controller;
use core\models\Image as I;
use core\classes\Response;

class Image extends Controller
{
    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Response class instance
     * @var \core\classes\Response $response
     */
    protected $response;

    /**
     * Constructor
     * @param I $image
     * @param Response $response
     */
    public function __construct(I $image, Response $response)
    {
        parent::__construct();

        $this->image = $image;
        $this->response = $response;
    }

    /**
     * Outputs processed images
     */
    public function cache()
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

        $imagestyle_id = array_shift($parts);

        if ($parts[0] == 'image') {
            unset($parts[0]);
        }

        $image = implode('/', $parts);

        $server_file = GC_FILE_DIR . "/image/$image";

        if (!file_exists($server_file)) {
            $this->response->error404(false);
        }

        $preset_directory = GC_IMAGE_CACHE_DIR . "/$imagestyle_id";

        $image_directory = pathinfo($image, PATHINFO_DIRNAME);

        if ($image_directory) {
            $preset_directory = GC_IMAGE_CACHE_DIR . "/$imagestyle_id/$image_directory";
        }

        $cached_image = $preset_directory . '/' . basename($image);

        if (file_exists($cached_image)) {
            $this->response->file($cached_image, array('headers' => $this->headers($cached_image)));
        }

        if (!file_exists($preset_directory) && !mkdir($preset_directory, 0755, true)) {
            $this->response->error404(false);
        }

        $actions = $this->image->getImageStyleActions($imagestyle_id, true);

        if (!$actions) {
            $this->response->error404(false);
        }

        $actions['save'] = array('value' => array($cached_image));
        $this->image->modify($server_file, $actions);
        $this->response->file($cached_image, array('headers' => $this->headers($cached_image)));
    }

    /**
     * Returns cache headers
     * @param string $file
     * @return array
     */
    protected function headers($file)
    {
        $timestamp = filemtime($file);
        $expires = (int) $this->config->get('image_cache_lifetime', 31536000); // 1 year

        $headers[] = array('Last-Modified', gmdate('D, d M Y H:i:s T', $timestamp));
        $headers[] = array('Cache-Control', "public, max-age=$expires");

        return $headers;
    }

}
