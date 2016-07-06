<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\job\search;

use core\models\Search;
use core\models\Product as P;

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
     * @param P $product
     * @param \core\handlers\job\search\Search $search
     */
    public function __construct(P $product, Search $search)
    {
        $this->search = $search;
        $this->product = $product;
    }

    /**
     * Processes one job iteration
     * @param array $job
     * @param string $operation_id
     * @param integer $done
     * @param array $context
     * @param array $options
     * @return array
     */
    public function process($job, $operation_id, $done, $context, $options)
    {
        $offset = isset($context['offset']) ? (int) $context['offset'] : 0;
        $items = $this->product->getList($options + array(
            'limit' => array($offset, $options['index_limit'])));

        if (!$items) {
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
    protected function index($products)
    {
        foreach ($products as $product) {
            $this->search->index('product_id', $product['product_id']);
        }
    }
}
