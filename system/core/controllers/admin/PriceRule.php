<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\PriceRule as P;
use core\models\Currency;
use core\models\Price;
use core\classes\Tool;

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
     * @param P $rule
     * @param Currency $currency
     * @param Price $price
     */
    public function __construct(P $rule, Currency $currency, Price $price)
    {
        parent::__construct();

        $this->rule = $rule;
        $this->currency = $currency;
        $this->price = $price;
    }

    /**
     * Displays the price rule overview page
     */
    public function rules()
    {
        $query = $this->getFilterQuery();
        $total = $this->setPager($this->getTotalRules($query), $query);

        $this->data['price_rules'] = $this->getRules($total, $query);
        $this->data['stores'] = $this->store->getNames();

        $filters = array('name', 'code', 'value', 'value_type', 'weight', 'status', 'store_id', 'type');
        $this->setFilter($filters, $query);

        $action = $this->request->post('action');
        $selected = $this->request->post('selected', array());
        $value = $this->request->post('value');

        if ($action) {
            $this->action($selected, $action, $value);
        }

        $this->setTitleRules();
        $this->setBreadcrumbRules();
        $this->outputRules();
    }

    /**
     * Returns total number of price rules for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalRules($query)
    {
        return $this->rule->getList(array('count' => true) + $query);
    }

    /**
     * Applies an action to the selected price rules
     * @param array $selected
     * @param string $action
     * @param string $value
     * @return boolean
     */
    protected function action($selected, $action, $value)
    {
        $deleted = $updated = 0;
        foreach ($selected as $rule_id) {
            if ($action == 'status' && $this->access('price_rule_edit')) {
                $updated += (int) $this->rule->update($rule_id, array('status' => $value));
            }

            if ($action == 'delete' && $this->access('price_rule_delete')) {
                $deleted += (int) $this->rule->delete($rule_id);
            }
        }

        if ($updated) {
            $this->session->setMessage($this->text('Updated %num price rules', array('%num' => $updated)), 'success');

            return true;
        }

        if ($deleted) {
            $this->session->setMessage($this->text('Deleted %num price rules', array('%num' => $deleted)), 'success');

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
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
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
    protected function getRules($limit, $query)
    {
        $rules = $this->rule->getList(array('limit' => $limit) + $query);

        foreach ($rules as &$rule) {
            if ($rule['value_type'] == 'fixed') {
                $rule['value'] = $this->price->decimal($rule['value'], $rule['currency']);
            }
        }

        return $rules;
    }

    /**
     * Displays the price rule edit form
     * @param type $rule_id
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

        $this->prepareConditions();

        $this->setTitleEdit($rule);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Sets titles on the edit rules page
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
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
        $this->setBreadcrumb(array('text' => $this->text('Price rules'), 'url' => $this->url('admin/sale/price')));
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
     * @param integer|null $rule_id
     * @return array
     */
    protected function get($rule_id)
    {
        if (!is_numeric($rule_id)) {
            return array();
        }

        $rule = $this->rule->get($rule_id);

        if (!$rule) {
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
    protected function delete($rule)
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
    protected function submit($rule = array())
    {
        $this->submitted = $this->request->post('price_rule', '', false);

        $this->validate($rule);

        if ($this->formErrors()) {
            $this->data['price_rule'] = $this->submitted;

            return;
        }

        if ($this->submitted['value_type'] == 'fixed') {
            $this->submitted['value'] = $this->price->amount((float) $this->submitted['value'], $this->submitted['currency'], false);
        }

        if (isset($rule['price_rule_id'])) {
            $this->controlAccess('price_rule_edit');
            $this->rule->update($rule['price_rule_id'], $this->submitted);
            $this->redirect('admin/sale/price', $this->text('Price rule has been updated'), 'success');
        }

        $this->controlAccess('price_rule_add');
        $this->rule->add($this->submitted);
        $this->redirect('admin/sale/price', $this->text('Price rule has been added'), 'success');
    }

    /**
     * Validates a rule
     * @param type $rule
     */
    protected function validate($rule = array())
    {
        if (isset($rule['price_rule_id'])) {
            $this->submitted['price_rule_id'] = $rule['price_rule_id'];
        }

        // Fix checkboxes
        $this->submitted['status'] = !empty($this->submitted['status']);

        if (!isset($this->submitted['store_id'])) {
            $this->submitted['store_id'] = $this->store->getDefault();
        }

        $this->validateName();
        $this->validateCode();
        $this->validateValue();
        $this->validateWeight();
        $this->validateConditions();
    }

    /**
     * Validates a rule name
     * @return boolean
     */
    protected function validateName()
    {
        if (!isset($this->submitted['name'])) {
            return true;
        }

        if (!$this->submitted['name'] || mb_strlen($this->submitted['name']) > 255) {
            $this->data['form_errors']['name'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));

            return false;
        }

        return true;
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
            $this->data['form_errors']['code'] = $this->text('Content must not exceed %s characters', array('%s' => 255));

            return false;
        }

        $price_rule_id = isset($this->submitted['price_rule_id']) ? $this->submitted['price_rule_id'] : null;

        if ($this->rule->codeExists($this->submitted['code'], $this->submitted['store_id'], $price_rule_id)) {
            $this->data['form_errors']['code'] = $this->text('This price rule code already exists');

            return false;
        }

        return true;
    }

    /**
     * Validates a price value
     * @return boolean
     */
    protected function validateValue()
    {
        if (!is_numeric($this->submitted['value'])) {
            $this->data['form_errors']['value'] = $this->text('Only numeric values allowed');

            return false;
        }

        if ($this->submitted['value_type'] == 'percent') {
            if (abs($this->submitted['value']) > 100) {
                $this->data['form_errors']['value'] = $this->text('Percent value must not be greater than 100');

                return false;
            }

            return true;
        }

        if (strlen($this->submitted['value']) > 10) {
            $this->data['form_errors']['value'] = $this->text('Content must not exceed %s characters', array('%s' => 10));

            return false;
        }

        return true;
    }

    /**
     * Validates the rule weight
     * @return boolean
     */
    protected function validateWeight()
    {
        if ($this->submitted['weight']) {
            if (!is_numeric($this->submitted['weight']) || strlen($this->submitted['weight']) > 2) {
                $this->data['form_errors']['weight'] = $this->text('Only numeric value and no more than %s digits', array('%s' => 2));

                return false;
            }

            return true;
        }

        $this->submitted['weight'] = 0;

        return true;
    }

    /**
     * Validates rule conditions
     * @return boolean
     */
    protected function validateConditions()
    {
        if (empty($this->submitted['data']['conditions'])) {
            return true;
        }

        $modified_conditions = $error_lines = array();
        $existing_operators = array_map('htmlspecialchars', $this->rule->getConditionOperators());
        $conditions = Tool::stringToArray($this->submitted['data']['conditions']);

        foreach ($conditions as $line => $condition) {
            $line++;

            $condition = trim($condition);
            $parts = array_map('trim', explode(' ', $condition));
            
            $condition_id = array_shift($parts);
            $operator = array_shift($parts);

            $parameters = array_filter(explode(',', implode('', $parts)), function ($value) {
                return ($value !== "");
            });

            if (empty($parameters)) {
                $error_lines[] = $line;
                continue;
            }

            if (!in_array(htmlspecialchars($operator), $existing_operators)) {
                $error_lines[] = $line;
                continue;
            }

            $validator = $this->rule->getConditionHandler($condition_id, 'validate');

            if (!$validator) {
                $error_lines[] = $line;
                continue;
            }

            if (call_user_func_array($validator, array(&$parameters, $this->submitted)) !== true) {
                $error_lines[] = $line;
                continue;
            }

            $modified_conditions[] = array(
                'id' => $condition_id,
                'operator' => $operator,
                'value' => $parameters,
                'original' => $condition,
                'weight' => $line,
            );
        }

        if (!empty($error_lines)) {
            $this->data['form_errors']['conditions'] = $this->text('Something wrong on lines %num', array('%num' => implode(',', $error_lines)));

            return false;
        }

        $this->submitted['data']['conditions'] = $modified_conditions;

        return true;
    }

    /**
     * Converts an array of conditions into multiline string
     * @return null
     */
    protected function prepareConditions()
    {
        if (empty($this->data['price_rule']['data']['conditions'])) {
            return;
        }

        if (!is_array($this->data['price_rule']['data']['conditions'])) {
            return;
        }

        Tool::sortWeight($this->data['price_rule']['data']['conditions']);

        $conditions = array();
        foreach ($this->data['price_rule']['data']['conditions'] as $i => $info) {
            $conditions[] = $info['original'];
        }

        $this->data['price_rule']['data']['conditions'] = implode("\n", $conditions);
    }
}
