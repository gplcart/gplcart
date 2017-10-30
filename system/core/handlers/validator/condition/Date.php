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
 * Contains methods to validate date conditions
 */
class Date
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
     * Validates the date condition
     * @param array $values
     * @return boolean|string
     */
    public function date(array $values)
    {
        if (count($values) != 1) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $timestamp = strtotime(reset($values));

        if (empty($timestamp)) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        return true;
    }

}
