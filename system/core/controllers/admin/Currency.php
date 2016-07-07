<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Currency as ModelsCurrency;

/**
 * Handles incoming requests and outputs data related to currency
 */
class Currency extends Controller
{

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Constructor
     * @param C $currency
     */
    public function __construct(ModelsCurrency $currency)
    {
        parent::__construct();

        $this->currency = $currency;
    }

    /**
     * Displays the currency overview page
     */
    public function currencies()
    {
        $this->data['default_currency'] = $this->currency->getDefault();
        $this->data['currencies'] = $this->currency->getList();

        $this->setTitleCurrencies();
        $this->setBreadcrumbCurrencies();
        $this->outputCurrencies();
    }

    /**
     * Displays the currency edit form
     * @param string|null $code
     */
    public function edit($code = null)
    {
        $currency = $this->get($code);

        $this->data['currency'] = $currency;
        $this->data['default_currency'] = $this->currency->getDefault();

        if ($this->request->post('delete')) {
            $this->delete($currency);
        }

        if ($this->request->post('save')) {
            $this->submit($currency);
        }

        $this->setTitleEdit($currency);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Renders the currency overview page
     */
    protected function outputCurrencies()
    {
        $this->output('settings/currency/list');
    }

    /**
     * Sets titles on the currency overview page
     */
    protected function setTitleCurrencies()
    {
        $this->setTitle($this->text('Currencies'));
    }

    /**
     * Sets breadcrumb on the currency overview page
     */
    protected function setBreadcrumbCurrencies()
    {
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
    }

    /**
     * Renders the currency edit page
     */
    protected function outputEdit()
    {
        $this->output('settings/currency/edit');
    }

    /**
     * Sets titles on the currency edit page
     * @param array $currency
     */
    protected function setTitleEdit(array $currency)
    {
        if (isset($currency['code'])) {
            $title = $this->text('Edit currency %name', array('%name' => $currency['name']));
        } else {
            $title = $this->text('Add currency');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the currency edit page
     */
    protected function setBreadcrumbEdit()
    {
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->setBreadcrumb(array('url' => $this->url('admin/settings/currency'), 'text' => $this->text('Currencies')));
    }

    /**
     * Returns a currency
     * @param string $code
     * @return array
     */
    protected function get($code)
    {
        if (empty($code)) {
            return array(); // Add new currency
        }

        $currency = $this->currency->get($code);

        if (empty($currency)) {
            $this->outputError(404);
        }

        return $currency;
    }

    /**
     * Deletes a currency
     * @param array $currency
     * @return array
     */
    protected function delete(array $currency)
    {
        if (empty($currency['code'])) {
            return; // Nothing to delete
        }

        $this->controlAccess('currency_delete');

        if ($this->currency->delete($currency['code'])) {
            $this->redirect('admin/settings/currency', $this->text('Currency %code has been deleted', array(
                        '%code' => $currency['code'])), 'success');
        }

        $this->redirect('', $this->text('Unable to delete this currency. The most probable reason - it is default currency or used by modules'), 'danger');
    }

    /**
     * Saves a currency
     * @param array $currency
     */
    protected function submit(array $currency)
    {
        $this->submitted = $this->request->post('currency', array());
        $this->validate($currency);
        $errors = $this->formErrors();

        if (!empty($errors)) {
            $this->data['currency'] = $this->submitted;
            return;
        }

        if (isset($currency['code'])) {
            $this->controlAccess('currency_edit');
            $this->currency->update($currency['code'], $this->submitted);
            $this->redirect('admin/settings/currency', $this->text('Currency %code has been updated', array(
                        '%code' => $currency['code'])), 'success');
        }

        $this->controlAccess('currency_add');
        $this->currency->add($this->submitted);
        $this->redirect('admin/settings/currency', $this->text('Currency has been added'), 'success');
    }

    /**
     * Validates a currency data
     * @param array $currency
     */
    protected function validate(array $currency)
    {
        // Fix checkboxes
        $this->submitted['status'] = !empty($this->submitted['status']);
        $this->submitted['default'] = !empty($this->submitted['default']);

        // Default currency always enabled
        if ($this->submitted['default']) {
            $this->submitted['status'] = 1;
        }

        $this->validateCode($currency);
        $this->validateName($currency);
        $this->validateNumericCode($currency);
        $this->validateSymbol($currency);
        $this->validateMajorUnit($currency);
        $this->validateMinorUnit($currency);
        $this->validateConvertionRate($currency);
        $this->validateDecimals($currency);
        $this->validateRoundingStep($currency);
    }

    /**
     * Validates the currency code field
     * @param array $currency
     * @return boolean
     */
    protected function validateCode(array $currency)
    {
        if (!preg_match('/^[a-zA-Z]{3}$/', $this->submitted['code'])) {
            $this->data['form_errors']['code'] = $this->text('Invalid currency code. You must only use ISO 4217 codes');
            return false;
        }

        $this->submitted['code'] = strtoupper($this->submitted['code']);
        $existsing_code = isset($currency['code']) ? $currency['code'] : null;

        if ($existsing_code !== $this->submitted['code'] && $this->currency->get($this->submitted['code'])) {
            $this->data['form_errors']['code'] = $this->text('This currency code already exists');
            return false;
        }

        return true;
    }

    /**
     * Validates the name field
     * @param array $currency
     * @return boolean
     */
    protected function validateName(array $currency)
    {
        if (empty($this->submitted['name']) || mb_strlen($this->submitted['name']) > 255) {
            $this->data['form_errors']['name'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates the numeric code field
     * @param array $currency
     * @return boolean
     */
    protected function validateNumericCode(array $currency)
    {
        if (!preg_match('/^[0-9]{3}$/', $this->submitted['numeric_code'])) {
            $this->data['form_errors']['numeric_code'] = $this->text('Numeric currency code must contain only 3 digits. See ISO 4217');
            return false;
        }

        return true;
    }

    /**
     * Validates the symbol field
     * @param array $currency
     * @return boolean
     */
    protected function validateSymbol(array $currency)
    {
        if (empty($this->submitted['symbol'])) {
            $this->data['form_errors']['symbol'] = $this->text('Required field');
            return false;
        }

        return true;
    }

    /**
     * Validates the major unit field
     * @param array $currency
     * @return boolean
     */
    protected function validateMajorUnit(array $currency)
    {
        if (empty($this->submitted['major_unit'])) {
            $this->data['form_errors']['major_unit'] = $this->text('Required field');
            return false;
        }

        return true;
    }

    /**
     * Validates the minor unit field
     * @param array $currency
     * @return boolean
     */
    protected function validateMinorUnit(array $currency)
    {
        if (empty($this->submitted['minor_unit'])) {
            $this->data['form_errors']['minor_unit'] = $this->text('Required field');
            return false;
        }

        return true;
    }

    /**
     * Validates the convertion rate field
     * @param array $currency
     * @return boolean
     */
    protected function validateConvertionRate(array $currency)
    {
        if (empty($this->submitted['convertion_rate'])) {
            $this->submitted['convertion_rate'] = 1;
            return true;
        }

        if (!is_numeric($this->submitted['convertion_rate'])) {
            $this->data['form_errors']['convertion_rate'] = $this->text('Only numeric values allowed');
            return false;
        }

        $this->submitted['convertion_rate'] = abs($this->submitted['convertion_rate']);
        return true;
    }

    /**
     * Validates the decimals field
     * @param array $currency
     * @return boolean
     */
    protected function validateDecimals(array $currency)
    {
        if (empty($this->submitted['decimals'])) {
            $this->submitted['decimals'] = 2;
            return true;
        }

        if (!is_numeric($this->submitted['decimals'])) {
            $this->data['form_errors']['decimals'] = $this->text('Only numeric values allowed');
            return false;
        }

        return true;
    }

    /**
     * Validates the rounding step field
     * @param array $currency
     * @return boolean
     */
    protected function validateRoundingStep(array $currency)
    {
        if (empty($this->submitted['rounding_step'])) {
            $this->submitted['rounding_step'] = 0;
            return true;
        }

        if (!is_numeric($this->submitted['rounding_step'])) {
            $this->data['form_errors']['rounding_step'] = $this->text('Only numeric values allowed');
            return false;
        }

        return true;
    }

}
