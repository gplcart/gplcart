<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\Handler;
use gplcart\core\models\Trigger as TriggerModel,
    gplcart\core\models\Condition as ConditionModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

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
        $this->validateStatusComponent();
        $this->validateNameComponent();
        $this->validateStoreIdComponent();
        $this->validateWeightComponent();
        $this->validateConditionsTrigger();

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
            $this->setErrorUnavailable('update', $this->language->text('Trigger'));
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
        $value = $this->getSubmitted('data.conditions');

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $this->setErrorRequired('data.conditions', $this->language->text('Conditions'));
            return false;
        }

        $errors = $modified = array();
        $submitted = $this->getSubmitted();
        $operators = $this->condition->getOperators();
        $prepared_operators = array_map('htmlspecialchars', array_keys($operators));

        foreach ($value as $line => $condition) {

            $line++;
            $parts = gplcart_string_explode_whitespace($condition, 3);

            $condition_id = array_shift($parts);
            $operator = array_shift($parts);

            $parameters = array_filter(explode(',', implode('', $parts)), function ($value) {
                return ($value !== "");
            });

            if (empty($parameters)) {
                $errors[] = $this->language->text('Error on line @num: !error', array(
                    '@num' => $line,
                    '!error' => $this->language->text('No parameters')));
                continue;
            }

            if (!in_array(htmlspecialchars($operator), $prepared_operators)) {
                $errors[] = $this->language->text('Error on line @num: !error', array(
                    '@num' => $line,
                    '!error' => $this->language->text('Invalid operator')));
                continue;
            }

            $parameters = array_unique($parameters);
            $result = $this->callValidator($condition_id, array($parameters, $operator, $condition_id, $submitted));

            if ($result !== true) {
                $errors[] = $this->language->text('Error on line @num: !error', array('@num' => $line, '!error' => $result));
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
            $this->setError('data.conditions', implode('<br>', $errors));
        }

        if (!$this->isError()) {
            $this->setSubmitted('data.conditions', $modified);
            return true;
        }

        return false;
    }

    /**
     * Call a validator handler
     * @param string $condition_id
     * @param array $args
     * @return mixed
     */
    protected function callValidator($condition_id, array $args)
    {
        try {
            $handlers = $this->condition->getHandlers();
            return Handler::call($handlers, $condition_id, 'validate', $args);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

}
