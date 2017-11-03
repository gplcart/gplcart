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
use gplcart\tests\phpunit\support\Tool as ToolHelper;
use gplcart\tests\phpunit\support\File as FileHelper;

class PhpUnitTest extends PHPUnit_Extensions_Database_TestCase
{

    /**
     * File helper class instance
     * @var \gplcart\tests\phpunit\support\File $file
     */
    protected $file;

    /**
     * Tool helper class instance
     * @var \gplcart\tests\phpunit\support\Tool $tool
     */
    protected $tool;

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
     * An array of fixtures
     * @var array
     */
    protected $fixtures = array();

    /**
     * @param null|string $name
     * @param array $data
     * @param string $dname
     */
    public function __construct($name = null, array $data = array(), $dname = '')
    {
        parent::__construct($name, $data, $dname);

        $this->file = new FileHelper;
        $this->tool = new ToolHelper;
    }

    /**
     * Outputs debugging data
     * @param mixed $data
     */
    protected function dump($data)
    {
        $this->expectOutputString('');
        print_r($data);
    }

    /**
     * Returns the test database connection
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        if (isset($this->conn)) {
            return $this->conn;
        }

        if (!isset(self::$pdo)) {
            static::$pdo = new PDO('sqlite::memory:');
        }

        $this->conn = $this->createDefaultDBConnection(static::$pdo, ':memory:');
        return $this->conn;
    }

    /**
     * Returns the test dataset
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        $dataset = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet;

        foreach ($this->fixtures as $fixture) {
            $dataset->addDataSet($this->createFixtureDataSet($fixture));
        }

        return $dataset;
    }

    /**
     * Creates a dataset for the fixture
     * @param string $fixture
     * @return \PHPUnit_Extensions_Database_DataSet_XmlDataSet
     * @throws \InvalidArgumentException
     */
    protected function createFixtureDataSet($fixture)
    {
        $dir = __DIR__ . '/fixtures';
        $file = "$dir/$fixture.xml";

        if (!is_file($file)) {
            throw new \InvalidArgumentException("File '$file' not found for fixture '$fixture'");
        }

        return $this->createXmlDataSet($file);
    }

    /**
     * Assets that fixture table equals to the fixture source
     * @param string $fixture
     */
    protected function assertFixtureTable($fixture)
    {
        $actual = $this->getConnection()->createQueryTable($fixture, "SELECT * FROM $fixture");
        $expected = $this->createFixtureDataSet($fixture)->getTable($fixture);
        $this->assertTablesEqual($expected, $actual, "Table for fixture $fixture does not match expected structure");
    }

    /**
     * Performs assertions shared by all tests of a test case
     */
    protected function assertPreConditions()
    {
        foreach ($this->fixtures as $fixture) {
            $this->assertFixtureTable($fixture);
        }
    }

    /**
     * Set up test database using fixture(s)
     * @param string|array $fixtures
     * @return \PDO
     */
    protected function setUpTestDatabase($fixtures)
    {
        $this->fixtures = (array) $fixtures;

        $dataset = $this->getDataSet();
        $pdo = $this->getConnection()->getConnection();

        foreach ($dataset->getTableNames() as $table) {

            $pdo->exec("DROP TABLE IF EXISTS $table;");
            $meta = $dataset->getTableMetaData($table);
            $create = "CREATE TABLE IF NOT EXISTS $table ";

            $cols = array();
            foreach ($meta->getColumns() as $col) {
                $cols[] = "$col VARCHAR(255)";
            }

            $create .= '(' . implode(',', $cols) . ');';
            $pdo->exec($create);
        }

        parent::setUp();

        return $pdo;
    }

    /**
     * Clear up database
     */
    protected function tearDown()
    {
        if (!empty($this->fixtures)) {
            $pdo = $this->getConnection()->getConnection();
            foreach ($this->getDataSet()->getTableNames() as $table) {
                $pdo->exec("DROP TABLE IF EXISTS $table;");
            }

            parent::tearDown();
        }
        
        //$this->getMockBuilder($className)->
    }

}
