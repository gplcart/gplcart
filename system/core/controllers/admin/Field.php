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
    public function fields()
    {
        if ($this->isSubmitted('action')) {
            $this->action();
        }

        $query = $this->getFilterQuery();
        $total = $this->getTotalFields($query);
        $limit = $this->setPager($total, $query);
        $fields = $this->getFields($limit, $query);
        $widget_types = $this->field->widgetTypes();

        $this->setData('fields', $fields);
        $this->setData('widget_types', $widget_types);

        $allowed = array('title', 'type', 'widget');
        $this->setFilter($allowed, $query);

        $this->setTitleFields();
        $this->setBreadcrumbFields();
        $this->outputFields();
    }

    /**
     * Displays the field edit form
     * @param integer|null $field_id
     */
    public function edit($field_id = null)
    {
        $field = $this->get($field_id);
        $widget_types = $this->field->widgetTypes();

        $this->setData('field', $field);
        $this->setData('widget_types', $widget_types);

        if ($this->isSubmitted('delete')) {
            $this->delete($field);
        }

        if ($this->isSubmitted('save')) {
            $this->submit($field);
        }

        $this->setTitleEdit($field);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Returns an array of fields
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getFields(array $limit, array $query)
    {
        return $this->field->getList(array('limit' => $limit) + $query);
    }

    /**
     * Returns total number of fields for pager
     * @param array $query
     * @return array
     */
    protected function getTotalFields(array $query)
    {
        return $this->field->getList(array('count' => true) + $query);
    }

    /**
     * Renders the field overview page
     */
    protected function outputFields()
    {
        $this->output('content/field/list');
    }

    /**
     * Sets titles on the field overview page
     */
    protected function setTitleFields()
    {
        $this->setTitle($this->text('Fields'));
    }

    /**
     * Sets breadcrumbs on the field overview page
     */
    protected function setBreadcrumbFields()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));
    }

    /**
     * Renders the field edit page
     */
    protected function outputEdit()
    {
        $this->output('content/field/edit');
    }

    /**
     * Sets titles on the field edit form
     * @param array $field
     */
    protected function setTitleEdit(array $field)
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
    protected function setBreadcrumbEdit()
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
    protected function get($field_id)
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
    protected function delete(array $field)
    {
        $this->controlAccess('field_delete');

        if ($this->field->delete($field['field_id'])) {
            $this->redirect('admin/content/field', $this->text('Field has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Unable to delete this field. The most probable reason - it is used by one or more products'), 'danger');
    }

    /**
     * Applies an action to the selected fields
     * @return boolean
     */
    protected function action()
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
            $this->session->setMessage($this->text('Fields have been deleted'), 'success');
            return true;
        }

        return false;
    }

    /**
     * Saves a submitted field values
     * @param array $field
     * @return null
     */
    protected function submit(array $field)
    {

        $this->setSubmitted('field');
        $this->validate($field);

        if ($this->hasErrors('field')) {
            return;
        }

        if (isset($field['field_id'])) {
            $this->controlAccess('field_edit');
            $this->field->update($field['field_id'], $this->getSubmitted());
            $this->redirect('admin/content/field', $this->text('Field has been updated'), 'success');
        }

        $this->controlAccess('field_add');
        $this->field->add($this->getSubmitted());
        $this->redirect('admin/content/field', $this->text('Field has been added'), 'success');
    }

    /**
     * Performs validation checks on the given field
     * @param array $field
     */
    protected function validate(array $field)
    {
        $this->addValidator('title', array('length' => array('min' => 1, 'max' => 255)));
        $this->addValidator('translation', array());
        $this->setValidators($field);
    }

}
