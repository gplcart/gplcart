<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\phpunit\support;

use PDO;
use PHPUnit_Extensions_Database_TestCase;
use PHPUnit_Extensions_Database_DataSet_CompositeDataSet;

/**
 * Helper class for testing database
 */
class Database extends PHPUnit_Extensions_Database_TestCase
{

    /**
     * PDO object
     * @var \PDO $pdo
     */
    static protected $pdo;

    /**
     * A new DefaultDatabaseConnection using the given PDO connection and database schema name
     * @var \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     */
    protected $conn;

    /**
     * Returns the test database connection
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        if (!isset($this->conn)) {
            if (!isset(self::$pdo)) {
                static::$pdo = new PDO('sqlite::memory:');
            }

            $this->conn = $this->createDefaultDBConnection(static::$pdo, ':memory:');
        }

        return $this->conn;
    }

    /**
     * Returns the test dataset
     * @param array $fixtures
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet(array $fixtures)
    {
        $dataset = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet;

        $dir = __DIR__ . '/fixtures';
        foreach ($fixtures as $fixture) {
            $path = "$dir/$fixture.xml";
            $dataset->addDataSet($this->createMySQLXMLDataSet($path));
        }

        return $dataset;
    }

    /**
     * Loads the appropriate XML fixtures combining them into a single DataSet that PHPUnit can use to insert records
     * @param \PHPUnit_Extensions_Database_DataSet_IDataSet $dataset
     */
    protected function loadDataSet($dataset)
    {
        $this->getDatabaseTester()->setDataSet($dataset);
        $this->getDatabaseTester()->onSetUp();
    }

}
