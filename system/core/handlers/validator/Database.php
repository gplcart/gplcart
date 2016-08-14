<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Alias as ModelsAlias;
use core\models\Country as ModelsCountry;
use core\models\Language as ModelsLanguage;
use core\models\Currency as ModelsCurrency;

/**
 * Provides methods to validate various database related data
 */
class Database
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Alias model instance
     * @var \core\models\Alias $alias
     */
    protected $alias;

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;
    
    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;
    
    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsAlias $alias
     * @param ModelsCountry $country
     * @param ModelsCurrency $currency
     */
    public function __construct(ModelsLanguage $language, ModelsAlias $alias,
            ModelsCountry $country, ModelsCurrency $currency)
    {
        $this->alias = $alias;
        $this->country = $country;
        $this->language = $language;
        $this->currency = $currency;
    }

    /**
     * Checks if an alias exists in the database
     * @param string $alias
     * @param array $options
     * @return boolean|string
     */
    public function aliasUnique($alias, array $options = array())
    {
        if (empty($alias)) {
            return true;
        }

        $check_alias = true;
        if (isset($options['data']['alias']) && ($options['data']['alias'] === $alias)) {
            $check_alias = false;
        }

        if ($check_alias && $this->alias->exists($alias)) {
            return $this->language->text('URL alias already exists');
        }

        return true;
    }

    /**
     * Checks if a country code already exists in the database
     * @param string $code
     * @param array $options
     * @return boolean|string
     */
    public function countryCodeUnique($code, array $options = array())
    {
        $check = true;
        if (isset($options['data']['code']) && ($options['data']['code'] === $code)) {
            $check = false;
        }

        if ($check && $this->country->get($code)) {
            return $this->language->text('Country code %code already exists', array('%code' => $code));
        }

        return true;
    }
    
    /**
     * 
     * @param type $code
     * @param array $options
     * @return boolean
     */
    public function currencyCodeUnique($code, array $options = array())
    {
        $code = strtoupper($code);
        
        $check = true;
        if (isset($options['data']['code']) && ($options['data']['code'] === $code)) {
            $check = false;
        }

        if ($check && $this->currency->get($code)) {
            return $this->language->text('Currency code %code already exists', array('%code' => $code));
        }

        return true;
    }

}
