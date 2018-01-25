<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use Exception;
use gplcart\core\handlers\validator\Component as ComponentValidator;
use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\models\Price as PriceModel;
use gplcart\core\models\PriceRule as PriceRuleModel;
use gplcart\core\models\Trigger as TriggerModel;

/**
 * Provides methods to validate price rule data
 */
class PriceRule extends ComponentValidator
{

    /**
     * Price rule model instance
     * @var \gplcart\core\models\PriceRule $rule
     */
    protected $price_rule;

    /**
     * Trigger model instance
     * @var \gplcart\core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * @param PriceRuleModel $rule
     * @param TriggerModel $trigger
     * @param CurrencyModel $currency
     * @param PriceModel $price
     */
    public function __construct(PriceRuleModel $rule, TriggerModel $trigger,
                                CurrencyModel $currency, PriceModel $price)
    {
        parent::__construct();

        $this->price = $price;
        $this->price_rule = $rule;
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
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validatePriceRule();
        $this->validateName();
        $this->validateWeight();
        $this->validateBool('status');
        $this->validateUsedPriceRule();
        $this->validateTriggerPriceRule();
        $this->validateCurrencyPriceRule();
        $this->validateValueTypePriceRule();
        $this->validateCodePriceRule();
        $this->validateValuePriceRule();

        $this->unsetSubmitted('update');

        return $this->getResult();
    }

    /**
     * Validates a price rule to be updated
     * @return boolean|null
     */
    protected function validatePriceRule()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->price_rule->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('Price rule'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a trigger ID
     * @return boolean|null
     */
    protected function validateTriggerPriceRule()
    {
        $field = 'trigger_id';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Trigger');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $trigger = $this->trigger->get($value);

        if (empty($trigger)) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates "Times used" field
     * @return boolean|null
     */
    protected function validateUsedPriceRule()
    {
        $field = 'used';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Times used');

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if (strlen($value) > 10) {
            $this->setErrorLengthRange($field, $label, 0, 10);
            return false;
        }

        return true;
    }

    /**
     * Validates a submitted currency
     * @return boolean|null
     */
    protected function validateCurrencyPriceRule()
    {
        $field = 'currency';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Currency');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $currency = $this->currency->get($value);

        if (empty($currency)) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates a price rule value type
     * @return boolean|null
     */
    protected function validateValueTypePriceRule()
    {
        $field = 'value_type';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $types = $this->price_rule->getTypes();

        if (empty($types[$value])) {
            $this->setErrorInvalid($field, $this->translation->text('Value type'));
            return false;
        }

        return true;
    }

    /**
     * Validates a price rule code
     * @return boolean|null
     */
    protected function validateCodePriceRule()
    {
        $field = 'code';
        $value = $this->getSubmitted($field);

        if (empty($value)) {
            return null;
        }

        $updating = $this->getUpdating();

        if (isset($updating['code']) && $updating['code'] === $value) {
            return true;
        }

        $label = $this->translation->text('Code');

        if (mb_strlen($value) > 255) {
            $this->setErrorLengthRange($field, $label, 0, 255);
            return false;
        }

        $rules = $this->price_rule->getList(array('code' => $value));

        if (empty($rules)) {
            return true;
        }

        foreach ((array) $rules as $rule) {
            if ($rule['code'] === $value) {
                $this->setErrorExists($field, $label);
                return false;
            }
        }

        return true;
    }

    /**
     * Validates a price rule value
     * @return boolean|null
     */
    protected function validateValuePriceRule()
    {
        $field = 'value';

        if ($this->isExcluded($field) || $this->isError('value_type')) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        if (!isset($value)) {
            $this->setErrorRequired($field, $this->translation->text('Value'));
            return false;
        }

        $updating = $this->getUpdating();
        $submitted_value_type = $this->getSubmitted('value_type');

        if (isset($submitted_value_type)) {
            $value_type = $submitted_value_type;
        } else if (isset($updating['value_type'])) {
            $value_type = $updating['value_type'];
        } else {
            $this->setErrorUnavailable('value_type', $this->translation->text('Value type'));
            return false;
        }

        try {
            $handlers = $this->price_rule->getTypes();
            return static::call($handlers, $value_type, 'validate', array($value, $this));
        } catch (Exception $ex) {
            $this->setError($field, $ex->getMessage());
            return false;
        }
    }

    /**
     * Validates the value of percent type
     * @param string $value
     * @return boolean
     */
    public function validateValuePercentPriceRule($value)
    {
        $field = 'value';
        $label = $this->translation->text('Value');

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if ($value == 0 || abs($value) > 100) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates the value of fixed type
     * @param string $value
     * @return boolean
     */
    public function validateValueFixedPriceRule($value)
    {
        $field = 'value';
        $label = $this->translation->text('Value');

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if (strlen($value) > 8) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates the value of final type
     * @param string $value
     * @return boolean
     */
    public function validateValueFinalPriceRule($value)
    {
        $field = 'value';
        $label = $this->translation->text('Value');

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if ($value < 0 || strlen($value) > 8) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        return true;
    }

}
