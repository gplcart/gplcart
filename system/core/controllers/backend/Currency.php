<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\traits\Listing as ListingTrait;

/**
 * Handles incoming requests and outputs data related to currency
 */
class Currency extends Controller
{

    use ListingTrait;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

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
        $this->setFilterListCurrency();
        $this->setPagerListCurrency();

        $this->setData('currencies', (array) $this->getListCurrency());
        $this->setData('default_currency', $this->currency->getDefault());

        $this->outputListCurrency();
    }

    /**
     * Sets the filter on the currency overview page
     */
    protected function setFilterListCurrency()
    {
        $this->setFilter($this->getAllowedFiltersCurrency());
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListCurrency()
    {
        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->getListCurrency(true)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of allowed filters
     * @return array
     */
    protected function getAllowedFiltersCurrency()
    {
        return array('name', 'code', 'symbol', 'conversion_rate', 'status',
            'modified', 'default', 'in_database');
    }

    /**
     * Returns an array of sorted currencies
     * @param bool $count
     * @return array|int
     */
    protected function getListCurrency($count = false)
    {
        $currencies = $this->currency->getList();

        $allowed = $this->getAllowedFiltersCurrency();
        $this->filterList($currencies, $allowed, $this->query_filter);
        $this->sortList($currencies, $allowed, $this->query_filter, array('modified' => 'desc'));

        if ($count) {
            return count($currencies);
        }

        $this->limitList($currencies, $this->data_limit);
        return $currencies;
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

        $this->setData('edit', isset($code));
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
        return isset($this->data_currency['code'])
            && $this->access('currency_delete')
            && !$this->isPosted() && $this->currency->canDelete($this->data_currency['code']);
    }

    /**
     * Set a currency data
     * @param string $code
     */
    protected function setCurrency($code)
    {
        $this->data_currency = array();

        if (!empty($code)) {
            $this->data_currency = $this->currency->get($code);
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
            $this->redirect('admin/settings/currency', $this->text('Currency has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Currency has not been deleted'), 'warning');
    }

    /**
     * Updates a currency
     */
    protected function updateCurrency()
    {
        $this->controlAccess('currency_edit');

        if ($this->currency->update($this->data_currency['code'], $this->getSubmitted())) {
            $this->redirect('admin/settings/currency', $this->text('Currency has been updated'), 'success');
        }

        $this->redirect('', $this->text('Currency has not been updated'), 'warning');
    }

    /**
     * Adds a new currency
     */
    protected function addCurrency()
    {
        $this->controlAccess('currency_add');

        if ($this->currency->add($this->getSubmitted())) {
            $this->redirect('admin/settings/currency', $this->text('Currency has been added'), 'success');
        }

        $this->redirect('', $this->text('Currency has not been added'), 'warning');
    }

    /**
     * Sets titles on the currency edit page
     */
    protected function setTitleEditCurrency()
    {
        if (isset($this->data_currency['code'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_currency['name']));
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
     * Render and output the currency edit page
     */
    protected function outputEditCurrency()
    {
        $this->output('settings/currency/edit');
    }

}
