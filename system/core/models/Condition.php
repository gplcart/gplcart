<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Container;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to trigger conditions
 */
class Condition extends Model
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * An array of processed conditions and their results
     * @var array
     */
    protected $processed = array();

    /**
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();

        $this->language = $language;
    }

    /**
     * Sets a condition ID is processed
     * @param string $condition_id
     * @param mixed $result
     */
    public function setProcessed($condition_id, $result = true)
    {
        $this->processed[$condition_id] = $result;
    }

    /**
     * Returns an array of processed condition results
     * @return array
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * Whether all conditions are met
     * @param array $trigger
     * @param array $data
     * @return boolean
     */
    public function isMet(array $trigger, array $data)
    {
        $met = null;
        $this->hook->attach('condition.met.before', $trigger, $data, $met, $this);

        if (isset($met)) {
            return (bool) $met;
        }

        if (empty($trigger['data']['conditions'])) {
            return false;
        }

        $met = true;
        $handlers = $this->getHandlers();

        foreach ($trigger['data']['conditions'] as $condition) {

            if (empty($handlers[$condition['id']]['handlers']['process'])) {
                continue;
            }

            $class = $handlers[$condition['id']]['handlers']['process'];
            $instance = Container::get($class);

            $result = call_user_func_array(array($instance, $class[1]), array($condition, $data, $this));
            $this->setProcessed($condition['id'], $result);

            if ($result !== true) {
                $met = false;
                break;
            }
        }

        $this->hook->attach('condition.met.after', $trigger, $data, $met, $this);
        return (bool) $met;
    }

    /**
     * Returns an array of condition operators
     * @return array
     */
    public function getOperators()
    {
        return array(
            "<" => $this->language->text('Less than'),
            ">" => $this->language->text('Greater than'),
            "=" => $this->language->text('Equal (is in list)'),
            "<=" => $this->language->text('Less than or equal to'),
            ">=" => $this->language->text('Greater than or equal to'),
            "!=" => $this->language->text('Not equal (is not in list)')
        );
    }

    /**
     * Returns an array of condition handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &gplcart_static(__METHOD__);

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = require GC_CONFIG_CONDITION;

        array_walk($handlers, function(&$handler) {
            $handler['title'] = $this->language->text($handler['title']);
            $handler['description'] = $this->language->text($handler['description']);
        });

        $this->hook->attach('condition.handlers', $handlers, $this);
        return $handlers;
    }

}
