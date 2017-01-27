<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Controller methods related to products
 */
trait ControllerProduct
{

    /**
     * Sets product formatted price
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Price $price_model
     * @param \gplcart\core\models\Product $product_model
     * @param array $product
     * @param array $options
     */
    protected function setProductPriceTrait($controller, $price_model, $product_model,
            array &$product, array $options = array())
    {
        $options += array('calculate' => true);

        if (!empty($options['calculate'])) {
            //$calculated = $this->product->calculate($product, $this->store_id);
            //$product['price'] = $calculated['total'];
        }

        $product['price_formatted'] = $price_model->format($product['price'], $product['currency']);
    }

}
