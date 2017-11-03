<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

// Parent
use gplcart\core\Config;
use gplcart\core\models\File as FileModel,
    gplcart\core\models\User as UserModel,
    gplcart\core\models\Store as StoreModel,
    gplcart\core\models\Alias as AliasModel,
    gplcart\core\helpers\Request as RequestHelper,
    gplcart\core\models\Language as LanguageModel;
// New
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
     * @param Config $config
     * @param LanguageModel $language
     * @param FileModel $file
     * @param UserModel $user
     * @param StoreModel $store
     * @param AliasModel $alias
     * @param RequestHelper $request
     * @param CityModel $city
     * @param StateModel $state
     * @param CountryModel $country
     * @param ZoneModel $zone
     */
    public function __construct(Config $config, LanguageModel $language, FileModel $file,
            UserModel $user, StoreModel $store, AliasModel $alias, RequestHelper $request,
            CityModel $city, StateModel $state, CountryModel $country, ZoneModel $zone)
    {
        parent::__construct($config, $language, $file, $user, $store, $alias, $request);

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
            $this->setErrorUnavailable('update', $this->language->text('City'));
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
        $label = $this->language->text('State');
        $state_id = $this->getSubmitted($field);

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
     * @return boolean
     */
    protected function validateZoneCity()
    {
        $field = 'zone_id';
        $label = $this->language->text('Zone');
        $zone_id = $this->getSubmitted($field);

        if (empty($zone_id)) {
            return true;
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
        $label = $this->language->text('Country');
        $code = $this->getSubmitted($field);

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
