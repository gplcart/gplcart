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
     * Sets a URL to the item considering its possible alias
     * @param \gplcart\core\Controller $controller
     * @param array $data
     * @param array $options
     */
    protected function setItemUrlTrait($controller, array &$data, array $options)
    {
        $id = $data[$options['id_key']];
        $entity = preg_replace('/_id$/', '', $options['id_key']);
        $data['url'] = empty($data['alias']) ? $controller->url("$entity/$id") : $controller->url($data['alias']);
    }

    /**
     * Sets to the item its rendered HTML
     * @param \gplcart\core\Controller $controller
     * @param array $product
     * @param array $options
     */
    protected function setItemRenderedProductTrait($controller, array &$product,
            array $options)
    {
        $options += array(
            'buttons' => array(
                'cart_add', 'wishlist_add', 'compare_add'));

        $data = array(
            'product' => $product,
            'buttons' => $options['buttons']
        );

        $this->setItemRenderedTrait($controller, $product, $data, $options);
    }

    /**
     * Adds to the item rendered HTML
     * @param \gplcart\core\Controller $controller
     * @param array $item
     * @param array $data
     * @param array $options
     */
    protected function setItemRenderedTrait($controller, array &$item,
            array $data, array $options)
    {
        $item['rendered'] = $controller->render($options['template_item'], $data);
    }

}
