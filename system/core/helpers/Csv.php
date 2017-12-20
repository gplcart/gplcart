<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

use UnexpectedValueException;

/**
 * Provides methods to read CSV data
 */
class Csv
{

    /**
     * File handler
     * @var resource
     */
    protected $handle;

    /**
     * Current CSV line
     * @var string
     */
    protected $current_line;

    /**
     * Current offset in bytes
     * @var integer
     */
    protected $current_position;

    /**
     * Total CSV file size in bytes
     * @var integer
     */
    protected $total;

    /**
     * Path to CSV file
     * @var string
     */
    protected $file;

    /**
     * Max number of rows to parse
     * @var integer
     */
    protected $limit;

    /**
     * Final offset in bytes
     * @var integer
     */
    protected $last_position = 0;

    /**
     * Starting offset in bytes
     * @var integer
     */
    protected $offset = 0;

    /**
     * Array of header names
     * @var array
     */
    protected $header = array();

    /**
     * Skip or not first line
     * @var boolean
     */
    protected $skip_header = false;

    /**
     * CSV delimiter
     * @var string
     */
    protected $delimiter = ",";

    /**
     * Constructor
     */
    public function __construct()
    {
        ini_set('auto_detect_line_endings', true);
    }

    /**
     * Closes file handler
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Sets file to parse
     * @param string $file
     * @param integer $filesize
     * @return $this
     */
    public function open($file, $filesize = null)
    {
        $this->handle = fopen($file, 'r');

        if (!is_resource($this->handle)) {
            throw new UnexpectedValueException('Failed to open CSV file');
        }

        $this->file = $file;
        $this->total = isset($filesize) ? (int) $filesize : filesize($file);
        return $this;
    }

    /**
     * Close file handle
     */
    public function close()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    /**
     * Sets max lines to parse
     * @param integer $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }

    /**
     * Sets separator between columns
     * @param string $character
     * @return $this
     */
    public function setDelimiter($character)
    {
        $this->delimiter = $character;
        return $this;
    }

    /**
     * Get first line of CSV file
     * @return array
     */
    public function getHeader()
    {
        $this->limit = 1;
        $header = $this->read();
        return empty($header) ? array() : reset($header);
    }

    /**
     * Sets header (first line)
     * @param array $header
     * @return $this
     */
    public function setHeader(array $header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * CSV reader
     * @return array
     */
    public function read()
    {
        $rows = array();

        $start = $this->offset ? $this->offset : $this->last_position;
        $this->last_position = 0;

        $parsed = 0;
        for ($this->rewind($start); $this->valid(); $this->next()) {
            $line = trim($this->current(), "\r\n");

            if (empty($line)) {
                continue;
            }

            if ($this->skip_header) {
                $this->skip_header = false;
                continue;
            }

            $quoted = false;
            $current_index = 0;
            $current_field = '';
            $fields = array();

            while ($current_index <= strlen($line)) {
                if ($quoted) {
                    $next_quote_index = strpos($line, '"', $current_index);

                    if ($next_quote_index === false) {
                        $current_field .= substr($line, $current_index);
                        $this->next();

                        if (!$this->valid()) {
                            $fields[] = $current_field;
                            break;
                        }

                        $current_field .= "\n";
                        $line = trim($this->current(), "\r\n");
                        $current_index = 0;
                        continue;
                    }

                    $current_field .= substr($line, $current_index, $next_quote_index - $current_index);

                    if (isset($line[$next_quote_index + 1]) && $line[$next_quote_index + 1] === '"') {
                        $current_field .= '"';
                        $current_index = $next_quote_index + 2;
                    } else {
                        $quoted = false;
                        $current_index = $next_quote_index + 1;
                    }
                } else {
                    $next_quote_index = strpos($line, '"', $current_index);
                    $next_delimiter_index = strpos($line, $this->delimiter, $current_index);

                    if ($next_quote_index === false) {
                        $next_index = $next_delimiter_index;
                    } elseif ($next_delimiter_index === false) {
                        $next_index = $next_quote_index;
                    } else {
                        $next_index = min($next_quote_index, $next_delimiter_index);
                    }

                    if ($next_index === false) {
                        $current_field .= substr($line, $current_index);
                        $fields[] = $current_field;
                        break;
                    } elseif ($line[$next_index] === $this->delimiter) {
                        $length = ($next_index + strlen($this->delimiter) - 1) - $current_index;
                        $current_field .= substr($line, $current_index, $length);
                        $fields[] = $current_field;
                        $current_field = '';
                        $current_index += $length + 1;
                    } else {
                        $quoted = true;
                        $current_field .= substr($line, $current_index, $next_index - $current_index);
                        $current_index = $next_index + 1;
                    }
                }
            }

            if (!empty($this->header)) {
                $row = array();
                foreach ($this->header as $key => $name) {
                    $field = array_shift($fields);
                    $row[$key] = isset($field) ? $field : '';
                }
            } else {
                $row = $fields;
            }

            $rows[] = $row;

            $parsed++;

            if (!empty($this->limit) && $parsed >= $this->limit) {
                $this->last_position = $this->currentPosition();
                break;
            }
        }

        return $rows;
    }

    /**
     * Moves pointer to a certain position
     * @param integer $position
     */
    protected function rewind($position = 0)
    {
        if (isset($this->handle)) {
            fseek($this->handle, $position);
            $this->next();
        }
    }

    /**
     * Sets current string and offset
     * @return null|integer
     */
    protected function next()
    {
        if (isset($this->handle)) {
            $this->current_line = feof($this->handle) ? null : fgets($this->handle);
            $this->current_position = ftell($this->handle);
            return $this->current_line;
        }

        return null;
    }

    /**
     * Determines if CSV line is valid
     * @return boolean
     */
    protected function valid()
    {
        return isset($this->current_line);
    }

    /**
     * Gets current CSV row
     * @return string
     */
    protected function current()
    {
        return $this->current_line;
    }

    /**
     * Gets current offset in bytes
     * @return integer
     */
    protected function currentPosition()
    {
        return $this->current_position;
    }

    /**
     * Get latest file pointer offset in bytes
     * @return integer
     */
    public function getOffset()
    {
        return $this->last_position;
    }

    /**
     * Sets initial file offset in bytes
     * @param integer $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Force to skip first row (header)
     * @return $this
     */
    public function skipHeader()
    {
        $this->skip_header = true;
        return $this;
    }

    /**
     * Parses CSV into multidimensional array
     * @return array
     */
    public function parse()
    {
        if (!empty($this->total)) {
            return $this->read();
        }

        return array();
    }

}
