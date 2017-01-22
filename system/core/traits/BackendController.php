<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Methods for backend controller
 */
trait BackendController
{

    /**
     * Adds thumb url to an array of files
     * @param \gplcart\core\models\Image $image
     * @param \gplcart\core\Config $config
     * @param array $items
     */
    protected function attachThumbsTrait($image, $config, array &$items)
    {
        $imagestyle = $config->get('image_style_admin', 2);

        foreach ($items as &$item) {
            $item['thumb'] = $image->url($imagestyle, $item['path']);
        }
    }

    /**
     * Adds thumb url to a single file
     * @param \gplcart\core\models\Image $image
     * @param \gplcart\core\Config $config
     * @param array $item
     */
    protected function attachThumbTrait($image, $config, array &$item)
    {
        $imagestyle = $config->get('image_style_admin', 2);
        $item['thumb'] = $image->url($imagestyle, $item['path']);
    }

    /**
     * Adds full store url for every entity in the array
     * @param \gplcart\core\models\Store $store
     * @param array $items
     * @param string $entity
     * @return array $items
     */
    protected function attachEntityUrlTrait($store, array &$items, $entity)
    {
        $stores = $store->getList();

        foreach ($items as &$item) {
            $item['url'] = '';
            if (isset($stores[$item['store_id']])) {
                $url = $store->url($stores[$item['store_id']]);
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
     * @param \gplcart\core\helpers\Request $request
     * @param \gplcart\core\models\Image $image
     * @param array $data
     * @param string $entity
     */
    protected function deleteImagesTrait($request, $image, array $data, $entity)
    {
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

        return $image->deleteMultiple($options);
    }

}
