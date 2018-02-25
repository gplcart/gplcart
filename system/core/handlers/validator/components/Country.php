<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component;
use gplcart\core\models\Country as CountryModel;
use gplcart\core\models\CountryState as CountryStateModel;
use gplcart\core\models\Zone as ZoneModel;

/**
 * Provides methods to validate various database related data
 */
class Country extends Component
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
     * @var \gplcart\core\models\CountryState $state
     */
    protected $state;

    /**
     * @param CountryModel $country
     * @param CountryStateModel $state
     * @param ZoneModel $zone
     */
    public function __construct(CountryModel $country, CountryStateModel $state, ZoneModel $zone)
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
        $this->validateBool('status');
        $this->validateCodeCountry();
        $this->validateName();
        $this->validateNativeNameCountry();
        $this->validateZoneCountry();
        $this->validateFormatCountry();

        $this->unsetSubmitted('update');

        return $this->getResult();
    }

    /**
     * Validates the country to be updated
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
            $this->setErrorUnavailable('update', $this->translation->text('Country'));
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
     * Validates a native country name
     * @return boolean|null
     */
    protected function validateNativeNameCountry()
    {
        $field = 'native_name';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        if (empty($value) || mb_strlen($value) > 255) {
            $this->setErrorLengthRange($field, $this->translation->text('Native name'));
            return false;
        }

        return true;
    }

    /**
     * Validates country format
     * @return boolean
     */
    protected function validateFormatCountry()
    {
        $field = 'format';
        $format = $this->getSubmitted($field);

        if (!isset($format)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Format');

        if (!is_array($format)) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $default = $this->country->getDefaultFormat();

        if (!array_intersect_key($format, $default)) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        foreach ($format as $key => $value) {

            if (!is_array($value) || !array_intersect_key($value, $default[$key])) {
                $this->setErrorInvalid($field, $label);
                return false;
            }

            foreach ($value as $v) {
                if (!in_array(gettype($v), array('string', 'integer', 'boolean'))) {
                    $this->setErrorInvalid($field, $label);
                    return false;
                }
            }
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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Code');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (preg_match('/^[a-zA-Z]{2}$/', $value) !== 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $code = strtoupper($value);
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
