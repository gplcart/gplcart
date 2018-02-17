<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\models\Translation;

/**
 * Base condition validator handler
 */
class Base
{

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * @param Translation $translation
     */
    public function __construct(Translation $translation)
    {
        $this->translation = $translation;
    }

    /**
     * Validates an integer value
     * @param array $values
     * @return boolean|string
     */
    public function validateInteger(array $values)
    {
        if (count($values) != 1) {
            $vars = array('@field' => $this->translation->text('Condition'));
            return $this->translation->text('@field has invalid value', $vars);
        }

        $value = reset($values);

        if (strlen($value) > 10) {
            $vars = array('@max' => 10, '@field' => $this->translation->text('Value'));
            return $this->translation->text('@field must not be longer than @max characters', $vars);
        }

        if (ctype_digit($value)) {
            return true;
        }

        $vars = array('@field' => $this->translation->text('Condition'));
        return $this->translation->text('@field has invalid value', $vars);
    }

}
