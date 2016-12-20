<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Price as PriceModel;
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
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Constructor
     * @param PriceRuleModel $rule
     * @param TriggerModel $trigger
     * @param CurrencyModel $currency
     * @param PriceModel $price
     */
    public function __construct(PriceRuleModel $rule, TriggerModel $trigger,
            CurrencyModel $currency, PriceModel $price)
    {
        parent::__construct();

        $this->rule = $rule;
        $this->price = $price;
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
        $this->submitted = &$submitted;

        $this->validatePriceRule($options);
        $this->validateName($options);
        $this->validateWeight($options);
        $this->validateUsedPriceRule($options);
        $this->validateTriggerPriceRule($options);
        $this->validateCurrencyPriceRule($options);
        $this->validateValueTypePriceRule($options);
        $this->validateCodePriceRule($options);
        $this->validateValuePriceRule($options);

        return $this->getResult();
    }

    /**
     * Validates a price rule to be updated
     * @param array $options
     * @return boolean|null
     */
    protected function validatePriceRule(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->rule->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Price rule'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a trigger ID
     * @param array $options
     * @return boolean|null
     */
    protected function validateTriggerPriceRule(array $options)
    {
        $trigger_id = $this->getSubmitted('trigger_id', $options);

        if ($this->isUpdating() && !isset($trigger_id)) {
            return null;
        }

        if (empty($trigger_id)) {
            $vars = array('@field' => $this->language->text('Trigger'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('trigger_id', $error, $options);
            return false;
        }

        if (!is_numeric($trigger_id)) {
            $vars = array('@field' => $this->language->text('Trigger'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('trigger_id', $error, $options);
            return false;
        }

        $trigger = $this->trigger->get($trigger_id);

        if (empty($trigger)) {
            $vars = array('@name' => $this->language->text('Trigger'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('trigger_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates "Times used" field
     * @param array $options
     * @return boolean|null
     */
    protected function validateUsedPriceRule(array $options)
    {
        $used = $this->getSubmitted('used', $options);

        if (!isset($used)) {
            return null;
        }

        if (!is_numeric($used)) {
            $vars = array('@field' => $this->language->text('Times used'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('used', $error, $options);
            return false;
        }

        if (strlen($used) > 10) {
            $vars = array('@max' => 10, '@field' => $this->language->text('Times used'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('used', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a submitted currency
     * @param array $options
     * @return boolean|null
     */
    protected function validateCurrencyPriceRule(array $options)
    {
        $code = $this->getSubmitted('currency', $options);

        if ($this->isUpdating() && !isset($code)) {
            return null;
        }

        if (empty($code)) {
            $vars = array('@field' => $this->language->text('Currency'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('currency', $error, $options);
            return false;
        }

        $currency = $this->currency->get($code);

        if (empty($currency)) {
            $vars = array('@name' => $this->language->text('Currency'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('currency', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a price rule value type
     * @param array $options
     * @return boolean|null
     */
    protected function validateValueTypePriceRule(array $options)
    {
        $type = $this->getSubmitted('value_type', $options);

        if ($this->isUpdating() && !isset($type)) {
            return null;
        }

        if (empty($type) || !in_array($type, array('percent', 'fixed'))) {
            $vars = array('@field' => $this->language->text('Value type'), '@allowed' => 'percent, fixed');
            $error = $this->language->text('@field has invalid value. Allowed values: @allowed', $vars);
            $this->setError('value_type', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a price rule code
     * @param array $options
     * @return boolean|null
     */
    protected function validateCodePriceRule(array $options)
    {
        $code = $this->getSubmitted('code', $options);

        if (empty($code)) {
            return null;
        }

        $updating = $this->getUpdating();

        if (isset($updating['code']) && $updating['code'] === $code) {
            return true;
        }

        if (mb_strlen($code) > 255) {
            $vars = array('@max' => 255, '@field' => $this->language->text('Code'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('code', $error, $options);
            return false;
        }

        $rules = $this->rule->getList(array('code' => $code));

        if (empty($rules)) {
            return true;
        }

        // Search for exact match
        // because $this->rule->getList() uses LIKE for "code" field
        foreach ($rules as $rule) {

            if ($rule['code'] === $code) {
                $vars = array('@object' => $this->language->text('Code'));
                $error = $this->language->text('@object already exists', $vars);
                $this->setError('code', $error, $options);
                return false;
            }
        }

        return true;
    }

    /**
     * Validates a price rule value
     * @param array $options
     * @return boolean|null
     */
    protected function validateValuePriceRule(array $options)
    {
        $value = $this->getSubmitted('value', $options);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (!isset($value) || strlen($value) > 10) {
            $vars = array('@min' => 1, '@max' => 10, '@field' => $this->language->text('Value'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('value', $error, $options);
            return false;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Value'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('value', $error, $options);
            return false;
        }

        if ($this->isError()) {
            return true;
        }

        $updating = $this->getUpdating();
        $submitted_currency = $this->getSubmitted('currency', $options);
        $submitted_value_type = $this->getSubmitted('value_type', $options);

        // Prepare value
        if (isset($submitted_value_type)) {
            $value_type = $submitted_value_type;
        } else if (isset($updating['value_type'])) {
            $value_type = $updating['value_type'];
        }

        if (isset($submitted_currency)) {
            $currency = $submitted_currency;
        } else if (isset($updating['currency'])) {
            $currency = $updating['currency'];
        }

        if (isset($value_type) && isset($currency) && $value_type === 'fixed') {
            $value = $this->price->amount((float) $value, $currency, false);
            $this->setSubmitted('value', $value, $options);
        }

        return true;
    }

}
