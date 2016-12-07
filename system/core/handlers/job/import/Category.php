<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\job\import;

use core\models\Category as CategoryModel;
use core\handlers\job\import\Base as BaseHandler;

/**
 * Imports categories
 */
class Category extends BaseHandler
{

    /**
     * Category model instance
     * @var \core\models\Category $category
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
     * Adds/updates a single entity
     * @return boolean
     */
    protected function set()
    {
        if (empty($this->data['update'])) {
            $result = (bool) $this->category->add($this->data);
            $this->job['inserted'] += (int) $result;
            return true;
        }

        $result = (bool) $this->category->update($this->data['category_id'], $this->data);
        $this->job['updated'] += (int) $result;
        return true;
    }

}
