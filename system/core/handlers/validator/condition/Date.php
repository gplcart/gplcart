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
 * Contains methods to validate date conditions
 */
class Date
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
     * Validates the date condition
     * @param array $values
     * @return boolean|string
     */
    public function date(array $values)
    {
        if (count($values) != 1) {
            $vars = array('@field' => $this->translation->text('Condition'));
            return $this->translation->text('@field has invalid value', $vars);
        }

        $timestamp = strtotime(reset($values));

        if (empty($timestamp)) {
            $vars = array('@field' => $this->translation->text('Condition'));
            return $this->translation->text('@field has invalid value', $vars);
        }

        return true;
    }

}
