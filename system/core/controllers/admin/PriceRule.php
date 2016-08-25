<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\classes\Tool;
use core\models\Price as ModelsPrice;
use core\models\Currency as ModelsCurrency;
use core\models\PriceRule as ModelsPriceRule;

/**
 * Handles incoming requests and outputs data related to price rules
 */
class PriceRule extends Controller
{

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
     */
    public function __construct(ModelsPriceRule $rule, ModelsCurrency $currency,
            ModelsPrice $price)
    {
        parent::__construct();

        $this->rule = $rule;
        $this->price = $price;
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

        $filters = array('name', 'code', 'value', 'value_type',
            'weight', 'status', 'store_id', 'type');

        $this->setFilter($filters, $query);

        $this->setTitleListPriceRule();
        $this->setBreadcrumbListPriceRule();
        $this->outputListPriceRule();
    }

    /**
     * Displays the price rule edit form
     * @param mixed $rule_id
     */
    public function editPriceRule($rule_id = null)
    {
        $rule = $this->getPriceRule($rule_id);
        $stores = $this->store->getList();
        $currencies = $this->currency->getList(true);
        $operators = $this->rule->getConditionOperators();
        $conditions = $this->rule->getConditionHandlers();

        $this->setData('stores', $stores);
        $this->setData('price_rule', $rule);
        $this->setData('operators', $operators);
        $this->setData('currencies', $currencies);
        $this->setData('conditions', $conditions);

        $this->submitPriceRule($rule);

        $this->setDataEditPriceRule();

        $this->setTitleEditPriceRule($rule);
        $this->setBreadcrumbEditPriceRule();
        $this->outputEditPriceRule();
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
     * Applies an action to the selected price rules
     */
    protected function actionPriceRule()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return;
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
            $message = $this->text('Updated %num price rules', array(
                '%num' => $updated));
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num price rules', array(
                '%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }
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
        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the rules overview page
     */
    protected function outputListPriceRule()
    {
        $this->output('sale/price/list');
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
        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin'));

        $breadcrumbs[] = array(
            'text' => $this->text('Price rules'),
            'url' => $this->url('admin/sale/price'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders templates for rule edit page
     */
    protected function outputEditPriceRule()
    {
        $this->output('sale/price/edit');
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

        if ($rule['value_type'] == 'fixed') {
            $rule['value'] = $this->price->decimal($rule['value'], $rule['currency']);
        }

        return $rule;
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
     * Saves a submitted rule
     * @param array $rule
     * @return null
     */
    protected function submitPriceRule(array $rule = array())
    {
        if ($this->isPosted('delete')) {
            return $this->deletePriceRule($rule);
        }

        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('price_rule', null, false);

        $this->validatePriceRule($rule);

        if ($this->hasErrors('price_rule')) {
            return;
        }

        if (isset($rule['price_rule_id'])) {
            return $this->updatePriceRule($rule);
        }

        $this->addPriceRule();
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
     * Validates a submitted rule
     * @param array $rule
     */
    protected function validatePriceRule(array $rule = array())
    {
        $this->setSubmittedBool('status');

        if (isset($rule['price_rule_id'])) {
            $this->setSubmitted('price_rule_id', $rule['price_rule_id']);
        }

        $this->addValidator('name', array(
            'length' => array('min' => 1, 'max' => 255)
        ));

        $this->addValidator('type', array(
            'required' => array()
        ));

        $this->addValidator('currency', array(
            'required' => array()
        ));

        $this->addValidator('value', array(
            'numeric' => array(),
            'length' => array('min' => 1, 'max' => 10)
        ));

        $this->addValidator('value_type', array(
            'required' => array()
        ));

        $this->addValidator('weight', array(
            'numeric' => array(),
            'length' => array('max' => 2)
        ));

        $this->addValidator('data.conditions', array(
            'pricerule_conditions' => array()
        ));

        $options = array(
            'store_id' => $this->getSubmitted('store_id'),
            'price_rule_id' => $this->getSubmitted('price_rule_id')
        );

        $type = $this->getSubmitted('type');

        $this->addValidator('code', array(
            'length' => array(
                'max' => 255,
                'min' => ($type === 'order') ? 1 : null
            ),
            'price_rule_code' => $options
        ));

        $errors = $this->setValidators($rule);

        if (empty($errors)) {

            $value_type = $this->getSubmitted('value_type');

            if ($value_type === 'fixed') {

                $value = $this->getSubmitted('value');
                $currency = $this->getSubmitted('currency');

                $amount = $this->price->amount((float) $value, $currency, false);
                $this->setSubmitted('value', $amount);
            }

            $conditions = $this->getValidatorResult('data.conditions');
            $this->setSubmitted('data.conditions', $conditions);
        }
    }

    /**
     * Converts an array of conditions into a multiline string
     * @return null
     */
    protected function setDataEditPriceRule()
    {
        $conditions = $this->getData('price_rule.data.conditions');

        if (!empty($conditions) && is_array($conditions)) {

            Tool::sortWeight($conditions);

            $modified = array();
            foreach ($conditions as $condition) {
                $modified[] = $condition['original'];
            }

            $this->setData('price_rule.data.conditions', implode("\n", $modified));
        }
    }

}
