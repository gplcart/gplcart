<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\models\Field as ModelsField;
use core\controllers\admin\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to product fields
 */
class Field extends BackendController
{

    /**
     * Field model instance
     * @var \core\models\Field $field
     */
    protected $field;

    /**
     * Constructor
     * @param ModelsField $field
     */
    public function __construct(ModelsField $field)
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

        $query = $this->getFilterQuery();
        $total = $this->getTotalField($query);
        $limit = $this->setPager($total, $query);
        $fields = $this->getListField($limit, $query);
        $widget_types = $this->field->getWidgetTypes();

        $this->setData('fields', $fields);
        $this->setData('widget_types', $widget_types);

        $allowed = array('title', 'type', 'widget', 'field_id');
        $this->setFilter($allowed, $query);

        $this->setTitleListField();
        $this->setBreadcrumbListField();
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

        return null;
    }

    /**
     * Returns total number of fields for pager
     * @param array $query
     * @return array
     */
    protected function getTotalField(array $query)
    {
        $query['count'] = true;
        return $this->field->getList($query);
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
        $field = $this->getField($field_id);

        $types = $this->field->getTypes();
        $widget_types = $this->field->getWidgetTypes();

        $this->setData('field', $field);
        $this->setData('types', $types);
        $this->setData('widget_types', $widget_types);

        $this->submitField($field);

        $this->setTitleEditField($field);
        $this->setBreadcrumbEditField();
        $this->outputEditField();
    }

    /**
     * Returns a field
     * @param integer $field_id
     * @return array
     */
    protected function getField($field_id)
    {
        if (!is_numeric($field_id)) {
            return array();
        }

        $field = $this->field->get($field_id);

        if (empty($field)) {
            $this->outputError(404);
        }

        return $field;
    }

    /**
     * Saves a submitted field values
     * @param array $field
     * @return null|void
     */
    protected function submitField(array $field)
    {
        if ($this->isPosted('delete')) {
            return $this->deleteField($field);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('field');
        $this->validateField($field);

        if ($this->hasErrors('field')) {
            return null;
        }

        if (isset($field['field_id'])) {
            return $this->updateField($field);
        }

        return $this->addField();
    }

    /**
     * Deletes a field
     * @param array $field
     */
    protected function deleteField(array $field)
    {
        $this->controlAccess('field_delete');

        $deleted = $this->field->delete($field['field_id']);

        if ($deleted) {
            $message = $this->text('Field has been deleted');
            $this->redirect('admin/content/field', $message, 'success');
        }

        $message = $this->text('Unable to delete this field');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Performs validation checks on the given field
     * @param array $field
     */
    protected function validateField(array $field)
    {
        $this->setSubmitted('update', $field);
        $this->validate('field');
    }

    /**
     * Updates a field
     * @param array $field
     */
    protected function updateField(array $field)
    {
        $this->controlAccess('field_edit');

        $values = $this->getSubmitted();
        $this->field->update($field['field_id'], $values);

        $message = $this->text('Field has been updated');
        $this->redirect('admin/content/field', $message, 'success');
    }

    /**
     * Adds a new field
     */
    protected function addField()
    {
        $this->controlAccess('field_add');

        $values = $this->getSubmitted();
        $this->field->add($values);

        $message = $this->text('Field has been added');
        $this->redirect('admin/content/field', $message, 'success');
    }

    /**
     * Sets titles on the field edit form
     * @param array $field
     */
    protected function setTitleEditField(array $field)
    {
        if (isset($field['field_id'])) {
            $title = $this->text('Edit field %name', array('%name' => $field['title']));
        } else {
            $title = $this->text('Add field');
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
