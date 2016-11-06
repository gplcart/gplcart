<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\models\Currency as ModelsCurrency;
use core\controllers\admin\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to currency
 */
class Currency extends BackendController
{

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Constructor
     * Currency constructor.
     * @param ModelsCurrency $currency
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
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the currency overview page
     */
    protected function outputListCurrency()
    {
        $this->output('settings/currency/list');
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

        $can_delete = (isset($currency['code'])//
                && $this->access('currency_delete')//
                && ($default_currency != $currency['code'])//
                && !$this->isPosted());

        $this->setData('can_delete', $can_delete);

        $this->submitCurrency($currency);

        $this->setTitleEditCurrency($currency);
        $this->setBreadcrumbEditCurrency();
        $this->outputEditCurrency();
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
     * Saves a currency
     * @param array $currency
     * @return null
     */
    protected function submitCurrency(array $currency)
    {
        if ($this->isPosted('delete') && isset($currency['code'])) {
            return $this->deleteCurrency($currency);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('currency', null, 'raw');
        $this->validateCurrency($currency);

        if ($this->hasErrors('currency')) {
            return null;
        }

        if (isset($currency['code'])) {
            return $this->updateCurrency($currency);
        }

        return $this->addCurrency();
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

        if ($deleted) {
            $message = $this->text('Currency %code has been deleted', array(
                '%code' => $currency['code']
            ));
            $this->redirect('admin/settings/currency', $message, 'success');
        }

        $message = $this->text('Cannot delete this currency');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Validates a currency data
     * @param array $currency
     */
    protected function validateCurrency(array $currency)
    {
        $this->setSubmittedBool('status');
        $this->setSubmittedBool('default');
        $this->setSubmitted('update', $currency);
        $this->validate('currency');
    }

    /**
     * Updates a currency
     * @param array $currency
     */
    protected function updateCurrency(array $currency)
    {
        $this->controlAccess('currency_edit');

        $values = $this->getSubmitted();
        $this->currency->update($currency['code'], $values);

        $message = $this->text('Currency %code has been updated', array(
            '%code' => $currency['code']
        ));

        $this->redirect('admin/settings/currency', $message, 'success');
    }

    /**
     * Adds a new currency
     */
    protected function addCurrency()
    {
        $this->controlAccess('currency_add');

        $values = $this->getSubmitted();
        $this->currency->add($values);

        $message = $this->text('Currency has been added');
        $this->redirect('admin/settings/currency', $message, 'success');
    }

    /**
     * Sets titles on the currency edit page
     * @param array $currency
     */
    protected function setTitleEditCurrency(array $currency)
    {
        if (isset($currency['code'])) {
            $title = $this->text('Edit currency %name', array(
                '%name' => $currency['name']
            ));
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
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/settings/currency'),
            'text' => $this->text('Currencies')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the currency edit page
     */
    protected function outputEditCurrency()
    {
        $this->output('settings/currency/edit');
    }

}
