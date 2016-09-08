<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\classes\Cache;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to shipping
 */
class Shipping extends Model
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
     * Returns an array of shipping methods and services
     * @return array
     */
    public function getMethods()
    {
        $methods = &Cache::memory('shipping.methods');

        if (isset($methods)) {
            return $methods;
        }

        $methods = array();

        $methods['pickup'] = array(
            'title' => $this->language->text('Pickup'),
            'description' => $this->language->text('Customer must pick up his items himself at the store'),
            'services' => array(
                'pickup' => array(
                    'title' => '',
                    'description' => '',
                    'image' => '',
                    'status' => true,
                    'price' => $this->config->get('shipping_pickup_price', 0),
                    'currency' => $this->config->get('currency', 'USD')
                )
            )
        );

        $this->hook->fire('shipping.method', $methods);
        return $methods;
    }

    /**
     * Returns an array of calculated prices per shipping service 
     * @param array $cart
     * @param array $order
     * @return array
     */
    public function calculate(array $cart, array $order)
    {
        $results = array();
        $methods = $this->getMethods();

        // Code

        $data = array('cart' => $cart, 'order' => $order);
        $this->hook->fire('shipping.calculate', $results, $methods, $data);
        return $results;
    }

}
