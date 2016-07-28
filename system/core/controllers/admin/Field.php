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
        $query = $this->getFilterQuery();
        $limit = $this->setPager($this->getTotalFields($query), $query);

        $this->data['fields'] = $this->getFields($limit, $query);
        $this->data['widget_types'] = $this->field->widgetTypes();

        $this->setFilter(array('title', 'type', 'widget'), $query);

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

        $this->data['field'] = $field;
        $this->data['widget_types'] = $this->field->widgetTypes();

        if ($this->request->post('delete')) {
            $this->delete($field);
        }

        if ($this->request->post('save')) {
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
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
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
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->setBreadcrumb(array('url' => $this->url('admin/content/field'), 'text' => $this->text('Fields')));
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
     * Saves a submitted field values
     * @param array $field
     * @return null
     */
    protected function submit(array $field)
    {
        $this->submitted = $this->request->post('field', array());
        $this->validate();

        $errors = $this->getErrors();

        if (!empty($errors)) {
            $this->data['field'] = $this->submitted + $field;
            return;
        }

        if (isset($field['field_id'])) {
            $this->controlAccess('field_edit');
            $this->field->update($field['field_id'], $this->submitted);
            $this->redirect('admin/content/field', $this->text('Field has been updated'), 'success');
        }

        $this->controlAccess('field_add');
        $this->field->add($this->submitted);
        $this->redirect('admin/content/field', $this->text('Field has been added'), 'success');
    }

    /**
     * Performs validation checks on the given field
     */
    protected function validate()
    {
        $this->validateTitle();
        $this->validateTranslation();
    }

    /**
     * Validates field title
     * @return boolean
     */
    protected function validateTitle()
    {
        if (empty($this->submitted['title']) || mb_strlen($this->submitted['title']) > 255) {
            $this->errors['title'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
            return false;
        }
        return true;
    }

    /**
     * Validates field translations
     * @return boolean
     */
    protected function validateTranslation()
    {
        if (empty($this->submitted['translation'])) {
            return true;
        }

        $has_errors = false;

        foreach ($this->submitted['translation'] as $code => $translation) {
            if (empty($translation['title'])) {
                continue;
            }

            if (mb_strlen($translation['title']) > 255) {
                $this->errors['translation'][$code]['title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $has_errors = true;
            }
        }

        return !$has_errors;
    }

}
