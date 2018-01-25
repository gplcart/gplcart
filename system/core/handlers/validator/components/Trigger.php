<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use Exception;
use gplcart\core\handlers\validator\Component as ComponentValidator;
use gplcart\core\models\Condition as ConditionModel;
use gplcart\core\models\Trigger as TriggerModel;

/**
 * Provides methods to validate trigger data
 */
class Trigger extends ComponentValidator
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
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateTrigger();
        $this->validateBool('status');
        $this->validateName();
        $this->validateStoreId();
        $this->validateWeight();
        $this->validateConditionsTrigger();

        $this->unsetSubmitted('update');

        return $this->getResult();
    }

    /**
     * Validates a trigger to be updated
     * @return boolean
     */
    protected function validateTrigger()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->trigger->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('Trigger'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates and modifies trigger conditions
     * @return boolean|null
     */
    public function validateConditionsTrigger()
    {
        $field = 'data.conditions';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Conditions');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_array($value)) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $submitted = $this->getSubmitted();
        $operators = array_map('htmlspecialchars', array_keys($this->condition->getOperators()));

        $errors = $modified = array();
        foreach ($value as $line => $condition) {

            $line++;

            $parts = gplcart_string_explode_whitespace($condition, 3);
            $condition_id = array_shift($parts);
            $operator = array_shift($parts);
            $parameters = array_filter(explode(',', implode('', $parts)), function ($value) {
                return $value !== '';
            });

            if (empty($parameters)) {
                $errors[] = $line;
                continue;
            }

            if (!in_array(htmlspecialchars($operator), $operators)) {
                $errors[] = $line;
                continue;
            }

            $args = array($parameters, $operator, $condition_id, $submitted);
            $result = $this->validateConditionTrigger($condition_id, $args);

            if ($result !== true) {
                $errors[] = $line;
                continue;
            }

            $modified[] = array(
                'weight' => $line,
                'id' => $condition_id,
                'value' => $parameters,
                'operator' => $operator,
                'original' => "$condition_id $operator " . implode(',', $parameters),
            );
        }

        if (!empty($errors)) {
            $error = $this->translation->text('Error in @num definition', array('@num' => implode(',', $errors)));
            $this->setError($field, $error);
        }

        if ($this->isError()) {
            return false;
        }

        $this->setSubmitted($field, $modified);
        return true;
    }

    /**
     * Call a validator handler
     * @param string $condition_id
     * @param array $args
     * @return mixed
     */
    protected function validateConditionTrigger($condition_id, array $args)
    {
        try {
            $handlers = $this->condition->getHandlers();
            return static::call($handlers, $condition_id, 'validate', $args);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

}
