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
    public function listCurrency()
    {
        $currencies = $this->currency->getList();
        $default = $this->currency->getDefault();

        $this->setData('currencies', $currencies);
        $this->setData('default_currency', $default);

        $this->setTitleListCurrency();
        $this->setBreadcrumbListCurrency();
        $this->outputListCurrency();
    }

    /**
     * Displays the currency edit form
     * @param string|null $code
     */
    public function editCurrency($code = null)
    {
        $currency = $this->getCurrency($code);
        $default_currency = $this->currency->getDefault();

        $this->setData('currency', $currency);
        $this->setData('default_currency', $default_currency);

        $can_delete = (isset($currency['code'])
                && $this->access('currency_delete')
                && ($default_currency != $currency['code'])
                && !$this->isPosted());

        $this->setdata('can_delete', $can_delete);

        if ($this->isPosted('delete')) {
            $this->deleteCurrency($currency);
        }

        if ($this->isPosted('save')) {
            $this->submitCurrency($currency);
        }

        $this->setTitleEditCurrency($currency);
        $this->setBreadcrumbEditCurrency();
        $this->outputEditCurrency();
    }

    /**
     * Renders the currency overview page
     */
    protected function outputListCurrency()
    {
        $this->output('settings/currency/list');
    }

    /**
     * Sets titles on the currency overview page
     */
    protected function setTitleListCurrency()
    {
        $this->setTitle($this->text('Currencies'));
    }

    /**
     * Sets breadcrumb on the currency overview page
     */
    protected function setBreadcrumbListCurrency()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));
    }

    /**
     * Renders the currency edit page
     */
    protected function outputEditCurrency()
    {
        $this->output('settings/currency/edit');
    }

    /**
     * Sets titles on the currency edit page
     * @param array $currency
     */
    protected function setTitleEditCurrency(array $currency)
    {
        if (isset($currency['code'])) {
            $title = $this->text('Edit currency %name', array(
                '%name' => $currency['name']));
        } else {
            $title = $this->text('Add currency');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the currency edit page
     */
    protected function setBreadcrumbEditCurrency()
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
     * or displays a 404 error on invalid code
     * @param string $code
     * @return array
     */
    protected function getCurrency($code)
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
    protected function deleteCurrency(array $currency)
    {
        $this->controlAccess('currency_delete');

        $deleted = $this->currency->delete($currency['code']);

        if (!$deleted) {
            $this->redirect('', $this->text('Cannot delete this currency'), 'danger');
        }
        
        $message = $this->text('Currency %code has been deleted', array(
            '%code' => $currency['code']));
        $this->redirect('admin/settings/currency', $message, 'success');
    }

    /**
     * Saves a currency
     * @param array $currency
     * @return null
     */
    protected function submitCurrency(array $currency)
    {
        $this->setSubmitted('currency');
        $this->validateCurrency($currency);

        if ($this->hasErrors('currency')) {
            return;
        }

        if (isset($currency['code'])) {
            $this->updateCurrency($currency);
        }

        $this->addCurrency();
    }

    /**
     * Updates a currency
     * @param array $currency
     */
    protected function updateCurrency(array $currency)
    {
        $this->controlAccess('currency_edit');
        $this->currency->update($currency['code'], $this->getSubmitted());
        
        $message = $this->text('Currency %code has been updated', array(
                    '%code' => $currency['code']));
        
        $this->redirect('admin/settings/currency', $message, 'success');
    }

    /**
     * Adds a new currency
     */
    protected function addCurrency()
    {
        $this->controlAccess('currency_add');
        $this->currency->add($this->getSubmitted());
        
        $message = $this->text('Currency has been added');
        $this->redirect('admin/settings/currency', $message, 'success');
    }

    /**
     * Validates a currency data
     * @param array $currency
     */
    protected function validateCurrency(array $currency)
    {
        $this->setSubmittedBool('status');
        $this->setSubmittedBool('default');

        // Default currency always enabled
        if ($this->getSubmitted('default')) {
            $this->setSubmitted('status', 1);
        }

        // Validate fields
        $this->addValidator('code', array(
            'regexp' => array(
                'pattern' => '/^[A-Z]{3}$/', // latin upper-case, 3 chars
                'required' => true
            ),
            'currency_code_unique' => array()
        ));

        $this->addValidator('name', array(
            'length' => array('min' => 1, 'max' => 50)));

        $this->addValidator('numeric_code', array(
            'regexp' => array(
                'pattern' => '/^[0-9]{3}$/', // numeric, 3 chars
                'required' => true
        )));

        $this->addValidator('symbol', array(
            'length' => array('min' => 1, 'max' => 10)));

        $this->addValidator('major_unit', array(
            'length' => array('min' => 1, 'max' => 50)));

        $this->addValidator('minor_unit', array(
            'length' => array('min' => 1, 'max' => 50)));

        $this->addValidator('convertion_rate', array(
            'length' => array('min' => 1, 'max' => 10),
            'regexp' => array(
                'required' => true,
                'pattern' => '/^[0-9]\d*(\.\d+)?$/' // decimal or integer positive
        )));

        $this->addValidator('decimals', array(
            'regexp' => array(
                'pattern' => '/^[0-2]+$/', // numeric positive, 0-2
                'required' => true)));

        $this->addValidator('rounding_step', array(
            'length' => array('min' => 1, 'max' => 10),
            'regexp' => array(
                'required' => true,
                'pattern' => '/^[0-9]\d*(\.\d+)?$/' // decimal or integer positive
        )));

        $this->setValidators($currency);
    }

}
