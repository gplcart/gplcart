<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Field as ModelsField;

/**
 * Handles incoming requests and outputs data related to product fields
 */
class Field extends Controller
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
        if ($this->isPosted('action')) {
            $this->actionField();
        }

        $query = $this->getFilterQuery();
        $total = $this->getTotalField($query);
        $limit = $this->setPager($total, $query);
        $fields = $this->getListField($limit, $query);
        $widget_types = $this->field->widgetTypes();

        $this->setData('fields', $fields);
        $this->setData('widget_types', $widget_types);

        $allowed = array('title', 'type', 'widget');
        $this->setFilter($allowed, $query);

        $this->setTitleListField();
        $this->setBreadcrumbListField();
        $this->outputListField();
    }

    /**
     * Displays the field edit form
     * @param integer|null $field_id
     */
    public function editField($field_id = null)
    {
        $field = $this->getField($field_id);
        $widget_types = $this->field->widgetTypes();

        $this->setData('field', $field);
        $this->setData('widget_types', $widget_types);

        if ($this->isPosted('delete')) {
            $this->deleteField($field);
        }

        if ($this->isPosted('save')) {
            $this->submitField($field);
        }

        $this->setTitleEditField($field);
        $this->setBreadcrumbEditField();
        $this->outputEditField();
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
     * Renders the field overview page
     */
    protected function outputListField()
    {
        $this->output('content/field/list');
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
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));
    }

    /**
     * Renders the field edit page
     */
    protected function outputEditField()
    {
        $this->output('content/field/edit');
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
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/content/field'),
            'text' => $this->text('Fields')));
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
     * Deletes a field
     * @param array $field
     */
    protected function deleteField(array $field)
    {
        $this->controlAccess('field_delete');

        $deleted = $this->field->delete($field['field_id']);

        if ($deleted) {
            $this->redirect('admin/content/field', $this->text('Field has been deleted'), 'success');
        }

        $text = $this->text('Unable to delete this field.'
                . ' The most probable reason - it is used by one or more products');

        $this->redirect('', $text, 'danger');
    }

    /**
     * Applies an action to the selected fields
     */
    protected function actionField()
    {
        $action = (string) $this->request->post('action');
        $selected = (array) $this->request->post('selected', array());

        $deleted = 0;
        foreach ($selected as $field_id) {
            if ($action === 'delete' && $this->access('field_delete')) {
                $deleted += (int) $this->field->delete($field_id);
            }
        }

        if ($deleted > 0) {
            $this->setMessage($this->text('Fields have been deleted'), 'success', true);
        }
    }

    /**
     * Saves a submitted field values
     * @param array $field
     * @return null
     */
    protected function submitField(array $field)
    {
        $this->setSubmitted('field');
        $this->validateField($field);

        if ($this->hasErrors('field')) {
            return;
        }

        if (isset($field['field_id'])) {
            $this->updateField($field);
        }

        $this->addField();
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
        $this->redirect('admin/content/field', $this->text('Field has been updated'), 'success');
    }

    /**
     * Adds a new field
     */
    protected function addField()
    {
        $this->controlAccess('field_add');
        $values = $this->getSubmitted();
        $this->field->add($values);
        $this->redirect('admin/content/field', $this->text('Field has been added'), 'success');
    }

    /**
     * Performs validation checks on the given field
     * @param array $field
     */
    protected function validateField(array $field)
    {
        $this->addValidator('title', array(
            'length' => array('min' => 1, 'max' => 255)
        ));

        $this->addValidator('weight', array(
            'numeric' => array(),
            'length' => array('max' => 2)
        ));

        if (empty($field['field_id'])) {

            $this->addValidator('type', array(
                'required' => array()
            ));
        }

        $this->addValidator('widget', array(
            'required' => array()
        ));

        $this->addValidator('translation', array(
            'translation' => array()
        ));

        $this->setValidators($field);
    }

}
