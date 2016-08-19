<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Product as ModelsProduct;
use core\models\Language as ModelsLanguage;

/**
 * Provides methods to validate various database related data
 */
class Product
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Alias model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsProduct $product
     */
    public function __construct(ModelsLanguage $language, ModelsProduct $product)
    {
        $this->product = $product;
        $this->language = $language;
    }

    /**
     * Checks if a product in the database
     * @param string $value
     * @param array $options
     * @return boolean|string
     */
    public function exists($value, array $options = array())
    {
        if (empty($value) && empty($options['required'])) {
            return true;
        }
        
        $product = $this->product->get($value);
        
        if (empty($product)) {
            return $this->language->text('Product does not exist');
        }
        
        return array('result' => $product);
    }

}
