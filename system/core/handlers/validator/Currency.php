<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Currency as ModelsCurrency;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate various currency related data
 */
class Currency extends BaseValidator
{

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Constructor
     * @param ModelsCurrency $currency
     */
    public function __construct(ModelsCurrency $currency)
    {
        parent::__construct();
        $this->currency = $currency;
    }

    public function currency(array &$submitted)
    {
        $this->validateDefault($submitted);
        $this->validateStatus($submitted);
        $this->validateCodeCurrency($submitted);
        $this->validateNameCurrency($submitted);
        $this->validateNumericCodeCurrency($submitted);
        $this->validateSymbolCurrency($submitted);
        $this->validateMajorUnitCurrency($submitted);
        $this->validateMinorUnitCurrency($submitted);
        $this->validateConvertionRateCurrency($submitted);
        $this->validateDecimalsCurrency($submitted);
        $this->validateRoundingStepCurrency($submitted);
        $this->validateSymbolPlacementCurrency($submitted);
        $this->validateCodePlacementCurrency($submitted);

        return empty($this->errors) ? true : $this->errors;
    }

    /**
     * Validates symbol placement field value
     * @param array $submitted
     * @return boolean
     */
    protected function validateSymbolPlacementCurrency(array &$submitted)
    {
        if (isset($submitted['symbol_placement']) && !in_array($submitted['symbol_placement'], array('before', 'after'))) {
            $this->errors['symbol_placement'] = $this->language->text('Symbol placement can be either "before" or "after"');
            return false;
        }

        return true;
    }

    /**
     * Validates code placement field value
     * @param array $submitted
     * @return boolean
     */
    protected function validateCodePlacementCurrency(array &$submitted)
    {
        if (isset($submitted['code_placement']) && !in_array($submitted['code_placement'], array('before', 'after'))) {
            $this->errors['code_placement'] = $this->language->text('Code placement can be either "before" or "after"');
            return false;
        }

        return true;
    }

    /**
     * Validates currency symbol
     * @param array $submitted
     * @return boolean
     */
    protected function validateSymbolCurrency(array &$submitted)
    {
        if (empty($submitted['symbol']) || mb_strlen($submitted['symbol']) > 10) {
            $options = array('@min' => 1, '@max' => 10, '@field' => $this->language->text('Symbol'));
            $this->errors['symbol'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates currency major unit
     * @param array $submitted
     * @return boolean
     */
    protected function validateMajorUnitCurrency(array &$submitted)
    {
        if (empty($submitted['major_unit']) || mb_strlen($submitted['major_unit']) > 50) {
            $options = array('@min' => 1, '@max' => 50, '@field' => $this->language->text('Major unit'));
            $this->errors['major_unit'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates currency minor unit
     * @param array $submitted
     * @return boolean
     */
    protected function validateMinorUnitCurrency(array &$submitted)
    {
        if (empty($submitted['minor_unit']) || mb_strlen($submitted['minor_unit']) > 50) {
            $options = array('@min' => 1, '@max' => 50, '@field' => $this->language->text('Minor unit'));
            $this->errors['minor_unit'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates currency convertion rate
     * @param array $submitted
     * @return boolean
     */
    protected function validateConvertionRateCurrency(array &$submitted)
    {
        if (empty($submitted['convertion_rate']) || strlen($submitted['convertion_rate']) > 10) {
            $options = array('@min' => 1, '@max' => 10, '@field' => $this->language->text('Convertion rate'));
            $this->errors['convertion_rate'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        if (!preg_match('/^[0-9]\d*(\.\d+)?$/', $submitted['convertion_rate'])) {
            $this->errors['convertion_rate'] = $this->language->text('Invalid convertion rate. It must be decimal or integer positive value');
            return false;
        }

        return true;
    }

    /**
     * Validates currency rounding step
     * @param array $submitted
     * @return boolean
     */
    protected function validateRoundingStepCurrency(array &$submitted)
    {
        if (!isset($submitted['rounding_step']) || strlen($submitted['rounding_step']) > 10) {
            $options = array('@min' => 1, '@max' => 10, '@field' => $this->language->text('Rounding step'));
            $this->errors['rounding_step'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        if (!preg_match('/^[0-9]\d*(\.\d+)?$/', $submitted['rounding_step'])) {
            $this->errors['rounding_step'] = $this->language->text('Invalid rounding step value. It must be decimal or integer positive value');
            return false;
        }

        return true;
    }

    /**
     * Validates currency decimals
     * @param array $submitted
     * @return boolean
     */
    protected function validateDecimalsCurrency(array &$submitted)
    {
        if (!isset($submitted['decimals'])) {
            $this->errors['decimals'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Decimals')
            ));
            return false;
        }

        if (!preg_match('/^[0-2]+$/', $submitted['decimals'])) {
            $this->errors['decimals'] = $this->language->text('Invalid decimals value. It must be 0 - 2');
            return false;
        }

        return true;
    }

    /**
     * Validates currency name
     * @param array $submitted
     * @return boolean
     */
    protected function validateNameCurrency(array &$submitted)
    {
        if (empty($submitted['name']) || mb_strlen($submitted['name']) > 50) {
            $options = array('@min' => 1, '@max' => 50, '@field' => $this->language->text('Name'));
            $this->errors['name'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates currency numeric code
     * @param array $submitted
     * @return boolean
     */
    protected function validateNumericCodeCurrency(array &$submitted)
    {
        if (empty($submitted['numeric_code'])) {
            $this->errors['numeric_code'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Numeric code')
            ));
            return false;
        }

        if (!preg_match('/^[0-9]{3}$/', $submitted['numeric_code'])) {
            $this->errors['numeric_code'] = $this->language->text('Invalid numeric code. It must conform ISO 4217 standard');
            return false;
        }

        if (isset($submitted['currency']['numeric_code']) //
                && ($submitted['currency']['numeric_code'] == $submitted['numeric_code'])) {
            return true;
        }

        $existing = $this->currency->getByNumericCode($submitted['numeric_code']);

        if (empty($existing)) {
            return true;
        }

        $this->errors['numeric_code'] = $this->language->text('@object already exists', array(
            '@object' => $this->language->text('Numeric code')));
        return false;
    }

    /**
     * Validates currency code
     * @param array $submitted
     * @return boolean
     */
    protected function validateCodeCurrency(array &$submitted)
    {
        if (empty($submitted['code'])) {
            $this->errors['code'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Code')
            ));
            return false;
        }

        if (!preg_match('/^[A-Z]{3}$/', $submitted['code'])) {
            $this->errors['code'] = $this->language->text('Invalid currency code. It must conform ISO 4217 standard');
            return false;
        }

        $submitted['code'] = strtoupper($submitted['code']);

        if (isset($submitted['currency']['code']) //
                && ($submitted['currency']['code'] === $submitted['code'])) {
            return true;
        }

        $existing = $this->currency->get($submitted['code']);

        if (empty($existing)) {
            return true;
        }

        $this->errors['code'] = $this->language->text('@object already exists', array(
            '@object' => $this->language->text('Code')));
        return false;
    }

}
