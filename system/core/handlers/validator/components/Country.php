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
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateCountry();
        $this->validateWeight();
        $this->validateStatus();
        $this->validateCodeCountry();
        $this->validateName();
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
            $vars = array('@name' => $this->language->text('Country'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
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
        $zone_id = $this->getSubmitted('zone_id');

        if (empty($zone_id)) {
            return null;
        }

        if (!is_numeric($zone_id)) {
            $vars = array('@field' => $this->language->text('Zone'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('zone_id', $error);
            return false;
        }

        $zone = $this->zone->get($zone_id);

        if (empty($zone['zone_id'])) {
            $vars = array('@name' => $this->language->text('Zone'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('zone_id', $error);
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
        $native_name = $this->getSubmitted('native_name');

        if ($this->isUpdating() && !isset($native_name)) {
            return null;
        }

        if (empty($native_name) || mb_strlen($native_name) > 255) {
            $vars = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Native name'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('native_name', $error);
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
        $code = $this->getSubmitted('code');

        if ($this->isUpdating() && !isset($code)) {
            return null;
        }

        if (empty($code)) {
            $vars = array('@field' => $this->language->text('Code'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('code', $error);
            return false;
        }

        if (preg_match('/^[A-Z]{2}$/', $code) !== 1) {
            $error = $this->language->text('Invalid country code. It must conform to ISO 3166-2 standard');
            $this->setError('code', $error);
            return false;
        }

        $code = strtoupper($code);

        $updating = $this->getUpdating();
        if (isset($updating['code']) && ($updating['code'] === $code)) {
            return true;
        }

        $existing = $this->country->get($code);

        if (!empty($existing['code'])) {
            $vars = array('@name' => $this->language->text('Code'));
            $error = $this->language->text('@name already exists', $vars);
            $this->setError('code', $error);
            return false;
        }

        $this->setSubmitted('code', $code);
        return true;
    }

}
