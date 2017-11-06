<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\phpunit\support;

use PHPUnit_Extensions_Database_TestCase;
use gplcart\core\Database as SystemDatabase;
use gplcart\tests\phpunit\support\Tool as ToolHelper;
use gplcart\tests\phpunit\support\File as FileHelper;
use gplcart\tests\phpunit\support\ArrayDataSet as ArrayDataSetHelper;

/**
 * A TestCase extension that provides extra functionality for testing GPLCart
 */
class PhpUnitTest extends PHPUnit_Extensions_Database_TestCase
{

    /**
     * System database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

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
     * Returns a class instance with auto-mocked class dependencies
     * @param syring $class
     * @param array $mock_config
     * @return object
     * @throws \ReflectionException
     */
    protected function getInstance($class, array $mock_config = array())
    {
        if (!class_exists($class)) {
            throw new \ReflectionException("Class $class does not exist");
        }

        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (empty($constructor)) {
            return new $class;
        }

        $parameters = $constructor->getParameters();

        if (empty($parameters)) {
            return new $class;
        }

        $mock_config = array_change_key_case($mock_config);

        $dependencies = array();
        foreach ($parameters as $pos => $parameter) {

            /* @var $param_reflection \ReflectionClass */
            $param_reflection = $parameter->getClass();
            $param_class_name = strtolower($param_reflection->getName());
            $config = empty($mock_config[$param_class_name]) ? array() : $mock_config[$param_class_name];

            if (empty($config['object'])) {
                $dependencies[$pos] = $this->getMockFromConfig($param_reflection, $config);
            } else {
                $dependencies[$pos] = $config['object'];
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Create a mock object for a class
     * @param \ReflectionClass $reflection
     * @param array $config
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockFromConfig($reflection, array $config = array())
    {
        $config += array(
            'disable_clone' => true,
            'disable_autoload' => true,
            'disable_constructor' => true,
            'constructor_arguments' => null
        );

        $method_names = array();
        /* @var $method \ReflectionMethod */
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $method_name = $method->getName();
            if (strpos($method_name, '__') !== 0) { // Exclude __construct() etc
                $method_names[] = $method_name;
            }
        }

        $builder = $this->getMockBuilder($reflection->getName())->setMethods($method_names);

        if (isset($config['constructor_arguments'])) {
            $builder->setConstructorArgs($config['constructor_arguments']);
        }

        if ($config['disable_constructor']) {
            $builder->disableOriginalConstructor();
        }

        if ($config['disable_clone']) {
            $builder->disableOriginalClone();
        }

        if ($config['disable_autoload']) {
            $builder->disableAutoload();
        }

        $mock = $builder->getMock();

        if (empty($config['methods'])) {
            return $mock;
        }

        foreach ($config['methods'] as $method => $params) {
            $params += array('expects' => $this->any(), 'with' => array(), 'will' => null);
            /* @var $mocker \PHPUnit_Framework_MockObject_Builder_InvocationMocker */
            $mocker = $mock->expects($params['expects'])->method($method);
            $mocker->withConsecutive($params['with']);
            if (isset($params['will'])) {
                $mocker->will($params['will']);
            } else if (array_key_exists('return', $params)) {
                $mocker->will($this->returnValue($params['return']));
            }
        }

        return $mock;
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
            static::$pdo = new \PDO('sqlite::memory:');
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
        $dataset = new \PHPUnit_Extensions_Database_DataSet_CompositeDataSet;

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
        $file = $this->getFixtureDirectory() . "/$fixture.php";

        if (!is_file($file)) {
            throw new \InvalidArgumentException("File '$file' not found for fixture '$fixture'");
        }

        $data = require $file;
        return new ArrayDataSetHelper(array($fixture => $data));
    }

    /**
     * Returns path to fixture directory
     * @return string
     */
    protected function getFixtureDirectory()
    {
        return __DIR__ . '/fixtures';
    }

    /**
     * Returns an array of all available fixture files
     * @param bool $only_filenames
     * @return array
     */
    protected function scanFixtures($only_filenames = true)
    {
        $files = glob($this->getFixtureDirectory() . "/*.php");

        if (!$only_filenames) {
            return $files;
        }

        $names = array();
        foreach ($files as $file) {
            $names[] = pathinfo($file, PATHINFO_FILENAME);
        }

        return $names;
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
     * @param string|array|null $fixtures
     * @return \PDO
     */
    protected function setTestDatabase($fixtures = null)
    {
        if (!isset($fixtures)) {
            $fixtures = $this->scanFixtures();
        }

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

        return $pdo;
    }

    /**
     * Set up system database class
     * @param string|array|null $fixtures
     */
    protected function setSystemDatabase($fixtures = null)
    {
        $this->db = new SystemDatabase;
        $this->db->set($this->setTestDatabase($fixtures));
        return $this->db;
    }

    /**
     * Clear up database
     */
    protected function dropTestDatabase()
    {
        if (!empty($this->fixtures)) {
            $pdo = $this->getConnection()->getConnection();
            foreach ($this->getDataSet()->getTableNames() as $table) {
                $pdo->exec("DROP TABLE IF EXISTS $table;");
            }
        }
    }

}
