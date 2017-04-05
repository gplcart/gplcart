<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to currency
 */
class Currency extends BackendController
{

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * The current currency
     * @var array
     */
    protected $data_currency = array();

    /**
     * Constructor
     * Currency constructor.
     * @param CurrencyModel $currency
     */
    public function __construct(CurrencyModel $currency)
    {
        parent::__construct();

        $this->currency = $currency;
    }

    /**
     * Displays the currency overview page
     */
    public function listCurrency()
    {
        $this->setTitleListCurrency();
        $this->setBreadcrumbListCurrency();

        $this->setData('currencies', $this->currency->getList());
        $this->setData('default_currency', $this->currency->getDefault());

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
        $this->setCurrency($code);

        $this->setTitleEditCurrency();
        $this->setBreadcrumbEditCurrency();

        $this->setData('currency', $this->data_currency);
        $this->setData('can_delete', $this->canDeleteCurrency());
        $this->setData('default_currency', $this->currency->getDefault());

        $this->submitCurrency();
        $this->outputEditCurrency();
    }

    /**
     * Saves a currency
     * @return null
     */
    protected function submitCurrency()
    {
        if ($this->isPosted('delete')) {
            $this->deleteCurrency();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateCurrency()) {
            return null;
        }

        if (isset($this->data_currency['code'])) {
            $this->updateCurrency();
        } else {
            $this->addCurrency();
        }
    }

    /**
     * Validates a currency data
     * @return bool
     */
    protected function validateCurrency()
    {
        $this->setSubmitted('currency');

        $this->setSubmittedBool('status');
        $this->setSubmittedBool('default');
        $this->setSubmitted('update', $this->data_currency);

        $this->validateComponent('currency');

        return !$this->hasErrors();
    }

    /**
     * Whether the currency can be deleted
     * @return bool
     */
    protected function canDeleteCurrency()
    {
        return isset($this->data_currency['code'])//
                && $this->access('currency_delete')//
                && ($this->currency->getDefault() != $this->data_currency['code'])//
                && !$this->isPosted();
    }

    /**
     * Returns a currency
     * or displays a 404 error on invalid code
     * @param string $code
     * @return array
     */
    protected function setCurrency($code)
    {
        if (empty($code)) {
            return array();
        }

        $currency = $this->currency->get($code);

        if (empty($currency)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_currency = (array) $currency;
    }

    /**
     * Deletes a currency
     */
    protected function deleteCurrency()
    {
        $this->controlAccess('currency_delete');

        $deleted = $this->currency->delete($this->data_currency['code']);

        if ($deleted) {
            $message = $this->text('Currency has been deleted');
            $this->redirect('admin/settings/currency', $message, 'success');
        }

        $message = $this->text('Unable to delete this currency');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Updates a currency
     */
    protected function updateCurrency()
    {
        $this->controlAccess('currency_edit');
        $this->currency->update($this->data_currency['code'], $this->getSubmitted());

        $message = $this->text('Currency has been updated');
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
     * Sets titles on the currency edit page
     */
    protected function setTitleEditCurrency()
    {
        $title = $this->text('Add currency');

        if (isset($this->data_currency['code'])) {
            $vars = array('%name' => $this->data_currency['name']);
            $title = $this->text('Edit currency %name', $vars);
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
