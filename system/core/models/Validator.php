<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook,
    gplcart\core\Handler;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to data validation
 */
class Validator
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param Hook $hook
     * @param LanguageModel $language
     */
    public function __construct(Hook $hook, LanguageModel $language)
    {
        $this->hook = $hook;
        $this->language = $language;
    }

    /**
     * Performs validation using the given handler
     * @param string $handler_id
     * @param mixed $submitted
     * @param array $options
     * @return mixed
     */
    public function run($handler_id, &$submitted, array $options = array())
    {
        $result = null;
        $this->hook->attach('validator.run.before', $submitted, $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $result = $this->callHandler($handler_id, $submitted, $options);
        $this->hook->attach('validator.run.after', $submitted, $options, $result, $this);

        if ($result === true) {
            return true;
        }

        return empty($result) ? $this->language->text('Failed validation') : $result;
    }

    /**
     * Call a validation handler
     * @param string $handler_id
     * @param mixed $submitted
     * @param array $options
     * @return mixed
     */
    public function callHandler($handler_id, &$submitted, array $options)
    {
        try {
            $handlers = $this->getHandlers();
            $handler = Handler::get($handlers, $handler_id, 'validate');
            return call_user_func_array($handler, array(&$submitted, $options));
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Returns an array of validator handlers
     * @return array
     */
    protected function getHandlers()
    {
        $handlers = &gplcart_static('validator.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = (array) gplcart_config_get(GC_FILE_CONFIG_VALIDATOR);
        $this->hook->attach('validator.handlers', $handlers, $this);
        return $handlers;
    }

}
