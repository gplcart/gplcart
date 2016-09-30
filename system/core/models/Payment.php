<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\classes\Tool;
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
     * Returns an array of payment methods
     * @param boolean $enabled
     * @return array
     */
    public function getMethods($enabled = false)
    {
        $methods = &Cache::memory('payment.method');

        if (isset($methods)) {
            return $methods;
        }

        $methods = $this->getDefault();

        $this->hook->fire('payment.method', $methods);

        if ($enabled) {
            $methods = array_filter($methods, function ($method) {
                return !empty($method['status']);
            });
        }
        
        Tool::sortWeight($methods);
        return $methods;
    }

    /**
     * Returns an array of default payment methods
     * @return array
     */
    protected function getDefault()
    {
        $methods = array();

        $methods['cod'] = array(
            'title' => $this->language->text('Cash on delivery'),
            'description' => $this->language->text('Payment for an order is made at the time of delivery'),
            'image' => '',
            'status' => true,
            'weight' => 0,
            'template' => array('select' => '', 'submit' => '')
        );

        return $methods;
    }

}
