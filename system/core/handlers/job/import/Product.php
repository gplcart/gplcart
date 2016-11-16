<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\job\import;

use core\models\Product as ModelsProduct;
use core\handlers\job\import\Base as BaseHandler;

/**
 * Imports products
 */
class Product extends BaseHandler
{

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Constructor
     * @param ModelsProduct $product
     */
    public function __construct(ModelsProduct $product)
    {
        parent::__construct();

        $this->product = $product;
    }

    /**
     * Processes one import iteration
     * @param array $job
     */
    public function process(array &$job)
    {
        $this->start($job);
        $this->import();
        $this->finish();
    }

    /**
     * Adds/updates an array of entities
     */
    protected function import()
    {
        foreach ($this->rows as $row) {

            $this->prepare($row);

            if ($this->validate()) {
                $this->set();
            }
        }
    }

    /**
     * Prepares a product data
     * @param array $row
     */
    protected function prepare(array $row)
    {
        parent::prepare($row);

        $this->prepareUserId();
        $this->prepareImages();
    }

    /**
     * Prepares images
     */
    protected function prepareImages()
    {
        if (!empty($this->data['images'])) {
            $this->data['images'] = $this->getImages($this->data['images']);
        }
    }

    /**
     * Prepares user ID
     */
    protected function prepareUserId()
    {
        if (empty($this->data['update']) && empty($this->data['user_id'])) {
            $this->data['user_id'] = $this->user->id();
        }
    }

    /**
     * Adds/updates a single entity
     */
    protected function set()
    {
        if (empty($this->data['update'])) {
            $result = (bool) $this->product->add($this->data);
            $this->job['inserted'] += (int) $result;
        } else {
            $result = (bool) $this->product->update($this->data['product_id'], $this->data);
            $this->job['updated'] += (int) $result;
        }
    }

}
