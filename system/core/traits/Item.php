<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains methods to set various data to entity items
 */
trait Item
{

    /**
     * Adds "In comparison" boolean flag
     * @param array $item
     * @param \gplcart\core\models\ProductCompare $compare_model
     * @return array
     */
    public function setItemInComparison(array &$item, $compare_model)
    {
        $item['in_comparison'] = $compare_model->exists($item['product_id']);
        return $item;
    }

    /**
     * Adds "In wishlist" boolean flag to the item
     * @param array $item
     * @param string|int $user_id
     * @param int $store_id
     * @param \gplcart\core\models\Wishlist $wishlist_model
     * @return array
     */
    public function setItemInWishlist(&$item, $user_id, $store_id, $wishlist_model)
    {
        $conditions = array(
            'user_id' => $user_id,
            'store_id' => $store_id,
            'product_id' => $item['product_id']
        );

        $item['in_wishlist'] = $wishlist_model->exists($conditions);
        return $item;
    }

    /**
     * Adds a full formatted total amount to the item
     * @param array $item
     * @param \gplcart\core\models\Price $price_model
     * @return array
     */
    public function setItemTotalFormatted(array &$item, $price_model)
    {
        $item['total_formatted'] = $price_model->format($item['total'], $item['currency']);
        return $item;
    }

    /**
     * Add a formatted total amount without currency sign to the item
     * @param array $item
     * @param \gplcart\core\models\Price $price_model
     * @return array
     */
    public function setItemTotalFormattedNumber(array &$item, $price_model)
    {
        $item['total_formatted_number'] = $price_model->format($item['total'], $item['currency'], true, false);
        return $item;
    }

    /**
     * Add a thumb URL to the item
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     * @param array $options
     * @return array
     */
    public function setItemThumb(array &$item, $image_model, $options = array())
    {
        $options += array('imagestyle' => $this->config('image_style', 3));

        if (!empty($options['path'])) {
            $item['thumb'] = $image_model->url($options['imagestyle'], $options['path']);
        } else if (!empty($item['path'])) {
            $item['thumb'] = $image_model->url($options['imagestyle'], $item['path']);
        } else if (empty($item['images'])) {
            $item['thumb'] = $image_model->getThumb($item, $options);
        } else {
            $first = reset($item['images']);
            $item['thumb'] = $image_model->url($options['imagestyle'], $first['path']);
            foreach ($item['images'] as &$image) {
                $image['url'] = $image_model->urlFromPath($image['path']);
                $image['thumb'] = $image_model->url($options['imagestyle'], $image['path']);
                $this->setItemIsThumbPlaceholder($image, $image_model);
            }
        }

        $this->setItemIsThumbPlaceholder($item, $image_model);
        return $item;
    }

    /**
     * Sets product thumbs
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     * @return array
     */
    public function setItemThumbProduct(array &$item, $image_model)
    {
        $options = array(
            'imagestyle' => $this->configTheme('image_style_product', 6));

        if (empty($item['images'])) {
            $item['images'][] = array(
                'thumb' => $image_model->getPlaceholder($options['imagestyle']));
        } else {
            $this->setItemThumb($item, $image_model, $options);
        }

        return $item;
    }

    /**
     * Sets a boolean flag indicating that the thumb is an image placeholder
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     * @return array
     */
    public function setItemIsThumbPlaceholder(array &$item, $image_model)
    {
        if (!empty($item['thumb'])) {
            $item['thumb_placeholder'] = $image_model->isPlaceholder($item['thumb']);
        }

        return $item;
    }

    /**
     * Add thumb URLs to the cart items
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     * @return array
     */
    public function setItemThumbCart(array &$item, $image_model)
    {
        $options = array(
            'path' => '',
            'imagestyle' => $this->configTheme('image_style_cart', 3)
        );

        if (empty($item['product']['combination_id']) && !empty($item['product']['images'])) {
            $imagefile = reset($item['product']['images']);
            $options['path'] = $imagefile['path'];
        }

        if (!empty($item['product']['file_id']) && !empty($item['product']['images'][$item['product']['file_id']]['path'])) {
            $options['path'] = $item['product']['images'][$item['product']['file_id']]['path'];
        }

        if (empty($options['path'])) {
            $item['thumb'] = $image_model->getPlaceholder($options['imagestyle']);
        } else {
            $this->setItemThumb($item, $image_model, $options);
        }

        return $item;
    }

    /**
     * Add alias URL to an entity
     * @param array $item
     * @param array $options
     * @return array
     */
    public function setItemUrl(array &$item, array $options = array())
    {
        if (empty($options['id_key'])) {
            return $item;
        }

        $id = $item[$options['id_key']];
        $entity = preg_replace('/_id$/', '', $options['id_key']);
        $item['url'] = empty($item['alias']) ? $this->url("$entity/$id") : $this->url($item['alias']);
        $query = $this->getQuery(null, array(), 'array');
        $item['url_query'] = empty($item['alias']) ? $this->url("$entity/$id", $query) : $this->url($item['alias'], $query);

        return $item;
    }

    /**
     * Sets full entity URL
     * @param array $item
     * @param \gplcart\core\models\Store $store_model
     * @param string $entity
     * @return array
     */
    public function setItemEntityUrl(array &$item, $store_model, $entity)
    {
        if (isset($item['store_id']) && isset($item["{$entity}_id"])) {
            $store = $store_model->get($item['store_id']);
            if (!empty($store)) {
                $url = $store_model->url($store);
                $item['url'] = "$url/$entity/{$item["{$entity}_id"]}";
            }
        }

        return $item;
    }

    /**
     * Adds a rendered product to the item
     * @param array $item
     * @param array $options
     * @return array
     */
    public function setItemRenderedProduct(array &$item, $options = array())
    {
        if (empty($options['template_item'])) {
            return $item;
        }

        $options += array(
            'buttons' => array(
                'cart_add', 'wishlist_add', 'compare_add'));

        $data = array(
            'item' => $item,
            'buttons' => $options['buttons']
        );

        $this->setItemRendered($item, $data, $options);
        return $item;
    }

    /**
     * Sets bundled products
     * @param array $item
     * @param \gplcart\core\models\Product $product_model
     * @param \gplcart\core\models\Image $image_model
     * @param array $options
     * @return array
     */
    public function setItemProductBundle(&$item, $product_model, $image_model, $options = array())
    {
        if (empty($item['bundle'])) {
            return $item;
        }

        $data = array(
            'status' => 1,
            'store_id' => $item['store_id'],
            'sku' => explode(',', $item['bundle'])
        );

        $products = $product_model->getList($data);
        $product_ids = array_keys($products);

        foreach ($products as &$product) {
            $this->setItemThumb($product, $image_model, array('id_key' => 'product_id', 'id_value' => $product_ids));
            $this->setItemRenderedBundleProduct($product, $options);
        }

        $item['bundle'] = $products;
        return $item;
    }

    /**
     * Sets rendered product bundled item
     * @param array $item
     * @param array $options
     * @return array
     */
    public function setItemRenderedBundleProduct(array &$item, array $options = array())
    {
        $options += array('template_item' => 'product/item/bundle');
        $this->setItemRendered($item, array('item' => $item), $options);
        return $item;
    }

    /**
     * Add a rendered content to the item
     * @param array $item
     * @param array $data
     * @param array $options
     * @return array
     */
    public function setItemRendered(array &$item, $data, $options = array())
    {
        if (!empty($options['template_item'])) {
            $item['rendered'] = $this->render($options['template_item'], $data, true);
        }

        return $item;
    }

    /**
     * Add a formatted price to the item
     * @param array $item
     * @param \gplcart\core\models\Price $price_model
     * @param string|null $currency
     * @return array
     */
    public function setItemPriceFormatted(array &$item, $price_model, $currency = null)
    {
        if (!isset($currency)) {
            $currency = $item['currency'];
        }

        $price = $price_model->convert($item['price'], $item['currency'], $currency);
        $item['price_formatted'] = $price_model->format($price, $currency);

        if (isset($item['original_price'])) {
            $price = $price_model->convert($item['original_price'], $item['currency'], $currency);
            $item['original_price_formatted'] = $price_model->format($price, $currency);
        }

        return $item;
    }

    /**
     * Add a calculated product price to the item
     * @param array $item
     * @param \gplcart\core\models\Product $product_model
     * @return array
     */
    public function setItemPriceCalculated(array &$item, $product_model)
    {
        $calculated = $product_model->calculate($item);

        if (empty($calculated)) {
            return $item;
        }

        if ($item['price'] != $calculated['total']) {
            $item['original_price'] = $item['price'];
        }

        $item['price'] = $calculated['total'];
        $item['price_rule_components'] = $calculated['components'];
        return $item;
    }

    /**
     * Sets boolean flag indicating that item's URL matches the current URL
     * @param array $item
     * @param string $base
     * @param string $path
     * @return array
     */
    public function setItemUrlActive(array &$item, $base, $path)
    {
        if (isset($item['url'])) {
            $item['active'] = substr($item['url'], strlen($base)) === $path;
        }

        return $item;
    }

    /**
     * Add indentation string indicating item's depth (only for categories)
     * @param array $item
     * @param string $char
     * @return array
     */
    public function setItemIndentation(array &$item, $char = '<span class="indentation"></span>')
    {
        if (isset($item['depth'])) {
            $item['indentation'] = str_repeat($char, $item['depth']);
        }

        return $item;
    }

}
