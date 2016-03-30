<?php

/**
 * @package CSV parser
 * @author Iurii Makukh <ymakux@gmail.com>
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\classes;

class Csv
{

    /**
     *
     * @var resource File handler 
     */
    protected $handle;

    /**
     *
     * @var string Current CSV line 
     */
    protected $current_line;

    /**
     *
     * @var integer Current offset in bytes 
     */
    protected $current_position;

    /**
     *
     * @var integer Total CSV file size in bytes
     */
    protected $total;

    /**
     *
     * @var string Path to CSV file
     */
    protected $file;

    /**
     *
     * @var integer Max number of rows to parse
     */
    protected $limit;

    /**
     *
     * @var integer Final offset in bytes
     */
    protected $last_position;

    /**
     *
     * @var integer Starting offset in bytes
     */
    protected $offset;

    /**
     *
     * @var array Array of header names
     */
    protected $header;

    /**
     *
     * @var boolean Skip or not first line
     */
    protected $skip_header;

    /**
     *
     * @var string CSV delimiter
     */
    protected $delimiter;

    /**
     * Sets up variables
     */
    public function __construct()
    {
        $this->last_position = 0;
        $this->offset = 0;
        $this->header = array();
        $this->skip_header = false;
        $this->delimiter = ",";

        ini_set('auto_detect_line_endings', true);
    }

    /**
     * Closes file handler
     */
    public function __destruct()
    {
        if (isset($this->handle)) {
            fclose($this->handle);
        }
    }

    /**
     * Sets file to parse
     * @param string $file. Absolute path to CSV file
     * @return \Csv
     */
    public function setFile($file, $filesize = null)
    {
        $this->file = $file;
        $this->total = isset($filesize) ? (int) $filesize : filesize($file);
        $this->handle = fopen($file, 'r');
        return $this;
    }

    /**
     * Sets max lines to parse
     * @param integer $limit
     * @return \Csv
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }

    /**
     * Sets separator between columns
     * @param string $character
     * @return \Csv
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
        return $header ? reset($header) : array();
    }

    /**
     * Sets header (first line)
     * @param array $header. Array of names
     * @return \Csv
     */
    public function setHeader($header)
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

            if ($this->header) {
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

            if ($this->limit && $parsed >= $this->limit) {
                // Remember last offset
                $this->last_position = $this->currentPosition();
                break;
            }
        }

        return $rows;
    }

    /**
     * Moves pointer to a certain position
     * @param integer $position Bytes
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
     * @return mixed
     */
    protected function next()
    {
        if (isset($this->handle)) {
            $this->current_line = feof($this->handle) ? null : fgets($this->handle);
            $this->current_position = ftell($this->handle);
            return $this->current_line;
        }

        return;
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
     * @return \Csv
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Force to skip first row (header)
     * @return \Csv
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
        if ($this->total) {
            return $this->read();
        }

        return array();
    }

}
