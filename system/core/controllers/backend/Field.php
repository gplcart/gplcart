<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Field as FieldModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to product fields
 */
class Field extends BackendController
{

    /**
     * Field model instance
     * @var \gplcart\core\models\Field $field
     */
    protected $field;

    /**
     * The current field
     * @var array
     */
    protected $data_field = array();

    /**
     * Constructor
     * @param FieldModel $field
     */
    public function __construct(FieldModel $field)
    {
        parent::__construct();

        $this->field = $field;
    }

    /**
     * Displays the field overview page
     */
    public function listField()
    {
        $this->actionField();

        $this->setTitleListField();
        $this->setBreadcrumbListField();

        $query = $this->getFilterQuery();

        $allowed = array('title', 'type', 'widget', 'field_id');
        $this->setFilter($allowed, $query);

        $total = $this->getTotalField($query);
        $limit = $this->setPager($total, $query);

        $this->setData('fields', $this->getListField($limit, $query));
        $this->setData('widget_types', $this->field->getWidgetTypes());

        $this->outputListField();
    }

    /**
     * Applies an action to the selected fields
     */
    protected function actionField()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $selected = (array) $this->request->post('selected', array());

        $deleted = 0;
        foreach ($selected as $field_id) {
            if ($action === 'delete' && $this->access('field_delete')) {
                $deleted += (int) $this->field->delete($field_id);
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Fields have been deleted');
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Returns total number of fields for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalField(array $query)
    {
        $query['count'] = true;
        return (int) $this->field->getList($query);
    }

    /**
     * Returns an array of fields
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListField(array $limit, array $query)
    {
        $query['limit'] = $limit;
        return $this->field->getList($query);
    }

    /**
     * Sets titles on the field overview page
     */
    protected function setTitleListField()
    {
        $this->setTitle($this->text('Fields'));
    }

    /**
     * Sets breadcrumbs on the field overview page
     */
    protected function setBreadcrumbListField()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the field overview page
     */
    protected function outputListField()
    {
        $this->output('content/field/list');
    }

    /**
     * Displays the field edit form
     * @param integer|null $field_id
     */
    public function editField($field_id = null)
    {
        $this->setField($field_id);

        $this->setTitleEditField();
        $this->setBreadcrumbEditField();

        $this->setData('field', $this->data_field);
        $this->setData('types', $this->field->getTypes());
        $this->setData('can_delete', $this->canDeleteField());
        $this->setData('widget_types', $this->field->getWidgetTypes());

        $this->submitField();
        $this->outputEditField();
    }

    /**
     * Saves a submitted field values
     * @return null
     */
    protected function submitField()
    {
        if ($this->isPosted('delete')) {
            $this->deleteField();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateField()) {
            return null;
        }

        if (isset($this->data_field['field_id'])) {
            $this->updateField();
        } else {
            $this->addField();
        }
    }

    /**
     * Performs validation checks on the given field
     * @return bool
     */
    protected function validateField()
    {
        $this->setSubmitted('field');

        $this->setSubmitted('update', $this->data_field);
        $this->validateComponent('field');

        return !$this->hasErrors('field');
    }

    /**
     * Whether the field can be deleted
     * @return bool
     */
    protected function canDeleteField()
    {
        return isset($this->data_field['field_id'])//
                && $this->field->canDelete($this->data_field['field_id'])//
                && $this->access('field_delete');
    }

    /**
     * Returns a field
     * @param integer $field_id
     * @return array
     */
    protected function setField($field_id)
    {
        if (!is_numeric($field_id)) {
            return array();
        }

        $field = $this->field->get($field_id);

        if (empty($field)) {
            $this->outputHttpStatus(404);
        }

        $this->data_field = $field;
        return $field;
    }

    /**
     * Deletes a field
     */
    protected function deleteField()
    {
        $this->controlAccess('field_delete');

        $deleted = $this->field->delete($this->data_field['field_id']);

        if ($deleted) {
            $message = $this->text('Field has been deleted');
            $this->redirect('admin/content/field', $message, 'success');
        }

        $message = $this->text('Unable to delete this field');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Updates a field
     */
    protected function updateField()
    {
        $this->controlAccess('field_edit');

        $values = $this->getSubmitted();
        $this->field->update($this->data_field['field_id'], $values);

        $message = $this->text('Field has been updated');
        $this->redirect('admin/content/field', $message, 'success');
    }

    /**
     * Adds a new field
     */
    protected function addField()
    {
        $this->controlAccess('field_add');

        $this->field->add($this->getSubmitted());

        $message = $this->text('Field has been added');
        $this->redirect('admin/content/field', $message, 'success');
    }

    /**
     * Sets titles on the field edit form
     */
    protected function setTitleEditField()
    {
        $title = $this->text('Add field');

        if (isset($this->data_field['field_id'])) {
            $vars = array('%name' => $this->data_field['title']);
            $title = $this->text('Edit field %name', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the field edit form
     */
    protected function setBreadcrumbEditField()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/content/field'),
            'text' => $this->text('Fields')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the field edit page
     */
    protected function outputEditField()
    {
        $this->output('content/field/edit');
    }

}
