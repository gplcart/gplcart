<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\models\Language as LanguageModel;

/**
 * Contains methods to validate price conditions
 */
class Price
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Constructor
     * @param CurrencyModel $currency
     * @param LanguageModel $language
     */
    public function __construct(CurrencyModel $currency, LanguageModel $language)
    {
        $this->currency = $currency;
        $this->language = $language;
    }

    /**
     * Validates a price condition
     * @param array $values
     * @return boolean|string
     */
    public function price(array $values)
    {
        if (count($values) != 1) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $components = array_map('trim', explode('|', reset($values)));

        if (count($components) > 2) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        if (!is_numeric($components[0])) {
            $vars = array('@field' => $this->language->text('Price'));
            return $this->language->text('@field must be numeric', $vars);
        }

        if (strlen($components[0]) > 8) {
            $vars = array('@max' => 8, '@field' => $this->language->text('Price'));
            return $this->language->text('@field must not be longer than @max characters', $vars);
        }

        if (empty($components[1])) {
            $components[1] = $this->currency->getDefault();
        }

        $currency = $this->currency->get($components[1]);

        if (empty($currency)) {
            $vars = array('@name' => $this->language->text('Currency'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

}
