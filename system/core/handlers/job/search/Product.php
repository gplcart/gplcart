<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\job\search;

use core\models\Search as ModelsSearch;
use core\models\Product as ModelsProduct;

/**
 * Provides methods to index products
 */
class Product
{

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Search model instance
     * @var \core\models\Search $search
     */
    protected $search;

    /**
     * Constructor
     * @param ModelsProduct $product
     * @param ModelsSearch $search
     */
    public function __construct(ModelsProduct $product, ModelsSearch $search)
    {
        $this->search = $search;
        $this->product = $product;
    }

    /**
     * Processes one job iteration
     * @param array $job
     * @param integer $done
     * @param array $context
     * @param array $options
     * @return array
     */
    public function process(array $job, $done, array $context)
    {
        $options = $job['data'];
        $offset = isset($context['offset']) ? (int) $context['offset'] : 0;
        $items = $this->product->getList($options + array(
            'limit' => array($offset, $options['index_limit'])));

        if (empty($items)) {
            return array('done' => $job['total']);
        }

        $this->index($items);
        $done = count($items);
        $offset += $done;

        return array('done' => $done, 'context' => array('offset' => $offset));
    }

    /**
     * Indexes products
     * @param array $products
     */
    protected function index(array $products)
    {
        foreach ($products as $product) {
            $this->search->index('product_id', $product['product_id']);
        }
    }

}
