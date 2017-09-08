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
        $this->actionListField();

        $this->setTitleListField();
        $this->setBreadcrumbListField();

        $this->setFilterListField();
        $this->setTotalListField();
        $this->setPagerLimit();

        $this->setData('fields', $this->getListField());
        $this->setData('widget_types', $this->field->getWidgetTypes());

        $this->outputListField();
    }

    /**
     * Sets filter on the field overview page
     */
    protected function setFilterListField()
    {
        $allowed = array('title', 'type', 'widget', 'field_id');
        $this->setFilter($allowed);
    }

    /**
     * Applies an action to the selected fields
     */
    protected function actionListField()
    {
        list($selected, $action) = $this->getPostedAction();

        if (empty($action)) {
            return null;
        }

        $deleted = 0;
        foreach ($selected as $field_id) {
            if ($action === 'delete' && $this->access('field_delete')) {
                $deleted += (int) $this->field->delete($field_id);
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num items', array('%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Set a total number of fields found for the filter conditions
     */
    protected function setTotalListField()
    {
        $query = $this->query_filter;
        $query['count'] = true;
        $this->total = (int) $this->field->getList($query);
    }

    /**
     * Returns an array of fields
     * @return array
     */
    protected function getListField()
    {
        $query = $this->query_filter;
        $query['limit'] = $this->limit;

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
        $this->setBreadcrumbHome();
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

        $this->submitEditField();
        $this->outputEditField();
    }

    /**
     * Handles a submitted field data
     */
    protected function submitEditField()
    {
        if ($this->isPosted('delete')) {
            $this->deleteField();
        } else if ($this->isPosted('save') && $this->validateEditField()) {
            if (isset($this->data_field['field_id'])) {
                $this->updateField();
            } else {
                $this->addField();
            }
        }
    }

    /**
     * Validates an array of submitted field data
     * @return bool
     */
    protected function validateEditField()
    {
        $this->setSubmitted('field');
        $this->setSubmitted('update', $this->data_field);

        $this->validateComponent('field');

        return !$this->hasErrors();
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
     * Set a field data
     * @param integer $field_id
     */
    protected function setField($field_id)
    {
        if (is_numeric($field_id)) {
            $this->data_field = $this->field->get($field_id);
            if (empty($this->data_field)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Deletes a field
     */
    protected function deleteField()
    {
        $this->controlAccess('field_delete');
        if ($this->field->delete($this->data_field['field_id'])) {
            $this->redirect('admin/content/field', $this->text('Field has been deleted'), 'success');
        }
        $this->redirect('', $this->text('Unable to delete'), 'danger');
    }

    /**
     * Updates a field
     */
    protected function updateField()
    {
        $this->controlAccess('field_edit');
        $this->field->update($this->data_field['field_id'], $this->getSubmitted());
        $this->redirect('admin/content/field', $this->text('Field has been updated'), 'success');
    }

    /**
     * Adds a new field
     */
    protected function addField()
    {
        $this->controlAccess('field_add');
        $this->field->add($this->getSubmitted());
        $this->redirect('admin/content/field', $this->text('Field has been added'), 'success');
    }

    /**
     * Sets title on the field edit form
     */
    protected function setTitleEditField()
    {
        if (isset($this->data_field['field_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_field['title']));
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
        $this->setBreadcrumbHome();

        $breadcrumb = array(
            'url' => $this->url('admin/content/field'),
            'text' => $this->text('Fields')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the field edit page
     */
    protected function outputEditField()
    {
        $this->output('content/field/edit');
    }

}
