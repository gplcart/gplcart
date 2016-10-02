<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Country as ModelsCountry;
use core\models\Language as ModelsLanguage;
use core\models\State as ModelsState;

/**
 * Provides methods to validate various database related data
 */
class Country
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsCountry $country
     * @param ModelsState $state
     */
    public function __construct(
        ModelsLanguage $language,
        ModelsCountry $country,
        ModelsState $state
    ) {
        $this->state = $state;
        $this->country = $country;
        $this->language = $language;
    }

    /**
     * Checks if a country code already exists in the database
     * @param string $code
     * @param array $options
     * @return boolean|string
     */
    public function codeUnique($code, array $options = array())
    {
        $code = strtoupper($code);

        if (isset($options['data']['code']) && ($options['data']['code'] === $code)) {
            return true;
        }

        $existing = $this->country->get($code);

        if (empty($existing)) {
            return true;
        }

        return $this->language->text('Country code %code already exists', array(
            '%code' => $code
        ));
    }

    /**
     * Checks country format fields
     * @param string $value
     * @param array $options
     * @return boolean|array
     */
    public function format($value, array $options = array())
    {
        if(empty($value) && empty($options['required'])){
            return true;
        }

        $country = empty($value['country']) ? '' : $value['country'];

        $countries = $this->country->getNames(true);
        $format = $this->country->getFormat($country, true);
        
        $conditions = array('status' => 1, 'country' => $country);
        $states = $this->state->getList($conditions);

        $errors = array();
        foreach ($format as $field => $info) {

            if ($field === 'state_id' && empty($states)) {
                continue;
            }

            if ($field === 'country' && $country === '' && !empty($countries)) {
                $errors['country'] = $this->language->text('Required field');
            }

            if (empty($info['required'])) {
                continue;
            }

            if (empty($value[$field]) || mb_strlen($value[$field]) > 255) {
                $errors[$field] = $this->language->text('Content must be %min - %max characters long', array(
                    '%min' => 1,
                    '%max' => 255
                ));
            }
        }

        return empty($errors) ? true : $errors;
    }

}
