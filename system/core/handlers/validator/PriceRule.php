<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Trigger as TriggerModel;
use core\models\Currency as CurrencyModel;
use core\models\PriceRule as PriceRuleModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate price rule data
 */
class PriceRule extends BaseValidator
{

    /**
     * Price rule model instance
     * @var \core\models\PriceRule $rule
     */
    protected $rule;

    /**
     * Trigger model instance
     * @var \core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Constructor
     * @param PriceRuleModel $rule
     * @param TriggerModel $trigger
     */
    public function __construct(PriceRuleModel $rule, TriggerModel $trigger,
            CurrencyModel $currency)
    {
        parent::__construct();

        $this->rule = $rule;
        $this->trigger = $trigger;
        $this->currency = $currency;
    }

    /**
     * Performs full price rule validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function priceRule(array &$submitted, array $options = array())
    {
        $this->validatePriceRule($submitted);
        $this->validateName($submitted);
        $this->validateWeight($submitted);
        $this->validateUsedPriceRule($submitted);
        $this->validateTriggerPriceRule($submitted);
        $this->validateCurrencyPriceRule($submitted);
        $this->validateValueTypePriceRule($submitted);
        $this->validateCodePriceRule($submitted);
        $this->validateValuePriceRule($submitted);

        return $this->getResult();
    }

    /**
     * Validates a price rule to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validatePriceRule(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {
            $data = $this->rule->get($submitted['update']);
            if (empty($data)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Price rule')));
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

    /**
     * Validates a trigger ID
     * @param array $submitted
     * @return boolean
     */
    protected function validateTriggerPriceRule(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['trigger_id'])) {
            return null;
        }

        if (empty($submitted['trigger_id'])) {
            $this->errors['trigger_id'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Trigger')
            ));
            return false;
        }

        if (!is_numeric($submitted['trigger_id'])) {
            $options = array('@field' => $this->language->text('Trigger'));
            $this->errors['trigger_id'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        $trigger = $this->trigger->get($submitted['trigger_id']);

        if (empty($trigger)) {
            $this->errors['trigger_id'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Trigger')));
            return false;
        }

        return true;
    }

    /**
     * Validates "Times used" field
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateUsedPriceRule(array &$submitted)
    {
        if (!isset($submitted['user'])) {
            return null;
        }

        if (!is_numeric($submitted['used'])) {
            $options = array('@field' => $this->language->text('Times used'));
            $this->errors['used'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        if (strlen($submitted['used']) > 10) {
            $options = array('@max' => 10, '@field' => $this->language->text('Times used'));
            $this->errors['used'] = $this->language->text('@field must not be longer than @max characters', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a submitted currency
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateCurrencyPriceRule(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['currency'])) {
            return null;
        }

        if (empty($submitted['currency'])) {
            $this->errors['currency'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Currency')
            ));
            return false;
        }

        $currency = $this->currency->get($submitted['currency']);

        if (empty($currency)) {
            $this->errors['currency'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Currency')));
            return false;
        }

        return true;
    }

    /**
     * Validates a price rule value type
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateValueTypePriceRule(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['value_type'])) {
            return null;
        }

        if (empty($submitted['value_type']) || !in_array($submitted['value_type'], array('percent', 'fixed'))) {
            $this->errors['value_type'] = $this->language->text('Allowed value types: percent, fixed');
            return false;
        }

        return true;
    }

    /**
     * Validates a price rule code
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateCodePriceRule(array &$submitted)
    {
        if (empty($submitted['code'])) {
            return null;
        }

        if (isset($submitted['update']['code'])//
                && $submitted['update']['code'] === $submitted['code']) {
            return true;
        }

        if (mb_strlen($submitted['code']) > 255) {
            $options = array('@max' => 255, '@field' => $this->language->text('Code'));
            $this->errors['code'] = $this->language->text('@field must not be longer than @max characters', $options);
            return false;
        }

        $rules = $this->rule->getList(array('code' => $submitted['code']));

        if (empty($rules)) {
            return true;
        }

        // Search for exact match
        // because $this->rule->getList() uses LIKE for "code" field
        foreach ($rules as $rule) {

            if ($rule['code'] !== $submitted['code']) {
                continue;
            }

            $options = array('@code' => $submitted['code']);
            $this->errors['code'] = $this->language->text('Code @code already exists', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a price rule value
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateValuePriceRule(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['value'])) {
            return null;
        }

        if (!isset($submitted['value']) || strlen($submitted['value']) > 10) {
            $options = array('@min' => 1, '@max' => 10, '@field' => $this->language->text('Value'));
            $this->errors['value'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        if (!is_numeric($submitted['value'])) {
            $options = array('@field' => $this->language->text('Value'));
            $this->errors['value'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        if (!empty($this->errors)) {
            return true;
        }

        // Prepare value
        if (isset($submitted['value_type'])) {
            $value_type = $submitted['value_type'];
        } else if (isset($submitted['update']['value_type'])) {
            $value_type = $submitted['update']['value_type'];
        }

        if (isset($submitted['currency'])) {
            $currency = $submitted['currency'];
        } else if (isset($submitted['update']['currency'])) {
            $currency = $submitted['update']['currency'];
        }

        if (isset($value_type) && isset($currency) && $value_type === 'fixed') {
            $submitted['value'] = $this->price->amount((float) $submitted['value'], $currency, false);
        }

        return true;
    }

}
