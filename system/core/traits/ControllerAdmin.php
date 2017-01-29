<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Methods for administrators
 */
trait ControllerAdmin
{

    /**
     * Adds thumb url to an array of files
     * @param \gplcart\core\Controller $controller
     * @param array $items
     */
    protected function attachThumbsTrait($controller, array &$items)
    {
        $imagestyle = $controller->config('image_style_admin', 2);

        /* @var $image_model \gplcart\core\models\Image */
        $image_model = $controller->getInstance('image');

        foreach ($items as &$item) {
            $item['thumb'] = $image_model->url($imagestyle, $item['path']);
        }
    }

    /**
     * Adds thumb url to a single file
     * @param \gplcart\core\Controller $controller
     * @param array $item
     */
    protected function attachThumbTrait($controller, array &$item)
    {
        $imagestyle = $controller->config('image_style_admin', 2);

        /* @var $image_model \gplcart\core\models\Image */
        $image_model = $controller->getInstance('image');

        $item['thumb'] = $image_model->url($imagestyle, $item['path']);
    }

    /**
     * Adds full store url for every entity in the array
     * @param \gplcart\core\Controller $controller
     * @param array $items
     * @param string $entity
     * @return array
     */
    protected function attachEntityUrlTrait($controller, array &$items, $entity)
    {
        /* @var $store_model \gplcart\core\models\Store */
        $store_model = $controller->getInstance('store');

        $stores = $store_model->getList();

        foreach ($items as &$item) {
            $item['url'] = '';
            if (isset($stores[$item['store_id']])) {
                $url = $store_model->url($stores[$item['store_id']]);
                $item['url'] = "$url/$entity/{$item["{$entity}_id"]}";
            }
        }

        return $items;
    }

    /**
     * Adds rendered images to the edit entity form
     * @param \gplcart\core\Controller $controller
     * @param array $images
     * @param string $entity
     */
    protected function setImagesTrait($controller, array $images, $entity)
    {
        $data = array('images' => $images, 'name_prefix' => $entity);
        $html = $controller->render('common/image/attache', $data);
        $controller->setData('attached_images', $html);
    }

    /**
     * Deletes submitted image file IDs
     * @param \gplcart\core\Controller $controller
     * @param array $data
     * @param string $entity
     */
    protected function deleteImagesTrait($controller, array $data, $entity)
    {
        /* @var $request \gplcart\core\helpers\Request */
        $request = $controller->getInstance('request');

        /* @var $image_model \gplcart\core\models\Image */
        $image_model = $controller->getInstance('image');

        $file_ids = $request->post('delete_images', array());

        if (empty($file_ids) || empty($data["{$entity}_id"])) {
            return null;
        }

        $options = array(
            'file_id' => $file_ids,
            'file_type' => 'image',
            'id_key' => "{$entity}_id",
            'id_value' => $data["{$entity}_id"]
        );

        return $image_model->deleteMultiple($options);
    }

}
