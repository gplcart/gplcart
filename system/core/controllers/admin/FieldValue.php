<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\File as ModelsFile;
use core\models\Field as ModelsField;
use core\models\Image as ModelsImage;
use core\models\FieldValue as ModelsFieldValue;

/**
 * Handles incoming requests and outputs data related to field values
 */
class FieldValue extends Controller
{

    /**
     * Field model instance
     * @var \core\models\Field $field
     */
    protected $field;

    /**
     * FieldValue module instance
     * @var \core\models\FieldValue $value
     */
    protected $value;

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Field value model instance
     * @var \core\models\FieldValue $field_value
     */
    protected $field_value;

    /**
     * Constructor
     * @param ModelsField $field
     * @param ModelsFieldValue $field_value
     * @param ModelsImage $image
     * @param ModelsFile $file
     */
    public function __construct(ModelsField $field,
            ModelsFieldValue $field_value, ModelsImage $image, ModelsFile $file)
    {
        parent::__construct();

        $this->file = $file;
        $this->field = $field;
        $this->image = $image;
        $this->field_value = $field_value;
    }

    /**
     * Displays the field values overview page
     * @param integer $field_id
     */
    public function values($field_id)
    {
        $field = $this->getField($field_id);

        $query = $this->getFilterQuery();
        $total = $this->getTotalFieldValues($field_id, $query);
        $limit = $this->setPager($total, $query);
        $values = $this->getFieldValues($limit, $field_id, $query);

        $this->setData('field', $field);
        $this->setData('values', $values);

        $allowed = array('title', 'color', 'weight', 'image');
        $this->setFilter($allowed, $query);

        if ($this->isPosted('action')) {
            $this->action();
        }

        $this->setTitleValues($field);
        $this->setBreadcrumbValues();
        $this->outputValues();
    }

    /**
     * Displays the field value edit form
     * @param integer $field_id
     * @param integer|null $field_value_id
     */
    public function edit($field_id, $field_value_id = null)
    {
        $field = $this->getField($field_id);
        $field_value = $this->get($field_value_id);
        $widget_types = $this->field->widgetTypes();

        $field_value['field_id'] = $field_id;

        $this->setData('field', $field);
        $this->setData('field_value', $field_value);
        $this->setData('widget_types', $widget_types);

        if ($this->isPosted('delete')) {
            $this->delete($field_value, $field);
        }

        if ($this->isPosted('save')) {
            $this->submit($field_value, $field);
        }

        $this->setDataFieldValue();

        $this->setTitleEdit($field_value, $field);
        $this->setBreadcrumbEdit($field);
        $this->outputEdit();
    }

    /**
     * Returns total number of values for a given field and conditions
     * @param integer $field_id
     * @param array $query
     * @return array
     */
    protected function getTotalFieldValues($field_id, array $query)
    {
        $options = array(
            'count' => true,
            'field_id' => $field_id
        );

        $options += $query;
        return $this->field_value->getList($options);
    }

    /**
     * Sets titles on the field values overview page
     * @param array $field
     */
    protected function setTitleValues(array $field)
    {
        $this->setTitle($this->text('Values of %s', array(
                    '%s' => $this->truncate($field['title']))));
    }

    /**
     * Sets breadcrumbs on the field values overview page
     */
    protected function setBreadcrumbValues()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/content/field'),
            'text' => $this->text('Fields')));
    }

    /**
     * Renders the field values overview page
     */
    protected function outputValues()
    {
        $this->output('content/field/value/list');
    }

    /**
     * Returns a field
     * @param integer $field_id
     * @return array
     */
    protected function getField($field_id)
    {
        $field = $this->field->get($field_id);

        if (empty($field)) {
            $this->outputError(404);
        }

        return $field;
    }

    /**
     * Returns an array of field values for a given field
     * @param array $limit
     * @param integer $field_id
     * @param array $query
     * @return array
     */
    protected function getFieldValues(array $limit, $field_id, array $query)
    {
        $options = array(
            'limit' => $limit,
            'field_id' => $field_id
        );

        $options += $query;
        $values = $this->field_value->getList($options);
        $preset = $this->config->get('admin_image_preset', 2);

        foreach ($values as &$value) {
            if (!empty($value['path'])) {
                $value['thumb'] = $this->image->url($preset, $value['path']);
            }
        }

        return $values;
    }

    /**
     * Applies anaction to the selected field values
     * @return boolean
     */
    protected function action()
    {
        $action = (string) $this->request->post('action');
        $selected = (array) $this->request->post('selected', array());

        if ($action === 'weight' && $this->access('field_value_edit')) {
            foreach ($selected as $field_value_id => $weight) {
                $this->field_value->update($field_value_id, array('weight' => $weight));
            }

            $this->response->json(array('success' => $this->text('Field values have been reordered')));
        }

        $deleted = 0;
        foreach ($selected as $field_value_id) {
            if ($action === 'delete' && $this->access('field_value_delete')) {
                $deleted += (int) $this->field_value->delete($field_value_id);
            }
        }

        if ($deleted > 0) {
            $this->session->setMessage($this->text('Deleted %num field values', array('%num' => $deleted)), 'success');
            return true;
        }

        return false;
    }

    /**
     * Renders the edit field value page
     */
    protected function outputEdit()
    {
        $this->output('content/field/value/edit');
    }

    /**
     * Sets titles on the edit field value page
     * @param array $field_value
     * @param array $field
     */
    protected function setTitleEdit(array $field_value, array $field)
    {
        if (isset($field_value['field_value_id'])) {
            $title = $this->text('Edit field value %name', array(
                '%name' => $this->truncate($field_value['title'])));
        } else {
            $title = $this->text('Add value for field %name', array(
                '%name' => $this->truncate($field['title'])));
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit field value page
     * @param array $field
     */
    protected function setBreadcrumbEdit(array $field)
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/content/field'),
            'text' => $this->text('Fields')));

        $this->setBreadcrumb(array(
            'url' => $this->url("admin/content/field/value/{$field['field_id']}"),
            'text' => $this->text('Values of %s', array('%s' => $field['title']))));
    }

    /**
     * Returns a field value
     * @param integer $field_value_id
     * @return array
     */
    protected function get($field_value_id)
    {
        if (!is_numeric($field_value_id)) {
            return array();
        }

        $field_value = $this->field_value->get($field_value_id);

        if (empty($field_value)) {
            $this->outputError(404);
        }

        return $field_value;
    }

    /**
     * Deletes a field value
     * @param array $field_value
     * @param array $field
     */
    protected function delete(array $field_value, array $field)
    {
        $this->controlAccess('field_value_delete');
        
        $deleted = $this->field_value->delete($field_value['field_value_id']);

        if ($deleted) {
            $this->redirect("admin/content/field/value/{$field['field_id']}", $this->text('Field value %name has been deleted', array(
                        '%name' => $field_value['title'])), 'success');
        }

        $this->redirect('', $this->text('Failed to delete field value %name. The most probable reason - it is used somewhere', array(
                    '%name' => $field_value['title'])), 'warning');
    }

    /**
     * Deletes a saved field value image
     * @param array $field_value
     * @param array $field
     * @return boolean
     */
    protected function deleteImage(array $field_value, array $field)
    {
        $this->field_value->update($field_value['field_value_id'], array('file_id' => 0));

        $file = $this->file->get($field_value['file_id']);
        $this->file->delete($field_value['file_id']);

        return $this->file->deleteFromDisk($file);
    }

    /**
     * Saves a field value
     * @param array $field_value
     * @param array $field
     * @return null
     */
    protected function submit(array $field_value, array $field)
    {
        $this->setSubmitted('field_value');

        $this->validate($field_value, $field);

        if ($this->hasErrors('field_value')) {
            return;
        }

        if (isset($field_value['field_value_id'])) {
            $this->controlAccess('field_value_edit');
            $this->field_value->update($field_value['field_value_id'], $this->getSubmitted());
            $this->redirect("admin/content/field/value/{$field['field_id']}", $this->text('Field value %name has been updated', array('%name' => $field_value['title'])), 'success');
        }

        $this->controlAccess('field_value_add');
        $this->field_value->add($this->getSubmitted());
        $this->redirect("admin/content/field/value/{$field['field_id']}", $this->text('Field value has been added'), 'success');
    }

    /**
     * Performs validation checks on the given field value
     * @param array $field_value
     * @param array $field
     */
    protected function validate(array $field_value, array $field)
    {
        $this->setSubmitted('field_id', $field['field_id']);

        $this->addValidator('title', array('length' => array('min' => 1, 'max' => 255)));
        $this->addValidator('weight', array('numeric' => array(), 'length' => array('max' => 2)));
        $this->addValidator('translation', array('translation' => array()));

        $this->addValidator('color', array(
            'regexp' => array(
                'pattern' => '/#([a-fA-F0-9]{3}){1,2}\b/',
                'required' => ($field['widget'] === 'color')
        )));

        $this->addValidator('file', array(
            'upload' => array(
                'control_errors' => true,
                'path' => 'image/upload/field_value',
                'file' => $this->request->file('file')
        )));

        $errors = $this->setValidators($field_value);

        if (empty($errors)) {

            if ($this->isPosted('delete_image')) {
                $this->deleteImage($field_value, $field);
            }

            $uploaded = $this->getValidatorResult('file');
            $this->setSubmitted('path', $uploaded);
        }
    }

    /**
     * Modifies the field values array
     */
    protected function setDataFieldValue()
    {
        $path = $this->getData('field_value.path');

        if (!empty($path)) {
            $preset = $this->config->get('admin_image_preset', 2);
            $thumb = $this->image->url($preset, $path);
            $this->setData('field_value.thumb', $thumb);
        }
    }

}
