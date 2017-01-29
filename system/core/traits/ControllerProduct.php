<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Controller methods to work with products
 */
trait ControllerProduct
{

    /**
     * Set product calculated price
     * @param \gplcart\core\Controller $controller
     * @param array $product
     * @param array $options
     */
    protected function setProductCalculatedPriceTrait($controller, &$product)
    {
        /* @var $product_model \gplcart\core\models\Product */
        $product_model = $controller->getInstance('product');

        $calculated = $product_model->calculate($product, $controller->store('store_id'));
        $product['price'] = $calculated['total'];
    }

}
