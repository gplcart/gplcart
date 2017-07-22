<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Trigger as TriggerModel,
    gplcart\core\models\Condition as ConditionModel;
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
        $this->actionListTrigger();

        $this->setTitleListTrigger();
        $this->setBreadcrumbListTrigger();

        $this->setFilterListTrigger();
        $this->setTotalListTrigger();
        $this->setPagerLimit();

        $this->setData('triggers', $this->getListTrigger());

        $this->outputListTrigger();
    }

    /**
     * Set filter on the trigger overview page
     */
    protected function setFilterListTrigger()
    {
        $this->setFilter(array('store_id', 'status', 'name'));
    }

    /**
     * Applies an action to the selected triggers
     */
    protected function actionListTrigger()
    {
        $value = $this->getPosted('value', '', true, 'string');
        $action = $this->getPosted('action', '', true, 'string');
        $selected = $this->getPosted('selected', array(), true, 'array');

        if (empty($action)) {
            return null;
        }

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
            $message = $this->text('Updated %num items', array('%num' => $updated));
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num items', array('%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Sets a total number of triggers found for the filter conditions
     */
    protected function setTotalListTrigger()
    {
        $query = $this->query_filter;
        $query['count'] = true;
        $this->total = (int) $this->trigger->getList($query);
    }

    /**
     * Returns an array of triggers
     * @return array
     */
    protected function getListTrigger()
    {
        $query = $this->query_filter;
        $query['limit'] = $this->limit;

        return (array) $this->trigger->getList($query);
    }

    /**
     * Sets title on the trigger overview page
     */
    protected function setTitleListTrigger()
    {
        $this->setTitle($this->text('Triggers'));
    }

    /**
     * Sets breadcrumbs on the trigger overview page
     */
    protected function setBreadcrumbListTrigger()
    {
        $this->setBreadcrumbBackend();
    }

    /**
     * Render and output the trigger overview page
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
        $this->setData('can_delete', $this->canDeleteTrigger());
        $this->setData('conditions', $this->getConditionsTrigger());
        $this->setData('operators', $this->getConditionOperatorsTrigger());

        $this->submitEditTrigger();

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
     * Sets a trigger data
     * @param integer $trigger_id
     */
    protected function setTrigger($trigger_id)
    {
        if (is_numeric($trigger_id)) {
            $this->data_trigger = $this->trigger->get($trigger_id);
            if (empty($this->data_trigger)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Handles a submitted trigger data
     */
    protected function submitEditTrigger()
    {
        if ($this->isPosted('delete')) {
            $this->deleteTrigger();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateEditTrigger()) {
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
    protected function validateEditTrigger()
    {
        $this->setSubmitted('trigger', null, false);
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
            $message = $this->text('Unable to delete');
            $this->redirect('', $message, 'warning');
        }

        $message = $this->text('Trigger has been deleted');
        $this->redirect('admin/settings/trigger', $message, 'success');
    }

    /**
     * Updates a trigger
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
     * Adds a new trigger
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
     */
    protected function setDataEditTrigger()
    {
        $conditions = $this->getData('trigger.data.conditions');

        if (empty($conditions) || !is_array($conditions)) {
            return null;
        }

        if (!$this->isError()) {
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
        $this->setBreadcrumbBackend();

        $breadcrumb = array(
            'url' => $this->url('admin/settings/trigger'),
            'text' => $this->text('Triggers')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the edit trigger page
     */
    protected function outputEditTrigger()
    {
        $this->output('settings/trigger/edit');
    }

}
