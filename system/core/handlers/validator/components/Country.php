<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\Zone as ZoneModel,
    gplcart\core\models\State as StateModel,
    gplcart\core\models\Country as CountryModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate various database related data
 */
class Country extends ComponentValidator
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
     * State model instance
     * @var \gplcart\core\models\State $state
     */
    protected $state;

    /**
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
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateCountry();
        $this->validateWeightComponent();
        $this->validateStatusComponent();
        $this->validateCodeCountry();
        $this->validateNameComponent();
        $this->validateNativeNameCountry();
        $this->validateZoneCountry();

        return $this->getResult();
    }

    /**
     * Validates a country to be updated
     * @return boolean|null
     */
    protected function validateCountry()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->country->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->language->text('Country'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a zone ID
     * @return boolean|null
     */
    protected function validateZoneCountry()
    {
        $field = 'zone_id';
        $label = $this->language->text('Zone');
        $zone_id = $this->getSubmitted($field);

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
     * Validates a native country name
     * @return boolean|null
     */
    protected function validateNativeNameCountry()
    {
        $field = 'native_name';
        $label = $this->language->text('Native name');
        $native_name = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($native_name)) {
            return null;
        }

        if (empty($native_name) || mb_strlen($native_name) > 255) {
            $this->setErrorLengthRange($field, $label);
            return false;
        }
        return true;
    }

    /**
     * Validates a country code
     * @return boolean|null
     */
    protected function validateCodeCountry()
    {
        $field = 'code';
        $label = $this->language->text('Code');
        $code = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($code)) {
            return null;
        }

        if (empty($code)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (preg_match('/^[A-Z]{2}$/', $code) !== 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $code = strtoupper($code);

        $updating = $this->getUpdating();
        if (isset($updating['code']) && ($updating['code'] === $code)) {
            return true;
        }

        $existing = $this->country->get($code);

        if (!empty($existing['code'])) {
            $this->setErrorExists($field, $label);
            return false;
        }

        $this->setSubmitted('code', $code);
        return true;
    }

}
