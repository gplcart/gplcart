<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use Exception;
use gplcart\core\models\ImageStyle as ImageStyleModel;
use gplcart\core\handlers\validator\BaseComponent as BaseComponentValidator;

/**
 * Provides methods to validate image style data
 */
class ImageStyle extends BaseComponentValidator
{

    /**
     * Image style model instance
     * @var \gplcart\core\models\ImageStyle $image_style
     */
    protected $image_style;

    /**
     * @param ImageStyleModel $image_style
     */
    public function __construct(ImageStyleModel $image_style)
    {
        parent::__construct();

        $this->image_style = $image_style;
    }

    /**
     * Performs full image style data validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function imageStyle(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateImageStyle();
        $this->validateName();
        $this->validateStatus();
        $this->validateActionsImageStyle();

        $this->unsetSubmitted('update');
        return $this->getResult();
    }

    /**
     * Validates an image style to be updated
     * @return boolean|null
     */
    protected function validateImageStyle()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $imagestyle = $this->image_style->get($id);

        if (empty($imagestyle)) {
            $this->setErrorUnavailable('update', $this->translation->text('Image style'));
            return false;
        }

        $this->setSubmitted('update', $imagestyle);
        return true;
    }

    /**
     * Validates image actions
     * @return boolean|null
     */
    public function validateActionsImageStyle()
    {
        $field = 'actions';

        if ($this->isExcludedField($field)) {
            return null;
        }

        $actions = $this->getSubmitted($field);
        $label = $this->translation->text('Actions');

        if ($this->isUpdating() && !isset($actions)) {
            return null;
        }

        if (empty($actions)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $modified = $errors = $processed = array();
        foreach ($actions as $line => $action) {

            $parts = gplcart_string_explode_whitespace($action, 2);

            // Check action uniqueness
            $key = implode('', $parts);
            if (in_array($key, $processed)) {
                $this->setError($field, $this->translation->text('Actions must be unique'));
                return false;
            }
            $processed[] = $key;

            $action_id = array_shift($parts);
            $value = array_filter(explode(',', implode('', $parts)));

            if (!$this->validateActionImageStyle($action_id, $value)) {
                $errors[] = $line + 1;
                continue;
            }

            $modified[$action_id] = array('value' => $value, 'weight' => $line);
        }

        if (!empty($errors)) {
            $error = $this->translation->text('Error on @num action definition', array('@num' => implode(',', $errors)));
            $this->setError($field, $error);
        }

        if ($this->isError()) {
            return false;
        }

        $this->setSubmitted($field, $modified);
        return true;
    }

    /**
     * Calls an appropriate validator method for the given action ID
     * @param string $action_id
     * @param array $value
     * @return boolean
     */
    protected function validateActionImageStyle($action_id, array &$value)
    {
        $handler = $this->image_style->getActionHandler($action_id);

        if (empty($handler)) {
            return false;
        }

        try {
            $callback = static::get($handler, null, 'validate');
            return call_user_func_array($callback, array(&$value));
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Validates "Crop" action
     * @param array $value
     * @return boolean
     */
    public function validateActionCropImageStyle(array $value)
    {
        return count(array_filter(array_slice($value, 0, 4), 'ctype_digit')) == 4;
    }

    /**
     * Validates "Resize", "Fit to ..." actions
     * @param array $value
     * @return boolean
     */
    public function validateActionResizeImageStyle(array $value)
    {
        return count($value) == 1 && is_numeric($value[0]);
    }

    /**
     * Validates "Thumbnail" action
     * @param array $value
     * @return boolean
     */
    public function validateActionThumbnailImageStyle(array $value)
    {
        return count(array_filter(array_slice($value, 0, 2), 'ctype_digit')) == 2;
    }

}
