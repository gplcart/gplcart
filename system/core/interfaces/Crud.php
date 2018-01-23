<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\interfaces;

/**
 * Constraint for CRUD (create, read, update, delete) models
 */
interface Crud
{

    /**
     * Returns a single entity
     * @param mixed $condition
     * @return array
     */
    public function get($condition);

    /**
     * Returns an array of entities
     * @param array $options
     * @return array|int
     */
    public function getList(array $options = array());

    /**
     * Adds an entity
     * @param array $data
     * @return int
     */
    public function add(array $data);

    /**
     * Deletes an entity
     * @param int $id
     * @return bool
     */
    public function delete($id);

    /**
     * Updates an entity
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data);
}