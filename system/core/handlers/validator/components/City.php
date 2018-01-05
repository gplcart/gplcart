<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\Zone as ZoneModel,
    gplcart\core\models\City as CityModel,
    gplcart\core\models\State as StateModel,
    gplcart\core\models\Country as CountryModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate city data
 */
class City extends ComponentValidator
{

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * Zone model instance
     * @var \gplcart\core\models\Zone $zone
     */
    protected $zone;

    /**
     * City model instance
     * @var \gplcart\core\models\City $city
     */
    protected $city;

    /**
     * State model instance
     * @var \gplcart\core\models\State $state
     */
    protected $state;

    /**
     * @param CityModel $city
     * @param StateModel $state
     * @param CountryModel $country
     * @param ZoneModel $zone
     */
    public function __construct(CityModel $city, StateModel $state, CountryModel $country,
            ZoneModel $zone)
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
     * @return boolean|array
     */
    public function city(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateCity();
        $this->validateStatus();
        $this->validateName();
        $this->validateStateCity();
        $this->validateZoneCity();
        $this->validateCountryCity();

        return $this->getResult();
    }

    /**
     * Validates a city to be updated
     * @return boolean|null
     */
    protected function validateCity()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->city->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('City'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a state ID
     * @return boolean|null
     */
    protected function validateStateCity()
    {
        $field = 'state_id';

        if (isset($this->options['field']) && $this->options['field'] !== $field) {
            return null;
        }

        $state_id = $this->getSubmitted($field);
        $label = $this->translation->text('State');

        if ($this->isUpdating() && !isset($state_id)) {
            return null;
        }

        if (empty($state_id)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($state_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $state = $this->state->get($state_id);

        if (empty($state['state_id'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates a zone ID
     * @return boolean|null
     */
    protected function validateZoneCity()
    {
        $field = 'zone_id';
        $zone_id = $this->getSubmitted($field);
        $label = $this->translation->text('Zone');

        if (empty($zone_id)) {
            return null;
        }

        if (!is_numeric($zone_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $zone = $this->zone->get($zone_id);

        if (empty($zone['zone_id'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates a country code
     * @return boolean|null
     */
    protected function validateCountryCity()
    {
        $field = 'country';

        if (isset($this->options['field']) && $this->options['field'] !== $field) {
            return null;
        }

        $code = $this->getSubmitted($field);
        $label = $this->translation->text('Country');

        if ($this->isUpdating() && !isset($code)) {
            return null;
        }

        if (empty($code)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $country = $this->country->get($code);

        if (empty($country['code'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

}
