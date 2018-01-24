<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\controllers\backend\Controller as BackendController;
use gplcart\core\models\Field as FieldModel;
use gplcart\core\models\FieldValue as FieldValueModel;
use gplcart\core\models\File as FileModel;
use gplcart\core\models\TranslationEntity as TranslationEntityModel;

/**
 * Handles incoming requests and outputs data related to field values
 */
class FieldValue extends BackendController
{

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * Field model instance
     * @var \gplcart\core\models\Field $field
     */
    protected $field;

    /**
     * Field value model instance
     * @var \gplcart\core\models\FieldValue $field_value
     */
    protected $field_value;

    /**
     * Entity translation model instance
     * @var \gplcart\core\models\TranslationEntity $translation_entity
     */
    protected $translation_entity;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

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
     * @param TranslationEntityModel $translation_entity
     */
    public function __construct(FieldModel $field, FieldValueModel $field_value, FileModel $file,
                                TranslationEntityModel $translation_entity)
    {
        parent::__construct();

        $this->file = $file;
        $this->field = $field;
        $this->field_value = $field_value;
        $this->translation_entity = $translation_entity;
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
        $this->setPagerListFieldValue();

        $this->setData('field', $this->data_field);
        $this->setData('values', $this->getListFieldValue());

        $this->outputListFieldValue();
    }

    /**
     * Set filter on the field value overview page
     */
    protected function setFilterListFieldValue()
    {
        $allowed = array('title', 'color', 'weight', 'image', 'field_value_id');
        $this->setFilter($allowed);
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
        list($selected, $action) = $this->getPostedAction();

        $deleted = 0;
        foreach ($selected as $field_value_id) {
            if ($action === 'delete' && $this->access('field_value_delete')) {
                $deleted += (int) $this->field_value->delete($field_value_id);
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListFieldValue()
    {
        $conditions = $this->query_filter;
        $conditions['count'] = true;
        $conditions['field_id'] = $this->data_field['field_id'];

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->field_value->getList($conditions)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of field values for a given field
     * @return array
     */
    protected function getListFieldValue()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;
        $conditions['field_id'] = $this->data_field['field_id'];

        $values = (array) $this->field_value->getList($conditions);
        return $this->prepareFieldValues($values);
    }

    /**
     * Prepare an array of field values
     * @param array $values
     * @return array
     */
    protected function prepareFieldValues(array $values)
    {
        foreach ($values as &$value) {
            $this->setItemThumb($value, $this->image, array('placeholder' => false));
        }

        return $values;
    }

    /**
     * Sets title on the field values overview page
     */
    protected function setTitleListFieldValue()
    {
        $text = $this->text('Values of %name', array('%name' => $this->data_field['title']));
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
        $this->setData('languages', $this->language->getList(array('in_database' => true)));

        $this->submitEditFieldValue();
        $this->setDataImagesEditFieldValue();

        $this->outputEditFieldValue();
    }

    /**
     * Adds images on the edit field value page
     */
    protected function setDataImagesEditFieldValue()
    {
        $options = array(
            'single' => true,
            'entity' => 'field_value',
            'images' => $this->getData('field_value.images', array())
        );

        $this->setItemThumb($options, $this->image);
        $this->setData('attached_images', $this->getWidgetImages($this->language, $options));
    }

    /**
     * Handles a submitted field value data
     */
    protected function submitEditFieldValue()
    {
        if ($this->isPosted('delete')) {
            $this->deleteFieldValue();
        } else if ($this->isPosted('save') && $this->validateEditFieldValue()) {
            $this->deleteImageFieldValue();
            if (isset($this->data_field_value['field_value_id'])) {
                $this->updateFieldValue();
            } else {
                $this->addFieldValue();
            }
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
            $field_value = $this->field_value->get($field_value_id);
            if (empty($field_value)) {
                $this->outputHttpStatus(404);
            }

            $this->data_field_value = $this->prepareFieldValue($field_value);
        }
    }

    /**
     * Prepare an array of field value data
     * @param array $field_value
     * @return array
     */
    protected function prepareFieldValue(array $field_value)
    {
        $this->setItemImages($field_value, 'field_value', $this->image);
        $this->setItemTranslation($field_value, 'field_value', $this->translation_entity);

        if (!empty($field_value['images'])) {
            foreach ($field_value['images'] as &$file) {
                $this->setItemTranslation($file, 'file', $this->translation_entity);
            }
        }

        return $field_value;
    }

    /**
     * Deletes a field value
     */
    protected function deleteFieldValue()
    {
        $this->controlAccess('field_value_delete');

        if ($this->field_value->delete($this->data_field_value['field_value_id'])) {
            $url = "admin/content/field/value/{$this->data_field['field_id']}";
            $this->redirect($url, $this->text('Field value has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Field value has not been deleted'), 'warning');
    }

    /**
     * Deletes a field value image
     */
    protected function deleteImageFieldValue()
    {
        $this->controlAccess('field_value_edit');

        $file_ids = $this->getPosted('delete_images', array(), true, 'array');

        if (!empty($file_ids) && isset($this->data_field_value['field_value_id'])) {
            $file_id = reset($file_ids);
            $this->file->deleteAll($file_id, false);
            $this->field_value->update($this->data_field_value['field_value_id'], array('file_id' => 0));
        }
    }

    /**
     * Updates a field value
     */
    protected function updateFieldValue()
    {
        $this->controlAccess('field_value_edit');

        if ($this->field_value->update($this->data_field_value['field_value_id'], $this->getSubmitted())) {
            $url = "admin/content/field/value/{$this->data_field['field_id']}";
            $this->redirect($url, $this->text('Field value has been updated'), 'success');
        }

        $this->redirect('', $this->text('Field value has not been updated'), 'warning');
    }

    /**
     * Adds a new field value
     */
    protected function addFieldValue()
    {
        $this->controlAccess('field_value_add');

        if ($this->field_value->add($this->getSubmitted())) {
            $url = "admin/content/field/value/{$this->data_field['field_id']}";
            $this->redirect($url, $this->text('Field value has been added'), 'success');
        }

        $this->redirect('', $this->text('Field value has not been added'), 'warning');
    }

    /**
     * Sets titles on the edit field value page
     */
    protected function setTitleEditFieldValue()
    {
        if (isset($this->data_field_value['field_value_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_field_value['title']));
        } else {
            $title = $this->text('Add value for field %name', array('%name' => $this->data_field['title']));
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
