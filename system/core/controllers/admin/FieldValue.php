<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\controllers\admin\Controller as BackendController;
use core\models\Field as ModelsField;
use core\models\FieldValue as ModelsFieldValue;
use core\models\File as ModelsFile;
use core\models\Image as ModelsImage;

/**
 * Handles incoming requests and outputs data related to field values
 */
class FieldValue extends BackendController
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
    public function __construct(
        ModelsField $field,
        ModelsFieldValue $field_value,
        ModelsImage $image,
        ModelsFile $file
    ) {
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
    public function listFieldValue($field_id)
    {
        $field = $this->getField($field_id);

        $this->actionFieldValue();

        $query = $this->getFilterQuery();
        $total = $this->getTotalFieldValue($field_id, $query);
        $limit = $this->setPager($total, $query);
        $values = $this->getListFieldValue($limit, $field_id, $query);

        $this->setData('field', $field);
        $this->setData('values', $values);

        $allowed = array('title', 'color', 'weight', 'image');
        $this->setFilter($allowed, $query);

        $this->setTitleListFieldValue($field);
        $this->setBreadcrumbListFieldValue();
        $this->outputListFieldValue();
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
     * Applies an action to the selected field values
     */
    protected function actionFieldValue()
    {
        $action = (string)$this->request->post('action');

        if (empty($action)) {
            return;
        }

        $selected = (array)$this->request->post('selected', array());

        if ($action === 'weight' && $this->access('field_value_edit')) {

            foreach ($selected as $field_value_id => $weight) {
                $this->field_value->update($field_value_id, array('weight' => $weight));
            }

            $this->response->json(array(
                'success' => $this->text('Field values have been reordered')
            ));
        }

        $deleted = 0;
        foreach ($selected as $field_value_id) {
            if ($action === 'delete' && $this->access('field_value_delete')) {
                $deleted += (int)$this->field_value->delete($field_value_id);
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num field values', array(
                '%num' => $deleted
            ));
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Returns total number of values for a given field and conditions
     * @param integer $field_id
     * @param array $query
     * @return array
     */
    protected function getTotalFieldValue($field_id, array $query)
    {
        $options = array(
            'count' => true,
            'field_id' => $field_id
        );

        $options += $query;
        return $this->field_value->getList($options);
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
        $options = array(
            'limit' => $limit,
            'field_id' => $field_id
        );

        $options += $query;
        $values = $this->field_value->getList($options);
        $preset = $this->config('admin_image_preset', 2);

        foreach ($values as &$value) {
            if (!empty($value['path'])) {
                $value['thumb'] = $this->image->url($preset, $value['path']);
            }
        }

        return $values;
    }

    /**
     * Sets titles on the field values overview page
     * @param array $field
     */
    protected function setTitleListFieldValue(array $field)
    {
        $text = $this->text('Values of %field', array(
            '%field' => $this->truncate($field['title'])
        ));

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
        $field = $this->getField($field_id);
        $field_value = $this->getFieldValue($field_value_id);
        $widget_types = $this->field->widgetTypes();

        $this->setData('field', $field);
        $this->setData('field_value', $field_value);
        $this->setData('widget_types', $widget_types);

        $this->submitFieldValue($field_value, $field);

        $this->setDataEditFieldValue();

        $this->setTitleEditFieldValue($field_value, $field);
        $this->setBreadcrumbEditFieldValue($field);
        $this->outputEditFieldValue();
    }

    /**
     * Returns a field value
     * @param integer $field_value_id
     * @return array
     */
    protected function getFieldValue($field_value_id)
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
     * Saves a field value
     * @param array $field_value
     * @param array $field
     * @return null|void
     */
    protected function submitFieldValue(array $field_value, array $field)
    {
        if ($this->isPosted('delete')) {
            return $this->deleteFieldValue($field_value, $field);
        }

        if ($this->isPosted('delete_image')) {
            return $this->deleteImageFieldValue($field_value);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('field_value');

        $this->validateFieldValue($field_value, $field);

        if ($this->hasErrors('field_value')) {
            return null;
        }

        if (isset($field_value['field_value_id'])) {
            return $this->updateFieldValue($field_value, $field);
        }

        return $this->addFieldValue($field);
    }

    /**
     * Deletes a field value
     * @param array $field_value
     * @param array $field
     */
    protected function deleteFieldValue(array $field_value, array $field)
    {
        $this->controlAccess('field_value_delete');

        $deleted = $this->field_value->delete($field_value['field_value_id']);

        if ($deleted) {
            $url = "admin/content/field/value/{$field['field_id']}";
            $message = $this->text('Field value %name has been deleted', array(
                '%name' => $field_value['title']
            ));

            $this->redirect($url, $message, 'success');
        }

        $message = $this->text('Failed to delete field value %name.'
            . ' The most probable reason - it is used somewhere', array(
            '%name' => $field_value['title']
        ));

        $this->redirect('', $message, 'warning');
    }

    /**
     * Deletes a saved field value image
     * @param array $field_value
     * @return boolean
     */
    protected function deleteImageFieldValue(array $field_value)
    {
        $this->controlAccess('field_value_edit');

        $this->field_value->update($field_value['field_value_id'], array('file_id' => 0));
        $file = $this->file->get($field_value['file_id']);
        $this->file->delete($field_value['file_id']);

        return $this->file->deleteFromDisk($file);
    }

    /**
     * Performs validation checks on the given field value
     * @param array $field_value
     * @param array $field
     */
    protected function validateFieldValue(array $field_value, array $field)
    {
        $this->setSubmitted('field_id', $field['field_id']);

        $this->addValidator('title', array(
            'length' => array('min' => 1, 'max' => 255)
        ));

        $this->addValidator('weight', array(
            'numeric' => array(),
            'length' => array('max' => 2)
        ));

        $this->addValidator('translation', array(
            'translation' => array()
        ));

        $this->addValidator('color', array(
            'regexp' => array(
                'pattern' => '/#([a-fA-F0-9]{3}){1,2}\b/',
                'required' => ($field['widget'] === 'color')
            )
        ));

        $this->addValidator('file', array(
            'upload' => array(
                'control_errors' => true,
                'path' => 'image/upload/field_value',
                'file' => $this->request->file('file')
            )
        ));

        $errors = $this->setValidators($field_value);

        if (empty($errors)) {
            $uploaded = $this->getValidatorResult('file');
            $this->setSubmitted('path', $uploaded);
        }
    }

    /**
     * Updates a field value with submitted data
     * @param array $field_value
     * @param array $field
     */
    protected function updateFieldValue(array $field_value, array $field)
    {
        $this->controlAccess('field_value_edit');

        $submitted = $this->getSubmitted();
        $this->field_value->update($field_value['field_value_id'], $submitted);

        $url = "admin/content/field/value/{$field['field_id']}";
        $message = $this->text('Field value %name has been updated', array(
            '%name' => $field_value['title']
        ));

        $this->redirect($url, $message, 'success');
    }

    /**
     * Adds a new field value using a submitted data
     * @param array $field
     */
    protected function addFieldValue(array $field)
    {
        $this->controlAccess('field_value_add');

        $submitted = $this->getSubmitted();
        $this->field_value->add($submitted);

        $url = "admin/content/field/value/{$field['field_id']}";
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
            $preset = $this->config('admin_image_preset', 2);
            $thumb = $this->image->url($preset, $path);
            $this->setData('field_value.thumb', $thumb);
        }
    }

    /**
     * Sets titles on the edit field value page
     * @param array $field_value
     * @param array $field
     */
    protected function setTitleEditFieldValue(array $field_value, array $field)
    {
        if (isset($field_value['field_value_id'])) {
            $title = $this->text('Edit field value %name', array(
                '%name' => $this->truncate($field_value['title'])
            ));
        } else {
            $title = $this->text('Add value for field %name', array(
                '%name' => $this->truncate($field['title'])
            ));
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit field value page
     * @param array $field
     */
    protected function setBreadcrumbEditFieldValue(array $field)
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
            'url' => $this->url("admin/content/field/value/{$field['field_id']}"),
            'text' => $this->text('Values of %s', array('%s' => $field['title']))
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
