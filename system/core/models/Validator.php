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
     * Array of validation errors
     * @var array
     */
    protected $errors = array();

    /**
     * Array of fields to be validated
     * @var array
     */
    protected $fields = array();

    /**
     * Array of validation results
     * @var array
     */
    protected $results = array();

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
            return true; // Certainly passed validation
        }

        if (empty($result)) {
            // Unknown handler or failed validation
            return $this->language->text('Failed to pass validation');
        }

        // Checker returned some data that can be used in the future
        // It can be an array of results (passed validation)
        // or an array of errors
        return $result;
    }

    /**
     * Performs validation against an array of fields
     * @param array $submitted
     * @param array $data
     * @return \core\models\Validator
     */
    public function set(array &$submitted = array(), array &$data = array())
    {
        foreach ($this->fields as $field => $validators) {
            foreach ($validators as $handler_id => $options) {

                // Skip any further validations in case of previous error(s)
                if (!empty($options['control_errors']) && !empty($this->errors)) {
                    return $this;
                }

                // Pass submitted values to the checker
                $options['submitted'] = $submitted;

                // Pass an extra data to the checker
                if (!isset($options['data'])) {
                    $options['data'] = $data;
                }

                // Get a value from the submitted values
                $value = Tool::getArrayValue($submitted, $field);

                // Call a handler for this checker
                $result = $this->check($handler_id, $value, $options);

                if ($result === true) {
                    continue; // Certainly passed validation, go to the next checker
                }

                if (isset($result['result'])) {

                    // Passed validation.
                    // Save returned results from this checker for the future
                    Tool::setArrayValue($this->results, $field, $result['result']);

                    if (!empty($options['set_submitted'])) {
                        // Add the results directly to the submitted values
                        // so they can be used by the next checker/validator
                        $submitted = Tool::merge($submitted, $result['result']);
                    }

                    if (!empty($options['set_data'])) {
                        // Add the results directly to the data values
                        // so they can be used by the next checker/validator
                        $data = Tool::merge($data, $result['result']);
                    }

                    continue;
                }

                // An error occurred, save it and stop the next checks
                Tool::setArrayValue($this->errors, $field, $result);
                break;
            }
        }

        return $this;
    }

    /**
     * Returns validation error(s)
     * @param string $field
     * @return mixed
     */
    public function getError($field = null)
    {
        if (isset($field)) {
            return Tool::getArrayValue($this->errors, $field);
        }

        return $this->errors;
    }

    /**
     * Retuns validation result(s)
     * @param string $field
     * @return mixed
     */
    public function getResult($field = null)
    {
        if (isset($field)) {
            return Tool::getArrayValue($this->results, $field);
        }

        return $this->results;
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

        $handlers['required'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Common', 'required')
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

        $handlers['upload'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\File', 'upload')
            ),
        );

        $handlers['alias_unique'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Alias', 'unique')
            ),
        );

        $handlers['regexp'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Common', 'regexp')
            ),
        );

        $handlers['date'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Common', 'date')
            ),
        );

        $handlers['images'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Common', 'images')
            ),
        );

        $handlers['cart_options'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Cart', 'options')
            ),
        );

        $handlers['cart_limits'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Cart', 'limits')
            ),
        );
        
        $handlers['collection_item_value'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\CollectionItem', 'value')
            ),
        );

        $handlers['country_code_unique'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Country', 'codeUnique')
            ),
        );

        $handlers['country_format'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Country', 'format')
            ),
        );

        $handlers['state_code_unique'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\State', 'codeUnique')
            ),
        );

        $handlers['store_domain_unique'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Store', 'domainUnique')
            ),
        );

        $handlers['store_basepath_unique'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Store', 'basepathUnique')
            ),
        );

        $handlers['currency_code_unique'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Currency', 'codeUnique')
            ),
        );

        $handlers['category_group_type_unique'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Category', 'groupTypeUnique')
            ),
        );

        $handlers['pricerule_code_unique'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\PriceRule', 'codeUnique')
            ),
        );

        $handlers['trigger_conditions'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Trigger', 'conditions')
            ),
        );

        $handlers['product_exists'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Product', 'exists')
            ),
        );

        $handlers['product_sku_unique'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Product', 'skuUnique')
            ),
        );

        $handlers['product_attributes'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Product', 'attributes')
            ),
        );

        $handlers['product_combinations'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Product', 'combinations')
            ),
        );

        $handlers['user_email_exists'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\User', 'emailExists')
            ),
        );

        $handlers['user_email_unique'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\User', 'emailUnique')
            ),
        );

        $handlers['user_name_unique'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\User', 'nameUnique')
            ),
        );

        $handlers['user_password'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\User', 'password')
            ),
        );

        $handlers['imagestyle_actions'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\ImageStyle', 'actions')
            ),
        );

        $this->hook->fire('validator.handlers', $handlers);
        return $handlers;
    }

}
