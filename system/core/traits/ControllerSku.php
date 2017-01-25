<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Methods for frontend controller
 */
trait ControllerSku
{

    /**
     * Returns an array of selected field option combination
     * @param \gplcart\core\controllers\frontend\Controller $controller
     * @param \gplcart\core\models\Sku $sku_model
     * @param array $product
     * @param array $field_value_ids
     * @return array
     */
    protected function getSelectedCombinationTrait(
    \gplcart\core\controllers\frontend\Controller $controller,
            \gplcart\core\models\Sku $sku_model, array $product,
            array $field_value_ids
    )
    {
        $response = $sku_model->selectCombination($product, $field_value_ids);

        $options = array(
            'calculate' => false,
            'imagestyle' => $controller->settings('image_style_product', 5),
            'path' => empty($response['combination']['path']) ? '' : $response['combination']['path']
        );

        $controller->setItemPrice($response, $options);
        $controller->setItemThumb($response, $options);

        return $response;
    }

}
