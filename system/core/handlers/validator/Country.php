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
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsCountry $country
     */
    public function __construct(ModelsLanguage $language, ModelsCountry $country)
    {
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
        if (isset($options['data']['code']) && ($options['data']['code'] === $code)) {
            return true;
        }

        $existing = $this->country->get($code);

        if (empty($existing)) {
            return true;
        }

        return $this->language->text('Country code %code already exists', array('%code' => $code));
    }

}
