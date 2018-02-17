<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\models\Currency;
use gplcart\core\models\Translation;

/**
 * Contains methods to validate price conditions
 */
class Price
{

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * @param Currency $currency
     * @param Translation $translation
     */
    public function __construct(Currency $currency, Translation $translation)
    {
        $this->currency = $currency;
        $this->translation = $translation;
    }

    /**
     * Validates a price condition
     * @param array $values
     * @return boolean|string
     */
    public function price(array $values)
    {
        if (count($values) != 1) {
            return $this->translation->text('@field has invalid value', array(
                '@field' => $this->translation->text('Condition')));
        }

        $components = array_map('trim', explode('|', reset($values)));

        if (count($components) > 2) {
            return $this->translation->text('@field has invalid value', array(
                '@field' => $this->translation->text('Condition')));
        }

        if (!is_numeric($components[0])) {
            return $this->translation->text('@field must be numeric', array(
                '@field' => $this->translation->text('Price')));
        }

        if (strlen($components[0]) > 8) {
            return $this->translation->text('@field must not be longer than @max characters', array(
                '@max' => 8,
                '@field' => $this->translation->text('Price')));
        }

        if (empty($components[1])) {
            $components[1] = $this->currency->getDefault();
        }

        $currency = $this->currency->get($components[1]);

        if (empty($currency)) {
            return $this->translation->text('@name is unavailable', array(
                '@name' => $this->translation->text('Currency')));
        }

        return true;
    }

}
