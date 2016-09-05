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
    public function code($value, array $options = array())
    {
        if (empty($value)) {
            return true;
        }

        $arguments = array(
            'code' => $value,
            'store_id' => $options['store_id']);

        $rules = $this->rule->getList($arguments);

        if (isset($options['price_rule_id'])) {
            // Editing, exclude it from the results
            unset($rules[$options['price_rule_id']]);
        }

        if (empty($rules)) {
            return true; // No similar code found, passed validation
        }

        // Search for exact match
        // because $this->rule->getList() uses LIKE for "code" field
        foreach ($rules as $rule) {
            if ($rule['code'] === $value) {
                return $this->language->text('Code %code already exists for this store', array(
                            '%code' => $value));
            }
        }

        return true;
    }

}
