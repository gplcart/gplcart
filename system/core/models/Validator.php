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
     * @param mixed $submitted
     * @param array $options
     * @return mixed
     */
    public function run($handler_id, &$submitted, array $options = array())
    {
        $this->hook->fire('validate.before', $submitted, $options);

        $result = null;
        $handlers = $this->getHandlers();

        // Do not use Handler::call()
        // as we need to pass $submitted by reference
        if (!empty($handlers[$handler_id]['handlers']['validate'])) {
            $class = $handlers[$handler_id]['handlers']['validate'];
            $instance = Container::instance($class);
            if (is_object($instance)) {
                $result = call_user_func_array(array($instance, $class[1]), array(&$submitted, $options));
            }
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

        // Files
        $handlers['image'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\FileType', 'image')
            ),
        );

        $handlers['p12'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\FileType', 'p12')
            ),
        );

        $handlers['csv'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\FileType', 'csv')
            ),
        );

        $handlers['zip'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\FileType', 'zip')
            ),
        );

        // Entity validators
        $handlers['cart'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Cart', 'cart')
            ),
        );

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

        $handlers['country'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Country', 'country')
            ),
        );

        $handlers['address'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Address', 'address')
            ),
        );

        $handlers['currency'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Currency', 'currency')
            ),
        );

        $handlers['field'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Field', 'field')
            ),
        );

        $handlers['field_value'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\FieldValue', 'fieldValue')
            ),
        );

        $handlers['file'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\File', 'file')
            ),
        );

        $handlers['image_style'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\ImageStyle', 'imageStyle')
            ),
        );

        $handlers['import'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Import', 'import')
            ),
        );

        $handlers['install'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Install', 'install')
            ),
        );

        $handlers['language'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Language', 'language')
            ),
        );

        $handlers['module_upload'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Module', 'upload')
            ),
        );

        $handlers['page'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Page', 'page')
            ),
        );

        $handlers['price_rule'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\PriceRule', 'priceRule')
            ),
        );

        $handlers['product'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Product', 'product')
            ),
        );

        $handlers['product_class'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\ProductClass', 'productClass')
            ),
        );

        $handlers['rating'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Rating', 'rating')
            ),
        );

        $handlers['review'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Review', 'review')
            ),
        );

        $handlers['settings'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Settings', 'settings')
            ),
        );

        $handlers['state'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\State', 'state')
            ),
        );

        $handlers['store'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Store', 'store')
            ),
        );

        $handlers['trigger'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Trigger', 'trigger')
            ),
        );

        $handlers['user'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\User', 'user')
            ),
        );

        $handlers['user_login'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\User', 'login')
            ),
        );

        $handlers['user_reset_password'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\User', 'resetPassword')
            ),
        );

        $handlers['user_role'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\UserRole', 'userRole')
            ),
        );

        $handlers['zone'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Zone', 'zone')
            ),
        );

        $handlers['order'] = array(
            'handlers' => array(
                'validate' => array('core\\handlers\\validator\\Order', 'order')
            ),
        );

        $this->hook->fire('validator.handlers', $handlers);
        return $handlers;
    }

}
