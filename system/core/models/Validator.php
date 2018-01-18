<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use Exception;
use gplcart\core\Hook,
    gplcart\core\Handler;
use gplcart\core\models\Translation as TranslationModel;

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
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * @param Hook $hook
     * @param TranslationModel $translation
     */
    public function __construct(Hook $hook, TranslationModel $translation)
    {
        $this->hook = $hook;
        $this->translation = $translation;
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

        return empty($result) ? $this->translation->text('Failed validation') : $result;
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
            $callback = Handler::get($handlers, $handler_id, 'validate');
            return call_user_func_array($callback, array(&$submitted, $options));
        } catch (Exception $ex) {
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
