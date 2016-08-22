<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\classes\Tool;
use core\models\Language as ModelsLanguage;

/**
 * Provides methods to validate image style data
 */
class ImageStyle
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param ModelsLanguage $language
     */
    public function __construct(ModelsLanguage $language)
    {
        $this->language = $language;
    }

    /**
     * Validates actions
     * @return boolean
     */
    public function actions($actions, array $options = array())
    {
        if (empty($actions)) {
            return true;
        }

        $modified = $errors = array();
        $array = Tool::stringToArray($actions);

        foreach ($array as $line => $action) {

            $valid = false;
            $parts = array_map('trim', explode(' ', trim($action)));
            $action_id = array_shift($parts);
            $value = array_filter(explode(',', implode('', $parts)));

            switch ($action_id) {
                case 'flip':
                    $valid = $this->actionFlip($value);
                    break;
                case 'rotate':
                    $valid = $this->actionRotate($value);
                    break;
                case 'brightness':
                    $valid = $this->actionBrightness($value);
                    break;
                case 'contrast':
                    $valid = $this->actionContrast($value);
                    break;
                case 'smooth':
                    $valid = $this->actionSmooth($value);
                    break;
                case 'fill':
                    $valid = $this->actionFill($value);
                    break;
                case 'colorize':
                    $valid = $this->actionColorize($value);
                    break;
                case 'crop':
                    $valid = $this->actionCrop($value);
                    break;
                case 'overlay':
                    $valid = $this->actionOverlay($value);
                    break;
                case 'text':
                    $valid = $this->actionText($value);
                    break;
                case 'fit_to_width':
                case 'fit_to_height':
                case 'pixelate':
                case 'opacity':
                    $valid = $this->actionOpacity($value);
                    break;
                case 'resize':
                case 'thumbnail':
                case 'best_fit':
                    $valid = $this->actionThumbnail($value);
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
                $errors[] = $line + 1;
                continue;
            }

            $modified[$action_id] = array(
                'value' => $value,
                'weight' => $line
            );
        }

        if (empty($errors)) {
            return array('result' => $modified);
        }

        return $this->language->text('Error on lines %num', array(
                    '%num' => implode(',', $errors)));
    }

    /**
     * Validates "Flip" action
     * @param array $value
     * @return boolean
     */
    protected function actionFlip(array $value)
    {
        return ((count($value) == 1)
                && in_array($value[0], array('x', 'y'), true));
    }

    /**
     * Validates "Rotate" action
     * @param array $value
     * @return boolean
     */
    protected function actionRotate(array $value)
    {
        return ((count($value) == 1)
                && is_numeric($value[0])
                && (0 <= (int) $value[0])
                && ((int) $value[0] <= 360));
    }

    /**
     * Validates "Brightness" action
     * @param array $value
     * @return boolean
     */
    protected function actionBrightness(array $value)
    {
        return ((count($value) == 1)
                && is_numeric($value[0])
                && (-255 <= (int) $value[0])
                && ((int) $value[0] <= 255));
    }

    /**
     * Validates "Contrast" action
     * @param array $value
     * @return boolean
     */
    protected function actionContrast(array $value)
    {
        return ((count($value) == 1)
                && is_numeric($value[0])
                && (-100 <= (int) $value[0]) && ((int) $value[0] <= 100));
    }

    /**
     * Validates "Smooth" action
     * @param array $value
     * @return boolean
     */
    protected function actionSmooth(array $value)
    {
        return ((count($value) == 1)
                && is_numeric($value[0])
                && (-10 <= (int) $value[0])
                && ((int) $value[0] <= 10));
    }

    /**
     * Validates "Fill" action
     * @param array $value
     * @return boolean
     */
    protected function actionFill(array $value)
    {
        return ((count($value) == 1)
                && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $value[0]));
    }

    /**
     * Validates "Colorize" action
     * @param array $value
     * @return boolean
     */
    protected function actionColorize(array $value)
    {
        return ((count($value) == 2)
                && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $value[0])
                && is_numeric($value[1]));
    }

    /**
     * Validates "Crop" action
     * @param array $value
     * @return boolean
     */
    protected function actionCrop(array $value)
    {
        return (count(array_filter(array_slice($value, 0, 4), 'is_numeric')) == 4);
    }

    /**
     * Validates "Overlay" action
     * @param array $value
     * @return boolean
     */
    protected function actionOverlay(array $value)
    {
        return ((count($value) == 5) && is_numeric($value[2])
                && is_numeric($value[3]) && is_numeric($value[4]));
    }

    /**
     * Validates "Text" action
     * @param array $value
     * @return boolean
     */
    protected function actionText(array $value)
    {
        return ((count($value) == 7)
                && is_numeric($value[2])
                && preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $value[3])
                && is_numeric($value[5]) && is_numeric($value[6]));
    }

    /**
     * Validates "Opacity" action
     * @param array $value
     * @return boolean
     */
    protected function actionOpacity(array $value)
    {
        return ((count($value) == 1) && is_numeric($value[0]));
    }

    /**
     * Validates "Thumbnail" action
     * @param array $value
     * @return boolean
     */
    protected function actionThumbnail(array $value)
    {
        return (count(array_filter(array_slice($value, 0, 2), 'is_numeric')) == 2);
    }

}
