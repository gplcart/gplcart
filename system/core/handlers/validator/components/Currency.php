<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

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
     * @param CurrencyModel $currency
     */
    public function __construct(CurrencyModel $currency)
    {
        parent::__construct();

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
            $this->setErrorUnavailable('update', $this->translation->text('Currency'));
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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        if (empty($value) || mb_strlen($value) > 10) {
            $this->setErrorLengthRange($field, $this->translation->text('Symbol'), 1, 10);
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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        if (empty($value) || mb_strlen($value) > 50) {
            $this->setErrorLengthRange($field, $this->translation->text('Major unit'), 1, 50);
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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        if (empty($value) || mb_strlen($value) > 50) {
            $this->setErrorLengthRange($field, $this->translation->text('Minor unit'), 1, 50);
            return false;
        }

        return true;
    }

    /**
     * Validates currency conversion rate
     * @return boolean|null
     */
    protected function validateConvertionRateCurrency()
    {
        $field = 'conversion_rate';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            return null;
        }

        $label = $this->translation->text('Conversion rate');

        if (empty($value) || strlen($value) > 10) {
            $this->setErrorLengthRange($field, $label, 1, 10);
            return false;
        }

        if (preg_match('/^[0-9]\d*(\.\d+)?$/', $value) !== 1) {
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
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Rounding step');

        if (strlen($value) > 10) {
            $this->setErrorLengthRange($field, $label, 1, 10);
            return false;
        }

        if (preg_match('/^[0-9]\d*(\.\d+)?$/', $value) !== 1) {
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
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        if (preg_match('/^[0-2]+$/', $value) !== 1) {
            $this->setErrorInvalid($field, $this->translation->text('Decimals'));
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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Numeric code');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (preg_match('/^[0-9]{3}$/', $value) !== 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $updating = $this->getUpdating();

        if (isset($updating['numeric_code']) && ($updating['numeric_code'] == $value)) {
            return true;
        }

        foreach ($this->currency->getList() as $currency) {
            if ($currency['numeric_code'] == $value) {
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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Code');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (preg_match('/^[a-zA-Z]{3}$/', $value) !== 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $code = strtoupper($value);
        $updating = $this->getUpdating();

        if (isset($updating['code']) && $updating['code'] === $code) {
            return true;
        }

        $existing = $this->currency->get($code);

        if (!empty($existing)) {
            $this->setErrorExists($field, $label);
            return false;
        }

        $this->setSubmitted($field, $code);
        return true;
    }

}
