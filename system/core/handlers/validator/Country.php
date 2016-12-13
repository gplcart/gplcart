<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Zone as ZoneModel;
use core\models\State as StateModel;
use core\models\Country as CountryModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate various database related data
 */
class Country extends BaseValidator
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
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

    /**
     * Constructor
     * @param CountryModel $country
     * @param StateModel $state
     * @param ZoneModel $zone
     */
    public function __construct(CountryModel $country, StateModel $state,
            ZoneModel $zone)
    {
        parent::__construct();

        $this->zone = $zone;
        $this->state = $state;
        $this->country = $country;
    }

    /**
     * Performs full validation of submitted country data
     * @param array $submitted
     * @param array $options
     * @return array|bool
     */
    public function country(array &$submitted, array $options = array())
    {
        $this->validateCountry($submitted);
        $this->validateWeight($submitted);
        $this->validateDefault($submitted);
        $this->validateStatus($submitted);
        $this->validateCodeCountry($submitted);
        $this->validateName($submitted);
        $this->validateNativeNameCountry($submitted);
        $this->validateZoneCountry($submitted);

        if (empty($this->errors) && empty($submitted['default'])) {
            $this->country->unsetDefault($submitted['code']);
        }

        return $this->getResult();
    }

    /**
     * Validates a country to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateCountry(array &$submitted)
    {
        if (!empty($submitted['update']) && is_string($submitted['update'])) {
            $data = $this->country->get($submitted['update']);
            if (empty($data)) {
                $this->errors['update'] = $this->language->text('@name is unavailable', array(
                    '@name' => $this->language->text('Country')));
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

    /**
     * Validates a zone ID
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateZoneCountry(array $submitted)
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
            $this->errors['zone_id'] = $this->language->text('@name is unavailable', array(
                '@name' => $this->language->text('Zone')));
            return false;
        }

        return true;
    }

    /**
     * Validates a native country name
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateNativeNameCountry(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['native_name'])) {
            return null;
        }

        if (empty($submitted['native_name']) || mb_strlen($submitted['native_name']) > 255) {
            $options = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Native name'));
            $this->errors['native_name'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a country code
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateCodeCountry(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['code'])) {
            return null;
        }

        if (empty($submitted['code'])) {
            $this->errors['code'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Code')
            ));
            return false;
        }

        if (!preg_match('/^[A-Z]{2}$/', $submitted['code'])) {
            $this->errors['code'] = $this->language->text('Invalid country code. It must conform to ISO 3166-2 standard');
            return false;
        }

        $submitted['code'] = strtoupper($submitted['code']);

        if (isset($submitted['country']['code'])//
                && ($submitted['country']['code'] === $submitted['code'])) {
            return true;
        }

        $country = $this->country->get($submitted['code']);

        if (empty($country)) {
            return true;
        }

        $this->errors['code'] = $this->language->text('@object already exists', array(
            '@object' => $this->language->text('Code')));
        return false;
    }

}
