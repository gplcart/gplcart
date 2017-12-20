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
 * Helper class to convert measurement units
 */
class Convertor
{

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
     * Constructor
     */
    public function __construct()
    {
        $this->setUnits($this->getDefaultUnits());
    }

    /**
     * Returns an array of default conversion units
     * @return array
     */
    protected function getDefaultUnits()
    {
        return array(
            'm' => array('base' => 'm', 'conversion' => 1), //meter - base unit for distance
            'dm' => array('base' => 'm', 'conversion' => 0.1), //decimeter
            'cm' => array('base' => 'm', 'conversion' => 0.01), //centimeter
            'mm' => array('base' => 'm', 'conversion' => 0.001), //milimeter
            'in' => array('base' => 'm', 'conversion' => 0.0254), //inch
            'm2' => array('base' => 'm2', 'conversion' => 1), //meter square - base unit for area
            'cm2' => array('base' => 'm2', 'conversion' => 0.0001), //centimeter square
            'mm2' => array('base' => 'm2', 'conversion' => 0.000001), //milimeter square
            'l' => array('base' => 'l', 'conversion' => 1), //litre - base unit for volume
            'ml' => array('base' => 'l', 'conversion' => 0.001), //mililitre
            'm3' => array('base' => 'l', 'conversion' => 1), //meters cubed
            'cm3' => array('base' => 'm3', 'conversion' => 0.000001),
            'mm3' => array('base' => 'm3', 'conversion' => 0.000000001),
            'in3' => array('base' => 'm3', 'conversion' => 0.000016387),
            'pt' => array('base' => 'l', 'conversion' => 0.56826125), //pint
            'gal' => array('base' => 'l', 'conversion' => 4.405), //gallon
            'kg' => array('base' => 'kg', 'conversion' => 1), //kilogram - base unit for weight
            'g' => array('base' => 'kg', 'conversion' => 0.001), //gram
            'mg' => array('base' => 'kg', 'conversion' => 0.000001), //miligram
            'lb' => array('base' => 'kg', 'conversion' => 0.453592), //pound
            'oz' => array('base' => 'kg', 'conversion' => 0.0283495), //ounce
            't' => array('base' => 'kg', 'conversion' => 1000), //metric tonne
        );
    }

    /**
     * Set from conversion value / unit
     * @param number $value
     * @param string $unit
     * @return $this
     * @throws UnexpectedValueException
     */
    public function from($value, $unit)
    {
        $this->base_unit = $this->value = null;

        $key = strtolower($unit);

        if (empty($this->units[$key]['base'])) {
            throw new UnexpectedValueException('Unit does not exist');
        }

        if (!isset($this->units[$key]['conversion'])) {
            throw new UnexpectedValueException("Conversion is not set for unit $unit");
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
     * @throws UnexpectedValueException
     */
    public function to($unit, $decimals = null, $round = true)
    {
        if (!isset($this->value)) {
            throw new UnexpectedValueException('From value not set');
        }

        if (is_array($unit)) {
            return $this->toMany($unit, $decimals, $round);
        }

        $key = strtolower($unit);

        if (empty($this->units[$key]['base'])) {
            throw new UnexpectedValueException('Unit does not exist');
        }

        if (!isset($this->base_unit)) {
            $this->base_unit = $this->units[$key]['base'];
        }

        if ($this->units[$key]['base'] != $this->base_unit) {
            throw new UnexpectedValueException('Cannot convert between units of different types');
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

    /**
     * Convert from value to all compatible units
     * @param number $decimals
     * @param bool $round
     * @return array
     * @throws UnexpectedValueException
     */
    public function toAll($decimals = null, $round = true)
    {
        if (!isset($this->value)) {
            throw new UnexpectedValueException('From value not set');
        }

        if (empty($this->base_unit)) {
            throw new UnexpectedValueException('No from unit set');
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
     * Add conversion Unit
     * @param string $unit
     * @param string $base
     * @param mixed $conversion
     * @return $this
     * @throws UnexpectedValueException
     */
    public function setUnit($unit, $base, $conversion)
    {
        if (isset($this->units[$unit])) {
            throw new UnexpectedValueException('Unit already exists');
        }

        if (!isset($this->units[$base]) && $base != $unit) {
            throw new UnexpectedValueException('Base unit does not exist');
        }

        $this->units[$unit] = array('base' => $base, 'conversion' => $conversion);
        return $this;
    }

    /**
     * Remove a conversion unit
     * @param string $unit
     * @return $this
     */
    public function unsetUnit($unit)
    {
        if ($this->units[$unit]['base'] != $unit) {
            unset($this->units[$unit]);
            return $this->units;
        }

        foreach ($this->units as $key => $data) {
            if ($data['base'] == $unit) {
                unset($this->units[$key]);
            }
        }

        return $this;
    }

    /**
     * List all available conversion units for given unit
     * @param string $unit
     * @return array
     * @throws UnexpectedValueException
     */
    public function getUnits($unit)
    {
        if (empty($this->units[$unit])) {
            throw new UnexpectedValueException('Unit does not exist');
        }

        $units = array();
        foreach ($this->units as $key => $data) {
            if ($data['base'] == $this->units[$unit]['base']) {
                array_push($units, $key);
            }
        }

        return $units;
    }

    /**
     * Sets conversion units
     * @param array $units
     * @return $this
     */
    public function setUnits(array $units)
    {
        $this->units = $units;
        return $this;
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

}
