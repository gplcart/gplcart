<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Zone as ModelsZone;
use core\models\City as ModelsCity;
use core\models\State as ModelsState;
use core\models\Country as ModelsCountry;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate city data
 */
class City extends BaseValidator
{

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * Zone model instance
     * @var \core\models\Zone $zone
     */
    protected $zone;

    /**
     * City model instance
     * @var \core\models\City $city
     */
    protected $city;

    /**
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

    /**
     * Constructor
     * @param ModelsCity $city
     * @param ModelsState $state
     * @param ModelsCountry $country
     * @param ModelsZone $zone
     */
    public function __construct(ModelsCity $city, ModelsState $state,
            ModelsCountry $country, ModelsZone $zone)
    {
        parent::__construct();

        $this->city = $city;
        $this->zone = $zone;
        $this->state = $state;
        $this->country = $country;
    }

    /**
     * Performs full city data validation
     * @param array $submitted
     * @param array $options
     */
    public function city(array &$submitted, array $options = array())
    {
        $this->validateStatus($submitted);
        $this->validateNameCity($submitted);
        $this->validateStateCity($submitted);
        $this->validateZoneCity($submitted);
        $this->validateCountryCity($submitted);

        return empty($this->errors) ? true : $this->errors;
    }

    /**
     * Validates city name
     * @param array $submitted
     */
    protected function validateNameCity(array $submitted)
    {
        if (empty($submitted['name']) || mb_strlen($submitted['name']) > 255) {
            $options = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Name'));
            $this->errors['name'] = $this->language->text('@field must be @min - @max characters long', $options);
        }
    }

    /**
     * Validates a state ID
     * @param array $submitted
     * @return boolean
     */
    protected function validateStateCity(array $submitted)
    {
        if (empty($submitted['state_id'])) {
            $this->errors['state_id'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('State')
            ));
            return false;
        }

        if (!is_numeric($submitted['state_id'])) {
            $options = array('@field' => $this->language->text('State'));
            $this->errors['state_id'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        $state = $this->state->get($submitted['state_id']);

        if (empty($state)) {
            $this->errors['state_id'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('State')));
            return false;
        }
        return true;
    }

    /**
     * Validates a zone ID
     * @param array $submitted
     */
    protected function validateZoneCity(array $submitted)
    {
        if (!isset($submitted['zone_id'])) {
            return true;
        }

        if (!is_numeric($submitted['zone_id'])) {
            $options = array('@field' => $this->language->text('Zone'));
            $this->errors['zone_id'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        $zone = $this->zone->get($submitted['zone_id']);

        if (empty($zone)) {
            $this->errors['zone_id'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Zone')));
            return false;
        }
        
        return true;
    }

    /**
     * Validates a country code
     * @param array $submitted
     */
    protected function validateCountryCity(array $submitted)
    {
        if (empty($submitted['country'])) {
            $this->errors['country'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Country')
            ));
            return false;
        }

        $country = $this->country->get($submitted['country']);

        if (empty($country)) {
            $this->errors['country'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Country')));
            return false;
        }

        return true;
    }

}
