<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\job\export;

use core\classes\Tool;
use core\models\Price as ModelsPrice;
use core\models\Export as ModelsExport;
use core\models\Product as ModelsProduct;
use core\models\FieldValue as ModelsFieldValue;

/**
 * Provides methods to export products
 */
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
     * @param ModelsProduct $product
     * @param ModelsPrice $price
     * @param ModelsFieldValue $field_value
     * @param ModelsExport $export
     */
    public function __construct(ModelsProduct $product, ModelsPrice $price,
            ModelsFieldValue $field_value, ModelsExport $export)
    {
        $this->price = $price;
        $this->export = $export;
        $this->product = $product;
        $this->field_value = $field_value;
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
    public function process(array $job, $operation_id, $done, array $context,
            array $options)
    {
        $operation = $options['operation'];

        $limit = $options['export_limit'];
        $offset = isset($context['offset']) ? (int) $context['offset'] : 0;
        $items = $this->product->getList($options + array('limit' => array($offset, $limit)));

        if (empty($items)) {
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
    protected function export(array $products, array $options)
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
