<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\job\export;

use core\models\Price as PriceModel;
use core\models\Product as ProductModel;
use core\handlers\job\export\Base as BaseHandler;

/**
 * Product export handler
 */
class Product extends BaseHandler
{

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Constructor
     * @param ProductModel $product
     * @param PriceModel $price
     */
    public function __construct(ProductModel $product, PriceModel $price)
    {
        parent::__construct();

        $this->price = $price;
        $this->product = $product;
    }

    /**
     * Processes one job iteration
     * @param array $job
     */
    public function process(array &$job)
    {
        $this->start($job)->export()->finish();
    }

    /**
     * Exports products to the CSV file
     */
    protected function export()
    {
        $options = $this->job['data']['options'];
        $options += array('limit' => array($this->offset, $this->limit));
        $this->items = $this->product->getList($options);

        foreach ($this->items as $item) {
            $data = $this->getData($item);
            $this->prepare($data, $item);
            $this->write($data);
        }

        return $this;
    }

    /**
     * Returns a total number of products to be imported
     * @param array $options
     * @return integer
     */
    public function total(array $options)
    {
        $options['count'] = true;
        return $this->product->getList($options);
    }

    /**
     * Prepares export data
     * @param array $data
     * @param array $item
     */
    protected function prepare(array &$data, array $item)
    {
        $this->attachImages($data, $item);
        $this->preparePrice($data, $item);
        $this->prepareImages($data, $item);
    }

    /**
     * Attaches product images
     * @param array $data
     * @param array $item
     */
    protected function attachImages(array &$data, array $item)
    {
        $images = $this->image->getList('product_id', $item['product_id']);
        if (!empty($images)) {
            $data['images'] = $images;
        }
    }

    /**
     * Prepares prices
     * @param array $data
     * @param array $item
     */
    protected function preparePrice(array &$data, array $item)
    {
        if (isset($data['price'])) {
            $data['price'] = $this->price->decimal($data['price'], $item['currency']);
        }
    }

}
