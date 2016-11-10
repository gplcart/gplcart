<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Image as ModelsImage;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate image style data
 */
class ImageStyle extends BaseValidator
{

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Constructor
     * @param ModelsImage $image
     */
    public function __construct(ModelsImage $image)
    {
        parent::__construct();

        $this->image = $image;
    }

    /**
     * Performs full image style validation
     * @param array $submitted
     * @param array $action
     */
    public function imageStyle(array &$submitted)
    {
        $this->validateImageStyle($submitted);
        $this->validateName($submitted);
        $this->validateStatus($submitted);
        $this->validateActionsImageStyle($submitted);

        return empty($this->errors) ? true : $this->errors;
    }

    /**
     * Validates an image style to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateImageStyle(array &$submitted)
    {
        if (!empty($submitted['update']) && is_string($submitted['update'])) {
            $imagestyle = $this->image->getStyle($submitted['update']);
            if (empty($imagestyle)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Image style')));
                return false;
            }

            $submitted['update'] = $imagestyle;
        }

        return true;
    }

    /**
     * Validates image actions
     * @param array $submitted
     * @param array $options
     * @return boolean
     */
    public function validateActionsImageStyle(&$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['actions'])) {
            return null;
        }

        if (empty($submitted['actions'])) {
            $this->errors['actions'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Actions')
            ));
            return false;
        }

        $modified = $errors = array();
        foreach ($submitted['actions'] as $line => $action) {

            $valid = false;
            $parts = array_map('trim', explode(' ', trim($action)));
            $action_id = array_shift($parts);
            $value = array_filter(explode(',', implode('', $parts)));

            switch ($action_id) {
                case 'flip':
                case 'rotate':
                case 'brightness':
                case 'contrast':
                case 'smooth':
                case 'fill':
                case 'colorize':
                case 'crop':
                case 'overlay':
                case 'text':
                    $valid = $this->{"validateAction{$action_id}ImageStyle"}($value);
                    break;
                case 'fit_to_width':
                case 'fit_to_height':
                case 'pixelate':
                case 'opacity':
                    $valid = $this->validateActionOpacityImageStyle($value);
                    break;
                case 'resize':
                case 'thumbnail':
                case 'best_fit':
                    $valid = $this->validateActionThumbnailImageStyle($value);
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
                    break;
            }

            if (!$valid) {
                $errors[] = $line + 1;
                continue;
            }

            $modified[$action_id] = array(
                'value' => $value,
                'weight' => $line
            );
        }

        if (!empty($errors)) {
            $this->errors['actions'] = $this->language->text('Error on lines %num', array(
                '%num' => implode(',', $errors)));
        }

        if (empty($this->errors)) {
            $submitted['actions'] = $modified;
            return true;
        }

        return false;
    }

    /**
     * Validates "Flip" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionFlipImageStyle(array $value)
    {
        return ((count($value) == 1)//
                && in_array($value[0], array('x', 'y'), true));
    }

    /**
     * Validates "Rotate" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionRotateImageStyle(array $value)
    {
        return ((count($value) == 1)//
                && is_numeric($value[0])//
                && (0 <= (int) $value[0])//
                && ((int) $value[0] <= 360));
    }

    /**
     * Validates "Brightness" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionBrightnessImageStyle(array $value)
    {
        return ((count($value) == 1)//
                && is_numeric($value[0])//
                && (-255 <= (int) $value[0])//
                && ((int) $value[0] <= 255));
    }

    /**
     * Validates "Contrast" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionContrastImageStyle(array $value)
    {
        return ((count($value) == 1)//
                && is_numeric($value[0])//
                && (-100 <= (int) $value[0])//
                && ((int) $value[0] <= 100));
    }

    /**
     * Validates "Smooth" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionSmoothImageStyle(array $value)
    {
        return ((count($value) == 1)//
                && is_numeric($value[0])//
                && (-10 <= (int) $value[0])//
                && ((int) $value[0] <= 10));
    }

    /**
     * Validates "Fill" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionFillImageStyle(array $value)
    {
        return ((count($value) == 1)//
                && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $value[0]));
    }

    /**
     * Validates "Colorize" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionColorizeImageStyle(array $value)
    {
        return ((count($value) == 2)//
                && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $value[0])//
                && is_numeric($value[1]));
    }

    /**
     * Validates "Crop" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionCropImageStyle(array $value)
    {
        return (count(array_filter(array_slice($value, 0, 4), 'is_numeric')) == 4);
    }

    /**
     * Validates "Overlay" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionOverlayImageStyle(array $value)
    {
        return ((count($value) == 5) && is_numeric($value[2])//
                && is_numeric($value[3]) && is_numeric($value[4]));
    }

    /**
     * Validates "Text" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionTextImageStyle(array $value)
    {
        return ((count($value) == 7)//
                && is_numeric($value[2])//
                && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $value[3])//
                && is_numeric($value[5]) && is_numeric($value[6]));
    }

    /**
     * Validates "Opacity" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionOpacityImageStyle(array $value)
    {
        return ((count($value) == 1) && is_numeric($value[0]));
    }

    /**
     * Validates "Thumbnail" action
     * @param array $value
     * @return boolean
     */
    protected function validateActionThumbnailImageStyle(array $value)
    {
        return (count(array_filter(array_slice($value, 0, 2), 'is_numeric')) == 2);
    }

}
