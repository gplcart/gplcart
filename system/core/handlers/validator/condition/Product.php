<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\models\Category as CategoryModel;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\Sku as SkuModel;
use gplcart\core\models\Translation as TranslationModel;

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
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

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
     * @param ProductModel $product
     * @param CategoryModel $category
     * @param TranslationModel $translation
     * @param SkuModel $sku
     */
    public function __construct(ProductModel $product, CategoryModel $category,
                                TranslationModel $translation, SkuModel $sku)
    {
        $this->sku = $sku;
        $this->product = $product;
        $this->category = $category;
        $this->translation = $translation;
    }

    /**
     * Validates the product ID condition
     * @param array $values
     * @return boolean|string
     */
    public function id(array $values)
    {
        $count = count($values);
        $ids = array_filter($values, 'ctype_digit');

        if ($count != count($ids)) {
            return $this->translation->text('@field has invalid value', array(
                '@field' => $this->translation->text('Condition')));
        }

        $existing = array_filter($values, function ($product_id) {
            $product = $this->product->get($product_id);
            return isset($product['product_id']);
        });

        if ($count != count($existing)) {
            return $this->translation->text('@name is unavailable', array(
                '@name' => $this->translation->text('Product')));
        }

        return true;
    }

    /**
     * Validates the category ID condition
     * @param array $values
     * @return boolean|string
     */
    public function categoryId(array $values)
    {
        $count = count($values);
        $ids = array_filter($values, 'ctype_digit');

        if ($count != count($ids)) {
            return $this->translation->text('@field has invalid value', array(
                '@field' => $this->translation->text('Condition')));
        }

        $existing = array_filter($values, function ($category_id) {
            $category = $this->category->get($category_id);
            return isset($category['category_id']);
        });

        if ($count != count($existing)) {
            return $this->translation->text('@name is unavailable', array(
                '@name' => $this->translation->text('Category')));
        }

        return true;
    }

    /**
     * Validates the SKU condition
     * @param array $values
     * @return boolean|string
     */
    public function sku(array $values)
    {
        $count = count($values);

        $existing = array_filter($values, function ($sku) {
            $sku = $this->sku->get(array('sku' => $sku));
            return !empty($sku);
        });

        if ($count != count($existing)) {
            return $this->translation->text('@name is unavailable', array(
                '@name' => $this->translation->text('SKU')));
        }

        return true;
    }

}
