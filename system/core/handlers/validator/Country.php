<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Zone as ZoneModel;
use gplcart\core\models\State as StateModel;
use gplcart\core\models\Country as CountryModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate various database related data
 */
class Country extends BaseValidator
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
        $this->submitted = &$submitted;

        $this->validateCountry($options);
        $this->validateWeight($options);
        $this->validateDefault($options);
        $this->validateStatus($options);
        $this->validateCodeCountry($options);
        $this->validateName($options);
        $this->validateNativeNameCountry($options);
        $this->validateZoneCountry($options);

        return $this->getResult();
    }

    /**
     * Validates a country to be updated
     * @param array $options
     * @return boolean|null
     */
    protected function validateCountry(array $options)
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
     * @param array $options
     * @return boolean|null
     */
    protected function validateZoneCountry(array $options)
    {
        $zone_id = $this->getSubmitted('zone_id', $options);

        if (empty($zone_id)) {
            return null;
        }

        if (!is_numeric($zone_id)) {
            $vars = array('@field' => $this->language->text('Zone'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('zone_id', $error, $options);
            return false;
        }

        $zone = $this->zone->get($zone_id);

        if (empty($zone['zone_id'])) {
            $vars = array('@name' => $this->language->text('Zone'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('zone_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a native country name
     * @param array $options
     * @return boolean|null
     */
    protected function validateNativeNameCountry(array $options)
    {
        $native_name = $this->getSubmitted('native_name', $options);

        if ($this->isUpdating() && !isset($native_name)) {
            return null;
        }

        if (empty($native_name) || mb_strlen($native_name) > 255) {
            $vars = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Native name'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('native_name', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a country code
     * @param array $options
     * @return boolean|null
     */
    protected function validateCodeCountry(array $options)
    {
        $code = $this->getSubmitted('code', $options);

        if ($this->isUpdating() && !isset($code)) {
            return null;
        }

        if (empty($code)) {
            $vars = array('@field' => $this->language->text('Code'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('code', $error, $options);
            return false;
        }

        if (preg_match('/^[A-Z]{2}$/', $code) !== 1) {
            $error = $this->language->text('Invalid country code. It must conform to ISO 3166-2 standard');
            $this->setError('code', $error, $options);
            return false;
        }

        $code = strtoupper($code);

        $updating = $this->getUpdating();
        if (isset($updating['code']) && ($updating['code'] === $code)) {
            return true;
        }

        $existing = $this->country->get($code);

        if (!empty($existing['code'])) {
            $vars = array('@object' => $this->language->text('Code'));
            $error = $this->language->text('@object already exists', $vars);
            $this->setError('code', $error, $options);
            return false;
        }

        $this->setSubmitted('code', $code, $options);
        return true;
    }

}
