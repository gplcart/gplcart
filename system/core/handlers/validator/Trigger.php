<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Trigger as ModelsTrigger;
use core\models\Condition as ModelsCondition;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate trigger data
 */
class Trigger extends BaseValidator
{

    /**
     * Condition model instance
     * @var \core\models\Condition $condition
     */
    protected $condition;

    /**
     * Trigger model instance
     * @var \core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * Constructor
     * @param ModelsCondition $condition
     * @param ModelsTrigger $trigger
     */
    public function __construct(ModelsCondition $condition,
            ModelsTrigger $trigger)
    {
        parent::__construct();

        $this->trigger = $trigger;
        $this->condition = $condition;
    }

    /**
     * Performs full trigger data validation
     * @param array $trigger
     */
    public function trigger(array &$submitted)
    {
        $this->validateTrigger($submitted);
        $this->validateStatus($submitted);
        $this->validateName($submitted);
        $this->validateStoreId($submitted);
        $this->validateWeight($submitted);
        $this->validateConditionsTrigger($submitted);

        return empty($this->errors) ? true : $this->errors;
    }

    /**
     * Validates a trigger to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateTrigger(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {

            $data = $this->trigger->get($submitted['update']);

            if (empty($data)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Trigger')));
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

    /**
     * Validates and modifies trigger conditions
     * @return boolean
     */
    public function validateConditionsTrigger(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['data']['conditions'])) {
            return null;
        }

        if (empty($submitted['data']['conditions'])) {
            $this->errors['data']['conditions'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Conditions')
            ));
            return false;
        }

        $errors = $modified = array();
        $operators = $this->condition->getOperators();
        $prepared_operators = array_map('htmlspecialchars', array_keys($operators));

        foreach ($submitted['data']['conditions'] as $line => $condition) {
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

            $result = call_user_func_array($validator, array($condition_id, $operator, &$parameters, $submitted));

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

        if (!empty($errors)) {
            $this->errors['data']['conditions'] = implode('<br>', $errors);
        }

        if (empty($this->errors)) {
            $submitted['data']['conditions'] = $modified;
            return true;
        }

        return false;
    }

}
