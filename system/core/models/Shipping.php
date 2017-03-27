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
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to shipping
 */
class Shipping extends Model
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
     * Returns an array of shipping methods
     * @param array $data
     * @return array
     */
    public function getList(array $data = array())
    {
        $methods = &Cache::memory(array(__METHOD__ => $data));

        if (isset($methods)) {
            return $methods;
        }

        $methods = $this->getDefaultList();
        $this->hook->fire('shipping.methods', $methods);

        $weights = array();
        foreach ($methods as $id => $method) {
            if (!empty($data['status']) && empty($method['status'])) {
                unset($methods[$id]);
                continue;
            }
            if (!empty($data['module']) && (empty($method['module']) || !in_array($method['module'], (array) $data['module']))) {
                unset($methods[$id]);
                continue;
            }
            if (!isset($data['weight'])) {
                $data['weight'] = 0;
            }
            $weights[] = $data['weight'];
        }

        if (empty($methods)) {
            return array();
        }

        // Sort by weight then by key
        array_multisort($weights, SORT_ASC, array_keys($methods), SORT_ASC, $methods);
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
    protected function getDefaultList()
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
