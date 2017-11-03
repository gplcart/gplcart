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
use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate various currency related data
 */
class Currency extends ComponentValidator
{

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * @param Config $config
     * @param LanguageModel $language
     * @param FileModel $file
     * @param UserModel $user
     * @param StoreModel $store
     * @param AliasModel $alias
     * @param RequestHelper $request
     * @param CurrencyModel $currency
     */
    public function __construct(Config $config, LanguageModel $language, FileModel $file,
            UserModel $user, StoreModel $store, AliasModel $alias, RequestHelper $request,
            CurrencyModel $currency)
    {
        parent::__construct($config, $language, $file, $user, $store, $alias, $request);

        $this->currency = $currency;
    }

    /**
     * Performs full currency data validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function currency(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateCurrency();
        $this->validateDefault();
        $this->validateStatus();
        $this->validateCodeCurrency();
        $this->validateName();
        $this->validateNumericCodeCurrency();
        $this->validateSymbolCurrency();
        $this->validateMajorUnitCurrency();
        $this->validateMinorUnitCurrency();
        $this->validateConvertionRateCurrency();
        $this->validateDecimalsCurrency();
        $this->validateRoundingStepCurrency();

        // Remove data of updating currency
        // to prevent from saving in serialized string
        $this->unsetSubmitted('update');

        return $this->getResult();
    }

    /**
     * Validates a currency to be updated
     * @return boolean|null
     */
    protected function validateCurrency()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->currency->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->language->text('Currency'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates currency symbol
     * @return boolean|null
     */
    protected function validateSymbolCurrency()
    {
        $field = 'symbol';
        $label = $this->language->text('Symbol');
        $symbol = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($symbol)) {
            return null;
        }

        if (empty($symbol) || mb_strlen($symbol) > 10) {
            $this->setErrorLengthRange($field, $label, 1, 10);
            return false;
        }
        return true;
    }

    /**
     * Validates currency major unit
     * @return boolean|null
     */
    protected function validateMajorUnitCurrency()
    {
        $field = 'major_unit';
        $label = $this->language->text('Major unit');
        $major_unit = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($major_unit)) {
            return null;
        }

        if (empty($major_unit) || mb_strlen($major_unit) > 50) {
            $this->setErrorLengthRange($field, $label, 1, 50);
            return false;
        }
        return true;
    }

    /**
     * Validates currency minor unit
     * @return boolean|null
     */
    protected function validateMinorUnitCurrency()
    {
        $field = 'minor_unit';
        $label = $this->language->text('Minor unit');
        $minor_unit = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($minor_unit)) {
            return null;
        }

        if (empty($minor_unit) || mb_strlen($minor_unit) > 50) {
            $this->setErrorLengthRange($field, $label, 1, 50);
            return false;
        }
        return true;
    }

    /**
     * Validates currency Conversion rate
     * @return boolean|null
     */
    protected function validateConvertionRateCurrency()
    {
        $field = 'conversion_rate';
        $label = $this->language->text('Conversion rate');
        $conversion_rate = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($conversion_rate)) {
            return null;
        }

        if (empty($conversion_rate) || strlen($conversion_rate) > 10) {
            $this->setErrorLengthRange($field, $label, 1, 10);
            return false;
        }

        if (preg_match('/^[0-9]\d*(\.\d+)?$/', $conversion_rate) !== 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }
        return true;
    }

    /**
     * Validates currency rounding step
     * @return boolean|null
     */
    protected function validateRoundingStepCurrency()
    {
        $field = 'rounding_step';
        $label = $this->language->text('Rounding step');
        $rounding_step = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($rounding_step)) {
            return null;
        }

        if (!isset($rounding_step) || strlen($rounding_step) > 10) {
            $this->setErrorLengthRange($field, $label, 1, 10);
            return false;
        }

        if (preg_match('/^[0-9]\d*(\.\d+)?$/', $rounding_step) !== 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }
        return true;
    }

    /**
     * Validates currency decimals
     * @return boolean|null
     */
    protected function validateDecimalsCurrency()
    {
        $field = 'decimals';
        $label = $this->language->text('Decimals');
        $decimals = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($decimals)) {
            return null;
        }

        if (!isset($decimals)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (preg_match('/^[0-2]+$/', $decimals) !== 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }
        return true;
    }

    /**
     * Validates currency numeric code
     * @return boolean|null
     */
    protected function validateNumericCodeCurrency()
    {
        $field = 'numeric_code';
        $label = $this->language->text('Numeric code');
        $numeric_code = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($numeric_code)) {
            return null;
        }

        if (empty($numeric_code)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (preg_match('/^[0-9]{3}$/', $numeric_code) !== 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $updating = $this->getUpdating();

        if (isset($updating['numeric_code'])//
                && ($updating['numeric_code'] == $numeric_code)) {
            return true;
        }

        foreach ($this->currency->getList() as $currency) {
            if ($currency['numeric_code'] == $numeric_code) {
                $this->setErrorExists($field, $label);
                return false;
            }
        }

        return true;
    }

    /**
     * Validates currency code
     * @return boolean|null
     */
    protected function validateCodeCurrency()
    {
        $field = 'code';
        $label = $this->language->text('Code');
        $code = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($code)) {
            return null;
        }

        if (empty($code)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (preg_match('/^[A-Z]{3}$/', $code) !== 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $code = strtoupper($code);
        $updating = $this->getUpdating();

        if (isset($updating['code']) && ($updating['code'] === $code)) {
            return true;
        }

        $existing = $this->currency->get($code);

        if (!empty($existing)) {
            $this->setErrorExists($field, $label);
            return false;
        }

        $this->setSubmitted('code', $code);
        return true;
    }

}
