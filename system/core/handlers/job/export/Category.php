<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\job\export;

use gplcart\core\models\Category as CategoryModel;
use gplcart\core\handlers\job\export\Base as BaseHandler;

/**
 * Category export handler
 */
class Category extends BaseHandler
{

    /**
     * Category model instance
     * @var \gplcart\core\models\Category $category
     */
    protected $category;

    /**
     * Constructor
     * @param CategoryModel $category
     */
    public function __construct(CategoryModel $category)
    {
        parent::__construct();

        $this->category = $category;
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
     * Exports categories to the CSV file
     */
    protected function export()
    {
        $options = $this->job['data']['options'];
        $options += array('limit' => array($this->offset, $this->limit));

        $this->items = (array) $this->category->getList($options);

        foreach ($this->items as $item) {
            $data = $this->getData($item);
            $this->prepare($data, $item);
            $this->write($data);
        }

        return $this;
    }

    /**
     * Returns a total number of categories to be imported
     * @param array $options
     * @return integer
     */
    public function total(array $options)
    {
        $options['count'] = true;
        return $this->category->getList($options);
    }

    /**
     * Prepares data before exporting
     * @param array $data
     * @param array $item
     */
    protected function prepare(array &$data, array $item)
    {
        $this->attachImages($data, $item);
        $this->prepareImages($data, $item);
    }

    /**
     * Attaches category images
     * @param array $data
     * @param array $item
     */
    protected function attachImages(array &$data, array $item)
    {
        $options = array('order' => 'asc', 'sort' => 'weight', 'file_type' => 'image',
            'id_key' => 'category_id', 'id_value' => $item['category_id']);

        $data['images'] = $this->file->getList($options);
    }

}
