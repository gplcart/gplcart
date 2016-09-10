<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\classes;

use PDO;
use PDOException;
use core\exceptions\DatabaseException;

/**
 * Provides wrappers for PDO methods
 */
class Database extends PDO
{

    /**
     * Sets up the database connection
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        if (!empty($config)) {

            $dns = "{$config['type']}:host={$config['host']};port={$config['port']};dbname={$config['name']}";

            try {
                parent::__construct($dns, $config['user'], $config['password']);
                $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $exc) {
                throw new DatabaseException('Could not connect to database');
            }
        }
    }

    /**
     * Returns an array of database scheme
     * @param string $table
     * @return array
     */
    public function getScheme($table = null)
    {
        $data = include GC_CONFIG_DATABASE;

        if (empty($data)) {
            throw new DatabaseException('Failed to load database scheme');
        }

        if (isset($table)) {
            return empty($data[$table]) ? array() : $data[$table];
        }

        return $data;
    }

    /**
     * Returns a single array indexed by column name
     * @param string $sql
     * @param array $params
     * @param array $options
     * @return array
     */
    public function getArray($sql, array $params = array(),
            array $options = array())
    {
        $query = $this->run($sql, $params);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        $this->prepareResult($result, $options);
        return empty($result) ? array() : (array) $result;
    }

    /**
     * Returns an array of arrays
     * @param string $sql
     * @param array $params
     * @param array $options
     * @return array
     */
    public function getArrays($sql, array $params = array(),
            array $options = array())
    {
        $query = $this->run($sql, $params);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $this->prepareResults($result, $options);

        return empty($result) ? array() : (array) $result;
    }

    /**
     * Runs a SQL query with an array of placeholders
     * @param string $sql
     * @param array $params
     * @return object
     */
    public function run($sql, array $params = array())
    {
        $sth = $this->prepare($sql);

        foreach ($params as $key => $value) {
            $key = is_numeric($key) ? $key + 1 : ":$key";
            $sth->bindValue($key, $value);
        }

        $sth->execute($params);
        return $sth;
    }

    /**
     * 
     * @param type $data
     * @param array $options
     */
    protected function prepareResult(&$data, array $options)
    {
        if (!empty($options['unserialize'])) {
            foreach ((array) $options['unserialize'] as $field) {
                if (isset($data[$field])) {
                    $data[$field] = unserialize($data[$field]);
                } else {
                    $data[$field] = array();
                }
            }
        }
    }

    /**
     * 
     * @param type $results
     * @param array $options
     */
    protected function prepareResults(&$results, array $options)
    {
        foreach ($results as $index => &$result) {

            $this->prepareResult($result, $options);

            if (!empty($options['index'])) {
                $results[$result[$options['index']]] = $result;
                unset($results[$index]);
            }
        }
    }

    /**
     * Filters an array of data according to existing scheme for the given table
     * @param string $table
     * @param array $data
     * @return array
     */
    protected function filterValues($table, array $data)
    {
        $scheme = $this->getScheme($table);

        if (empty($scheme['fields'])) {
            return array();
        }

        $values = array_intersect_key($data, $scheme['fields']);

        if (empty($values)) {
            return array();
        }

        foreach ($values as $field => &$value) {
            $this->filterValue($scheme, $values, $field, $value);
        }

        return $values;
    }

    /**
     * Filters a single item to be saved in the database
     * @param array $scheme
     * @param array $values
     * @param string $field
     * @param mixed $value
     */
    protected function filterValue(array $scheme, array &$values, $field,
            &$value)
    {
        if (!empty($scheme['fields'][$field]['auto_increment'])) {
            unset($values[$field]); // Remove autoincremented fields
        }

        if (0 === strpos($scheme['fields'][$field]['type'], 'int')) {
            $value = intval($value); // Make value integer
        }

        if ($scheme['fields'][$field]['type'] === 'float') {
            $value = floatval($value); // Make value float
        }

        if (!empty($scheme['fields'][$field]['serialize']) && is_array($value)) {
            $value = serialize($value); // Serialize arrays
        }
    }

    /**
     * Returns an array of default field values for the given table
     * @param string $table
     * @return array
     */
    public function getDefaultValues($table)
    {
        $scheme = $this->getScheme($table);

        if (empty($scheme['fields'])) {
            return array();
        }

        $values = array();
        foreach ($scheme['fields'] as $name => $info) {

            if (array_key_exists('default', $info)) {
                $values[$name] = $info['default'];
                continue;
            }

            if (!empty($info['serialize'])) {
                $values[$name] = array();
            }
        }

        return $values;
    }

    /**
     * Returns an array of prepared values ready to insert into the database
     * @param string $table
     * @param array $data
     * @return array
     */
    public function prepareInsert($table, array $data)
    {
        $data += $this->getDefaultValues($table);
        return $this->filterValues($table, $data);
    }

    /**
     * Performs an INSERT query
     * @param string $table
     * @param array $data
     * @return mixed
     */
    public function insert($table, array $data, $prepare = true)
    {
        if ($prepare) {
            $data = $this->prepareInsert($table, $data);
        }

        if (empty($data)) {
            return false;
        }

        $keys = array_keys($data);
        $fields = implode(',', $keys);
        $values = ':' . implode(',:', $keys);

        $sth = $this->prepare("INSERT INTO $table ($fields) VALUES ($values)");

        foreach ($data as $key => $value) {
            $sth->bindValue(":$key", $value);
        }

        $sth->execute();
        return $this->lastInsertId();
    }

    /**
     * Performs a UPDATE query
     * @param string $table
     * @param array $data
     * @param array $conditions
     * @return integer|boolean
     */
    public function update($table, array $data, array $conditions,
            $filter = true)
    {
        if ($filter) {
            $data = $this->filterValues($table, $data);
        }

        if (empty($data)) {
            return false;
        }

        $farray = array();
        foreach ($data as $key => $value) {
            $farray[] = "$key=:$key";
        }

        $fields = implode(',', $farray);

        $carray = array();
        foreach ($conditions as $key => $value) {
            $carray[] = "$key=:$key";
        }

        $where = implode(' AND ', $carray);

        $stmt = $this->prepare("UPDATE $table SET $fields WHERE $where");

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Performs a DELETE query
     * @param string $table
     * @param array $conditions
     * @return integer|boolean
     */
    public function delete($table, array $conditions)
    {
        if (empty($conditions)) {
            return false;
        }

        $carray = array();
        foreach ($conditions as $key => $value) {
            $carray[] = "$key=:$key";
        }

        $where = implode(' AND ', $carray);
        $stmt = $this->prepare("DELETE FROM $table WHERE $where");

        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Creates tables using an array of scheme data
     * @param array $tables
     */
    public function import(array $tables)
    {
        $imported = 0;
        foreach ($tables as $table => $data) {

            $sql = $this->getSqlCreateTable($table, $data);

            if ($this->query($sql) !== false) {
                $imported++;
            }

            $alter = $this->getSqlAlterTable($table, $data);

            if (!empty($alter)) {
                $this->query($alter);
            }
        }

        return ($imported == count($tables));
    }

    /**
     * Returns a SQL that describes table fields.
     * Used to create tables
     * @param array $fields
     * @return string
     */
    protected function getSqlFields(array $fields)
    {
        $sql = array();
        foreach ($fields as $name => $info) {

            if (strpos($info['type'], 'text') !== false || $info['type'] === 'blob') {
                unset($info['default']);
            }

            $string = "{$info['type']}";

            if (isset($info['length'])) {
                $string .= "({$info['length']})";
            }

            if (!empty($info['not_null'])) {
                $string .= " NOT NULL";
            }

            if (isset($info['default'])) {
                $string .= " DEFAULT '{$info['default']}'";
            }

            if (!empty($info['auto_increment'])) {
                $string .= " AUTO_INCREMENT";
            }

            if (!empty($info['primary'])) {
                $string .= " PRIMARY KEY";
            }

            $sql[] = $name . ' ' . trim($string);
        }


        return implode(',', $sql);
    }

    /**
     * Returns a string with SQL query to create a table
     * @param string $table
     * @param array $data
     * @return string
     */
    protected function getSqlCreateTable($table, array $data)
    {
        $fields = $this->getSqlFields($data['fields']);
        $engine = isset($data['engine']) ? $data['engine'] : 'InnoDB';
        $collate = isset($data['collate']) ? $data['collate'] : 'utf8_general_ci';

        return "CREATE TABLE $table($fields) ENGINE=$engine CHARACTER SET utf8 COLLATE $collate";
    }

    /**
     * Returns a string with SQL query to alter a table
     * @param string $table
     * @param array $data
     * @return string
     */
    protected function getSqlAlterTable($table, array $data)
    {
        return empty($data['alter']) ? '' : "ALTER TABLE $table {$data['alter']}";
    }

}
