<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\models\Country;
use gplcart\core\models\State;
use gplcart\core\models\Translation;
use gplcart\core\models\Zone;

/**
 * Contains methods to validate shipping address conditions
 */
class ShippingAddress
{

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

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
     * @param Country $country
     * @param State $state
     * @param Zone $zone
     * @param Translation $translation
     */
    public function __construct(Country $country, State $state, Zone $zone, Translation $translation)
    {
        $this->zone = $zone;
        $this->state = $state;
        $this->country = $country;
        $this->translation = $translation;
    }

    /**
     * Validates the country code condition
     * @param array $values
     * @return boolean|string
     */
    public function countryCode(array $values)
    {
        $existing = array_filter($values, function ($code) {
            $country = $this->country->get($code);
            return isset($country['code']);
        });

        if (count($values) != count($existing)) {
            return $this->translation->text('@name is unavailable', array(
                '@name' => $this->translation->text('Country')));
        }

        return true;
    }

    /**
     * Validates the country state condition
     * @param array $values
     * @return boolean|string
     */
    public function stateId(array $values)
    {
        $count = count($values);
        $ids = array_filter($values, 'ctype_digit');

        if ($count != count($ids)) {
            return $this->translation->text('@field has invalid value', array(
                '@field' => $this->translation->text('Condition')));
        }

        $existing = array_filter($values, function ($state_id) {
            $state = $this->state->get($state_id);
            return isset($state['state_id']);
        });

        if ($count != count($existing)) {
            return $this->translation->text('@name is unavailable', array(
                '@name' => $this->translation->text('State')));
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
            return $this->translation->text('Unsupported operator');
        }

        $zone = $this->zone->get(reset($values));

        if (empty($zone)) {
            return $this->translation->text('@name is unavailable', array(
                '@name' => $this->translation->text('Condition')));
        }

        return true;
    }

}
