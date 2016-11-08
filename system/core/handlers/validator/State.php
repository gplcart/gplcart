<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Zone as ModelsZone;
use core\models\State as ModelsState;
use core\models\Country as ModelsCountry;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate country states data
 */
class State extends BaseValidator
{
    /**
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

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
     * Constructor
     * @param ModelsState $state
     * @param ModelsCountry $country
     * @param ModelsZone $zone
     */
    public function __construct(ModelsState $state, ModelsCountry $country,
            ModelsZone $zone)
    {
        parent::__construct();

        $this->zone = $zone;
        $this->state = $state;
        $this->country = $country;
    }

    /**
     * Performs full country state validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function state(array &$submitted, array $options = array())
    {
        $this->validateState($submitted);
        $this->validateStatus($submitted);
        $this->validateCodeState($submitted);
        $this->validateName($submitted);
        $this->validateCountryState($submitted);
        $this->validateZoneState($submitted);

        return empty($this->errors) ? true : $this->errors;
    }

    /**
     * Validates a state to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateState(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {

            $data = $this->state->get($submitted['update']);

            if (empty($data)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('State')));
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

    /**
     * Validates country code
     * @param array $submitted
     * @return boolean
     */
    protected function validateCountryState(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['country'])) {
            return null;
        }

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

    /**
     * Validates a state code
     * @param array $submitted
     * @return boolean
     */
    public function validateCodeState(array &$submitted)
    {
        if (isset($submitted['update']['code'])//
                && $submitted['update']['code'] === $submitted['code']) {
            return true;
        }

        if (empty($submitted['code'])) {
            $this->errors['code'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Code')
            ));
            return false;
        }

        $existing = $this->state->getByCode($submitted['code'], $submitted['country']);

        if (empty($existing)) {
            return true;
        }

        $this->errors['code'] = $this->language->text('@object already exists', array(
            '@object' => $this->language->text('Code')));
        return false;
    }

    /**
     * Validates a zone ID
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateZoneState(array $submitted)
    {
        if (empty($submitted['zone_id'])) {
            return null;
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

}
