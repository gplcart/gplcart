<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\classes\Tool;
use core\models\Alias as ModelsAlias;
use core\models\Country as ModelsCountry;
use core\models\Language as ModelsLanguage;
use core\models\Currency as ModelsCurrency;
use core\models\PriceRule as ModelsPriceRule;
use core\models\CategoryGroup as ModelsCategoryGroup;

/**
 * Provides methods to validate various database related data
 */
class Model
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
     * Category group model instance
     * @var \core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Price rule model instance
     * @var \core\models\PriceRule $rule
     */
    protected $rule;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsAlias $alias
     * @param ModelsCountry $country
     * @param ModelsCurrency $currency
     * @param ModelsCategoryGroup $category_group
     * @param ModelsPriceRule $rule
     */
    public function __construct(ModelsLanguage $language, ModelsAlias $alias,
            ModelsCountry $country, ModelsCurrency $currency,
            ModelsCategoryGroup $category_group, ModelsPriceRule $rule)
    {
        $this->rule = $rule;
        $this->alias = $alias;
        $this->country = $country;
        $this->language = $language;
        $this->currency = $currency;
        $this->category_group = $category_group;
    }

    /**
     * Checks if an alias exists in the database
     * @param string $alias
     * @param array $options
     * @return boolean|string
     */
    public function alias($alias, array $options = array())
    {
        if (!isset($alias) || $alias === '') {
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
    public function countryCode($code, array $options = array())
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
     * Validates currency code uniqueness
     * @param string $code
     * @param array $options
     * @return boolean
     */
    public function currencyCode($code, array $options = array())
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

    /**
     * Validates category group type
     * @param string $value
     * @param array $options
     * @return boolean
     */
    public function categoryGroupType($value, array $options = array())
    {
        if (empty($value)) {
            return true;
        }

        $arguments = array(
            'type' => $value,
            'store_id' => $options['store_id']);

        $category_groups = $this->category_group->getList($arguments);

        if (isset($options['category_group_id'])) {
            unset($category_groups[$options['category_group_id']]);
        }

        if (empty($category_groups)) {
            return true;
        }

        return $this->language->text('Category group with this type already exists for this store');
    }

    /**
     * Validates and modifies price rule conditions
     * @return boolean|array
     */
    public function priceRuleConditions($value, array $options = array())
    {
        if (empty($value)) {
            return true;
        }

        $modified = $errors = array();
        $operators = array_map('htmlspecialchars', $this->rule->getConditionOperators());
        $conditions = Tool::stringToArray($value);

        foreach ($conditions as $line => $condition) {
            $line++;

            $condition = trim($condition);
            $parts = array_map('trim', explode(' ', $condition));

            $condition_id = array_shift($parts);
            $operator = array_shift($parts);

            $parameters = array_filter(explode(',', implode('', $parts)), function ($value) {
                return ($value !== "");
            });

            if (empty($parameters)) {
                $errors[] = $line;
                continue;
            }

            if (!in_array(htmlspecialchars($operator), $operators)) {
                $errors[] = $line;
                continue;
            }

            $validator = $this->rule->getConditionHandler($condition_id, 'validate');

            if (empty($validator)) {
                $errors[] = $line;
                continue;
            }

            $result = call_user_func_array($validator, array(&$parameters, $options['submitted']));

            if ($result !== true) {
                $errors[] = $line;
                continue;
            }

            $modified[] = array(
                'id' => $condition_id,
                'operator' => $operator,
                'value' => $parameters,
                'original' => $condition,
                'weight' => $line,
            );
        }

        if (empty($errors)) {
            return array('result' => $modified);
        }

        return $this->language->text('Error on lines %num', array(
                    '%num' => implode(',', $errors)));
    }

    /**
     * Validates price rule code uniqueness
     * @param string $value
     * @param array $options
     * @return boolean
     */
    public function priceRuleCode($value, array $options = array())
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
