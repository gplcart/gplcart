<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\File as FileModel,
    gplcart\core\models\Field as FieldModel,
    gplcart\core\models\FieldValue as FieldValueModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to field values
 */
class FieldValue extends BackendController
{

    /**
     * Field model instance
     * @var \gplcart\core\models\Field $field
     */
    protected $field;

    /**
     * FieldValue module instance
     * @var \gplcart\core\models\FieldValue $value
     */
    protected $value;

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * Field value model instance
     * @var \gplcart\core\models\FieldValue $field_value
     */
    protected $field_value;

    /**
     * The current field data
     * @var array
     */
    protected $data_field = array();

    /**
     * The current field value data
     * @var array
     */
    protected $data_field_value = array();

    /**
     * @param FieldModel $field
     * @param FieldValueModel $field_value
     * @param FileModel $file
     */
    public function __construct(FieldModel $field, FieldValueModel $field_value,
            FileModel $file)
    {
        parent::__construct();

        $this->file = $file;
        $this->field = $field;
        $this->field_value = $field_value;
    }

    /**
     * Displays the field value overview page
     * @param integer $field_id
     */
    public function listFieldValue($field_id)
    {
        $this->setFieldFieldValue($field_id);
        $this->actionListFieldValue();

        $this->setTitleListFieldValue();
        $this->setBreadcrumbListFieldValue();

        $this->setFilterListFieldValue();
        $this->setTotalListFieldValue();
        $this->setPagerLimit();

        $this->setData('field', $this->data_field);
        $this->setData('values', $this->getListFieldValue());

        $this->outputListFieldValue();
    }

    /**
     * Set filter on the field value overview page
     */
    protected function setFilterListFieldValue()
    {
        $this->setFilter(array('title', 'color', 'weight', 'image', 'field_value_id'));
    }

    /**
     * Set a field data
     * @param integer $field_id
     */
    protected function setFieldFieldValue($field_id)
    {
        $this->data_field = $this->field->get($field_id);

        if (empty($this->data_field)) {
            $this->outputHttpStatus(404);
        }
    }

    /**
     * Applies an action to the selected field values
     */
    protected function actionListFieldValue()
    {
        $action = (string) $this->getPosted('action');

        if (empty($action)) {
            return null;
        }

        $selected = (array) $this->getPosted('selected', array());

        if ($action === 'weight' && $this->access('field_value_edit')) {
            $this->updateWeightFieldValue($selected);
            return null;
        }

        $deleted = 0;
        foreach ($selected as $field_value_id) {
            if ($action === 'delete' && $this->access('field_value_delete')) {
                $deleted += (int) $this->field_value->delete($field_value_id);
            }
        }

        if ($deleted > 0) {
            $options = array('@num' => $deleted);
            $message = $this->text('Deleted @num field values', $options);
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Updates weight of selected field values
     * @param array $items
     */
    protected function updateWeightFieldValue(array $items)
    {
        foreach ($items as $field_value_id => $weight) {
            $this->field_value->update($field_value_id, array('weight' => $weight));
        }

        $response = array(
            'success' => $this->text('Items have been reordered'));

        $this->response->json($response);
    }

    /**
     * Sets total number of values for a given field and conditions
     */
    protected function setTotalListFieldValue()
    {
        $options = array(
            'count' => true,
            'field_id' => $this->data_field['field_id']) + $this->query_filter;

        $this->total = (int) $this->field_value->getList($options);
    }

    /**
     * Returns an array of field values for a given field
     * @return array
     */
    protected function getListFieldValue()
    {
        $options = array(
            'limit' => $this->limit,
            'field_id' => $this->data_field['field_id']) + $this->query_filter;

        $values = (array) $this->field_value->getList($options);
        return $this->prepareFieldValues($values);
    }

    /**
     * Prepare an array of field values
     * @param array $values
     * @return array
     */
    protected function prepareFieldValues(array $values)
    {
        $imagestyle = $this->config('image_style_admin', 2);
        foreach ($values as &$value) {
            if (!empty($value['path'])) {
                $value['thumb'] = $this->image->url($imagestyle, $value['path']);
            }
        }
        return $values;
    }

    /**
     * Sets title on the field values overview page
     */
    protected function setTitleListFieldValue()
    {
        $vars = array('%name' => $this->truncate($this->data_field['title']));
        $text = $this->text('Values of %name', $vars);
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the field values overview page
     */
    protected function setBreadcrumbListFieldValue()
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
     * Renders the field values overview page
     */
    protected function outputListFieldValue()
    {
        $this->output('content/field/value/list');
    }

    /**
     * Displays the field value edit form
     * @param integer $field_id
     * @param integer|null $field_value_id
     */
    public function editFieldValue($field_id, $field_value_id = null)
    {
        $this->setFieldFieldValue($field_id);
        $this->setFieldValue($field_value_id);

        $this->setTitleEditFieldValue();
        $this->setBreadcrumbEditFieldValue();

        $this->setData('field', $this->data_field);
        $this->setData('field_value', $this->data_field_value);
        $this->setData('widget_types', $this->field->getWidgetTypes());

        $this->submitEditFieldValue();
        $this->setDataEditFieldValue();

        $this->outputEditFieldValue();
    }

    /**
     * Handles a submitted field value data
     */
    protected function submitEditFieldValue()
    {
        if ($this->isPosted('delete')) {
            $this->deleteFieldValue();
            return null;
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        if ($this->isPosted('delete_image')) {
            $this->deleteImageFieldValue();
        }

        if (!$this->validateEditFieldValue()) {
            return null;
        }

        if (isset($this->data_field_value['field_value_id'])) {
            $this->updateFieldValue();
        } else {
            $this->addFieldValue();
        }
    }

    /**
     * Validates a submitted field value
     * @return bool
     */
    protected function validateEditFieldValue()
    {
        $this->setSubmitted('field_value');
        $this->setSubmitted('update', $this->data_field_value);
        $this->setSubmitted('field_id', $this->data_field['field_id']);

        $this->validateComponent('field_value');
        return !$this->hasErrors();
    }

    /**
     * Set a field value data
     * @param integer $field_value_id
     */
    protected function setFieldValue($field_value_id)
    {
        if (is_numeric($field_value_id)) {
            $this->data_field_value = $this->field_value->get($field_value_id);
            if (empty($this->data_field_value)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Deletes a field value
     */
    protected function deleteFieldValue()
    {
        $this->controlAccess('field_value_delete');

        $deleted = $this->field_value->delete($this->data_field_value['field_value_id']);

        if ($deleted) {
            $url = "admin/content/field/value/{$this->data_field['field_id']}";
            $message = $this->text('Field value has been deleted');
            $this->redirect($url, $message, 'success');
        }

        $message = $this->text('Unable to delete this field value');
        $this->redirect('', $message, 'warning');
    }

    /**
     * Deletes a field value image
     */
    protected function deleteImageFieldValue()
    {
        $this->controlAccess('field_value_edit');

        $this->field_value->update($this->data_field_value['field_value_id'], array('file_id' => 0));
        $file = $this->file->get($this->data_field_value['file_id']);

        $this->file->delete($this->data_field_value['file_id']);
        $this->file->deleteFromDisk($file);
    }

    /**
     * Updates a field value
     */
    protected function updateFieldValue()
    {
        $this->controlAccess('field_value_edit');

        $submitted = $this->getSubmitted();
        $this->field_value->update($this->data_field_value['field_value_id'], $submitted);

        $url = "admin/content/field/value/{$this->data_field['field_id']}";
        $message = $this->text('Field value has been updated');
        $this->redirect($url, $message, 'success');
    }

    /**
     * Adds a new field value
     */
    protected function addFieldValue()
    {
        $this->controlAccess('field_value_add');

        $this->field_value->add($this->getSubmitted());

        $url = "admin/content/field/value/{$this->data_field['field_id']}";
        $message = $this->text('Field value has been added');
        $this->redirect($url, $message, 'success');
    }

    /**
     * Set template data on the edit field value page
     */
    protected function setDataEditFieldValue()
    {
        $path = $this->getData('field_value.path');

        if (!empty($path)) {
            $imagestyle = $this->config('image_style_admin', 2);
            $thumb = $this->image->url($imagestyle, $path);
            $this->setData('field_value.thumb', $thumb);
        }
    }

    /**
     * Sets titles on the edit field value page
     */
    protected function setTitleEditFieldValue()
    {
        $vars = array('%name' => $this->truncate($this->data_field['title']));
        $title = $this->text('Add value for field %name', $vars);

        if (isset($this->data_field_value['field_value_id'])) {
            $vars = array('%name' => $this->truncate($this->data_field_value['title']));
            $title = $this->text('Edit field value %name', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit field value page
     */
    protected function setBreadcrumbEditFieldValue()
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

        $breadcrumbs[] = array(
            'url' => $this->url("admin/content/field/value/{$this->data_field['field_id']}"),
            'text' => $this->text('Values of %name', array('%name' => $this->data_field['title']))
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the edit field value page
     */
    protected function outputEditFieldValue()
    {
        $this->output('content/field/value/edit');
    }

}
