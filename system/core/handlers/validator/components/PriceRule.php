<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

// Parent
use gplcart\core\Config;
use gplcart\core\models\File as FileModel,
    gplcart\core\models\User as UserModel,
    gplcart\core\models\Store as StoreModel,
    gplcart\core\models\Alias as AliasModel,
    gplcart\core\helpers\Request as RequestHelper,
    gplcart\core\models\Language as LanguageModel;
// New
use gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Trigger as TriggerModel,
    gplcart\core\models\Currency as CurrencyModel,
    gplcart\core\models\PriceRule as PriceRuleModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate price rule data
 */
class PriceRule extends ComponentValidator
{

    /**
     * Price rule model instance
     * @var \gplcart\core\models\PriceRule $rule
     */
    protected $rule;

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
     * @param Config $config
     * @param LanguageModel $language
     * @param FileModel $file
     * @param UserModel $user
     * @param StoreModel $store
     * @param AliasModel $alias
     * @param RequestHelper $request
     * @param PriceRuleModel $rule
     * @param TriggerModel $trigger
     * @param CurrencyModel $currency
     * @param PriceModel $price
     */
    public function __construct(Config $config, LanguageModel $language, FileModel $file,
            UserModel $user, StoreModel $store, AliasModel $alias, RequestHelper $request,
            PriceRuleModel $rule, TriggerModel $trigger, CurrencyModel $currency, PriceModel $price)
    {
        parent::__construct($config, $language, $file, $user, $store, $alias, $request);

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
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validatePriceRule();
        $this->validateName();
        $this->validateWeight();
        $this->validateUsedPriceRule();
        $this->validateTriggerPriceRule();
        $this->validateCurrencyPriceRule();
        $this->validateValueTypePriceRule();
        $this->validateCodePriceRule();
        $this->validateValuePriceRule();

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

        $data = $this->rule->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->language->text('Price rule'));
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
        $label = $this->language->text('Trigger');
        $trigger_id = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($trigger_id)) {
            return null;
        }

        if (empty($trigger_id)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($trigger_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $trigger = $this->trigger->get($trigger_id);

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
        $label = $this->language->text('Times used');
        $used = $this->getSubmitted($field);

        if (!isset($used)) {
            return null;
        }

        if (!is_numeric($used)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if (strlen($used) > 10) {
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
        $label = $this->language->text('Currency');
        $code = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($code)) {
            return null;
        }

        if (empty($code)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $currency = $this->currency->get($code);

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
        $type = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($type)) {
            return null;
        }

        if (empty($type) || !in_array($type, array('percent', 'fixed'))) {
            $this->setErrorInvalid($field, $this->language->text('Value type'));
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
        $label = $this->language->text('Code');
        $code = $this->getSubmitted($field);

        if (empty($code)) {
            return null;
        }

        $updating = $this->getUpdating();

        if (isset($updating['code']) && $updating['code'] === $code) {
            return true;
        }

        if (mb_strlen($code) > 255) {
            $this->setErrorLengthRange($field, $label, 0, 255);
            return false;
        }

        $rules = $this->rule->getList(array('code' => $code));

        if (empty($rules)) {
            return true;
        }

        // Search for exact match
        // because $this->rule->getList() uses LIKE for "code" field
        foreach ((array) $rules as $rule) {
            if ($rule['code'] === $code) {
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
        $label = $this->language->text('Value');
        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (!isset($value) || strlen($value) > 10) {
            $this->setErrorLengthRange($field, $label, 1, 10);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if ($this->isError()) {
            return true;
        }

        $updating = $this->getUpdating();
        $submitted_currency = $this->getSubmitted('currency');
        $submitted_value_type = $this->getSubmitted('value_type');

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
            $this->setSubmitted('value', $value);
        }

        return true;
    }

}
