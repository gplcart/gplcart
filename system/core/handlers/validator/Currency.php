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
        $this->validateSymbolPlacementCurrency();
        $this->validateCodePlacementCurrency();

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
     * @return boolean
     */
    protected function validateSymbolPlacementCurrency()
    {
        $symbol_placement = $this->getSubmitted('symbol_placement');

        if (!isset($symbol_placement) || in_array($symbol_placement, array('before', 'after'))) {
            return true;
        }

        $error = $this->language->text('Symbol placement can be either "before" or "after"');
        $this->setError('symbol_placement', $error);
        return false;
    }

    /**
     * Validates code placement field value
     * @return boolean
     */
    protected function validateCodePlacementCurrency()
    {
        $code_placement = $this->getSubmitted('code_placement');

        if (!isset($code_placement) || in_array($code_placement, array('before', 'after'))) {
            return true;
        }

        $error = $this->language->text('Code placement can be either "before" or "after"');
        $this->setError('code_placement', $error);
        return false;
    }

    /**
     * Validates currency symbol
     * @return boolean|null
     */
    protected function validateSymbolCurrency()
    {
        $symbol = $this->getSubmitted('symbol');

        if ($this->isUpdating() && !isset($symbol)) {
            return null;
        }

        if (empty($symbol) || mb_strlen($symbol) > 10) {
            $vars = array('@min' => 1, '@max' => 10, '@field' => $this->language->text('Symbol'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('symbol', $error);
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
        $major_unit = $this->getSubmitted('major_unit');

        if ($this->isUpdating() && !isset($major_unit)) {
            return null;
        }

        if (empty($major_unit) || mb_strlen($major_unit) > 50) {
            $vars = array('@min' => 1, '@max' => 50, '@field' => $this->language->text('Major unit'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('major_unit', $error);
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
        $minor_unit = $this->getSubmitted('minor_unit');

        if ($this->isUpdating() && !isset($minor_unit)) {
            return null;
        }

        if (empty($minor_unit) || mb_strlen($minor_unit) > 50) {
            $vars = array('@min' => 1, '@max' => 50, '@field' => $this->language->text('Minor unit'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('minor_unit', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates currency convertion rate
     * @return boolean|null
     */
    protected function validateConvertionRateCurrency()
    {
        $convertion_rate = $this->getSubmitted('convertion_rate');

        if ($this->isUpdating() && !isset($convertion_rate)) {
            return null;
        }

        if (empty($convertion_rate) || strlen($convertion_rate) > 10) {
            $vars = array('@min' => 1, '@max' => 10, '@field' => $this->language->text('Convertion rate'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('convertion_rate', $error);
            return false;
        }

        if (preg_match('/^[0-9]\d*(\.\d+)?$/', $convertion_rate) !== 1) {
            $error = $this->language->text('Invalid convertion rate. It must be decimal or integer positive value');
            $this->setError('convertion_rate', $error);
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
        $rounding_step = $this->getSubmitted('rounding_step');

        if ($this->isUpdating() && !isset($rounding_step)) {
            return null;
        }

        if (!isset($rounding_step) || strlen($rounding_step) > 10) {
            $vars = array('@min' => 1, '@max' => 10, '@field' => $this->language->text('Rounding step'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('rounding_step', $error);
            return false;
        }

        if (preg_match('/^[0-9]\d*(\.\d+)?$/', $rounding_step) !== 1) {
            $error = $this->language->text('Invalid rounding step value. It must be decimal or integer positive value');
            $this->setError('rounding_step', $error);
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
        $decimals = $this->getSubmitted('decimals');

        if ($this->isUpdating() && !isset($decimals)) {
            return null;
        }

        if (!isset($decimals)) {
            $vars = array('@field' => $this->language->text('Decimals'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('decimals', $error);
            return false;
        }

        if (preg_match('/^[0-2]+$/', $decimals) !== 1) {
            $error = $this->language->text('Invalid decimals value. It must be 0 - 2');
            $this->setError('decimals', $error);
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
        $numeric_code = $this->getSubmitted('numeric_code');

        if ($this->isUpdating() && !isset($numeric_code)) {
            return null;
        }

        if (empty($numeric_code)) {
            $vars = array('@field' => $this->language->text('Numeric code'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('numeric_code', $error);
            return false;
        }

        if (preg_match('/^[0-9]{3}$/', $numeric_code) !== 1) {
            $error = $this->language->text('Invalid numeric code. It must conform to ISO 4217 standard');
            $this->setError('numeric_code', $error);
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
        $this->setError('numeric_code', $error);
        return false;
    }

    /**
     * Validates currency code
     * @return boolean|null
     */
    protected function validateCodeCurrency()
    {
        $code = $this->getSubmitted('code');

        if ($this->isUpdating() && !isset($code)) {
            return null;
        }

        if (empty($code)) {
            $vars = array('@field' => $this->language->text('Code'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('code', $error);
            return false;
        }

        if (preg_match('/^[A-Z]{3}$/', $code) !== 1) {
            $error = $this->language->text('Invalid currency code. It must conform to ISO 4217 standard');
            $this->setError('code', $error);
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
            $this->setError('code', $error);
            return false;
        }

        $this->setSubmitted('code', $code);
        return true;
    }

}
