<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Collection of methods to attach various data to items
 */
trait Item
{

    /**
     * Adds the "In comparison" boolean flag
     * @param array $item
     * @param \gplcart\core\models\Compare $compare_model
     */
    protected function attachItemInComparisonTrait(array &$item,
            \gplcart\core\models\Compare $compare_model)
    {
        $item['in_comparison'] = $compare_model->exists($item['product_id']);
    }

    /**
     * Adds the "In wishlist" boolean flag to the item
     * @param array $item
     * @param string $cart_uid
     * @param integer $store_id
     * @param \gplcart\core\models\Wishlist $wishlist_model
     */
    protected function attachItemInWishlistTrait(&$item, $cart_uid, $store_id,
            \gplcart\core\models\Wishlist $wishlist_model)
    {
        $conditions = array(
            'user_id' => $cart_uid,
            'store_id' => $store_id,
            'product_id' => $item['product_id']
        );

        $item['in_wishlist'] = $wishlist_model->exists($conditions);
    }

    /**
     * Adds a full formatted total amount to the item
     * @param array $item
     * @param \gplcart\core\models\Price $price_model
     */
    protected function attachItemTotalFormattedTrait(array &$item,
            \gplcart\core\models\Price $price_model)
    {
        $item['total_formatted'] = $price_model->format($item['total'], $item['currency']);
    }

    /**
     * Add a formatted total amount without currency sign to the item
     * @param array $item
     * @param \gplcart\core\models\Price $price_model
     */
    protected function attachItemTotalFormattedNumberTrait(array &$item,
            \gplcart\core\models\Price $price_model)
    {
        $item['total_formatted_number'] = $price_model->format($item['total'], $item['currency'], true, false);
    }

    /**
     * Add a thumb URL to the item
     * @param array $data
     * @param array $options
     * @param \gplcart\core\models\Image $image_model
     * @return array
     */
    protected function attachItemThumbTrait(array &$data, array $options,
            \gplcart\core\models\Image $image_model)
    {
        if (empty($options['imagestyle'])) {
            return $data;
        }

        if (!empty($options['path'])) {
            $data['thumb'] = $image_model->url($options['imagestyle'], $options['path']);
            return $data;
        }

        if (!empty($data['path'])) {
            $data['thumb'] = $image_model->url($options['imagestyle'], $data['path']);
            return $data;
        }

        if (empty($data['images'])) {
            $data['thumb'] = $image_model->getThumb($data, $options);
            return $data; // Processing single item
        }

        foreach ($data['images'] as &$image) {
            $image['url'] = $image_model->urlFromPath($image['path']);
            $image['thumb'] = $image_model->url($options['imagestyle'], $image['path']);
        }

        return $data;
    }

    /**
     * Add thumb URLs to the cart items
     * @param array $item
     */
    protected function attachItemThumbCartTrait(array &$item, $imagestyle,
            \gplcart\core\models\Image $image_model)
    {
        $options = array(
            'path' => '',
            'imagestyle' => $imagestyle
        );

        if (empty($item['product']['combination_id']) && !empty($item['product']['images'])) {
            $imagefile = reset($item['product']['images']);
            $options['path'] = $imagefile['path'];
        }

        if (!empty($item['product']['file_id']) && !empty($item['product']['images'][$item['product']['file_id']]['path'])) {
            $options['path'] = $item['product']['images'][$item['product']['file_id']]['path'];
        }

        if (empty($options['path'])) {
            $item['thumb'] = $image_model->placeholder($options['imagestyle']);
        } else {
            $this->attachItemThumbTrait($item, $options, $image_model);
        }
    }

    /**
     * Add alias URL to an entity
     * @param array $data
     * @param array $options
     * @param \gplcart\core\Controller $controller
     */
    protected function attachItemUrlTrait(array &$data, array $options,
            \gplcart\core\Controller $controller)
    {
        if (isset($options['id_key']) && empty($options['no_item_url'])) {

            $id = $data[$options['id_key']];
            $entity = preg_replace('/_id$/', '', $options['id_key']);
            $data['url'] = empty($data['alias']) ? $controller->url("$entity/$id") : $controller->url($data['alias']);

            $query = $controller->getQuery();
            // URL with preserved query to retain view, sort etc
            $data['url_query'] = empty($data['alias']) ? $controller->url("$entity/$id", $query) : $controller->url($data['alias'], $query);
        }
    }

    /**
     * Adds a rendered product to the item
     * @param array $item
     * @param array $options
     * @param \gplcart\core\Controller $controller
     */
    protected function attachItemRenderedProductTrait(array &$item,
            array $options, \gplcart\core\Controller $controller)
    {
        if (empty($options['template_item'])) {
            return null;
        }

        $options += array(
            'buttons' => array(
                'cart_add', 'wishlist_add', 'compare_add'));

        $data = array(
            'product' => $item,
            'buttons' => $options['buttons']
        );

        $this->attachItemRenderedTrait($item, $data, $options, $controller);
    }

    /**
     * Add a rendered content to the item
     * @param array $item
     * @param array $data
     * @param array $options
     * @param \gplcart\core\Controller $controller
     */
    protected function attachItemRenderedTrait(array &$item, array $data,
            array $options, \gplcart\core\Controller $controller)
    {
        if (!empty($options['template_item'])) {
            $item['rendered'] = $controller->render($options['template_item'], $data, true);
        }
    }

    /**
     * Add a formatted price to the item
     * @param array $item
     * @param string $currency
     * @param \gplcart\core\models\Currency $currency_model
     * @param \gplcart\core\models\Price $price_model
     */
    protected function attachItemPriceFormattedTrait(array &$item, $currency,
            \gplcart\core\models\Currency $currency_model,
            \gplcart\core\models\Price $price_model)
    {
        $price = $currency_model->convert($item['price'], $item['currency'], $currency);
        $item['price_formatted'] = $price_model->format($price, $currency);

        if (isset($item['original_price'])) {
            $price = $currency_model->convert($item['original_price'], $item['currency'], $currency);
            $item['original_price_formatted'] = $price_model->format($price, $currency);
        }
    }

    /**
     * Add a calculated product price to the item
     * @param array $item
     * @param \gplcart\core\models\Product $product_model
     */
    protected function attachItemPriceCalculatedTrait(array &$item,
            \gplcart\core\models\Product $product_model)
    {
        $calculated = $product_model->calculate($item);

        if (empty($calculated)) {
            return null;
        }

        if ($item['price'] != $calculated['total']) {
            $item['original_price'] = $item['price'];
        }

        $item['price'] = $calculated['total'];
        $item['price_rule_components'] = $calculated['components'];
    }

    /**
     * Sets boolean flag indicating that item's URL matches the current URL
     * @param array $item
     * @param string $base
     * @param \gplcart\core\Controller $controller
     */
    protected function attachItemUrlActiveTrait(array &$item, $base,
            \gplcart\core\Controller $controller)
    {
        if (isset($item['url'])) {
            $path = substr($item['url'], strlen($base));
            $item['active'] = $controller->path($path);
        }
    }

    /**
     * Add indentation string indicating item's depth (only for categories)
     * @param array $item
     */
    protected function attachItemIndentationTrait(array &$item)
    {
        if (isset($item['depth'])) {
            $item['indentation'] = str_repeat('<span class="indentation"></span>', $item['depth']);
        }
    }

}
