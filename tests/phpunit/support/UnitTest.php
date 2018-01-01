<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\phpunit\support;

use PDO;
use ReflectionClass,
    ReflectionMethod;
use PHPUnit_Extensions_Database_TestCase,
    PHPUnit_Extensions_Database_DataSet_CompositeDataSet;
use gplcart\tests\phpunit\support\Tool as ToolHelper;
use gplcart\tests\phpunit\support\File as FileHelper;
use gplcart\tests\phpunit\support\ArrayDataSet as ArrayDataSetHelper;
use gplcart\core\Database as SystemDatabase;
use InvalidArgumentException,
    gplcart\core\exceptions\Dependency as DependencyException;

/**
 * A TestCase extension that provides extra functionality for testing GPLCart
 */
class UnitTest extends PHPUnit_Extensions_Database_TestCase
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
    protected static $pdo;

    /**
     * An array of fixtures
     * @var array
     */
    protected $fixtures = array();

    /**
     * A new DefaultDatabaseConnection using the given PDO connection and database schema name
     * @var \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     */
    protected $connection;

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
     * @param string $class
     * @param array $mock_config
     * @return object
     * @throws DependencyException
     */
    protected function getInstance($class, array $mock_config = array())
    {
        if (!class_exists($class)) {
            throw new DependencyException("Class $class does not exist");
        }

        $reflection = new ReflectionClass($class);
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

            if (is_object($config)) {
                $dependencies[$pos] = $config;
            } else {
                $dependencies[$pos] = $this->getMockFromConfig($param_reflection, $config);
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
            'clone' => false,
            'autoload' => false,
            'constructor' => false,
            'constructor_arguments' => null
        );

        $method_names = array();
        /* @var $method \ReflectionMethod */
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $method_name = $method->getName();
            if (strpos($method_name, '__') !== 0) { // Exclude __construct() etc
                $method_names[] = $method_name;
            }
        }

        $builder = $this->getMockBuilder($reflection->getName())->setMethods($method_names);

        if (isset($config['constructor_arguments'])) {
            $builder->setConstructorArgs($config['constructor_arguments']);
        }

        if (empty($config['constructor'])) {
            $builder->disableOriginalConstructor();
        }

        if (empty($config['clone'])) {
            $builder->disableOriginalClone();
        }

        if (empty($config['autoload'])) {
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
            } else if (!empty($params['callback'])) {
                $mocker->will($this->returnCallback($params['callback']));
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
        if (isset($this->connection)) {
            return $this->connection;
        }

        if (!isset(static::$pdo)) {
            static::$pdo = new PDO('sqlite::memory:');
        }

        $this->connection = $this->createDefaultDBConnection(static::$pdo, ':memory:');
        return $this->connection;
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
     * Creates a dataset object from the fixture
     * @param string $fixture
     * @return \PHPUnit_Extensions_Database_DataSet_XmlDataSet
     * @throws \InvalidArgumentException
     */
    protected function createFixtureDataSet($fixture)
    {
        return new ArrayDataSetHelper(array($fixture => $this->getFixtureData($fixture)));
    }

    /**
     * Returns an array of fixture data
     * @param string $fixture
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getFixtureData($fixture)
    {
        static $fixtures = array();

        if (isset($fixtures[$fixture])) {
            return $fixtures[$fixture];
        }

        $file = $this->getFixtureDirectory() . "/$fixture.php";

        if (!is_file($file)) {
            throw new InvalidArgumentException("File '$file' not found for fixture '$fixture'");
        }

        $fixtures[$fixture] = require $file;
        return $fixtures[$fixture];
    }

    /**
     * Remove first auto-incremented field from the fixture data
     * @param array $data
     * @return array
     */
    protected function removeFixtureAutoincrementField(array &$data)
    {
        foreach ($data as $pos => $row) {
            $index = 0;
            foreach ($row as $field => $value) {
                if ($index == 0 && strpos($field, '_id') !== false && is_integer($value)) {
                    unset($data[$pos][$field]);
                }
                $index++;
            }
        }

        return $data;
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
     * @return array
     */
    protected function scanFixtures()
    {
        $fixtures = array();
        foreach (glob($this->getFixtureDirectory() . "/*.php") as $file) {
            $fixtures[] = pathinfo($file, PATHINFO_FILENAME);
        }

        return $fixtures;
    }

    /**
     * Create test database using fixture(s)
     * @param string|array|null $fixtures
     * @return \PDO
     */
    protected function createDbFromFixture($fixtures = null)
    {
        if (!isset($fixtures)) {
            $fixtures = $this->scanFixtures();
        }

        $this->fixtures = (array) $fixtures;

        $dataset = $this->getDataSet();
        $pdo = $this->getConnection()->getConnection();

        foreach ($dataset->getTableNames() as $table) {

            $data = $this->getFixtureData($table);

            $pdo->exec("DROP TABLE IF EXISTS $table;");
            $meta = $dataset->getTableMetaData($table);
            $create = "CREATE TABLE IF NOT EXISTS $table ";

            $index = 0;
            $cols = array();
            foreach ($meta->getColumns() as $col) {
                $cols[$index] = "$col VARCHAR(255)";
                if ($index == 0 && isset($data[0][$col]) && strpos($col, '_id') !== false && is_integer($data[0][$col])) {
                    $cols[$index] = "$col INTEGER NOT NULL PRIMARY KEY";
                }
                $index++;
            }

            $create .= '(' . implode(',', $cols) . ');';
            $pdo->exec($create);
        }

        return $pdo;
    }

    /**
     * Returns the system database class
     * @param string|array|null $fixtures
     * @return \gplcart\core\Database
     */
    protected function getSystemDatabase($fixtures = null)
    {
        $db = new SystemDatabase;
        return $db->init($this->createDbFromFixture($fixtures));
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
     * Asserts that a record exists in the database
     * @param string $table
     * @param string $field
     * @param mixed $value
     */
    protected function assertDbRecordExists($table, $field, $value)
    {
        $result = static::$pdo->query("SELECT * FROM $table WHERE $field='$value'")->fetchObject();
        $this->assertNotEmpty($result);
    }

    /**
     * Asserts that a record does not exist in the database
     * @param string $table
     * @param string $field
     * @param mixed $value
     */
    protected function assertDbRecordNotExists($table, $field, $value)
    {
        $result = static::$pdo->query("SELECT * FROM $table WHERE $field='$value'")->fetchObject();
        $this->assertEmpty($result);
    }

    /**
     * Asserts that a record equals to the expected array
     * @param array $expected
     * @param string $table
     * @param string $field
     * @param mixed $value
     */
    protected function assertDbRecordEquals(array $expected, $table, $field, $value)
    {
        $actual = static::$pdo->query("SELECT * FROM $table WHERE $field='$value'")->fetchObject();
        $this->assertEquals($expected, (array) $actual);
    }

    /**
     * Asserts that a record not equals to the expected array
     * @param array $expected
     * @param string $table
     * @param string $field
     * @param mixed $value
     */
    protected function assertDbRecordNotEquals(array $expected, $table, $field, $value)
    {
        $actual = static::$pdo->query("SELECT * FROM $table WHERE $field='$value'")->fetchObject();
        $this->assertNotEquals($expected, (array) $actual);
    }

}
