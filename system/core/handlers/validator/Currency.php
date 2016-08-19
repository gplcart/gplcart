<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Language as ModelsLanguage;
use core\models\Currency as ModelsCurrency;

/**
 * Provides methods to validate various database related data
 */
class Currency
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsCurrency $currency
     */
    public function __construct(ModelsLanguage $language,
            ModelsCurrency $currency)
    {
        $this->language = $language;
        $this->currency = $currency;
    }

    /**
     * Validates currency code uniqueness
     * @param string $code
     * @param array $options
     * @return boolean
     */
    public function code($code, array $options = array())
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
