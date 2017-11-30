<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\handlers\condition\Base as BaseHandler;

/**
 * Provides methods to check product bundle conditions
 */
class ProductBundle extends BaseHandler
{

    /**
     * Whether the product is a bundled item and its ID is in a list of allowed IDs
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function itemId(array $condition, array $data)
    {
        if (!isset($data['product_id']) || !isset($data['bundle'])) {
            return false;
        }

        if (!is_array($data['bundle'])) {
            $data['bundle'] = explode(',', (string) $data['bundle']);
        }

        return $this->compare($data['bundle'], $condition['value'], $condition['operator']);
    }

    /**
     * Whether the product has the given number of bundled items
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function itemCount(array $condition, array $data)
    {
        if (!isset($data['product_id']) || !isset($data['bundle'])) {
            return false;
        }

        if (!is_array($data['bundle'])) {
            $data['bundle'] = explode(',', (string) $data['bundle']);
        }

        return $this->compare(count($data['bundle']), $condition['value'], $condition['operator']);
    }

}
