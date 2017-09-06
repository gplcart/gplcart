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
     * An array of currency data
     * @var array
     */
    protected $data_currency = array();

    /**
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
        $this->setBreadcrumbHome();
    }

    /**
     * Render and output the currency overview page
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

        $this->submitEditCurrency();
        $this->outputEditCurrency();
    }

    /**
     * Handles a submitted currency data
     */
    protected function submitEditCurrency()
    {
        if ($this->isPosted('delete')) {
            $this->deleteCurrency();
        } else if ($this->isPosted('save') && $this->validateEditCurrency()) {
            if (isset($this->data_currency['code'])) {
                $this->updateCurrency();
            } else {
                $this->addCurrency();
            }
        }
    }

    /**
     * Validates a submitted currency data
     * @return boolean
     */
    protected function validateEditCurrency()
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
     * Set a currency data
     * @param string $code
     */
    protected function setCurrency($code)
    {
        if (!empty($code)) {
            $this->data_currency = (array) $this->currency->get($code);
            if (empty($this->data_currency)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Deletes a currency
     */
    protected function deleteCurrency()
    {
        $this->controlAccess('currency_delete');

        if ($this->currency->delete($this->data_currency['code'])) {
            $message = $this->text('@item has been deleted', array('@item' => $this->text('Currency')));
            $this->redirect('admin/settings/currency', $message, 'success');
        }

        $this->redirect('', $this->text('Unable to delete'), 'danger');
    }

    /**
     * Updates a currency
     */
    protected function updateCurrency()
    {
        $this->controlAccess('currency_edit');
        $this->currency->update($this->data_currency['code'], $this->getSubmitted());
        $message = $this->text('@item has been updated', array('@item' => $this->text('Currency')));
        $this->redirect('admin/settings/currency', $message, 'success');
    }

    /**
     * Adds a new currency
     */
    protected function addCurrency()
    {
        $this->controlAccess('currency_add');
        $this->currency->add($this->getSubmitted());
        $message = $this->text('@item has been added', array('@item' => $this->text('Currency')));
        $this->redirect('admin/settings/currency', $message, 'success');
    }

    /**
     * Sets titles on the currency edit page
     */
    protected function setTitleEditCurrency()
    {
        if (isset($this->data_currency['code'])) {
            $vars = array('%name' => $this->data_currency['name']);
            $title = $this->text('Edit %name', $vars);
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
        $this->setBreadcrumbHome();

        $breadcrumb = array(
            'url' => $this->url('admin/settings/currency'),
            'text' => $this->text('Currencies')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the currency edit page
     */
    protected function outputEditCurrency()
    {
        $this->output('settings/currency/edit');
    }

}
