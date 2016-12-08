<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\backend;

use core\models\File as FileModel;
use core\models\Image as ImageModel;
use core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to images
 */
class ImageStyle extends BackendController
{

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
     * Constructor
     * @param ImageModel $image
     * @param FileModel $file
     */
    public function __construct(ImageModel $image, FileModel $file)
    {
        parent::__construct();

        $this->file = $file;
        $this->image = $image;
    }

    /**
     * Displays the image styles admin overview page
     */
    public function listImageStyle()
    {
        $this->clearCacheImageStyle();

        $imagestyles = $this->image->getStyleList();
        $this->setData('styles', $imagestyles);

        $this->setTitleListImageStyle();
        $this->setBreadcrumbListImageStyle();
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
        $imagestyle = $this->getImageStyle($style_id);
        $this->setData('imagestyle', $imagestyle);

        $this->submitImageStyle($imagestyle);

        $this->setDataEditImageStyle();
        $this->setTitleEditImageStyle($imagestyle);
        $this->setBreadcrumbEditImageStyle();
        $this->outputEditImageStyle();
    }

    /**
     * Returns an image style
     * @param integer $style_id
     * @return array
     */
    protected function getImageStyle($style_id)
    {
        if (!is_numeric($style_id)) {
            return array('actions' => array());
        }

        $imagestyle = $this->image->getStyle($style_id);

        if (empty($imagestyle)) {
            $this->outputError(404);
        }

        return $imagestyle;
    }

    /**
     * Saves an image style
     * @param array $imagestyle
     * @return bool|null|void
     */
    protected function submitImageStyle(array $imagestyle)
    {
        if ($this->isPosted('delete') && isset($imagestyle['imagestyle_id'])) {
            return $this->deleteImageStyle($imagestyle);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('imagestyle');
        $this->validateImageStyle($imagestyle);

        if ($this->hasErrors('imagestyle')) {
            return null;
        }

        if (isset($imagestyle['imagestyle_id'])) {
            return $this->updateImageStyle($imagestyle);
        }

        return $this->addImageStyle();
    }

    /**
     * Deletes an image style
     * @param array $imagestyle
     * @return void
     */
    protected function deleteImageStyle(array $imagestyle)
    {
        $this->controlAccess('image_style_delete');

        $this->image->deleteStyle($imagestyle['imagestyle_id']);
        $this->image->clearCache($imagestyle['imagestyle_id']);

        if (empty($imagestyle['default'])) {
            $message = $this->text('Image style has been deleted');
        } else {
            $message = $this->text('Image style has been reverted to default settings');
        }

        $this->redirect('admin/settings/imagestyle', $message, 'success');
    }

    /**
     * Validates an image style
     * @param array $imagestyle
     */
    protected function validateImageStyle(array $imagestyle)
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $imagestyle);
        $this->setSubmittedArray('actions');
        $this->validate('image_style');
    }

    /**
     * Updates an image styles using an array of submitted values
     * @param array $imagestyle
     */
    protected function updateImageStyle(array $imagestyle)
    {
        $this->controlAccess('image_style_edit');

        $submitted = $this->getSubmitted();
        $this->image->updateStyle($imagestyle['imagestyle_id'], $submitted);
        $this->image->clearCache($imagestyle['imagestyle_id']);

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

        if (!is_array($actions)) {
            return null;
        }

        gplcart_array_sort($actions);

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
        return null;
    }

    /**
     * Sets titles on the edit image style page
     * @param array $imagestyle
     */
    protected function setTitleEditImageStyle(array $imagestyle)
    {
        if (isset($imagestyle['imagestyle_id'])) {
            $title = $this->text('Edit image style %name', array(
                '%name' => $imagestyle['name']
            ));
        } else {
            $title = $this->text('Add image style');
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
