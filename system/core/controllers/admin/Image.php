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
class Image extends Controller
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
    public function styles()
    {

        $style_id = (string) $this->request->get('clear');
        if (!empty($style_id) && $this->image->clearCache($style_id)) {
            $this->redirect('', $this->text('Cache has been cleared'), 'success');
        }

        $imagestyles = $this->image->getImageStyleList();
        $this->setData('styles', $imagestyles);

        $this->setTitleStyles();
        $this->setBreadcrumbStyles();
        $this->outputStyles();
    }

    /**
     * Displays the image style edit form
     * @param integer|null $style_id
     */
    public function edit($style_id = null)
    {
        $imagestyle = $this->get($style_id);

        $this->setData('imagestyle', $imagestyle);

        if ($this->isPosted('delete')) {
            $this->delete($imagestyle);
        }

        if ($this->isPosted('save')) {
            $this->submit($imagestyle);
        }

        $this->setDataActions();

        $this->setTitleEdit($imagestyle);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Renders the image styles page
     */
    protected function outputStyles()
    {
        $this->output('settings/image/list');
    }

    /**
     * Sets titles on the image style overview page
     */
    protected function setTitleStyles()
    {
        $this->setTitle($this->text('Image styles'));
    }

    /**
     * Sets breadcrumbs on the image style overview page
     */
    protected function setBreadcrumbStyles()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));
    }

    /**
     * Renders the image style edit page
     */
    protected function outputEdit()
    {
        $this->output('settings/image/edit');
    }

    /**
     * Sets titles on the edit imagestyle page
     * @param array $imagestyle
     */
    protected function setTitleEdit(array $imagestyle)
    {
        if (isset($imagestyle['imagestyle_id'])) {
            $title = $this->text('Edit image style %name', array('%name' => $imagestyle['name']));
        } else {
            $title = $this->text('Add image style');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit imagestyle page
     */
    protected function setBreadcrumbEdit()
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
    protected function get($style_id)
    {
        if (!is_numeric($style_id)) {
            return array('actions' => array());
        }

        $imagestyle = $this->image->getImageStyle($style_id);

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
    protected function delete(array $imagestyle)
    {
        $this->controlAccess('image_style_delete');

        $this->image->deleteImageStyle($imagestyle['imagestyle_id']);
        $this->image->clearCache($imagestyle['imagestyle_id']);

        $this->redirect('admin/settings/imagestyle', $this->text('Image style has been deleted'), 'success');
    }

    /**
     * Saves an imagestyle
     * @param array $imagestyle
     * @return null
     */
    protected function submit(array $imagestyle)
    {
        $this->setSubmitted('imagestyle');
        $this->validate($imagestyle);

        if ($this->hasErrors('imagestyle')) {
            return;
        }

        if (isset($imagestyle['imagestyle_id'])) {
            $this->controlAccess('image_style_edit');
            $this->image->updateImageStyle($imagestyle['imagestyle_id'], $this->submitted);
            $this->image->clearCache($imagestyle['imagestyle_id']);
            $this->redirect('admin/settings/imagestyle', $this->text('Image style has been updated'), 'success');
        }

        $this->controlAccess('image_style_add');
        $this->image->addImageStyle($this->submitted);
        $this->redirect('admin/settings/imagestyle', $this->text('Image style has been added'), 'success');
    }

    /**
     * Validates an image style
     * @param array $imagestyle
     */
    protected function validate(array $imagestyle)
    {
        $this->addValidator('name', array('length' => array('min' => 1, 'max' => 255)));
        $this->validateActions();
    }

    /**
     * Validates actions
     * @return boolean
     */
    protected function validateActions()
    {
        $actions = $this->getSubmitted('actions');

        if (empty($actions)) {
            $this->setError('actions', $this->text('Required field'));
            return false;
        }

        $modified_actions = $error_lines = array();
        $array_actions = Tool::stringToArray($actions);

        foreach ($array_actions as $line => $action) {
            $valid = false;

            $parts = array_map('trim', explode(' ', trim($action)));
            $action_id = array_shift($parts);
            $value = array_filter(explode(',', implode('', $parts)));

            switch ($action_id) {
                case 'flip':
                    $valid = $this->validateActionFlip($value);
                    break;
                case 'rotate':
                    $valid = $this->validateActionRotate($value);
                    break;
                case 'brightness':
                    $valid = $this->validateActionBrightness($value);
                    break;
                case 'contrast':
                    $valid = $this->validateActionContrast($value);
                    break;
                case 'smooth':
                    $valid = $this->validateActionSmooth($value);
                    break;
                case 'fill':
                    $valid = $this->validateActionFill($value);
                    break;
                case 'colorize':
                    $valid = $this->validateActionColorize($value);
                    break;
                case 'crop':
                    $valid = $this->validateActionCrop($value);
                    break;
                case 'overlay':
                    $valid = $this->validateActionOverlay($value);
                    break;
                case 'text':
                    $valid = $this->validateActionText($value);
                    break;
                case 'fit_to_width':
                case 'fit_to_height':
                case 'pixelate':
                case 'opacity':
                    $valid = $this->validateActionOpacity($value);
                    break;
                case 'resize':
                case 'thumbnail':
                case 'best_fit':
                    $valid = $this->validateActionThumbnail($value);
                    break;
                case 'auto_orient':
                case 'desaturate':
                case 'invert':
                case 'edges':
                case 'emboss':
                case 'mean_remove':
                case 'blur':
                case 'sketch':
                case 'sepia':
                    $valid = empty($value);
            }

            if (!$valid) {
                $error_lines[] = $line + 1;
                continue;
            }

            $modified_actions[$action_id] = array('value' => $value, 'weight' => $line);
        }

        if (!empty($error_lines)) {
            $message = $this->text('Error on lines %num', array(
                '%num' => implode(',', $error_lines)));

            $this->setError('actions', $message);
            return false;
        }

        $this->setSubmitted('actions', $modified_actions);
    }

    /**
     * Validates "Flip" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionFlip(array $value)
    {
        return ((count($value) == 1) && in_array($value[0], array('x', 'y'), true));
    }

    /**
     * Validates "Rotate" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionRotate(array $value)
    {
        return ((count($value) == 1) && is_numeric($value[0]) && (0 <= (int) $value[0]) && ((int) $value[0] <= 360));
    }

    /**
     * Validates "Brightness" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionBrightness(array $value)
    {
        return ((count($value) == 1) && is_numeric($value[0]) && (-255 <= (int) $value[0]) && ((int) $value[0] <= 255));
    }

    /**
     * Validates "Contrast" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionContrast(array $value)
    {
        return ((count($value) == 1) && is_numeric($value[0]) && (-100 <= (int) $value[0]) && ((int) $value[0] <= 100));
    }

    /**
     * Validates "Smooth" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionSmooth(array $value)
    {
        return ((count($value) == 1) && is_numeric($value[0]) && (-10 <= (int) $value[0]) && ((int) $value[0] <= 10));
    }

    /**
     * Validates "Fill" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionFill(array $value)
    {
        return ((count($value) == 1) && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $value[0]));
    }

    /**
     * Validates "Colorize" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionColorize(array $value)
    {
        return ((count($value) == 2) && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $value[0]) && is_numeric($value[1]));
    }

    /**
     * Validates "Crop" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionCrop(array $value)
    {
        return (count(array_filter(array_slice($value, 0, 4), 'is_numeric')) == 4);
    }

    /**
     * Validates "Overlay" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionOverlay(array $value)
    {
        return ((count($value) == 5) && is_numeric($value[2]) && is_numeric($value[3]) && is_numeric($value[4]));
    }

    /**
     * Validates "Text" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionText(array $value)
    {
        return ((count($value) == 7) && is_numeric($value[2]) && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $value[3]) && is_numeric($value[5]) && is_numeric($value[6]));
    }

    /**
     * Validates "Opacity" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionOpacity(array $value)
    {
        return ((count($value) == 1) && is_numeric($value[0]));
    }

    /**
     * Validates "Thumbnail" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionThumbnail(array $value)
    {
        return (count(array_filter(array_slice($value, 0, 2), 'is_numeric')) == 2);
    }

    /**
     * Modifies imagestyle actions
     * @return null
     */
    protected function setDataActions()
    {

        $actions = $this->getData('imagestyle.actions');

        if (!is_array($actions)) {
            return;
        }

        Tool::sortWeight($actions);

        $modified_actions = array();
        foreach ($actions as $action_id => $info) {
            $action = $action_id;
            if (!empty($info['value'])) {
                $action .= ' ' . implode(',', $info['value']);
            }
            $modified_actions[] = $action;
        }

        $this->setData('imagestyle.actions', implode("\n", $modified_actions));
    }

}
