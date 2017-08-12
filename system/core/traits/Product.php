<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains product controller methods
 */
trait Product
{

    /**
     * Attach prepared fields to a product
     * @param array $product
     * @param \gplcart\core\models\ProductClass $product_model
     * @param \gplcart\core\Controller $controller
     */
    protected function setProductFieldsTrait(array &$product, $product_model,
            $controller)
    {
        $fields = $product_model->getFieldData($product['product_class_id']);
        $this->prepareProductFieldsTrait($product, $fields, 'option', $controller);
        $this->prepareProductFieldsTrait($product, $fields, 'attribute', $controller);
    }

    /**
     * Prepare an array of product field data
     * @param array $product
     * @param array $fields
     * @param string $type
     * @param \gplcart\core\Controller $controller
     */
    protected function prepareProductFieldsTrait(array &$product, array $fields,
            $type, $controller)
    {
        if (empty($product['field'][$type])) {
            return null;
        }

        $imagestyle = $controller->settings('image_style_option', 1);

        foreach ($product['field'][$type] as $field_id => $field_values) {
            foreach ($field_values as $field_value_id) {

                $options = array(
                    'imagestyle' => $imagestyle,
                    'placeholder' => false,
                    'path' => $fields[$type][$field_id]['values'][$field_value_id]['path']
                );

                $controller->setItemThumb($fields[$type][$field_id]['values'][$field_value_id], $options);

                if (isset($fields[$type][$field_id]['values'][$field_value_id]['title'])) {
                    $product['field_value_labels'][$type][$field_id][$field_value_id] = $fields[$type][$field_id]['values'][$field_value_id]['title'];
                }
            }
        }

        $product['fields'][$type] = $fields[$type];
    }

}
