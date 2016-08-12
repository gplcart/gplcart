<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Handler;
use core\classes\Cache;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to data validation
 */
class Validator extends Model
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param ModelsLanguage $language
     */
    public function __construct(ModelsLanguage $language)
    {
        parent::__construct();

        $this->language = $language;
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

        $handlers = array();

        $handlers['length'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Common', 'length')
            ),
        );

        $handlers['email'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Common', 'email')
            ),
        );

        $this->hook->fire('validator.handlers', $handlers);

        return $handlers;
    }

    /**
     * Performs validation using a given handler
     * @param string $handler_id
     * @param string $value
     * @param array $options
     * @return boolean
     */
    public function check($handler_id, $value, $options = array())
    {

        $handlers = $this->getHandlers();
        $arguments = array($value, $options);
        $result = Handler::call($handlers, $handler_id, 'validate', $arguments);

        if ($result === true) {
            return true;
        }

        if (empty($result)) {
            return $this->language->text('Failed to validate');
        }

        return (string) $result;
    }

}
