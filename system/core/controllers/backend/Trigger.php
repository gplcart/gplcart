<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Trigger as TriggerModel;
use gplcart\core\models\Condition as ConditionModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to triggers
 */
class Trigger extends BackendController
{

    /**
     * Condition model instance
     * @var \gplcart\core\models\Condition $condition
     */
    protected $condition;

    /**
     * Trigger model instance
     * @var \gplcart\core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * The current trigger
     * @var array
     */
    protected $data_trigger = array();

    /**
     * Constructor
     * @param TriggerModel $trigger
     * @param ConditionModel $condition
     */
    public function __construct(TriggerModel $trigger, ConditionModel $condition)
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

        $this->setTitleListTrigger();
        $this->setBreadcrumbListTrigger();

        $query = $this->getFilterQuery();

        $allowed = array('store_id', 'status', 'name');
        $this->setFilter($allowed, $query);

        $total = $this->getTotalTrigger($query);
        $limit = $this->setPager($total, $query);

        $this->setData('stores', $this->store->getNames());
        $this->setData('triggers', $this->getListTrigger($limit, $query));

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
    }

    /**
     * Returns total number of triggers
     * @param array $query
     * @return integer
     */
    protected function getTotalTrigger(array $query)
    {
        $query['count'] = true;
        return (int) $this->trigger->getList($query);
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
        return (array) $this->trigger->getList($query);
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
        $this->setTrigger($trigger_id);

        $this->setTitleEditTrigger();
        $this->setBreadcrumbEditTrigger();

        $this->setData('trigger', $this->data_trigger);
        $this->setData('stores', $this->store->getNames());
        $this->setData('can_delete', $this->canDeleteTrigger());
        $this->setData('conditions', $this->getConditionsTrigger());
        $this->setData('operators', $this->getConditionOperatorsTrigger());

        $this->submitTrigger();

        $this->setDataEditTrigger();
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
     * @return boolean
     */
    protected function canDeleteTrigger()
    {
        return isset($this->data_trigger['trigger_id']) && $this->access('trigger_delete');
    }

    /**
     * Returns a trigger
     * @param integer $trigger_id
     * @return array
     */
    protected function setTrigger($trigger_id)
    {
        if (!is_numeric($trigger_id)) {
            return array();
        }

        $trigger = $this->trigger->get($trigger_id);

        if (empty($trigger)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_trigger = $trigger;
    }

    /**
     * Saves an array of submitted trigger data
     * @return null
     */
    protected function submitTrigger()
    {
        if ($this->isPosted('delete')) {
            $this->deleteTrigger();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateTrigger()) {
            return null;
        }

        if (isset($this->data_trigger['trigger_id'])) {
            $this->updateTrigger();
        } else {
            $this->addTrigger();
        }
    }

    /**
     * Validates a submitted trigger
     * @return bool
     */
    protected function validateTrigger()
    {
        $this->setSubmitted('trigger');
        $this->setSubmittedBool('status');
        $this->setSubmittedArray('data.conditions');
        $this->setSubmitted('update', $this->data_trigger);

        $this->validateComponent('trigger');

        return !$this->hasErrors();
    }

    /**
     * Deletes a trigger
     */
    protected function deleteTrigger()
    {
        $this->controlAccess('trigger_delete');
        $deleted = $this->trigger->delete($this->data_trigger['trigger_id']);

        if (empty($deleted)) {
            $message = $this->text('Unable to delete this trigger');
            $this->redirect('', $message, 'warning');
        }

        $message = $this->text('Trigger has been deleted');
        $this->redirect('admin/settings/trigger', $message, 'success');
    }

    /**
     * Updates a trigger with the submitted values
     */
    protected function updateTrigger()
    {
        $this->controlAccess('trigger_edit');
        $submitted = $this->getSubmitted();

        $this->trigger->update($this->data_trigger['trigger_id'], $submitted);

        $message = $this->text('Trigger has been updated');
        $this->redirect('admin/settings/trigger', $message, 'success');
    }

    /**
     * Adds a new trigger using an array of submitted data
     */
    protected function addTrigger()
    {
        $this->controlAccess('trigger_add');

        $result = $this->trigger->add($this->getSubmitted());

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

        if (!$this->isError()) {
            // Do not sort on errors when "weight" is not set
            gplcart_array_sort($conditions);
        }

        $modified = array();
        foreach ($conditions as $condition) {
            $modified[] = is_string($condition) ? $condition : $condition['original'];
        }

        $this->setData('trigger.data.conditions', implode("\n", $modified));
    }

    /**
     * Sets title on the edit trigger page
     */
    protected function setTitleEditTrigger()
    {
        $title = $this->text('Add trigger');

        if (isset($this->data_trigger['name'])) {
            $vars = array('%name' => $this->data_trigger['name']);
            $title = $this->text('Edit trigger %name', $vars);
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
