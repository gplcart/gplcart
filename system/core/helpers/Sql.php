<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

/**
 * SQL builder helper
 */
class Sql
{

    /**
     * An array of clauses to be rendered
     * @var array
     */
    protected $clauses = array();

    /**
     * A numeric index of the current clause
     * @var integer
     */
    protected $index = 1;

    /**
     * An array of added parameters
     * @var array
     */
    protected $params = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        // Reserved
    }

    /**
     * Adds SELECT clause
     * @param string $sql
     * @return object \core\helpers\Sql
     */
    public function select($sql = '')
    {
        if (empty($sql)) {
            $sql = '*';
        }

        $this->addClause('SELECT', $sql);
        return $this;
    }

    /**
     * Adds LEFT JOIN clause
     * @param string $table
     * @param string $alias
     * @param string $on
     * @param array $params
     * @return object \core\helpers\Sql
     */
    public function leftJoin($table, $alias = '', $on = '', $params = array())
    {
        $arguments = array($table, $alias, $on);
        $this->addParams($params);
        $this->addClause('LEFT JOIN', $arguments);
        return $this;
    }

    /**
     * Adds WHERE clause
     * @param string $sql
     * @param array $params
     * @return object \core\helpers\Sql
     */
    public function where($sql, $params = array())
    {
        $this->addParams($params);
        $this->addClause('WHERE', $sql);
        return $this;
    }

    /**
     * Adds AND condition to WHERE clause
     * @param string $field
     * @param mixed $value
     * @param string $operator
     * @param array $params
     * @return object \core\helpers\Sql
     */
    public function andWhere($field, $value = '?', $operator = '=', $params = array())
    {
        $this->addParams($params);
        $this->addClause('AND', array($field, $value, $operator));
        return $this;
    }

    /**
     * Adds FROM clause
     * @param string $table
     * @param string $alias
     * @return object \core\helpers\Sql
     */
    public function from($table, $alias = '')
    {
        $arguments = array($table, $alias);
        $this->addClause('FROM', $arguments);
        return $this;
    }

    /**
     * Adds a SQL clause
     * @param string $name
     * @param array $value
     * @return string
     */
    protected function addClause($name, $value)
    {
        $this->clauses["$name|{$this->index}"] = $value;
        $this->index++;
        return $this;
    }

    /**
     * Adds query parameters
     * @param array $parameters
     */
    protected function addParams(array $parameters)
    {
        $this->params = array_merge($this->params, $parameters);
    }

    /**
     * Returns rendered SELECT clause
     * @param string $data
     * @return string
     */
    protected function renderSelect($data)
    {
        return $data;
    }

    /**
     * Returns rendered LEFT JOIN clause
     * @param array $data
     * @return string
     */
    protected function renderLeftJoin(array $data)
    {
        list($table, $alias, $on) = $data;
        $clause = "$table $alias";

        if (!empty($on)) {
            $clause .= " ON($on)";
        }

        return $clause;
    }

    /**
     * Returns rendered FROM clause
     * @param array $data
     * @return string
     */
    protected function renderFrom(array $data)
    {
        list($table, $alias) = $data;
        return "$table $alias";
    }

    /**
     * Renders SELECT clause
     * @param string $sql
     * @return string
     */
    protected function renderWhere($sql)
    {
        return $sql;
    }

    /**
     * Renders AND condition
     * @param array $data
     * @return string
     */
    protected function renderAnd(array $data)
    {
        list($field, $value, $operator) = $data;

        if (strtolower($operator) === 'in') {
            $value = rtrim(str_repeat('?,', count((array) $value)), ',');
            return " AND $field IN($value)";
        }

        return " AND $field $operator $value";
    }

    /**
     * Returns an array that contains SQL query and an array of placeholders
     * @return array
     */
    public function get()
    {
        $parts = array();
        foreach ($this->clauses as $key => $data) {
            $clause = strtok($key, '|');
            $method = str_replace(' ', '', "render$clause");
            $rendered = call_user_func_array(array($this, $method), array($data));
            $parts[] = "$clause $rendered";
        }

        // Normalize whitespaces
        $sql = preg_replace('/\s+/', ' ', implode(' ', $parts));
        return array($sql, $this->params);
    }

}
