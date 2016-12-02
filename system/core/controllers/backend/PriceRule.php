<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\backend;

use core\models\Price as ModelsPrice;
use core\models\Trigger as ModelsTrigger;
use core\models\Currency as ModelsCurrency;
use core\models\PriceRule as ModelsPriceRule;
use core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to price rules
 */
class PriceRule extends BackendController
{

    /**
     * Trigger model instance
     * @var \core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * Price rule model instance
     * @var \core\models\PriceRule $rule
     */
    protected $rule;

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Constructor
     * @param ModelsPriceRule $rule
     * @param ModelsCurrency $currency
     * @param ModelsPrice $price
     * @param ModelsTrigger $trigger
     */
    public function __construct(ModelsPriceRule $rule, ModelsCurrency $currency,
            ModelsPrice $price, ModelsTrigger $trigger)
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
        $this->actionPriceRule();

        $query = $this->getFilterQuery();
        $total = $this->getTotalPriceRule($query);
        $limit = $this->setPager($total, $query);
        $rules = $this->getListPriceRule($limit, $query);
        $stores = $this->store->getNames();

        $this->setData('price_rules', $rules);
        $this->setData('stores', $stores);

        $filters = $this->getAllowedFiltersPriceRule();

        $this->setFilter($filters, $query);
        $this->setTitleListPriceRule();
        $this->setBreadcrumbListPriceRule();
        $this->outputListPriceRule();
    }

    /**
     * Returns an array of allowed filters for price rule listing
     * @return array
     */
    protected function getAllowedFiltersPriceRule()
    {
        return array('name', 'code', 'value',
            'value_type', 'weight', 'status');
    }

    /**
     * Applies an action to the selected price rules
     * @return null
     */
    protected function actionPriceRule()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        $deleted = $updated = 0;
        foreach ($selected as $rule_id) {

            if ($action == 'status' && $this->access('price_rule_edit')) {
                $updated += (int) $this->rule->update($rule_id, array('status' => $value));
            }

            if ($action == 'delete' && $this->access('price_rule_delete')) {
                $deleted += (int) $this->rule->delete($rule_id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Updated %num price rules', array('%num' => $updated));
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num price rules', array('%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }

        return null;
    }

    /**
     * Returns total number of price rules for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalPriceRule(array $query)
    {
        $query['count'] = true;
        return $this->rule->getList($query);
    }

    /**
     * Returns an array of rules
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListPriceRule(array $limit, array $query)
    {
        $query['limit'] = $limit;
        $rules = $this->rule->getList($query);

        foreach ($rules as &$rule) {
            if ($rule['value_type'] == 'fixed') {
                $rule['value'] = $this->price->decimal($rule['value'], $rule['currency']);
            }
        }

        return $rules;
    }

    /**
     * Sets titles on the rules overview page
     */
    protected function setTitleListPriceRule()
    {
        $this->setTitle($this->text('Price rules'));
    }

    /**
     * Sets breadcrumbs on the rules overview page
     */
    protected function setBreadcrumbListPriceRule()
    {
        $breadcrumb = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the rules overview page
     */
    protected function outputListPriceRule()
    {
        $this->output('sale/price/list');
    }

    /**
     * Displays the price rule edit form
     * @param mixed $rule_id
     */
    public function editPriceRule($rule_id = null)
    {
        $rule = $this->getPriceRule($rule_id);

        $stores = $this->store->getList();
        $triggers = $this->getTriggersPriceRule();
        $currencies = $this->getCurrenciesPriceRule();

        $this->setData('stores', $stores);
        $this->setData('price_rule', $rule);
        $this->setData('triggers', $triggers);
        $this->setData('currencies', $currencies);

        $this->submitPriceRule($rule);

        $this->setTitleEditPriceRule($rule);
        $this->setBreadcrumbEditPriceRule();
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
     * Returns an array of rule data
     * @param mixed $rule_id
     * @return array
     */
    protected function getPriceRule($rule_id)
    {
        if (!is_numeric($rule_id)) {
            return array();
        }

        $rule = $this->rule->get($rule_id);

        if (empty($rule)) {
            $this->outputError(404);
        }

        return $this->preparePriceRule($rule);
    }

    /**
     * Prepares an array of price rule data
     * @param array $rule
     * @return array
     */
    protected function preparePriceRule(array $rule)
    {
        if ($rule['value_type'] == 'fixed') {
            $rule['value'] = $this->price->decimal($rule['value'], $rule['currency']);
        }

        return $rule;
    }

    /**
     * Saves a submitted rule
     * @param array $rule
     * @return null|void
     */
    protected function submitPriceRule(array $rule = array())
    {
        if ($this->isPosted('delete')) {
            return $this->deletePriceRule($rule);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('price_rule', null, false);
        $this->validatePriceRule($rule);

        if ($this->hasErrors('price_rule')) {
            return null;
        }

        if (isset($rule['price_rule_id'])) {
            return $this->updatePriceRule($rule);
        }

        return $this->addPriceRule();
    }

    /**
     * Deletes a rule
     * @param array $rule
     */
    protected function deletePriceRule(array $rule)
    {
        $this->controlAccess('price_rule_delete');
        $this->rule->delete($rule['price_rule_id']);

        $message = $this->text('Price rule has been deleted');
        $this->redirect('admin/sale/price', $message, 'success');
    }

    /**
     * Validates a submitted rule
     * @param array $rule
     */
    protected function validatePriceRule(array $rule = array())
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $rule);
        $this->validate('price_rule');
    }

    /**
     * Updates a price rule with submitted values
     * @param array $rule
     */
    protected function updatePriceRule(array $rule)
    {
        $this->controlAccess('price_rule_edit');

        $submitted = $this->getSubmitted();
        $this->rule->update($rule['price_rule_id'], $submitted);

        $message = $this->text('Price rule has been updated');
        $this->redirect('admin/sale/price', $message, 'success');
    }

    /**
     * Adds a new price rule
     */
    protected function addPriceRule()
    {
        $this->controlAccess('price_rule_add');

        $submitted = $this->getSubmitted();
        $this->rule->add($submitted);

        $message = $this->text('Price rule has been added');
        $this->redirect('admin/sale/price', $message, 'success');
    }

    /**
     * Sets titles on the edit rules page
     * @param array $rule
     */
    protected function setTitleEditPriceRule($rule)
    {
        $title = $this->text('Add price rule');

        if (isset($rule['price_rule_id'])) {
            $title = $this->text('Edit price rule %name', array('%name' => $rule['name']));
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit rules page
     */
    protected function setBreadcrumbEditPriceRule()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Price rules'),
            'url' => $this->url('admin/sale/price')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders templates for rule edit page
     */
    protected function outputEditPriceRule()
    {
        $this->output('sale/price/edit');
    }

}
