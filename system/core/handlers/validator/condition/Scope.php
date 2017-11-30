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
 * Contains methods to validate scope conditions
 */
class Scope
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
     * Validates the product scope condition
     * @param array $values
     * @param string $operator
     * @return boolean|string
     */
    public function product(array $values, $operator)
    {
        return $this->validate($values, $operator);
    }

    /**
     * Validates the cart scope condition
     * @param array $values
     * @param string $operator
     * @return boolean|string
     */
    public function cart(array $values, $operator)
    {
        return $this->validate($values, $operator);
    }

    /**
     * Validates the order scope condition
     * @param array $values
     * @param string $operator
     * @return boolean|string
     */
    public function order(array $values, $operator)
    {
        return $this->validate($values, $operator);
    }

    /**
     * Common scope validator
     * @param array $values
     * @param string $operator
     * @return boolean|string
     */
    protected function validate(array $values, $operator)
    {
        if (!in_array($operator, array('=', '!='))) {
            return $this->language->text('Unsupported operator');
        }

        if (count($values) != 1) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $scope = filter_var(reset($values), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if (!isset($scope)) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        return true;
    }

}
