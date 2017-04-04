<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\File as FileModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to images
 */
class ImageStyle extends BackendController
{

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * The current imagestyle
     * @var array
     */
    protected $data_imagestyle = array();

    /**
     * Constructor
     * @param FileModel $file
     */
    public function __construct(FileModel $file)
    {
        parent::__construct();

        $this->file = $file;
    }

    /**
     * Displays the image styles admin overview page
     */
    public function listImageStyle()
    {
        $this->clearCacheImageStyle();

        $this->setTitleListImageStyle();
        $this->setBreadcrumbListImageStyle();

        $this->setData('styles', $this->image->getStyleList());

        $this->outputListImageStyle();
    }

    /**
     * Clears cached images for an image style set in the URL query
     */
    protected function clearCacheImageStyle()
    {
        $style_id = (string) $this->request->get('clear');

        if (!empty($style_id) && $this->image->clearCache($style_id)) {
            $this->redirect('', $this->text('Cache has been cleared'), 'success');
        }
    }

    /**
     * Sets titles on the image style overview page
     */
    protected function setTitleListImageStyle()
    {
        $this->setTitle($this->text('Image styles'));
    }

    /**
     * Sets breadcrumbs on the image style overview page
     */
    protected function setBreadcrumbListImageStyle()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the image styles page
     */
    protected function outputListImageStyle()
    {
        $this->output('settings/image/list');
    }

    /**
     * Displays the image style edit form
     * @param integer|null $style_id
     */
    public function editImageStyle($style_id = null)
    {
        $this->setImageStyle($style_id);

        $this->setTitleEditImageStyle();
        $this->setBreadcrumbEditImageStyle();

        $this->setData('imagestyle', $this->data_imagestyle);

        $this->submitImageStyle();
        $this->setDataEditImageStyle();
        $this->outputEditImageStyle();
    }

    /**
     * Returns an image style
     * @param integer $style_id
     * @return array
     */
    protected function setImageStyle($style_id)
    {
        if (!is_numeric($style_id)) {
            $this->data_imagestyle = array('actions' => array());
            return $this->data_imagestyle;
        }

        $imagestyle = $this->image->getStyle($style_id);

        if (empty($imagestyle)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_imagestyle = $imagestyle;
    }

    /**
     * Saves an image style
     * @return null
     */
    protected function submitImageStyle()
    {
        if ($this->isPosted('delete')) {
            $this->deleteImageStyle();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateImageStyle()) {
            return null;
        }

        if (isset($this->data_imagestyle['imagestyle_id'])) {
            $this->updateImageStyle();
        } else {
            $this->addImageStyle();
        }
    }

    /**
     * Deletes an image style
     */
    protected function deleteImageStyle()
    {
        $this->controlAccess('image_style_delete');

        $this->image->deleteStyle($this->data_imagestyle['imagestyle_id']);
        $this->image->clearCache($this->data_imagestyle['imagestyle_id']);

        $message = $this->text('Image style has been reverted to default settings');

        if (empty($this->data_imagestyle['default'])) {
            $message = $this->text('Image style has been deleted');
        }

        $this->redirect('admin/settings/imagestyle', $message, 'success');
    }

    /**
     * Validates an image style
     * @return bool
     */
    protected function validateImageStyle()
    {
        $this->setSubmitted('imagestyle');

        $this->setSubmittedBool('status');
        $this->setSubmittedArray('actions');
        $this->setSubmitted('update', $this->data_imagestyle);

        $this->validateComponent('image_style');

        return !$this->hasErrors('imagestyle');
    }

    /**
     * Updates an image styles using an array of submitted values
     */
    protected function updateImageStyle()
    {
        $this->controlAccess('image_style_edit');

        $submitted = $this->getSubmitted();
        $this->image->updateStyle($this->data_imagestyle['imagestyle_id'], $submitted);
        $this->image->clearCache($this->data_imagestyle['imagestyle_id']);

        $message = $this->text('Image style has been updated');
        $this->redirect('admin/settings/imagestyle', $message, 'success');
    }

    /**
     * Adds a new image style using an array of submitted values
     */
    protected function addImageStyle()
    {
        $this->controlAccess('image_style_add');

        $submitted = $this->getSubmitted();
        $this->image->addStyle($submitted);

        $message = $this->text('Image style has been added');
        $this->redirect('admin/settings/imagestyle', $message, 'success');
    }

    /**
     * Modifies image style actions
     * @return null
     */
    protected function setDataEditImageStyle()
    {
        $actions = $this->getData('imagestyle.actions');

        if (!$this->isError()) {
            // Do not sort on errors when "weight" is not set
            gplcart_array_sort($actions);
        }

        $modified = array();
        foreach ($actions as $action_id => $info) {

            if (is_string($info)) {
                $modified[] = $info;
                continue;
            }

            $action = $action_id;

            if (!empty($info['value'])) {
                $action .= ' ' . implode(',', $info['value']);
            }

            $modified[] = $action;
        }

        $this->setData('imagestyle.actions', implode("\n", $modified));
    }

    /**
     * Sets titles on the edit image style page
     */
    protected function setTitleEditImageStyle()
    {
        $title = $this->text('Add image style');

        if (isset($this->data_imagestyle['imagestyle_id'])) {
            $vars = array('%name' => $this->data_imagestyle['name']);
            $title = $this->text('Edit image style %name', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit image style page
     */
    protected function setBreadcrumbEditImageStyle()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/settings/imagestyle'),
            'text' => $this->text('Image styles')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the image style edit page
     */
    protected function outputEditImageStyle()
    {
        $this->output('settings/image/edit');
    }

}
