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
use core\models\Condition as ModelsCondition;

/**
 * Provides methods to validate triggers related data
 */
class Trigger
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Condition model instance
     * @var \core\models\Condition $condition
     */
    protected $condition;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsCondition $condition
     */
    public function __construct(ModelsLanguage $language,
            ModelsCondition $condition)
    {
        $this->language = $language;
        $this->condition = $condition;
    }

    /**
     * Validates and modifies trigger conditions
     * @return boolean|array
     */
    public function conditions($value, array $options = array())
    {
        if (empty($value) && empty($options['required'])) {
            return true;
        }

        $modified = $errors = array();
        $operators = $this->condition->getOperators();
        $prepared_operators = array_map('htmlspecialchars', array_keys($operators));
        $conditions = Tool::stringToArray($value);

        foreach ($conditions as $line => $condition) {
            $line++;

            $condition = trim($condition);
            $parts = array_map('trim', explode(' ', $condition));

            $condition_id = array_shift($parts);
            $operator = array_shift($parts);

            $parameters = array_filter(explode(',', implode('', $parts)), function ($value) {
                return ($value !== "");
            });

            if (empty($parameters)) {
                $errors[] = $this->language->text('Error on line %num: no parameters', array('%num' => $line));
                continue;
            }

            if (!in_array(htmlspecialchars($operator), $prepared_operators)) {
                $errors[] = $this->language->text('Error on line %num: invalid operator', array('%num' => $line));
                continue;
            }

            $validator = $this->condition->getHandler($condition_id, 'validate');

            if (empty($validator)) {
                $errors[] = $this->language->text('Error on line %num: validator not found', array('%num' => $line));
                continue;
            }

            $result = call_user_func_array($validator, array($condition_id, $operator, &$parameters, $options['submitted']));

            if ($result !== true) {
                $error = empty($result) ? $this->language->text('did not pass validation') : (string) $result;
                $errors[] = $this->language->text('Error on line %num: !error', array('%num' => $line, '!error' => $error));
                continue;
            }

            $modified[] = array(
                'weight' => $line,
                'id' => $condition_id,
                'value' => $parameters,
                'operator' => $operator,
                'original' => $condition,
            );
        }

        if (empty($errors)) {
            return array('result' => $modified);
        }

        return implode('<br>', $errors);
    }

}
