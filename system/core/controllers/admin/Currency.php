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
        $this->setData('default_currency', $this->currency->getDefault());
        $this->setData('currencies', $this->currency->getList());

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
        $default_currency = $this->currency->getDefault();
        
        $this->setData('currency', $currency);
        $this->setData('default_currency', $default_currency);
        
        $can_delete = (isset($currency['code'])
                && $this->access('currency_delete')
                && ($default_currency != $currency['code'])
                && !$this->isSubmitted());
        
        $this->setdata('can_delete', $can_delete);

        if ($this->isSubmitted('delete')) {
            $this->delete($currency);
        }

        if ($this->isSubmitted('save')) {
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
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));
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
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));
        
        $this->setBreadcrumb(array(
            'url' => $this->url('admin/settings/currency'),
            'text' => $this->text('Currencies')));
    }

    /**
     * Returns a currency
     * @param string $code
     * @return array
     */
    protected function get($code)
    {
        if (empty($code)) {
            return array();
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
     * @return null
     */
    protected function submit(array $currency)
    {
        $this->setSubmitted('currency');
        $this->validate($currency);

        if ($this->hasErrors('currency')) {
            return;
        }

        if (isset($currency['code'])) {
            $this->controlAccess('currency_edit');
            $this->currency->update($currency['code'], $this->getSubmitted());
            $this->redirect('admin/settings/currency', $this->text('Currency %code has been updated', array(
                        '%code' => $currency['code'])), 'success');
        }

        $this->controlAccess('currency_add');
        $this->currency->add($this->getSubmitted());
        $this->redirect('admin/settings/currency', $this->text('Currency has been added'), 'success');
    }

    /**
     * Validates a currency data
     * @param array $currency
     */
    protected function validate(array $currency)
    {
        // Fix checkboxes
        $this->setSubmittedBool('status');
        $this->setSubmittedBool('default');

        // Default currency always enabled
        if ($this->getSubmitted('default')) {
            $this->setSubmitted('status', 1);
        }

        // Validate fields
        $this->addValidator('code', array(
            'regexp' => array('pattern' => '/^[a-zA-Z]{3}$/'),
            'currency_code_unique' => array()
        ));

        $this->addValidator('name', array(
            'length' => array('min' => 1, 'max' => 255)));

        $this->addValidator('numeric_code', array(
            'regexp' => array('pattern' => '/^[0-9]{3}$/')));

        $this->addValidator('symbol', array(
            'length' => array('min' => 1)));

        $this->addValidator('major_unit', array(
            'length' => array('min' => 1)));

        $this->addValidator('minor_unit', array(
            'length' => array('min' => 1)));

        $this->addValidator('convertion_rate', array(
            'numeric' => array()));

        $this->addValidator('decimals', array(
            'numeric' => array()));

        $this->addValidator('rounding_step', array(
            'numeric' => array()));

        $errors = $this->setValidators($currency);

        if (empty($errors)) {
            $this->setSubmitted('code', strtoupper($this->getSubmitted('code')));
            $this->setSubmitted('convertion_rate', abs($this->getSubmitted('convertion_rate')));
        }
    }

}
