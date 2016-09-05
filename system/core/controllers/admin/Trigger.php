<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\classes\Tool;
use core\models\Trigger as ModelsTrigger;
use core\models\Condition as ModelsCondition;
use core\controllers\admin\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to triggers
 */
class Trigger extends BackendController
{

    /**
     * Condition model instance
     * @var \core\models\Condition $condition
     */
    protected $condition;

    /**
     * Trigger model instance
     * @var \core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * Constructor
     * @param ModelsTrigger $trigger
     * @param ModelsTrigger $condition
     */
    public function __construct(ModelsTrigger $trigger,
            ModelsCondition $condition)
    {
        parent::__construct();

        $this->trigger = $trigger;
        $this->condition = $condition;
    }

    /**
     * Displays the trigger overview page
     */
    public function listTrigger()
    {
        $this->actionTrigger();

        $query = $this->getFilterQuery();
        $total = $this->getTotalTrigger($query);
        $limit = $this->setPager($total, $query);

        $stores = $this->store->getNames();
        $triggers = $this->getListTrigger($limit, $query);

        $this->setData('stores', $stores);
        $this->setData('triggers', $triggers);

        $allowed = array('store_id', 'status', 'name');
        $this->setFilter($allowed, $query);

        $this->setTitleListTrigger();
        $this->setBreadcrumbListTrigger();
        $this->outputListTrigger();
    }

    /**
     * Applies an action to the selected triggers
     */
    protected function actionTrigger()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        $deleted = $updated = 0;

        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('trigger_edit')) {
                $updated += (int) $this->trigger->update($id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('trigger_delete')) {
                $deleted += (int) $this->trigger->delete($id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Triggers have been updated');
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Triggers have been deleted');
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Returns total number of triggers
     * @param array $query
     * @return integer
     */
    protected function getTotalTrigger(array $query)
    {
        $query['count'] = true;
        return $this->trigger->getList($query);
    }

    /**
     * Returns an array of triggers
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListTrigger(array $limit, array $query)
    {
        $query['limit'] = $limit;
        return $this->trigger->getList($query);
    }

    /**
     * Sets title on the triggers overview page
     */
    protected function setTitleListTrigger()
    {
        $this->setTitle($this->text('Triggers'));
    }

    /**
     * Sets breadcrumbs on the triggers overview page
     */
    protected function setBreadcrumbListTrigger()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the triggers overview page
     */
    protected function outputListTrigger()
    {
        $this->output('settings/trigger/list');
    }

    /**
     * Displays the trigger edit page
     * @param null|integer $trigger_id
     */
    public function editTrigger($trigger_id = null)
    {
        $trigger = $this->getTrigger($trigger_id);

        $stores = $this->store->getNames();
        $conditions = $this->condition->getHandlers();
        $operators = $this->condition->getOperators();

        $this->setData('stores', $stores);
        $this->setData('trigger', $trigger);
        $this->setData('operators', $operators);
        $this->setData('conditions', $conditions);

        $this->submitTrigger($trigger);

        $this->setDataEditTrigger();

        $this->setTitleEditTrigger($trigger);
        $this->setBreadcrumbEditTrigger();
        $this->outputEditTrigger();
    }

    /**
     * Converts an array of conditions into a multiline string
     * @return null
     */
    protected function setDataEditTrigger()
    {
        $conditions = $this->getData('trigger.data.conditions');

        if (!empty($conditions) && is_array($conditions)) {

            Tool::sortWeight($conditions);

            $modified = array();
            foreach ($conditions as $condition) {
                $modified[] = $condition['original'];
            }

            $this->setData('trigger.data.conditions', implode("\n", $modified));
        }
    }

    /**
     * Returns a trigger
     * @param integer $trigger_id
     * @return array
     */
    protected function getTrigger($trigger_id)
    {
        if (!is_numeric($trigger_id)) {
            return array();
        }

        $trigger = $this->trigger->get($trigger_id);

        if (empty($trigger)) {
            $this->outputError(404);
        }

        return $trigger;
    }

    /**
     * Saves an array of submitted trigger data
     * @param array $trigger
     */
    protected function submitTrigger(array $trigger)
    {
        if ($this->isPosted('delete')) {
            return $this->deleteTrigger($trigger);
        }

        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('trigger');
        $this->validateTrigger($trigger);

        if ($this->hasErrors('trigger')) {
            return;
        }

        if (isset($trigger['trigger_id'])) {
            return $this->updateTrigger($trigger);
        }

        $this->addTrigger();
    }

    /**
     * Validates a submitted trigger
     * @param array $trigger
     */
    protected function validateTrigger(array $trigger)
    {
        $this->setSubmittedBool('status');

        $this->addValidator('name', array(
            'length' => array('min' => 1, 'max' => 255)
        ));

        $this->addValidator('store_id', array(
            'required' => array()
        ));

        $this->addValidator('weight', array(
            'numeric' => array(),
            'length' => array('max' => 2)
        ));

        $this->addValidator('data.conditions', array(
            'trigger_conditions' => array('required' => true),
        ));

        $errors = $this->setValidators($trigger);

        if (empty($errors)) {
            $conditions = $this->getValidatorResult('data.conditions');
            $this->setSubmitted('data.conditions', $conditions);
        }
    }

    /**
     * Updates a trigger with the submitted values
     * @param array $trigger
     */
    protected function updateTrigger(array $trigger)
    {
        $this->controlAccess('trigger_edit');
        $submitted = $this->getSubmitted();

        $result = $this->trigger->update($trigger['trigger_id'], $submitted);

        if (empty($result)) {
            $message = $this->text('Trigger has not been updated');
            $this->redirect('', $message, 'danger');
        }

        $message = $this->text('Trigger has been updated');
        $this->redirect('admin/settings/trigger', $message, 'success');
    }

    /**
     * Deletes a trigger
     * @param array $trigger
     */
    protected function deleteTrigger(array $trigger)
    {
        $this->controlAccess('trigger_delete');
        $deleted = $this->trigger->delete($trigger['trigger_id']);

        if (empty($deleted)) {
            $message = $this->text('Trigger has not been deleted');
            $this->redirect('', $message, 'warning');
        }

        $message = $this->text('Trigger has been deleted');
        $this->redirect('admin/settings/trigger', $message, 'success');
    }

    /**
     * Adds a new trigger using an array of submitted data
     */
    protected function addTrigger()
    {
        $this->controlAccess('trigger_add');
        $submitted = $this->getSubmitted();

        $result = $this->trigger->add($submitted);

        if (empty($result)) {
            $message = $this->text('Trigger has not been added');
            $this->redirect('', $message, 'warning');
        }

        $message = $this->text('Trigger has been added');
        $this->redirect('admin/settings/trigger', $message, 'success');
    }

    /**
     * Sets title on the edit trigger page
     */
    protected function setTitleEditTrigger(array $trigger)
    {
        if (isset($trigger['name'])) {
            $title = $this->text('Edit trigger %name', array(
                '%name' => $trigger['name']));
        } else {
            $title = $this->text('Add trigger');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit trigger page
     */
    protected function setBreadcrumbEditTrigger()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard'));

        $breadcrumbs[] = array(
            'url' => $this->url('admin/settings/trigger'),
            'text' => $this->text('Triggers'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the edit trigger page
     */
    protected function outputEditTrigger()
    {
        $this->output('settings/trigger/edit');
    }

}
