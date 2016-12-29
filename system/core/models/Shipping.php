<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Cache;
use core\models\Language as LanguageModel;

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
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();

        $this->language = $language;
    }

    /**
     * Returns an array of shipping methods
     * @param boolean $enabled
     * @return array
     */
    public function getList($enabled = false)
    {
        $methods = &Cache::memory('shipping.methods');

        if (isset($methods)) {
            return $methods;
        }

        $methods = $this->getDefault();

        if ($enabled) {
            $methods = array_filter($methods, function ($method) {
                return !empty($method['status']);
            });
        }

        $this->hook->fire('shipping.method', $methods);

        gplcart_array_sort($methods);
        return $methods;
    }

    /**
     * Returns a shipping method
     * @param string $method_id
     * @return array
     */
    public function get($method_id)
    {
        $methods = $this->getList();
        return empty($methods[$method_id]) ? array() : $methods[$method_id];
    }

    /**
     * Returns an array of default shipping methods
     * @return array
     */
    protected function getDefault()
    {
        $methods = array();

        $methods['pickup'] = array(
            'title' => $this->language->text('Pickup'),
            'description' => $this->language->text('Customer must pick up his items himself at the store'),
            'template' => array('complete' => ''),
            'image' => '',
            'status' => true,
            'weight' => 0
        );

        return $methods;
    }

}
