<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Controller methods related to images
 */
trait ControllerImage
{

    /**
     * Sets image thumbnail
     * @param \gplcart\core\models\Image $image_model
     * @param array $data
     * @param array $options
     * @return array
     */
    protected function setThumbTrait($image_model, array &$data,
            array $options = array())
    {
        if (empty($options['imagestyle'])) {
            return $data;
        }

        if (!empty($options['path'])) {
            $data['thumb'] = $image_model->url($options['imagestyle'], $options['path']);
            return $data;
        }

        if (empty($data['images'])) {
            $data['thumb'] = $image_model->getThumb($data, $options);
            return $data; // Processing single item, exit 
        }

        foreach ($data['images'] as &$image) {
            $image['thumb'] = $image_model->url($options['imagestyle'], $image['path']);
            $image['url'] = $image_model->urlFromPath($image['path']);
        }

        return $data;
    }

    /**
     * Sets product image thumbnail to the cart item
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Image $image_model
     * @param array $item
     */
    protected function setThumbCartTrait($controller, $image_model, array &$item)
    {
        $options = array(
            'path' => '',
            'imagestyle' => $controller->settings('image_style_cart', 3)
        );

        if (empty($item['product']['combination_id']) && !empty($item['product']['images'])) {
            $imagefile = reset($item['product']['images']);
            $options['path'] = $imagefile['path'];
        }

        if (!empty($item['product']['file_id'])//
                && !empty($item['product']['images'][$item['product']['file_id']]['path'])) {
            $options['path'] = $item['product']['images'][$item['product']['file_id']]['path'];
        }

        $this->setThumbTrait($image_model, $item, $options);
    }

}
