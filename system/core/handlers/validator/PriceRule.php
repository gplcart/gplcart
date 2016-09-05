<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Language as ModelsLanguage;
use core\models\PriceRule as ModelsPriceRule;

/**
 * Provides methods to validate price rules related data
 */
class PriceRule
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Price rule model instance
     * @var \core\models\PriceRule $rule
     */
    protected $rule;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsPriceRule $rule
     */
    public function __construct(ModelsLanguage $language, ModelsPriceRule $rule)
    {
        $this->rule = $rule;
        $this->language = $language;
    }

    /**
     * Validates price rule code uniqueness
     * @param string $value
     * @param array $options
     * @return boolean
     */
    public function codeUnique($value, array $options = array())
    {
        if (empty($value)) {
            return true;
        }

        if (isset($options['data']['code']) && $options['data']['code'] === $value) {
            return true;
        }

        $arguments = array('code' => $value);
        $rules = $this->rule->getList($arguments);

        if (empty($rules)) {
            return true;
        }

        // Search for exact match
        // because $this->rule->getList() uses LIKE for "code" field
        foreach ($rules as $rule) {
            if ($rule['code'] === $value) {
                return $this->language->text('Code %code already exists', array(
                            '%code' => $value));
            }
        }

        return true;
    }

}
