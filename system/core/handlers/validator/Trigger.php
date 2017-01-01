<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Trigger as TriggerModel;
use gplcart\core\models\Condition as ConditionModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate trigger data
 */
class Trigger extends BaseValidator
{

    /**
     * Condition model instance
     * @var \gplcart\core\models\Condition $condition
     */
    protected $condition;

    /**
     * Trigger model instance
     * @var \gplcart\core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * Constructor
     * @param ConditionModel $condition
     * @param TriggerModel $trigger
     */
    public function __construct(ConditionModel $condition, TriggerModel $trigger)
    {
        parent::__construct();

        $this->trigger = $trigger;
        $this->condition = $condition;
    }

    /**
     * Performs full trigger data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function trigger(array &$submitted, array $options)
    {
        $this->submitted = &$submitted;

        $this->validateTrigger($options);
        $this->validateStatus($options);
        $this->validateName($options);
        $this->validateStoreId($options);
        $this->validateWeight($options);
        $this->validateConditionsTrigger($options);

        return $this->getResult();
    }

    /**
     * Validates a trigger to be updated
     * @param array $options
     * @return boolean
     */
    protected function validateTrigger(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->trigger->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Trigger'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates and modifies trigger conditions
     * @param array $options
     * @return boolean|null
     */
    public function validateConditionsTrigger(array $options)
    {
        $value = $this->getSubmitted('data.conditions', $options);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Conditions'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('data.conditions', $error, $options);
            return false;
        }

        $errors = $modified = array();
        $operators = $this->condition->getOperators();
        $prepared_operators = array_map('htmlspecialchars', array_keys($operators));

        foreach ($value as $line => $condition) {
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

            $data = $this->getSubmitted();
            $result = call_user_func_array($validator, array($condition_id, $operator, &$parameters, $data));

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
            $this->setError('data.conditions', implode('<br>', $errors), $options);
        }

        if (!$this->isError()) {
            $this->setSubmitted('data.conditions', $modified, $options);
            return true;
        }

        return false;
    }

}
