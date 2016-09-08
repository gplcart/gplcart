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
 * Manages basic behaviors and data related to payment
 */
class Payment extends Model
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
     * Returns an array of payment methods and services
     * @return type
     */
    public function getMethods()
    {
        $methods = &Cache::memory('payment.methods');

        if (isset($methods)) {
            return $methods;
        }

        $methods = array();

        $methods['cod'] = array(
            'title' => $this->language->text('Cash on delivery'),
            'description' => $this->language->text('Payment for an order is made at the time of delivery'),
            'services' => array(
                'cod' => array(
                    'title' => '',
                    'description' => '',
                    'image' => '',
                    'status' => true,
                    'price' => $this->config->get('payment_cod_price', 0),
                    'currency' => $this->config->get('currency', 'USD')
                )
            )
        );

        $this->hook->fire('payment.method', $methods);
        return $methods;
    }

    /**
     * Returns an array of calculated prices per payment service 
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
        $this->hook->fire('payment.calculate', $results, $methods, $data);
        return $results;
    }

}
