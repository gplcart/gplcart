<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\backend;

use core\models\Trigger as TriggerModel;
use core\models\Condition as ConditionModel;
use core\controllers\backend\Controller as BackendController;

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
     * @param TriggerModel $trigger
     * @param ConditionModel $condition
     */
    public function __construct(TriggerModel $trigger,
            ConditionModel $condition)
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
            return null;
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

        return null;
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
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
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
        $conditions = $this->getConditionsTrigger();
        $operators = $this->getConditionOperatorsTrigger();
        $can_delete = $this->canDeleteTrigger($trigger);

        $this->setData('stores', $stores);
        $this->setData('trigger', $trigger);
        $this->setData('operators', $operators);
        $this->setData('conditions', $conditions);
        $this->setData('can_delete', $can_delete);

        $this->submitTrigger($trigger);

        $this->setDataEditTrigger();
        $this->setTitleEditTrigger($trigger);
        $this->setBreadcrumbEditTrigger();
        $this->outputEditTrigger();
    }

    /**
     * Returns an array of trigger conditions
     * @return array
     */
    protected function getConditionsTrigger()
    {
        return $this->condition->getHandlers();
    }

    /**
     * Returns an array of condition operators
     * @return array
     */
    protected function getConditionOperatorsTrigger()
    {
        return $this->condition->getOperators();
    }

    /**
     * Whether the trigger can be deleted
     * @param array $trigger
     * @return boolean
     */
    protected function canDeleteTrigger(array $trigger)
    {
        return (isset($trigger['trigger_id']) && $this->access('trigger_delete'));
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
     * @return null|void
     */
    protected function submitTrigger(array $trigger)
    {
        if ($this->isPosted('delete')) {
            return $this->deleteTrigger($trigger);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('trigger');
        $this->validateTrigger($trigger);

        if ($this->hasErrors('trigger')) {
            return null;
        }

        if (isset($trigger['trigger_id'])) {
            return $this->updateTrigger($trigger);
        }

        return $this->addTrigger();
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
     * Validates a submitted trigger
     * @param array $trigger
     */
    protected function validateTrigger(array $trigger)
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $trigger);
        $this->setSubmittedArray('data.conditions');
        $this->validate('trigger');
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
     * Converts an array of conditions into a multiline string
     * @return null
     */
    protected function setDataEditTrigger()
    {
        $conditions = $this->getData('trigger.data.conditions');

        if (empty($conditions) || !is_array($conditions)) {
            return null;
        }

        gplcart_array_sort($conditions);

        $modified = array();
        foreach ($conditions as $condition) {
            $modified[] = is_string($condition) ? $condition : $condition['original'];
        }

        $this->setData('trigger.data.conditions', implode("\n", $modified));
        return null;
    }

    /**
     * Sets title on the edit trigger page
     * @param array $trigger
     */
    protected function setTitleEditTrigger(array $trigger)
    {
        $title = $this->text('Add trigger');

        if (isset($trigger['name'])) {
            $title = $this->text('Edit trigger %name', array(
                '%name' => $trigger['name']
            ));
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
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/settings/trigger'),
            'text' => $this->text('Triggers')
        );

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
