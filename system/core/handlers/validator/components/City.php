<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component;
use gplcart\core\models\City as CityModel;
use gplcart\core\models\Country as CountryModel;
use gplcart\core\models\CountryState as CountryStateModel;
use gplcart\core\models\Zone as ZoneModel;

/**
 * Provides methods to validate city data
 */
class City extends Component
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
     * @var \gplcart\core\models\CountryState $state
     */
    protected $state;

    /**
     * @param CityModel $city
     * @param CountryStateModel $state
     * @param CountryModel $country
     * @param ZoneModel $zone
     */
    public function __construct(CityModel $city, CountryStateModel $state, CountryModel $country, ZoneModel $zone)
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
        $this->validateBool('status');
        $this->validateName();
        $this->validateStateCity();
        $this->validateZoneCity();
        $this->validateCountryCity();

        $this->unsetSubmitted('update');

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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        $label = $this->translation->text('Country state');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $state = $this->state->get($value);

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
        $value = $this->getSubmitted($field);

        if (empty($value)) {
            return null;
        }

        $label = $this->translation->text('Zone');

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $zone = $this->zone->get($value);

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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Country');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $country = $this->country->get($value);

        if (empty($country['code'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        $this->setSubmitted('country', $country['code']);
        return true;
    }

}
