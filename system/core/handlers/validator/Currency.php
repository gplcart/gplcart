<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate various currency related data
 */
class Currency extends BaseValidator
{

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Constructor
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
        $this->submitted = &$submitted;

        $this->validateCurrency($options);
        $this->validateDefault($options);
        $this->validateStatus($options);
        $this->validateCodeCurrency($options);
        $this->validateName($options);
        $this->validateNumericCodeCurrency($options);
        $this->validateSymbolCurrency($options);
        $this->validateMajorUnitCurrency($options);
        $this->validateMinorUnitCurrency($options);
        $this->validateConvertionRateCurrency($options);
        $this->validateDecimalsCurrency($options);
        $this->validateRoundingStepCurrency($options);
        $this->validateSymbolPlacementCurrency($options);
        $this->validateCodePlacementCurrency($options);

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
            $vars = array('@name' => $this->language->text('Currency'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates symbol placement field value
     * @param array $options
     * @return boolean
     */
    protected function validateSymbolPlacementCurrency(array $options)
    {
        $symbol_placement = $this->getSubmitted('symbol_placement', $options);

        if (!isset($symbol_placement) || in_array($symbol_placement, array('before', 'after'))) {
            return true;
        }

        $error = $this->language->text('Symbol placement can be either "before" or "after"');
        $this->setError('symbol_placement', $error, $options);
        return false;
    }

    /**
     * Validates code placement field value
     * @param array $options
     * @return boolean
     */
    protected function validateCodePlacementCurrency(array $options)
    {
        $code_placement = $this->getSubmitted('code_placement', $options);

        if (!isset($code_placement) || in_array($code_placement, array('before', 'after'))) {
            return true;
        }

        $error = $this->language->text('Code placement can be either "before" or "after"');
        $this->setError('code_placement', $error, $options);
        return false;
    }

    /**
     * Validates currency symbol
     * @param array $options
     * @return boolean|null
     */
    protected function validateSymbolCurrency(array $options)
    {
        $symbol = $this->getSubmitted('symbol', $options);

        if ($this->isUpdating() && !isset($symbol)) {
            return null;
        }

        if (empty($symbol) || mb_strlen($symbol) > 10) {
            $vars = array('@min' => 1, '@max' => 10, '@field' => $this->language->text('Symbol'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('symbol', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates currency major unit
     * @param array $options
     * @return boolean|null
     */
    protected function validateMajorUnitCurrency(array $options)
    {
        $major_unit = $this->getSubmitted('major_unit', $options);

        if ($this->isUpdating() && !isset($major_unit)) {
            return null;
        }

        if (empty($major_unit) || mb_strlen($major_unit) > 50) {
            $vars = array('@min' => 1, '@max' => 50, '@field' => $this->language->text('Major unit'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('major_unit', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates currency minor unit
     * @param array $options
     * @return boolean|null
     */
    protected function validateMinorUnitCurrency(array $options)
    {
        $minor_unit = $this->getSubmitted('minor_unit', $options);

        if ($this->isUpdating() && !isset($minor_unit)) {
            return null;
        }

        if (empty($minor_unit) || mb_strlen($minor_unit) > 50) {
            $vars = array('@min' => 1, '@max' => 50, '@field' => $this->language->text('Minor unit'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('minor_unit', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates currency convertion rate
     * @param array $options
     * @return boolean|null
     */
    protected function validateConvertionRateCurrency(array $options)
    {
        $convertion_rate = $this->getSubmitted('convertion_rate', $options);

        if ($this->isUpdating() && !isset($convertion_rate)) {
            return null;
        }

        if (empty($convertion_rate) || strlen($convertion_rate) > 10) {
            $vars = array('@min' => 1, '@max' => 10, '@field' => $this->language->text('Convertion rate'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('convertion_rate', $error, $options);
            return false;
        }

        if (preg_match('/^[0-9]\d*(\.\d+)?$/', $convertion_rate) !== 1) {
            $error = $this->language->text('Invalid convertion rate. It must be decimal or integer positive value');
            $this->setError('convertion_rate', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates currency rounding step
     * @param array $options
     * @return boolean|null
     */
    protected function validateRoundingStepCurrency(array $options)
    {
        $rounding_step = $this->getSubmitted('rounding_step', $options);

        if ($this->isUpdating() && !isset($rounding_step)) {
            return null;
        }

        if (!isset($rounding_step) || strlen($rounding_step) > 10) {
            $vars = array('@min' => 1, '@max' => 10, '@field' => $this->language->text('Rounding step'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('rounding_step', $error, $options);
            return false;
        }

        if (preg_match('/^[0-9]\d*(\.\d+)?$/', $rounding_step) !== 1) {
            $error = $this->language->text('Invalid rounding step value. It must be decimal or integer positive value');
            $this->setError('rounding_step', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates currency decimals
     * @param array $options
     * @return boolean|null
     */
    protected function validateDecimalsCurrency(array $options)
    {
        $decimals = $this->getSubmitted('decimals', $options);

        if ($this->isUpdating() && !isset($decimals)) {
            return null;
        }

        if (!isset($decimals)) {
            $vars = array('@field' => $this->language->text('Decimals'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('decimals', $error, $options);
            return false;
        }

        if (preg_match('/^[0-2]+$/', $decimals) !== 1) {
            $error = $this->language->text('Invalid decimals value. It must be 0 - 2');
            $this->setError('decimals', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates currency numeric code
     * @param array $options
     * @return boolean|null
     */
    protected function validateNumericCodeCurrency(array $options)
    {
        $numeric_code = $this->getSubmitted('numeric_code', $options);

        if ($this->isUpdating() && !isset($numeric_code)) {
            return null;
        }

        if (empty($numeric_code)) {
            $vars = array('@field' => $this->language->text('Numeric code'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('numeric_code', $error, $options);
            return false;
        }

        if (preg_match('/^[0-9]{3}$/', $numeric_code) !== 1) {
            $error = $this->language->text('Invalid numeric code. It must conform to ISO 4217 standard');
            $this->setError('numeric_code', $error, $options);
            return false;
        }

        $updating = $this->getUpdating();

        if (isset($updating['numeric_code']) //
                && ($updating['numeric_code'] == $numeric_code)) {
            return true;
        }

        $existing = $this->currency->getByNumericCode($numeric_code);

        if (empty($existing)) {
            return true;
        }

        $vars = array('@object' => $this->language->text('Numeric code'));
        $error = $this->language->text('@object already exists', $vars);
        $this->setError('numeric_code', $error, $options);
        return false;
    }

    /**
     * Validates currency code
     * @param array $options
     * @return boolean|null
     */
    protected function validateCodeCurrency(array $options)
    {
        $code = $this->getSubmitted('code', $options);

        if ($this->isUpdating() && !isset($code)) {
            return null;
        }

        if (empty($code)) {
            $vars = array('@field' => $this->language->text('Code'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('code', $error, $options);
            return false;
        }

        if (preg_match('/^[A-Z]{3}$/', $code) !== 1) {
            $error = $this->language->text('Invalid currency code. It must conform to ISO 4217 standard');
            $this->setError('code', $error, $options);
            return false;
        }

        $code = strtoupper($code);
        $updating = $this->getUpdating();

        if (isset($updating['code']) && ($updating['code'] === $code)) {
            return true;
        }

        $existing = $this->currency->get($code);

        if (!empty($existing)) {
            $vars = array('@object' => $this->language->text('Code'));
            $error = $this->language->text('@object already exists', $vars);
            $this->setError('code', $error, $options);
            return false;
        }

        $this->setSubmitted('code', $code, $options);
        return true;
    }

}
