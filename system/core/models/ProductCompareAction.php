<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook;
use gplcart\core\helpers\Url as UrlHelper;
use gplcart\core\models\Translation as TranslationModel,
    gplcart\core\models\ProductCompare as ProductCompareModel;

/**
 * Manages basic behaviors and data related to product comparison
 */
class ProductCompareAction
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * URL class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Product compare model instance
     * @var \gplcart\core\models\ProductCompare $compare
     */
    protected $compare;

    /**
     * @param Hook $hook
     * @param ProductCompareModel $compare
     * @param TranslationModel $translation
     * @param UrlHelper $url
     */
    public function __construct(Hook $hook, ProductCompareModel $compare,
                                TranslationModel $translation, UrlHelper $url)
    {
        $this->url = $url;
        $this->hook = $hook;
        $this->compare = $compare;
        $this->translation = $translation;
    }

    /**
     * Adds a product to comparison and returns an array of results
     * @param array $product
     * @param array $data
     * @return array
     */
    public function add(array $product, array $data)
    {
        $result = array();
        $this->hook->attach('product.compare.add.product.before', $product, $data, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        if (!$this->compare->add($product['product_id'])) {
            return $this->getResultError();
        }

        $result = $this->getResultAdded();
        $this->hook->attach('product.compare.add.product.after', $product, $data, $result, $this);
        return (array) $result;
    }

    /**
     * Removes a product from comparison and returns an array of result data
     * @param integer $product_id
     * @return array
     */
    public function delete($product_id)
    {
        $result = null;
        $this->hook->attach('product.compare.delete.product.before', $product_id, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        if (!$this->compare->delete($product_id)) {
            return $this->getResultError();
        }

        $result = $this->getResultDeleted();
        $this->hook->attach('product.compare.delete.product.after', $product_id, $result, $this);
        return (array) $result;
    }

    /**
     * Returns an array of resulting data when a product has been deleted from comparison
     * @return array
     */
    protected function getResultDeleted()
    {
        return array(
            'redirect' => '',
            'severity' => 'success',
            'quantity' => count($this->compare->getList()),
            'message' => $this->translation->text('Product has been removed from comparison')
        );
    }

    /**
     * Returns an array of resulting data when a product has been added to comparison
     * @return array
     */
    protected function getResultAdded()
    {
        $quantity = count($this->compare->getList());

        if ($quantity < $this->compare->getLimit()) {
            $quantity++;
        }

        $message = $this->translation->text('Product has been added to <a href="@url">comparison</a>', array(
            '@url' => $this->url->get('compare')));

        return array(
            'redirect' => '',
            'severity' => 'success',
            'quantity' => $quantity,
            'message' => $message
        );
    }

    /**
     * Returns an array of resulting data when an error has occurred
     * @return array
     */
    protected function getResultError()
    {
        return array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->translation->text('An error occured')
        );
    }

}
