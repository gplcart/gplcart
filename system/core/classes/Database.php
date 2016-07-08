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

}
