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
        $limit = $this->setPager($this->getTotalFieldValues($field_id, $query), $query);

        $this->data['field'] = $field;
        $this->data['values'] = $this->getFieldValues($limit, $field_id, $query);

        $this->setFilter(array('title', 'color', 'weight', 'image'), $query);

        $action = $this->request->post('action');
        $selected = $this->request->post('selected', array());

        if (!empty($action)) {
            $this->action($selected, $action);
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
        $field_value['field_id'] = $field_id;

        $this->data['field'] = $field;
        $this->data['field_value'] = $field_value;
        $this->data['widget_types'] = $this->field->widgetTypes();

        if ($this->request->post('delete')) {
            $this->delete($field_value, $field);
        }

        if ($this->request->post('save')) {
            $this->submit($field_value, $field);
        }

        $this->prepareFieldValue();

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
        return $this->field_value->getList(array('count' => true, 'field_id' => $field_id) + $query);
    }

    /**
     * Sets titles on the field values overview page
     * @param array $field
     */
    protected function setTitleValues(array $field)
    {
        $this->setTitle($this->text('Values of %s', array('%s' => $field['title'])));
    }

    /**
     * Sets breadcrumbs on the field values overview page
     */
    protected function setBreadcrumbValues()
    {
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->setBreadcrumb(array('url' => $this->url('admin/content/field'), 'text' => $this->text('Fields')));
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
        $values = $this->field_value->getList(array('limit' => $limit, 'field_id' => $field_id) + $query);

        foreach ($values as &$value) {
            if (!empty($value['path'])) {
                $value['thumb'] = $this->image->url($this->config->get('admin_image_preset', 2), $value['path']);
            }
        }

        return $values;
    }

    /**
     * Applies anaction to the selected field values
     * @param array $selected
     * @param string $action
     * @return boolean
     */
    protected function action(array $selected, $action)
    {
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
            $title = $this->text('Edit field value %name', array('%name' => $field_value['title']));
        } else {
            $title = $this->text('Add value for field %name', array('%name' => $field['title']));
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

        if ($this->field_value->delete($field_value['field_value_id'])) {
            $this->redirect("admin/content/field/value/{$field['field_id']}", $this->text('Field value %name has been deleted', array(
                        '%name' => $field_value['title'])), 'success');
        }

        $this->redirect('', $this->text('Failed to delete field value %name. The most probable reason - it is used somewhere', array(
                    '%name' => $field_value['title'])), 'warning');
    }

    /**
     * Saves a field value
     * @param array $field_value
     * @param array $field
     * @return null
     */
    protected function submit(array $field_value, array $field)
    {
        $this->submitted = $this->request->post('field_value', array());
        $this->submitted += $field_value;

        $this->validate($field);

        $errors = $this->formErrors();

        if (!empty($errors)) {
            $this->data['field_value'] = $this->submitted;
            return;
        }

        if (isset($field_value['field_value_id'])) {
            $this->controlAccess('field_value_edit');
            $this->field_value->update($field_value['field_value_id'], $this->submitted);
            $this->redirect("admin/content/field/value/{$field['field_id']}", $this->text('Field value %name has been updated', array('%name' => $field_value['title'])), 'success');
        }

        $this->controlAccess('field_value_add');
        $this->field_value->add($this->submitted);
        $this->redirect("admin/content/field/value/{$field['field_id']}", $this->text('Field value has been added'), 'success');
    }

    /**
     * Performs validation checks on the given field value
     * @param array $field
     */
    protected function validate(array $field)
    {
        $this->validateWeight($field);
        $this->validateColor($field);
        $this->validateTitle($field);
        $this->validateTranslation($field);
        $this->validateFile($field);
    }

    /**
     * Validates weight field
     * @return boolean
     */
    protected function validateWeight()
    {
        if ($this->submitted['weight']) {
            if (!is_numeric($this->submitted['weight']) || strlen($this->submitted['weight']) > 2) {
                $this->data['form_errors']['weight'] = $this->text('Only numeric value and no more than %s digits', array('%s' => 2));
                return false;
            }
            return true;
        }

        $this->submitted['weight'] = 0;
        return true;
    }

    /**
     * Validates color field
     * @param array $field
     * @return boolean
     */
    protected function validateColor(array $field)
    {
        if ($field['widget'] == 'color' && !preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $this->submitted['color'])) {
            $this->data['form_errors']['color'] = $this->text('Required field');
            return false;
        }

        return true;
    }

    /**
     * Validates title field
     * @param array $field
     * @return boolean
     */
    protected function validateTitle(array $field)
    {
        if (empty($this->submitted['title']) || mb_strlen($this->submitted['title']) > 255) {
            $this->data['form_errors']['title'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates field value translations
     * @param array $field
     * @return boolean
     */
    protected function validateTranslation(array $field)
    {
        if (empty($this->submitted['translation'])) {
            return true;
        }

        $has_errors = false;

        foreach ($this->submitted['translation'] as $code => $translation) {
            if (mb_strlen($translation['title']) > 255) {
                $this->data['form_errors']['translation'][$code]['title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $has_errors = true;
            }
        }

        return !$has_errors;
    }

    /**
     * Validates field value image
     * @param array $field
     * @return boolean
     */
    protected function validateFile(array $field)
    {
        $file = $this->request->file('file');

        if (empty($file)) {
            if ($field['widget'] == 'image') {
                $this->data['form_errors']['image'] = $this->text('Please upload an image');
                return false;
            }
            return true;
        }

        $this->file->setUploadPath('image/upload/field_value');

        if ($this->file->upload($file) !== true) {
            $this->data['form_errors']['image'] = $this->text('Unable to upload the file');
            return false;
        }

        $this->submitted['path'] = $this->file->path($this->file->getUploadedFile());
        return true;
    }

    /**
     * Modifies the field values array
     */
    protected function prepareFieldValue()
    {
        if (!empty($this->data['field_value']['path'])) {
            $this->data['field_value']['thumb'] = $this->image->url($this->config->get('admin_image_preset', 2), $this->data['field_value']['path']);
        }
    }
}
