<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\handlers\job\export;

use core\models\Product as P;
use core\models\FieldValue;
use core\models\Price;
use core\models\Export;
use core\classes\Tool;

class Product
{

    /**
     * Export model instance
     * @var \core\models\Export $export
     */
    protected $export;

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
     * FieldValue model instance
     * @var \core\models\FieldValue $field_value
     */
    protected $field_value;

    /**
     * Constructor
     * @param P $product
     * @param Price $price
     * @param FieldValue $field_value
     * @param Export $export
     */
    public function __construct(P $product, Price $price, FieldValue $field_value, Export $export)
    {
        $this->product = $product;
        $this->price = $price;
        $this->field_value = $field_value;
        $this->export = $export;
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
        $operation = $options['operation'];

        $limit = $options['export_limit'];
        $offset = isset($context['offset']) ? (int) $context['offset'] : 0;
        $items = $this->product->getList($options + array('limit' => array($offset, $limit)));

        if (!$items) {
            return array('done' => $job['total']);
        }

        $result = $this->export($items, $options);
        $errors = $this->export->getErrors($result['errors'], $operation);

        $done = count($items);
        $offset += $done;

        return array(
            'done' => $done,
            'errors' => $errors['count'],
            'context' => array('offset' => $offset));
    }

    /**
     * Exports products to CSV file
     * @param array $products
     * @param array $options
     * @return array
     */
    protected function export($products, $options)
    {
        $errors = array();
        $file = $options['operation']['file'];
        $header = $options['operation']['csv']['header'];

        foreach ($products as $product) {
            $fields = $this->export->getFields($header, $product);

            if (isset($fields['price'])) {
                $fields['price'] = $this->price->decimal($fields['price'], $product['currency']);
            }

            Tool::writeCsv($file, $fields, $this->export->getCsvDelimiter());
        }

        return array('errors' => $errors);
    }
}
