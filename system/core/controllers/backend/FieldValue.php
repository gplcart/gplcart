<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\File as FileModel;
use gplcart\core\models\Field as FieldModel;
use gplcart\core\models\FieldValue as FieldValueModel;
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
     * Constructor
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
     * Displays the field values overview page
     * @param integer $field_id
     */
    public function listFieldValue($field_id)
    {
        $this->setFieldFieldValue($field_id);

        $this->actionFieldValue();

        $this->setTitleListFieldValue();
        $this->setBreadcrumbListFieldValue();

        $query = $this->getFilterQuery();

        $allowed = array('title', 'color', 'weight', 'image', 'field_value_id');
        $this->setFilter($allowed, $query);

        $total = $this->getTotalFieldValue($field_id, $query);
        $limit = $this->setPager($total, $query);

        $this->setData('field', $this->data_field);
        $this->setData('values', $this->getListFieldValue($limit, $field_id, $query));

        $this->outputListFieldValue();
    }

    /**
     * Returns a field
     * @param integer $field_id
     * @return array
     */
    protected function setFieldFieldValue($field_id)
    {
        $field = $this->field->get($field_id);

        if (empty($field)) {
            $this->outputHttpStatus(404);
        }

        $this->data_field = $field;
        return $field;
    }

    /**
     * Applies an action to the selected field values
     */
    protected function actionFieldValue()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $selected = (array) $this->request->post('selected', array());

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
            'success' => $this->text('Field values have been reordered'));

        $this->response->json($response);
    }

    /**
     * Returns total number of values for a given field and conditions
     * @param integer $field_id
     * @param array $query
     * @return integer
     */
    protected function getTotalFieldValue($field_id, array $query)
    {
        $options = array('count' => true, 'field_id' => $field_id);
        $options += $query;

        return (int) $this->field_value->getList($options);
    }

    /**
     * Returns an array of field values for a given field
     * @param array $limit
     * @param integer $field_id
     * @param array $query
     * @return array
     */
    protected function getListFieldValue(array $limit, $field_id, array $query)
    {
        $options = array('limit' => $limit, 'field_id' => $field_id);
        $options += $query;

        $values = (array) $this->field_value->getList($options);
        $imagestyle = $this->config('image_style_admin', 2);

        foreach ($values as &$value) {
            if (!empty($value['path'])) {
                $value['thumb'] = $this->image->url($imagestyle, $value['path']);
            }
        }

        return $values;
    }

    /**
     * Sets titles on the field values overview page
     */
    protected function setTitleListFieldValue()
    {
        $vars = array('%field' => $this->truncate($this->data_field['title']));
        $text = $this->text('Values of %field', $vars);
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

        $this->submitFieldValue();
        $this->setDataEditFieldValue();

        $this->outputEditFieldValue();
    }

    /**
     * Saves a field value
     * @return null
     */
    protected function submitFieldValue()
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

        if (!$this->validateFieldValue()) {
            return null;
        }

        if (isset($this->data_field_value['field_value_id'])) {
            $this->updateFieldValue();
        } else {
            $this->addFieldValue();
        }
    }

    /**
     * Performs validation checks on the given field value
     * @return bool
     */
    protected function validateFieldValue()
    {
        $this->setSubmitted('field_value');

        $this->setSubmitted('update', $this->data_field_value);
        $this->setSubmitted('field_id', $this->data_field['field_id']);
        $this->validate('field_value');

        return !$this->hasErrors('field_value');
    }

    /**
     * Returns a field value
     * @param integer $field_value_id
     * @return array
     */
    protected function setFieldValue($field_value_id)
    {
        if (!is_numeric($field_value_id)) {
            return array();
        }

        $field_value = $this->field_value->get($field_value_id);

        if (empty($field_value)) {
            $this->outputHttpStatus(404);
        }

        $this->data_field_value = $field_value;
        return $field_value;
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
            $options = array('@name' => $this->data_field_value['title']);
            $message = $this->text('Field value @name has been deleted', $options);
            $this->redirect($url, $message, 'success');
        }

        $options = array('@name' => $this->data_field_value['title']);
        $message = $this->text('Failed to delete field value @name', $options);
        $this->redirect('', $message, 'warning');
    }

    /**
     * Deletes a saved field value image
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
     * Updates a field value with submitted data
     */
    protected function updateFieldValue()
    {
        $this->controlAccess('field_value_edit');

        $submitted = $this->getSubmitted();
        $this->field_value->update($this->data_field_value['field_value_id'], $submitted);

        $vars = array('@name' => $this->data_field_value['title']);
        $url = "admin/content/field/value/{$this->data_field['field_id']}";
        $message = $this->text('Field value @name has been updated', $vars);

        $this->redirect($url, $message, 'success');
    }

    /**
     * Adds a new field value using a submitted data
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
     * Modifies the field values array
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
            'text' => $this->text('Values of %s', array('%s' => $this->data_field['title']))
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the edit field value page
     */
    protected function outputEditFieldValue()
    {
        $this->output('content/field/value/edit');
    }

}
