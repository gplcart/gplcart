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
 * Provides methods to validate country states data
 */
class State extends BaseValidator
{

    /**
     * State model instance
     * @var \gplcart\core\models\State $state
     */
    protected $state;

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
     * Constructor
     * @param StateModel $state
     * @param CountryModel $country
     * @param ZoneModel $zone
     */
    public function __construct(StateModel $state, CountryModel $country,
            ZoneModel $zone)
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
    public function state(array $submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateState();
        $this->validateStatus();
        $this->validateCodeState();
        $this->validateName();
        $this->validateCountryState();
        $this->validateZoneState();

        return $this->getResult();
    }

    /**
     * Validates a state to be updated
     * @return boolean|null
     */
    protected function validateState()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->state->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('State'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates country code
     * @return boolean|null
     */
    protected function validateCountryState()
    {
        $value = $this->getSubmitted('country');

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Country'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('country', $error);
            return false;
        }

        $country = $this->country->get($value);

        if (empty($country)) {
            $vars = array('@name' => $this->language->text('Country'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('country', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a state code
     * @return boolean
     */
    public function validateCodeState()
    {
        $updating = $this->getUpdating();
        $value = $this->getSubmitted('code');

        if (isset($updating['code']) && $updating['code'] === $value) {
            return true;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Code'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('code', $error);
            return false;
        }

        $country = $this->getSubmitted('country');
        $existing = $this->state->getByCode($value, $country);

        if (!empty($existing)) {
            $vars = array('@name' => $this->language->text('Code'));
            $error = $this->language->text('@name already exists', $vars);
            $this->setError('code', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a zone ID
     * @return boolean|null
     */
    protected function validateZoneState()
    {
        $value = $this->getSubmitted('zone_id');

        if (empty($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Zone'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('zone_id', $error);
            return false;
        }

        $zone = $this->zone->get($value);

        if (empty($zone)) {
            $vars = array('@name' => $this->language->text('Zone'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('zone_id', $error);
            return false;
        }

        return true;
    }

}
