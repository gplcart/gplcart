<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Trigger as TriggerModel,
    gplcart\core\models\Currency as CurrencyModel,
    gplcart\core\models\PriceRule as PriceRuleModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to price rules
 */
class PriceRule extends BackendController
{

    /**
     * Trigger model instance
     * @var \gplcart\core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * Price rule model instance
     * @var \gplcart\core\models\PriceRule $rule
     */
    protected $rule;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * An array of price rule data
     * @var array
     */
    protected $data_rule = array();

    /**
     * @param PriceRuleModel $rule
     * @param CurrencyModel $currency
     * @param PriceModel $price
     * @param TriggerModel $trigger
     */
    public function __construct(PriceRuleModel $rule, CurrencyModel $currency,
            PriceModel $price, TriggerModel $trigger)
    {
        parent::__construct();

        $this->rule = $rule;
        $this->price = $price;
        $this->trigger = $trigger;
        $this->currency = $currency;
    }

    /**
     * Displays the price rule overview page
     */
    public function listPriceRule()
    {
        $this->setTitleListPriceRule();
        $this->setBreadcrumbListPriceRule();

        $this->actionListPriceRule();

        $this->setFilterListPriceRule();
        $this->setTotalListPriceRule();
        $this->setPagerLimit();

        $this->setData('stores', $this->store->getList());
        $this->setData('price_rules', $this->getListPriceRule());

        $this->outputListPriceRule();
    }

    /**
     * Sets a total number of results on the price rule overview page
     */
    protected function setTotalListPriceRule()
    {
        $query = $this->query_filter;
        $query['count'] = true;
        $this->total = (int) $this->rule->getList($query);
    }

    /**
     * Set filter on the price rule overview page
     */
    protected function setFilterListPriceRule()
    {
        $allowed = array('name', 'code', 'value',
            'value_type', 'weight', 'status', 'store_id', 'trigger_name');

        $this->setFilter($allowed);
    }

    /**
     * Applies an action to the selected price rules
     */
    protected function actionListPriceRule()
    {
        list($selected, $action, $value) = $this->getPostedAction();

        if (empty($action)) {
            return null;
        }

        $deleted = $updated = 0;
        foreach ($selected as $rule_id) {

            if ($action === 'status' && $this->access('price_rule_edit')) {
                $updated += (int) $this->rule->update($rule_id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('price_rule_delete')) {
                $deleted += (int) $this->rule->delete($rule_id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Updated %num items', array('%num' => $updated));
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num items', array('%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Returns an array of price rules
     * @return array
     */
    protected function getListPriceRule()
    {
        $query = $this->query_filter;
        $query['limit'] = $this->limit;
        $rules = (array) $this->rule->getList($query);

        return $this->prepareListPriceRule($rules);
    }

    /**
     * Prepare an array of price rules
     * @param array $rules
     * @return array
     */
    protected function prepareListPriceRule($rules)
    {
        foreach ($rules as &$rule) {
            if ($rule['value_type'] === 'fixed') {
                $rule['value'] = $this->price->decimal($rule['value'], $rule['currency']);
            }
        }

        return $rules;
    }

    /**
     * Sets titles on the price rule overview page
     */
    protected function setTitleListPriceRule()
    {
        $this->setTitle($this->text('Price rules'));
    }

    /**
     * Sets breadcrumbs on the price rule overview page
     */
    protected function setBreadcrumbListPriceRule()
    {
        $this->setBreadcrumbHome();
    }

    /**
     * Render and output the price rule overview page
     */
    protected function outputListPriceRule()
    {
        $this->output('sale/price/list');
    }

    /**
     * Displays the price rule edit form
     * @param null|integer $rule_id
     */
    public function editPriceRule($rule_id = null)
    {
        $this->setPriceRule($rule_id);

        $this->setTitleEditPriceRule();
        $this->setBreadcrumbEditPriceRule();

        $this->setData('price_rule', $this->data_rule);
        $this->setData('triggers', $this->getTriggersPriceRule());
        $this->setData('currencies', $this->getCurrenciesPriceRule());

        $this->submitEditPriceRule();
        $this->outputEditPriceRule();
    }

    /**
     * Returns an array of enabled triggers
     * @return array
     */
    protected function getTriggersPriceRule()
    {
        return $this->trigger->getList(array('status' => 1));
    }

    /**
     * Returns an array of enabled currencies
     * @return array
     */
    protected function getCurrenciesPriceRule()
    {
        return $this->currency->getList(true);
    }

    /**
     * Returns an array of price rule data
     * @param null|integer $rule_id
     */
    protected function setPriceRule($rule_id)
    {
        if (is_numeric($rule_id)) {
            $this->data_rule = $this->rule->get($rule_id);
            if (empty($this->data_rule)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Prepares an array of price rule data
     * @param array $rule
     * @return array
     */
    protected function preparePriceRule(array $rule)
    {
        if ($rule['value_type'] === 'fixed') {
            $rule['value'] = $this->price->decimal($rule['value'], $rule['currency']);
        }

        return $rule;
    }

    /**
     * Handles a submitted price rule
     */
    protected function submitEditPriceRule()
    {
        if ($this->isPosted('delete')) {
            $this->deletePriceRule();
        } else if ($this->isPosted('save') && $this->validateEditPriceRule()) {
            if (isset($this->data_rule['price_rule_id'])) {
                $this->updatePriceRule();
            } else {
                $this->addPriceRule();
            }
        }
    }

    /**
     * Validates a submitted price rule
     * @return bool
     */
    protected function validateEditPriceRule()
    {
        $this->setSubmitted('price_rule', null, false);
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_rule);

        $this->validateComponent('price_rule');

        return !$this->hasErrors();
    }

    /**
     * Deletes a price rule
     */
    protected function deletePriceRule()
    {
        $this->controlAccess('price_rule_delete');
        $this->rule->delete($this->data_rule['price_rule_id']);
        $this->redirect('admin/sale/price', $this->text('Price rule has been deleted'), 'success');
    }

    /**
     * Updates a price rule
     */
    protected function updatePriceRule()
    {
        $this->controlAccess('price_rule_edit');
        $this->rule->update($this->data_rule['price_rule_id'], $this->getSubmitted());
        $this->redirect('admin/sale/price', $this->text('Price rule has been updated'), 'success');
    }

    /**
     * Adds a new price rule
     */
    protected function addPriceRule()
    {
        $this->controlAccess('price_rule_add');
        $this->rule->add($this->getSubmitted());
        $this->redirect('admin/sale/price', $this->text('Price rule has been added'), 'success');
    }

    /**
     * Sets title on the edit price rule page
     */
    protected function setTitleEditPriceRule()
    {
        if (isset($this->data_rule['price_rule_id'])) {
            $vars = array('%name' => $this->data_rule['name']);
            $title = $this->text('Edit %name', $vars);
        } else {
            $title = $this->text('Add price rule');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit price rule page
     */
    protected function setBreadcrumbEditPriceRule()
    {
        $this->setBreadcrumbHome();

        $breadcrumb = array(
            'text' => $this->text('Price rules'),
            'url' => $this->url('admin/sale/price')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the edit price rule page
     */
    protected function outputEditPriceRule()
    {
        $this->output('sale/price/edit');
    }

}
