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
 * Manages basic behaviors and data related to data validation
 */
class Validator extends Model
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();

        $this->language = $language;
    }

    /**
     * Performs validation using a given handler
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

        $handlers = $this->getHandlers();

        if (!empty($handlers[$handler_id]['handlers']['validate'])) {
            $callable = $handlers[$handler_id]['handlers']['validate'];
            $instance = Container::get($callable[0]);
            $result = call_user_func_array(array($instance, $callable[1]), array(&$submitted, $options));
        }

        $this->hook->attach('validator.run.after', $submitted, $options, $result, $this);

        if ($result === true) {
            return true;
        }

        if (empty($result)) {
            return $this->language->text('Failed validation');
        }

        return $result;
    }

    /**
     * Returns an array of validator handlers
     * @return array
     */
    protected function getHandlers()
    {
        $handlers = &gplcart_static(__METHOD__);

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = require GC_CONFIG_VALIDATOR;
        $this->hook->attach('validator.handlers', $handlers, $this);
        return $handlers;
    }

}
