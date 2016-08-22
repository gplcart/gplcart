<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\classes\Tool;
use core\models\File as ModelsFile;
use core\models\Image as ModelsImage;

/**
 * Handles incoming requests and outputs data related to images
 */
class ImageStyle extends Controller
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
     * @param ModelsImage $image
     * @param ModelsFile $file
     */
    public function __construct(ModelsImage $image, ModelsFile $file)
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
        if ($this->isQuery('clear')) {
            $this->clearCacheImageStyle();
        }

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
     * Displays the image style edit form
     * @param integer|null $style_id
     */
    public function editImageStyle($style_id = null)
    {
        $imagestyle = $this->getImageStyle($style_id);

        $this->setData('imagestyle', $imagestyle);

        if ($this->isPosted('delete')) {
            $this->deleteImageStyle($imagestyle);
        }

        if ($this->isPosted('save')) {
            $this->submitImageStyle($imagestyle);
        }

        $this->setDataEditImageStyle();

        $this->setTitleEditImageStyle($imagestyle);
        $this->setBreadcrumbEditImageStyle();
        $this->outputEditImageStyle();
    }

    /**
     * Renders the image styles page
     */
    protected function outputListImageStyle()
    {
        $this->output('settings/image/list');
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
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));
    }

    /**
     * Renders the image style edit page
     */
    protected function outputEditImageStyle()
    {
        $this->output('settings/image/edit');
    }

    /**
     * Sets titles on the edit imagestyle page
     * @param array $imagestyle
     */
    protected function setTitleEditImageStyle(array $imagestyle)
    {
        if (isset($imagestyle['imagestyle_id'])) {
            $title = $this->text('Edit image style %name', array(
                '%name' => $imagestyle['name']));
        } else {
            $title = $this->text('Add image style');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit imagestyle page
     */
    protected function setBreadcrumbEditImageStyle()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/settings/imagestyle'),
            'text' => $this->text('Image styles')));
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
     * Deletes an imagestyle
     * @param array $imagestyle
     * @return boolean
     */
    protected function deleteImageStyle(array $imagestyle)
    {
        $this->controlAccess('image_style_delete');

        $this->image->deleteStyle($imagestyle['imagestyle_id']);
        $this->image->clearCache($imagestyle['imagestyle_id']);

        $this->redirect('admin/settings/imagestyle', $this->text('Image style has been deleted'), 'success');
    }

    /**
     * Saves an imagestyle
     * @param array $imagestyle
     * @return null
     */
    protected function submitImageStyle(array $imagestyle)
    {
        $this->setSubmitted('imagestyle');
        $this->validateImageStyle($imagestyle);

        if ($this->hasErrors('imagestyle')) {
            return;
        }

        if (isset($imagestyle['imagestyle_id'])) {
            $this->updateImageStyle($imagestyle);
        }

        $this->addImageStyle();
    }

    /**
     * Updates an imagestyles using an array of submitted values
     * @param array $imagestyle
     */
    protected function updateImageStyle(array $imagestyle)
    {
        $this->controlAccess('image_style_edit');

        $submitted = $this->getSubmitted();
        $this->image->updateStyle($imagestyle['imagestyle_id'], $submitted);
        $this->image->clearCache($imagestyle['imagestyle_id']);

        $this->redirect('admin/settings/imagestyle', $this->text('Image style has been updated'), 'success');
    }

    /**
     * Adds a new imagestyle using an array of submitted values
     */
    protected function addImageStyle()
    {
        $this->controlAccess('image_style_add');

        $submitted = $this->getSubmitted();
        $this->image->addStyle($submitted);
        $this->redirect('admin/settings/imagestyle', $this->text('Image style has been added'), 'success');
    }

    /**
     * Validates an image style
     * @param array $imagestyle
     */
    protected function validateImageStyle(array $imagestyle)
    {
        $this->addValidator('name', array(
            'length' => array('min' => 1, 'max' => 255)
        ));

        $this->addValidator('actions', array(
            'required' => array(),
            'imagestyle_actions' => array()
        ));

        $errors = $this->setValidators($imagestyle);

        if (empty($errors)) {
            $actions = $this->getValidatorResult('actions');
            $this->setSubmitted('actions', $actions);
        }
    }

    /**
     * Modifies imagestyle actions
     * @return null
     */
    protected function setDataEditImageStyle()
    {
        $actions = $this->getData('imagestyle.actions');

        if (is_array($actions)) {

            Tool::sortWeight($actions);

            $modified = array();
            foreach ($actions as $action_id => $info) {
                $action = $action_id;
                if (!empty($info['value'])) {
                    $action .= ' ' . implode(',', $info['value']);
                }
                $modified[] = $action;
            }

            $this->setData('imagestyle.actions', implode("\n", $modified));
        }
    }

}
