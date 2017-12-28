<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\phpunit\support;

use OutOfBoundsException;
use PHPUnit_Extensions_Database_DataSet_DefaultTable;
use PHPUnit_Extensions_Database_DataSet_AbstractDataSet;
use PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData;
use PHPUnit_Extensions_Database_DataSet_DefaultTableIterator;

/**
 * Implements the array data set
 * @link https://phpunit.de/manual/5.7/en/database.html
 */
class ArrayDataSet extends PHPUnit_Extensions_Database_DataSet_AbstractDataSet
{

    /**
     * An array of data tables
     * @var array
     */
    protected $tables = array();

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $table_name => $rows) {

            $columns = array();

            if (isset($rows[0])) {
                $columns = array_keys($rows[0]);
            }

            $meta_data = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData($table_name, $columns);
            $table = new PHPUnit_Extensions_Database_DataSet_DefaultTable($meta_data);

            foreach ($rows as $row) {
                $table->addRow($row);
            }

            $this->tables[$table_name] = $table;
        }
    }

    /**
     * Creates an iterator over the tables in the data set. If $reverse is true a reverse iterator will be returned
     * @param bool $reverse
     * @return PHPUnit_Extensions_Database_DataSet_DefaultTableIterator
     */
    protected function createIterator($reverse = false)
    {
        return new PHPUnit_Extensions_Database_DataSet_DefaultTableIterator($this->tables, $reverse);
    }

    /**
     * Returns a table object for the given table
     * @param string $table
     * @return PHPUnit_Extensions_Database_DataSet_DefaultTable
     * @throws OutOfBoundsException
     */
    public function getTable($table)
    {
        if (!isset($this->tables[$table])) {
            throw new OutOfBoundsException("$table is not a table in the current database");
        }

        return $this->tables[$table];
    }

}
