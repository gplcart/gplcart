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
    public function rules()
    {
        $query = $this->getFilterQuery();
        $total = $this->getTotalRules($query);
        $limit = $this->setPager($total, $query);
        $rules = $this->getRules($limit, $query);
        $stores = $this->store->getNames();

        $this->setData('price_rules', $rules);
        $this->setData('stores', $stores);

        $filters = array('name', 'code', 'value', 'value_type',
            'weight', 'status', 'store_id', 'type');

        $this->setFilter($filters, $query);

        if ($this->isSubmitted('action')) {
            $this->action();
        }

        $this->setTitleRules();
        $this->setBreadcrumbRules();
        $this->outputRules();
    }

    /**
     * Displays the price rule edit form
     * @param mixed $rule_id
     */
    public function edit($rule_id = null)
    {
        $rule = $this->get($rule_id);

        $this->data['price_rule'] = $rule;
        $this->data['stores'] = $this->store->getList();
        $this->data['operators'] = $this->rule->getConditionOperators();
        $this->data['currencies'] = $this->currency->getList(true);
        $this->data['conditions'] = $this->rule->getConditionHandlers();

        if ($this->request->post('delete')) {
            $this->delete($rule);
        }

        if ($this->request->post('save')) {
            $this->submit($rule);
        }

        $this->setDataConditions();

        $this->setTitleEdit($rule);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Returns total number of price rules for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalRules(array $query)
    {
        $query['count'] = true;
        return $this->rule->getList($query);
    }

    /**
     * Applies an action to the selected price rules
     * @return boolean
     */
    protected function action()
    {
        $value = (int) $this->request->post('value');
        $action = (string) $this->request->post('action');
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
            $this->session->setMessage($this->text('Updated %num price rules', array(
                        '%num' => $updated)), 'success');
            return true;
        }

        if ($deleted > 0) {
            $this->session->setMessage($this->text('Deleted %num price rules', array(
                        '%num' => $deleted)), 'success');
            return true;
        }

        return false;
    }

    /**
     * Sets titles on the rules overview page
     */
    protected function setTitleRules()
    {
        $this->setTitle($this->text('Price rules'));
    }

    /**
     * Sets breadcrumbs on the rules overview page
     */
    protected function setBreadcrumbRules()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));
    }

    /**
     * Renders the rules overview page
     */
    protected function outputRules()
    {
        $this->output('sale/price/list');
    }

    /**
     * Returns an array of rules
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getRules(array $limit, array $query)
    {
        $options = array('limit' => $limit);
        $options += $query;

        $rules = $this->rule->getList($options);

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
    protected function setTitleEdit($rule)
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
    protected function setBreadcrumbEdit()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));

        $this->setBreadcrumb(array(
            'text' => $this->text('Price rules'),
            'url' => $this->url('admin/sale/price')));
    }

    /**
     * Renders templates for rule edit page
     */
    protected function outputEdit()
    {
        $this->output('sale/price/edit');
    }

    /**
     * Returns an array of rule data
     * @param mixed $rule_id
     * @return array
     */
    protected function get($rule_id)
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
    protected function delete(array $rule)
    {
        $this->controlAccess('price_rule_delete');
        $this->rule->delete($rule['price_rule_id']);
        $this->redirect('admin/sale/price', $this->text('Price rule has been deleted'), 'success');
    }

    /**
     * Saves a submitted rule
     * @param array $rule
     * @return null
     */
    protected function submit(array $rule = array())
    {
        $this->setSubmitted('price_rule', null, false);

        $this->validate($rule);

        if ($this->hasErrors('price_rule')) {
            return;
        }

        if (isset($rule['price_rule_id'])) {
            $this->controlAccess('price_rule_edit');
            $this->rule->update($rule['price_rule_id'], $this->getSubmitted());
            $this->redirect('admin/sale/price', $this->text('Price rule has been updated'), 'success');
        }

        $this->controlAccess('price_rule_add');
        $this->rule->add($this->getSubmitted());
        $this->redirect('admin/sale/price', $this->text('Price rule has been added'), 'success');
    }

    /**
     * Validates a rule
     * @param array $rule
     */
    protected function validate(array $rule = array())
    {
        $this->setSubmittedBool('status');

        if (isset($rule['price_rule_id'])) {
            $this->setSubmitted('price_rule_id', $rule['price_rule_id']);
        }

        $this->addValidator('name', array(
            'length' => array('min' => 1, 'max' => 255)));

        $this->addValidator('value', array(
            'numeric' => array(),
            'length' => array('max' => 10)));

        $this->addValidator('weight', array(
            'numeric' => array(),
            'length' => array('max' => 2)));

        $this->addValidator('data.conditions', array(
            'pricerule_conditions' => array()));

        $this->validateCode();

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
     * Validates a coupon code
     * @return boolean
     */
    protected function validateCode()
    {
        if (empty($this->submitted['code'])) {
            return;
        }

        if (mb_strlen($this->submitted['code']) > 255) {
            $this->errors['code'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
            return false;
        }

        $price_rule_id = isset($this->submitted['price_rule_id']) ? $this->submitted['price_rule_id'] : null;

        if ($this->rule->codeExists($this->submitted['code'], $this->submitted['store_id'], $price_rule_id)) {
            $this->errors['code'] = $this->text('This price rule code already exists');
            return false;
        }

        return true;
    }

    /**
     * Converts an array of conditions into multiline string
     * @return null
     */
    protected function setDataConditions()
    {
        $conditions = $this->getData('price_rule.data.conditions');
        
        if(empty($conditions) || !is_array($conditions)){
            return;
        }

        Tool::sortWeight($conditions);

        $modified = array();
        foreach ($conditions as $i => $info) {
            $modified[] = $info['original'];
        }
        
        $this->setData('price_rule.data.conditions', implode("\n", $modified));
    }

}
