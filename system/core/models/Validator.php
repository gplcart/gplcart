<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Container;
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
     * Performs validation using a given handler
     * @param string $handler_id
     * @param array $submitted
     * @param array $options
     * @return mixed
     */
    public function run($handler_id, array &$submitted, array $options = array())
    {
        $this->hook->fire('validate.before', $submitted, $options);

        $result = null;
        $handlers = $this->getHandlers();

        // Do not use Handler::call()
        // as we need to pass $submitted by reference
        if (!empty($handlers[$handler_id]['handlers']['validate'])) {
            $class = $handlers[$handler_id]['handlers']['validate'];
            $instance = Container::instance($class);
            $result = call_user_func_array(array($instance, $class[1]), array(&$submitted, $options));
        }

        $this->hook->fire('validate.after', $submitted, $options, $result);

        if ($result === true) {
            return true;
        }

        if (empty($result)) {
            return $this->language->text('Failed to pass validation');
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

        $handlers = array();

        $handlers['category'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Category', 'category')
            ),
        );

        $handlers['category_group'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\CategoryGroup', 'categoryGroup')
            ),
        );

        $handlers['city'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\City', 'city')
            ),
        );
        
        $handlers['collection'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Collection', 'collection')
            ),
        );
        
        $handlers['collection_item'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\CollectionItem', 'collectionItem')
            ),
        );

        $this->hook->fire('validator.handlers', $handlers);
        return $handlers;
    }

}
