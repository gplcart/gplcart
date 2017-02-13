<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\models\Zone as ZoneModel,
    gplcart\core\models\State as StateModel,
    gplcart\core\models\Country as CountryModel,
    gplcart\core\models\Language as LanguageModel;

/**
 * Contains methods to validate payment address conditions
 */
class Payment
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \gplcart\core\models\State $state
     */
    protected $state;

    /**
     * Zone model instance
     * @var \gplcart\core\models\Zone $zone
     */
    protected $zone;

    /**
     * Constructor
     * @param CountryModel $country
     * @param StateModel $state
     * @param ZoneModel $zone
     * @param LanguageModel $language
     */
    public function __construct(CountryModel $country, StateModel $state,
            ZoneModel $zone, LanguageModel $language)
    {
        $this->zone = $zone;
        $this->state = $state;
        $this->country = $country;
        $this->language = $language;
    }

    /**
     * Validates a country code condition
     * @param array $values
     * @return boolean|string
     */
    public function countryCode(array $values)
    {
        $exists = array_filter($values, function ($code) {
            $country = $this->country->get($code);
            return isset($country['code']);
        });

        if (count($values) != count($exists)) {
            $vars = array('@name' => $this->language->text('Country'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates a country state condition
     * @param array $values
     * @return boolean|string
     */
    public function stateId(array $values)
    {
        $count = count($values);
        $ids = array_filter($values, 'is_numeric');

        if ($count != count($ids)) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $exists = array_filter($values, function ($state_id) {
            $state = $this->state->get($state_id);
            return isset($state['state_id']);
        });

        if ($count != count($exists)) {
            $vars = array('@name' => $this->language->text('State'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates a zone ID condition
     * @param array $values
     * @param string $operator
     * @return boolean
     */
    public function zoneId(array $values, $operator)
    {
        if (!in_array($operator, array('=', '!='))) {
            return $this->language->text('Unsupported operator');
        }

        $zone = $this->zone->get(reset($values));

        if (empty($zone)) {
            $vars = array('@name' => $this->language->text('Condition'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

}
