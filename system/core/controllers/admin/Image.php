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
        $this->data['styles'] = $this->image->getImageStyleList();

        $style_id = $this->request->get('clear');

        if (!empty($style_id) && $this->image->clearCache($style_id)) {
            $this->redirect('', $this->text('Cache has been cleared'), 'success');
        }

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
        $this->data['imagestyle'] = $imagestyle;

        if ($this->request->post('delete')) {
            $this->delete($imagestyle);
        }

        if ($this->request->post('save')) {
            $this->submit($imagestyle);
        }

        $this->prepareActions();

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
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
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
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->setBreadcrumb(array('url' => $this->url('admin/settings/imagestyle'), 'text' => $this->text('Image styles')));
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
        if (empty($imagestyle['imagestyle_id'])) {
            return false;
        }

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
        $this->submitted = $this->request->post('imagestyle');
        $this->validate();

        $errors = $this->formErrors();

        if (!empty($errors)) {
            $this->data['imagestyle'] = $this->submitted;
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
     */
    protected function validate()
    {
        $this->validateName();
        $this->validateActions();
    }

    /**
     * Validates name field
     * @return boolean
     */
    protected function validateName()
    {
        if (empty($this->submitted['name']) || mb_strlen($this->submitted['name']) > 255) {
            $this->data['form_errors']['name'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates actions
     * @return boolean
     */
    protected function validateActions()
    {
        if (empty($this->submitted['actions'])) {
            $this->data['form_errors']['actions'] = $this->text('Required field');
            return false;
        }

        $modified_actions = $error_lines = array();
        $actions = Tool::stringToArray($this->submitted['actions']);

        foreach ($actions as $line => $action) {
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
            $this->data['form_errors']['actions'] = $this->text('Something wrong on lines %num', array(
                '%num' => implode(',', $error_lines)));
            return false;
        }

        $this->submitted['actions'] = $modified_actions;
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
     * @return array
     */
    protected function prepareActions()
    {
        if (!is_array($this->data['imagestyle']['actions'])) {
            return;
        }

        Tool::sortWeight($this->data['imagestyle']['actions']);

        $actions = array();
        foreach ($this->data['imagestyle']['actions'] as $action_id => $info) {
            $action = $action_id;
            if (!empty($info['value'])) {
                $action .= ' ' . implode(',', $info['value']);
            }
            $actions[] = $action;
        }

        $this->data['imagestyle']['actions'] = implode("\n", $actions);
    }

}
