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
            } catch (PDOException $exception) {
                throw $exception;
            }
        }
    }

    /**
     * Performs an INSERT query
     * @param string $table
     * @param array $data
     * @return integer
     */
    public function insert($table, array $data)
    {
        ksort($data);

        $names = implode(',', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        $sth = $this->prepare("INSERT INTO $table ($names) VALUES ($values)");

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
     * @return integer
     */
    public function update($table, array $data, array $conditions)
    {
        ksort($data);

        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= "$key = :field_$key,";
        }

        $fields = rtrim($fields, ',');

        $where = '';

        $i = 0;
        foreach ($conditions as $key => $value) {
            if ($i == 0) {
                $where .= "$key = :where_$key";
            } else {
                $where .= " AND $key = :where_$key";
            }
            $i++;
        }

        $where = ltrim($where, ' AND ');
        $stmt = $this->prepare("UPDATE $table SET $fields WHERE $where");

        foreach ($data as $key => $value) {
            $stmt->bindValue(":field_$key", $value);
        }

        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":where_$key", $value);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Performs a DELETE query
     * @param string $table
     * @param mixed $conditions
     * @return integer
     */
    public function delete($table, $conditions)
    {
        if (empty($conditions)) {
            return false;
        }

        if ($conditions === 'all') {
            return $this->query("DELETE FROM $table");
        }

        $conditions = (array) $conditions;

        ksort($conditions);

        $where = '';

        $i = 0;
        foreach ($conditions as $key => $value) {
            if ($i == 0) {
                $where .= "$key = :$key";
            } else {
                $where .= " AND $key = :$key";
            }
            $i++;
        }

        $where = ltrim($where, ' AND ');
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
