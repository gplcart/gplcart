<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains methods for adding keys with different data to an item
 */
trait Item
{

    /**
     * Adds "total_formatted" key
     * @param array $item
     * @param \gplcart\core\models\Price $price_model
     */
    public function setItemTotalFormatted(array &$item, $price_model)
    {
        $item['total_formatted'] = $price_model->format($item['total'], $item['currency']);
    }

    /**
     * Adds "total_formatted_number" key
     * @param array $item
     * @param \gplcart\core\models\Price $price_model
     */
    public function setItemTotalFormattedNumber(array &$item, $price_model)
    {
        $item['total_formatted_number'] = $price_model->format($item['total'], $item['currency'], true, false);
    }

    /**
     * Adds "thumb" key
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     * @param array $options
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
    }

    /**
     * Adds "thumb_placeholder" key
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     */
    public function setItemIsThumbPlaceholder(array &$item, $image_model)
    {
        if (!empty($item['thumb'])) {
            $item['thumb_placeholder'] = $image_model->isPlaceholder($item['thumb']);
        }
    }

    /**
     * Add thumbs to the cart item
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     */
    public function setItemCartThumb(array &$item, $image_model)
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
    }

    /**
     * Adds URL keys
     * @param array $item
     * @param array $options
     */
    public function setItemUrl(array &$item, array $options = array())
    {
        if (!empty($options['entity']) && !empty($item[$options['entity'] . '_id'])) {
            $entity = $options['entity'];
            $entity_id = $item["{$entity}_id"];
            $item['url'] = empty($item['alias']) ? $this->url("$entity/$entity_id") : $this->url($item['alias']);
            $query = $this->getQuery(null, array(), 'array');
            $item['url_query'] = empty($item['alias']) ? $this->url("$entity/$entity_id", $query) : $this->url($item['alias'], $query);
        }
    }

    /**
     * Adds entity URL
     * @param array $item
     * @param \gplcart\core\models\Store $store_model
     * @param string $entity
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
    }

    /**
     * Adds "rendered" key
     * @param array $item
     * @param array $data
     * @param array $options
     */
    public function setItemRendered(array &$item, $data, $options = array())
    {
        if (!empty($options['template_item'])) {
            $item['rendered'] = $this->render($options['template_item'], $data, true);
        }
    }

    /**
     * Add keys with formatted prices
     * @param array $item
     * @param \gplcart\core\models\Price $price_model
     * @param string|null $currency
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
    }

    /**
     * Adjust an original price according to applied price rules
     * @param array $item
     * @param \gplcart\core\models\Product $product_model
     */
    public function setItemPriceCalculated(array &$item, $product_model)
    {
        $calculated = $product_model->calculate($item);

        if (!empty($calculated)) {

            if ($item['price'] != $calculated['total']) {
                $item['original_price'] = $item['price'];
            }

            $item['price'] = $calculated['total'];
            $item['price_rule_components'] = $calculated['components'];
        }
    }

    /**
     * Add "active"
     * @param array $item
     * @param string $base
     * @param string $path
     */
    public function setItemUrlActive(array &$item, $base, $path)
    {
        if (isset($item['url'])) {
            $item['active'] = substr($item['url'], strlen($base)) === $path;
        }
    }

    /**
     * Add "indentation" key
     * @param array $item
     * @param string $char
     */
    public function setItemIndentation(array &$item, $char = '<span class="indentation"></span>')
    {
        if (isset($item['depth'])) {
            $item['indentation'] = str_repeat($char, $item['depth']);
        }
    }

    /**
     * Adds "shipping_name" key
     * @param array $item
     * @param \gplcart\core\models\Shipping $shipping_model
     */
    public function setItemShippingName(&$item, $shipping_model)
    {
        if (isset($item['shipping'])) {
            $data = $shipping_model->get($item['shipping']);
            $item['shipping_name'] = empty($data['title']) ? 'Unknown' : $data['title'];
        }
    }

    /**
     * Adds "payment_name" key
     * @param array $item
     * @param \gplcart\core\models\Payment $payment_model
     */
    public function setItemPaymentName(&$item, $payment_model)
    {
        if (isset($item['payment'])) {
            $data = $payment_model->get($item['payment']);
            $item['payment_name'] = empty($data['title']) ? 'Unknown' : $data['title'];
        }
    }

    /**
     * Adds "store_name" key
     * @param array $item
     * @param \gplcart\core\models\Store $store_model
     */
    public function setItemStoreName(&$item, $store_model)
    {
        if (isset($item['store_id'])) {
            $data = $store_model->get($item['store_id']);
            $item['store_name'] = empty($data['name']) ? 'Unknown' : $data['name'];
        }
    }

    /**
     * Adds an address information for the order item
     * @param array $order
     * @param \gplcart\core\models\Address $address_model
     */
    public function setItemAddress(&$order, $address_model)
    {
        $order['address'] = array();

        foreach (array('shipping', 'payment') as $type) {
            $address = $address_model->get($order["{$type}_address"]);
            if (!empty($address)) {
                $order['address'][$type] = $address;
                $order['address_translated'][$type] = $address_model->getTranslated($order['address'][$type], true);
            }
        }
    }

    /**
     * Adds product thumb(s)
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     */
    public function setItemProductThumb(array &$item, $image_model)
    {
        $options = array(
            'imagestyle' => $this->configTheme('image_style_product', 6));

        if (empty($item['images'])) {
            $item['images'][] = array('thumb' => $image_model->getPlaceholder($options['imagestyle']));
        } else {
            $this->setItemThumb($item, $image_model, $options);
        }
    }

    /**
     * Adds "in_comparison" key
     * @param array $item
     * @param \gplcart\core\models\ProductCompare $compare_model
     */
    public function setItemProductInComparison(array &$item, $compare_model)
    {
        $item['in_comparison'] = $compare_model->exists($item['product_id']);
    }

    /**
     * Adds "in_wishlist" key
     * @param array $item
     * @param string|int $user_id
     * @param int $store_id
     * @param \gplcart\core\models\Wishlist $wishlist_model
     */
    public function setItemProductInWishlist(&$item, $user_id, $store_id, $wishlist_model)
    {
        $conditions = array(
            'user_id' => $user_id,
            'store_id' => $store_id,
            'product_id' => $item['product_id']
        );

        $item['in_wishlist'] = $wishlist_model->exists($conditions);
    }

    /**
     * Adds "rendered" key containing rendered product item
     * @param array $item
     * @param array $options
     */
    public function setItemProductRendered(array &$item, $options = array())
    {
        if (!empty($options['template_item'])) {

            $options += array(
                'buttons' => array(
                    'cart_add', 'wishlist_add', 'compare_add'));

            $data = array(
                'item' => $item,
                'buttons' => $options['buttons']
            );

            $this->setItemRendered($item, $data, $options);
        }
    }

    /**
     * Adds "bundled_products" key
     * @param array $item
     * @param \gplcart\core\models\Product $product_model
     * @param \gplcart\core\models\Image $image_model
     * @param array $options
     */
    public function setItemProductBundle(&$item, $product_model, $image_model, $options = array())
    {
        if (!empty($item['bundle'])) {

            $data = array(
                'status' => 1,
                'store_id' => $item['store_id'],
                'sku' => explode(',', $item['bundle'])
            );

            $products = $product_model->getList($data);
            $product_ids = array_keys($products);

            foreach ($products as &$product) {
                $this->setItemThumb($product, $image_model, array('entity' => 'product', 'entity_id' => $product_ids));
                $this->setItemProductBundleRendered($product, $options);
            }

            $item['bundled_products'] = $products;
        }
    }

    /**
     * Sets rendered product bundled item
     * @param array $item
     * @param array $options
     */
    public function setItemProductBundleRendered(array &$item, array $options = array())
    {
        $options += array('template_item' => 'product/item/bundle');
        $this->setItemRendered($item, array('item' => $item), $options);
    }

    /**
     * Adds "fields" key
     * @param array $item
     * @param \gplcart\core\models\Image $imodel
     * @param \gplcart\core\models\ProductClass $pcmodel
     * @param string $type
     * @param array $options
     */
    public function setItemProductFieldType(&$item, $imodel, $pcmodel, $type, $options = [])
    {
        if (empty($item['field'][$type]) || empty($item['product_class_id'])) {
            return null;
        }

        $fields = $pcmodel->getFieldData($item['product_class_id']);

        foreach ($item['field'][$type] as $field_id => $field_values) {
            foreach ($field_values as $field_value_id) {

                $options += array(
                    'placeholder' => false,
                    'path' => $fields[$type][$field_id]['values'][$field_value_id]['path']
                );

                $this->setItemThumb($fields[$type][$field_id]['values'][$field_value_id], $imodel, $options);

                if (isset($fields[$type][$field_id]['values'][$field_value_id]['title'])) {
                    $item['field_value_labels'][$type][$field_id][$field_value_id] = $fields[$type][$field_id]['values'][$field_value_id]['title'];
                }
            }
        }

        $item['fields'][$type] = $fields[$type];
    }

    /**
     * Set a field data to the product item
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     * @param \gplcart\core\models\ProductClass $class_model
     * @param array $options
     */
    public function setItemProductFields(&$item, $image_model, $class_model, $options = array())
    {
        $this->setItemProductFieldType($item, $image_model, $class_model, 'option', $options);
        $this->setItemProductFieldType($item, $image_model, $class_model, 'attribute', $options);
    }

    /**
     * Set a data to product combinations
     * @param array $item
     * @param \gplcart\core\models\Image $image_model
     * @param \gplcart\core\models\Price $price_model
     */
    public function setItemProductCombination(array &$item, $image_model, $price_model)
    {
        if (!empty($item['combination'])) {
            foreach ($item['combination'] as &$combination) {
                $combination['path'] = $combination['thumb'] = '';
                if (!empty($item['images'][$combination['file_id']])) {
                    $combination['path'] = $item['images'][$combination['file_id']]['path'];
                    $this->setItemThumb($combination, $image_model);
                }
                // @todo reuse a trait
                $combination['price'] = $price_model->decimal($combination['price'], $item['currency']);
            }
        }
    }

    /**
     * Adds "status_name" key
     * @param array $item
     * @param \gplcart\core\models\Order $order_model
     */
    public function setItemOrderStatusName(&$item, $order_model)
    {
        if (isset($item['status'])) {
            $data = $order_model->getStatusName($item['status']);
            $item['status_name'] = empty($data) ? 'Unknown' : $data;
        }
    }

    /**
     * Adds "is_new" key
     * @param array $item
     * @param \gplcart\core\models\Order $order_model
     */
    public function setItemOrderNew(&$item, $order_model)
    {
        $item['is_new'] = $order_model->isNew($item);
    }

    /**
     * Adds a cart component information for the order item
     * @param array $item
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Price $price_model
     */
    public function setItemOrderCartComponent(&$item, $controller, $price_model)
    {
        if (!empty($item['data']['components']['cart']['items'])) {
            foreach ($item['data']['components']['cart']['items'] as $sku => $component) {
                if ($item['cart'][$sku]['product_store_id'] != $item['store_id']) {
                    $item['cart'][$sku]['product_status'] = 0;
                }
                $item['cart'][$sku]['price_formatted'] = $price_model->format($component['price'], $item['currency']);
            }

            $html = $controller->render('backend|sale/order/panes/components/cart', array('order' => $item));
            $item['data']['components']['cart']['rendered'] = $html;
        }
    }

    /**
     * Adds a shipping component information for the order item
     * @param array $item
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Price $pmodel
     * @param \gplcart\core\models\Shipping $shmodel
     * @param \gplcart\core\models\Order $omodel
     */
    public function setItemOrderShippingComponent(&$item, $controller, $pmodel, $shmodel, $omodel)
    {
        if (isset($item['data']['components']['shipping']['price'])) {

            $method = $shmodel->get($item['shipping']);
            $value = $item['data']['components']['shipping']['price'];

            if (abs($value) == 0) {
                $value = 0;
            }

            $method['price_formatted'] = $pmodel->format($value, $item['currency']);

            $data = array(
                'method' => $method,
                'title' => $omodel->getComponentType('shipping')
            );

            $html = $controller->render('backend|sale/order/panes/components/method', $data);
            $item['data']['components']['shipping']['rendered'] = $html;
        }
    }

    /**
     * Adds a payment component information for the order item
     * @param array $item
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Price $pmodel
     * @param \gplcart\core\models\Payment $pamodel
     * @param \gplcart\core\models\Order $omodel
     */
    public function setItemOrderPaymentComponent(&$item, $controller, $pmodel, $pamodel, $omodel)
    {
        if (isset($item['data']['components']['payment']['price'])) {

            $method = $pamodel->get($item['payment']);
            $value = $item['data']['components']['payment']['price'];

            if (abs($value) == 0) {
                $value = 0;
            }

            $method['price_formatted'] = $pmodel->format($value, $item['currency']);

            $data = array(
                'method' => $method,
                'title' => $omodel->getComponentType('payment')
            );

            $html = $controller->render('backend|sale/order/panes/components/method', $data);
            $item['data']['components']['payment']['rendered'] = $html;
        }
    }

    /**
     * Adds a price rule component information for the order item
     * @param array $item
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Price $pmodel
     * @param \gplcart\core\models\PriceRule $prmodel
     */
    public function setItemOrderPriceRuleComponent(&$item, $controller, $pmodel, $prmodel)
    {
        foreach ($item['data']['components'] as $price_rule_id => $component) {

            if (!is_numeric($price_rule_id)) {
                continue;
            }

            $price_rule = $prmodel->get($price_rule_id);

            if (abs($component['price']) == 0) {
                $component['price'] = 0;
            }

            $data = array(
                'rule' => $price_rule,
                'price' => $pmodel->format($component['price'], $price_rule['currency']));

            $html = $controller->render('backend|sale/order/panes/components/rule', $data);
            $item['data']['components'][$price_rule_id]['rendered'] = $html;
        }
    }

}
