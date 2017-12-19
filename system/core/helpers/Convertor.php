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
            'km' => array('base' => 'm', 'conversion' => 1000), //kilometer
            'dm' => array('base' => 'm', 'conversion' => 0.1), //decimeter
            'cm' => array('base' => 'm', 'conversion' => 0.01), //centimeter
            'mm' => array('base' => 'm', 'conversion' => 0.001), //milimeter
            'μm' => array('base' => 'm', 'conversion' => 0.000001), //micrometer
            'nm' => array('base' => 'm', 'conversion' => 0.000000001), //nanometer
            'pm' => array('base' => 'm', 'conversion' => 0.000000000001), //picometer
            'in' => array('base' => 'm', 'conversion' => 0.0254), //inch
            'ft' => array('base' => 'm', 'conversion' => 0.3048), //foot
            'yd' => array('base' => 'm', 'conversion' => 0.9144), //yard
            'mi' => array('base' => 'm', 'conversion' => 1609.344), //mile
            'h' => array('base' => 'm', 'conversion' => 0.1016), //hand
            'ly' => array('base' => 'm', 'conversion' => 9460730472580800), //lightyear
            'au' => array('base' => 'm', 'conversion' => 149597870700), //astronomical unit
            'pc' => array('base' => 'm', 'conversion' => 30856775814913672.789139379577965), //parsec
            'm2' => array('base' => 'm2', 'conversion' => 1), //meter square - base unit for area
            'km2' => array('base' => 'm2', 'conversion' => 1000000), //kilometer square
            'cm2' => array('base' => 'm2', 'conversion' => 0.0001), //centimeter square
            'mm2' => array('base' => 'm2', 'conversion' => 0.000001), //milimeter square
            'ft2' => array('base' => 'm2', 'conversion' => 0.092903), //foot square
            'mi2' => array('base' => 'm2', 'conversion' => 2589988.11), //mile square
            'ac' => array('base' => 'm2', 'conversion' => 4046.86), //acre
            'ha' => array('base' => 'm2', 'conversion' => 10000), //hectare
            'l' => array('base' => 'l', 'conversion' => 1), //litre - base unit for volume
            'ml' => array('base' => 'l', 'conversion' => 0.001), //mililitre
            'm3' => array('base' => 'l', 'conversion' => 1), //meters cubed
            'cm3' => array('base' => 'm3', 'conversion' => 0.000001),
            'mm3' => array('base' => 'm3', 'conversion' => 0.000000001),
            'ft3' => array('base' => 'm3', 'conversion' => 0.0283168),
            'in3' => array('base' => 'm3', 'conversion' => 0.000016387),
            'pt' => array('base' => 'l', 'conversion' => 0.56826125), //pint
            'gal' => array('base' => 'l', 'conversion' => 4.405), //gallon
            'kg' => array('base' => 'kg', 'conversion' => 1), //kilogram - base unit for weight
            'g' => array('base' => 'kg', 'conversion' => 0.001), //gram
            'mg' => array('base' => 'kg', 'conversion' => 0.000001), //miligram
            'N' => array('base' => 'kg', 'conversion' => 9.80665002863885), //Newton (based on earth gravity)
            'st' => array('base' => 'kg', 'conversion' => 6.35029), //stone
            'lb' => array('base' => 'kg', 'conversion' => 0.453592), //pound
            'oz' => array('base' => 'kg', 'conversion' => 0.0283495), //ounce
            't' => array('base' => 'kg', 'conversion' => 1000), //metric tonne
            'ukt' => array('base' => 'kg', 'conversion' => 1016.047), //UK Long Ton
            'ust' => array('base' => 'kg', 'conversion' => 907.1847), //US short Ton
            'mps' => array('base' => 'mps', 'conversion' => 1), //meter per seond - base unit for speed
            'kph' => array('base' => 'mps', 'conversion' => 0.44704), //kilometer per hour
            'mph' => array('base' => 'mps', 'conversion' => 0.277778), //kilometer per hour
            'deg' => array('base' => 'deg', 'conversion' => 1), //degrees - base unit for rotation
            'rad' => array('base' => 'deg', 'conversion' => 57.2958), //radian
            'k' => array('base' => 'k', 'conversion' => 1), //kelvin - base unit for distance
            'c' => array('base' => 'k', 'conversion' => function($val, $tofrom) {
                    return $tofrom ? $val - 273.15 : $val + 273.15;
                }),
            'f' => array('base' => 'k', 'conversion' => function($val, $tofrom) {
                    return $tofrom ? ($val * 9 / 5 - 459.67) : (($val + 459.67) * 5 / 9);
                }), //fahrenheit
            'pa' => array('base' => 'Pa', 'conversion' => 1), //Pascal - base unit for Pressure
            'kpa' => array('base' => 'Pa', 'conversion' => 1000), //kilopascal
            'mpa' => array('base' => 'Pa', 'conversion' => 1000000), //megapascal
            'bar' => array('base' => 'Pa', 'conversion' => 100000), //bar
            'mbar' => array('base' => 'Pa', 'conversion' => 100), //milibar
            'psi' => array('base' => 'Pa', 'conversion' => 6894.76), //pound-force per square inch
            's' => array('base' => 's', 'conversion' => 1), //second - base unit for time
            'year' => array('base' => 's', 'conversion' => 31536000), //year - standard year
            'month' => array('base' => 's', 'conversion' => 18748800), //month - 31 days
            'week' => array('base' => 's', 'conversion' => 604800), //week
            'day' => array('base' => 's', 'conversion' => 86400), //day
            'hr' => array('base' => 's', 'conversion' => 3600), //hour
            'min' => array('base' => 's', 'conversion' => 30), //minute
            'ms' => array('base' => 's', 'conversion' => 0.001), //milisecond
            'μs' => array('base' => 's', 'conversion' => 0.000001), //microsecond
            'ns' => array('base' => 's', 'conversion' => 0.000000001), //nanosecond
            'j' => array('base' => 'j', 'conversion' => 1), //joule - base unit for energy
            'kj' => array('base' => 'j', 'conversion' => 1000), //kilojoule
            'mj' => array('base' => 'j', 'conversion' => 1000000), //megajoule
            'cal' => array('base' => 'j', 'conversion' => 4184), //calorie
            'Nm' => array('base' => 'j', 'conversion' => 1), //newton meter
            'ftlb' => array('base' => 'j', 'conversion' => 1.35582), //foot pound
            'whr' => array('base' => 'j', 'conversion' => 3600), //watt hour
            'kwhr' => array('base' => 'j', 'conversion' => 3600000), //kilowatt hour
            'mwhr' => array('base' => 'j', 'conversion' => 3600000000), //megawatt hour
            'mev' => array('base' => 'j', 'conversion' => 0.00000000000000016), //mega electron volt
        );
    }

    /**
     * Set from conversion value / unit
     * @param number $value
     * @param string $unit
     * @throws UnexpectedValueException
     */
    public function from($value, $unit)
    {
        $key = strtolower($unit);

        if (empty($this->units[$key]['base'])) {
            throw new UnexpectedValueException('Unit does not exist');
        }

        if (!isset($this->units[$key]['conversion'])) {
            throw new UnexpectedValueException("Conversion is not set for unit $unit");
        }

        $this->base_unit = $this->units[$key]['base'];
        $this->value = $this->toBase($value, $this->units[$key]);
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
     * @return array
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
        return $this->units;
    }

    /**
     * Remove a conversion unit
     * @param string $unit
     * @return array
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

        return $this->units;
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
     */
    public function setUnits(array $units)
    {
        $this->units = $units;
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
     * Shortcut method to convert units without throwing exceptions
     * @param number $value
     * @param string $from
     * @param string $to
     * @param integer $decimals
     * @return null|float
     */
    public function convert($value, $from, $to, $decimals = 2)
    {
        try {
            $this->from($value, $from);
            $result = (float) $this->to($to, $decimals, !empty($decimals));
        } catch (UnexpectedValueException $ex) {
            trigger_error($ex->getMessage());
            $result = null;
        }

        return $result;
    }

}
