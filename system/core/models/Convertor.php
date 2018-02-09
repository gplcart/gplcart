<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook;
use InvalidArgumentException;
use OutOfBoundsException;

/**
 * Contains methods to convert measurement units
 */
class Convertor
{
    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Value to convert
     * @var number
     */
    protected $value;

    /**
     * Base unit of value
     * @var string
     */
    protected $base_unit;

    /**
     * Array of unit conversion rules
     * @var array
     */
    protected $units = array();

    /**
     * @param Hook $hook
     */
    public function __construct(Hook $hook)
    {
        $this->hook = $hook;
        $this->units = $this->getDefaultUnits();
    }

    /**
     * Returns an array of default conversion units
     * @return array
     */
    protected function getDefaultUnits()
    {
        $units = gplcart_config_get(GC_FILE_CONFIG_UNIT);
        $this->hook->attach('unit.list', $units);
        return $units;
    }

    /**
     * Set from conversion value / unit
     * @param number $value
     * @param string $unit
     * @return $this
     * @throws OutOfBoundsException
     */
    public function from($value, $unit)
    {
        $this->base_unit = $this->value = null;

        $key = strtolower($unit);

        if (empty($this->units[$key]['base'])) {
            throw new OutOfBoundsException('Unit does not exist');
        }

        if (!isset($this->units[$key]['conversion'])) {
            throw new OutOfBoundsException("Conversion is not set for unit $unit");
        }

        $this->base_unit = $this->units[$key]['base'];
        $this->value = $this->toBase($value, $this->units[$key]);
        return $this;
    }

    /**
     * Convert from value to new unit
     * @param string|array $unit
     * @param null|integer $decimals
     * @param bool $round
     * @return mixed
     * @throws OutOfBoundsException
     * @throws InvalidArgumentException
     */
    public function to($unit, $decimals = null, $round = true)
    {
        if (!isset($this->value)) {
            throw new InvalidArgumentException('From value not set');
        }

        if (is_array($unit)) {
            return $this->toMany($unit, $decimals, $round);
        }

        $key = strtolower($unit);

        if (empty($this->units[$key]['base'])) {
            throw new OutOfBoundsException('Unit does not exist');
        }

        if (!isset($this->base_unit)) {
            $this->base_unit = $this->units[$key]['base'];
        }

        if ($this->units[$key]['base'] != $this->base_unit) {
            throw new InvalidArgumentException('Cannot convert between units of different types');
        }

        if (is_callable($this->units[$key]['conversion'])) {
            $result = $this->units[$unit]['conversion']($this->value, true);
        } else {
            $result = $this->value / $this->units[$key]['conversion'];
        }

        if (!isset($decimals)) {
            return $result;
        }

        if ($round) {
            return round($result, $decimals);
        }

        $shifter = $decimals ? pow(10, $decimals) : 1;
        return floor($result * $shifter) / $shifter;
    }

    /**
     * Convert from value to all compatible units
     * @param number $decimals
     * @param bool $round
     * @return array
     * @throws InvalidArgumentException
     */
    public function toAll($decimals = null, $round = true)
    {
        if (!isset($this->value)) {
            throw new InvalidArgumentException('From value not set');
        }

        if (empty($this->base_unit)) {
            throw new InvalidArgumentException('No from unit set');
        }

        $units = array();

        foreach ($this->units as $key => $data) {
            if ($data['base'] == $this->base_unit) {
                array_push($units, $key);
            }
        }

        return $this->toMany($units, $decimals, $round);
    }

    /**
     * List all available conversion units for given unit
     * @param string $unit
     * @return array
     * @throws OutOfBoundsException
     */
    public function getConversions($unit)
    {
        if (!isset($this->units[$unit]['base'])) {
            throw new OutOfBoundsException('Unit is not set');
        }

        $units = array();

        foreach ($this->units as $key => $value) {
            if (isset($value['base']) && $value['base'] === $this->units[$unit]['base']) {
                array_push($units, $key);
            }
        }

        return $units;
    }

    /**
     * Returns an array of units
     * @param null|string $type Filter by this type
     * @return array
     */
    public function getUnits($type = null)
    {
        if (!isset($type)) {
            return $this->units;
        }

        $filtered = array();
        foreach ($this->units as $key => $value) {
            if (isset($value['type']) && $value['type'] === $type) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Returns an array of unit names
     * @param null|string $type
     * @return array
     */
    public function getUnitNames($type = null)
    {
        $names = array();
        foreach ($this->getUnits($type) as $key => $value) {
            $names[$key] = $value['name'];
        }

        return $names;
    }

    /**
     * Shortcut method to convert units
     * @param number $value
     * @param string $from
     * @param string $to
     * @param integer $decimals
     * @return float
     */
    public function convert($value, $from, $to, $decimals = 2)
    {
        return (float) $this->from($value, $from)->to($to, $decimals, !empty($decimals));
    }

    /**
     * Convert from value to its base unit
     * @param number $value
     * @param array $unit
     * @return number
     */
    protected function toBase($value, array $unit)
    {
        if (is_callable($unit['conversion'])) {
            return $unit['conversion']($value, false);
        }

        return $value * $unit['conversion'];
    }

    /**
     * Iterate through multiple unit conversions
     * @param array $units
     * @param null|number $decimals
     * @param bool $round
     * @return array
     */
    protected function toMany(array $units, $decimals = null, $round = true)
    {
        $results = array();
        foreach ($units as $key) {
            $results[$key] = $this->to($key, $decimals, $round);
        }

        return $results;
    }

}
