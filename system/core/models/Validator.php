<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\Cache;
use gplcart\core\Container;
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
     * Constructor
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
        $this->hook->fire('validator.run.before', $submitted, $options);

        $result = null;
        $handlers = $this->getHandlers();

        if (!empty($handlers[$handler_id]['handlers']['validate'])) {
            $class = $handlers[$handler_id]['handlers']['validate'];
            $instance = Container::get($class);
            $result = call_user_func_array(array($instance, $class[1]), array(&$submitted, $options));
        }

        $this->hook->fire('validator.run.after', $submitted, $options, $result);

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
        $handlers = &Cache::memory('validator.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = include GC_CONFIG_VALIDATOR;

        $this->hook->fire('validator.list', $handlers);
        return $handlers;
    }

}
