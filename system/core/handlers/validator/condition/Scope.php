<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\models\Translation as TranslationModel;

/**
 * Contains methods to validate scope conditions
 */
class Scope
{

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * @param TranslationModel $translation
     */
    public function __construct(TranslationModel $translation)
    {
        $this->translation = $translation;
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
            return $this->translation->text('Unsupported operator');
        }

        if (count($values) != 1) {
            $vars = array('@field' => $this->translation->text('Condition'));
            return $this->translation->text('@field has invalid value', $vars);
        }

        $scope = filter_var(reset($values), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if (!isset($scope)) {
            $vars = array('@field' => $this->translation->text('Condition'));
            return $this->translation->text('@field has invalid value', $vars);
        }

        return true;
    }

}
