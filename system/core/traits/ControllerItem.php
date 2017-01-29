<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Controller methods to work with various items
 */
trait ControllerItem
{

    /**
     * @param \gplcart\core\Controller $controller
     * @param array $data
     * @param array $options
     */
    protected function setUrlTrait($controller, array &$data, array $options)
    {
        $id = $data[$options['id_key']];
        $entity = preg_replace('/_id$/', '', $options['id_key']);
        $data['url'] = empty($data['alias']) ? $controller->url("$entity/$id") : $controller->url($data['alias']);
    }

    /**
     * @param \gplcart\core\Controller $controller
     * @param array $product
     * @param array $options
     */
    protected function setRenderedProductTrait($controller, &$product, $options)
    {
        $options += array(
            'buttons' => array(
                'cart_add', 'wishlist_add', 'compare_add'));

        $data = array(
            'product' => $product,
            'buttons' => $options['buttons']
        );

        $this->setRenderedTrait($controller, $product, $data, $options);
    }

    /**
     * @param \gplcart\core\Controller $controller
     * @param array $item
     * @param array $data
     * @param array $options
     */
    protected function setRenderedTrait($controller, &$item, $data, $options)
    {
        $item['rendered'] = $controller->render($options['template_item'], $data);
    }

    /**
     * Sets formatted price
     * @param \gplcart\core\Controller $controller
     * @param array $product
     */
    protected function setFormattedPriceTrait($controller, &$item)
    {
        /* @var $price_model \gplcart\core\models\Price */
        $price_model = $controller->getInstance('price');
        $item['price_formatted'] = $price_model->format($item['price'], $item['currency']);
    }

    /**
     * @param \gplcart\core\Controller $controller
     * @param array $item
     */
    protected function setUrlActiveTrait($controller, array &$item)
    {
        $item['active'] = ($controller->base() . (string) $controller->isCurrentPath($item['url'])) !== '';
    }

    /**
     * @param array $item
     */
    protected function setIndentationTrait(array &$item)
    {
        $item['indentation'] = str_repeat('<span class="indentation"></span>', $item['depth']);
    }

}
