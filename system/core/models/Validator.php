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
use core\classes\Tool;
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
     * An array of validation errors
     * @var array
     */
    protected $errors = array();

    /**
     * An array of fields to be validated
     * @var array
     */
    protected $fields = array();

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
     * 
     * @param string $field
     * @param array $validators
     * @return \core\models\Validator
     */
    public function add($field, array $validators)
    {
        $this->fields[$field] = $validators;
        return $this;
    }

    /**
     * Performs validation using a given handler
     * @param string $handler_id
     * @param string $value
     * @return boolean
     */
    public function check($handler_id, $value, $options = array())
    {
        $handlers = $this->getHandlers();
        $result = Handler::call($handlers, $handler_id, 'validate', array($value, $options));

        $arguments = array($handler_id, $value, $options);
        $this->hook->fire('validate', $arguments, $result, $this);

        if ($result === true) {
            return true;
        }

        if (empty($result)) {
            return $this->language->text('Failed to validate');
        }

        return $result;
    }

    /**
     * Performs validation against an array of fields
     * @param array $submitted
     * @param array $data
     * @return \core\models\Validator
     */
    public function set($submitted = array(), array $data = array())
    {
        foreach ($this->fields as $field => $validators) {
            foreach ($validators as $handler_id => $options) {

                if (!isset($options['data'])) {
                    $options['data'] = $data;
                }

                $value = Tool::getArrayValue($submitted, $field);
                $result = $this->check($handler_id, $value, $options);

                if ($result !== true) {
                    Tool::setArrayValue($this->errors, $field, $result);
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Returns an array of validation errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
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
        
        $handlers['numeric'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Common', 'numeric')
            ),
        );

        $handlers['email'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Common', 'email')
            ),
        );

        $handlers['translation'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Common', 'translation')
            ),
        );

        $handlers['image'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\File', 'image')
            ),
        );

        $handlers['p12'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\File', 'p12')
            ),
        );

        $handlers['csv'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\File', 'csv')
            ),
        );

        $handlers['zip'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\File', 'zip')
            ),
        );

        $handlers['alias_unique'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Database', 'aliasUnique')
            ),
        );
        
        $handlers['regexp'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Common', 'regexp')
            ),
        );
        
        $handlers['country_code_unique'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Database', 'countryCodeUnique')
            ),
        );
        
        $handlers['currency_code_unique'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Database', 'currencyCodeUnique')
            ),
        );
        
        $handlers['category_group_type'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Database', 'categoryGroupType')
            ),
        );

        $this->hook->fire('validator.handlers', $handlers);

        return $handlers;
    }

}
