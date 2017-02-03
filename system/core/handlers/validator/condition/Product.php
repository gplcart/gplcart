<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\models\Sku as SkuModel;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\Language as LanguageModel;
use gplcart\core\models\Category as CategoryModel;

/**
 * Contains methods to validate product conditions
 */
class Product
{

    /**
     * SKU model instance
     * @var \gplcart\core\models\Sku $sku
     */
    protected $sku;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Category model instance
     * @var \gplcart\core\models\Category $category
     */
    protected $category;

    /**
     * Constructor
     * @param ProductModel $product
     * @param CategoryModel $category
     * @param LanguageModel $language
     * @param SkuModel $sku
     */
    public function __construct(ProductModel $product, CategoryModel $category,
            LanguageModel $language, SkuModel $sku)
    {
        $this->sku = $sku;
        $this->product = $product;
        $this->category = $category;
        $this->language = $language;
    }

    /**
     * Validates a product ID condition
     * @param array $values
     * @return boolean|string
     */
    public function id(array $values)
    {
        $count = count($values);
        $ids = array_filter($values, 'ctype_digit');

        if ($count != count($ids)) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $exists = array_filter($values, function ($product_id) {
            $product = $this->product->get($product_id);
            return isset($product['product_id']);
        });

        if ($count != count($exists)) {
            $vars = array('@name' => $this->language->text('Product'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates a product category ID condition
     * @param array $values
     * @return boolean|string
     */
    public function categoryId(array $values)
    {
        $count = count($values);
        $ids = array_filter($values, 'ctype_digit');

        if ($count != count($ids)) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $exists = array_filter($values, function ($category_id) {
            $category = $this->category->get($category_id);
            return isset($category['category_id']);
        });

        if ($count != count($exists)) {
            $vars = array('@name' => $this->language->text('Category'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates a product SKU condition
     * @param array $values
     * @return boolean|string
     */
    public function sku(array $values)
    {
        $count = count($values);

        $exists = array_filter($values, function ($sku) {
            $sku = $this->sku->get($sku);
            return !empty($sku);
        });

        if ($count != count($exists)) {
            $vars = array('@name' => $this->language->text('SKU'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

}
