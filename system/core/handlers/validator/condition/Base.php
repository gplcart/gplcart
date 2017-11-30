<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\models\Language as LanguageModel;

/**
 * Base condition validator handler
 */
class Base
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        $this->language = $language;
    }

    /**
     * Validates an integer value
     * @param array $values
     * @return boolean|string
     */
    public function validateInteger(array $values)
    {
        if (count($values) != 1) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $value = reset($values);

        if (strlen($value) > 10) {
            $vars = array('@max' => 10, '@field' => $this->language->text('Value'));
            return $this->language->text('@field must not be longer than @max characters', $vars);
        }

        if (ctype_digit($value)) {
            return true;
        }

        $vars = array('@field' => $this->language->text('Condition'));
        return $this->language->text('@field has invalid value', $vars);
    }

}
