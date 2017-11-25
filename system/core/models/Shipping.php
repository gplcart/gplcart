<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to shipping methods
 */
class Shipping
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param Hook $hook
     * @param LanguageModel $language
     */
    public function __construct(Hook $hook, LanguageModel $language)
    {
        $this->hook = $hook;
        $this->language = $language;
    }

    /**
     * Returns an array of shipping methods
     * @param array $data
     * @return array
     */
    public function getList(array $data = array())
    {
        $methods = &gplcart_static(gplcart_array_hash(array('shipping.methods' => $data)));

        if (isset($methods)) {
            return $methods;
        }

        $methods = $this->getDefaultList();
        $this->hook->attach('shipping.methods', $methods, $this);

        $weights = array();
        foreach ($methods as $id => &$method) {

            $method['id'] = $id;

            if (!isset($method['weight'])) {
                $method['weight'] = 0;
            }

            if (!empty($data['status']) && empty($method['status'])) {
                unset($methods[$id]);
                continue;
            }

            if (!empty($data['module']) && (empty($method['module']) || !in_array($method['module'], (array) $data['module']))) {
                unset($methods[$id]);
                continue;
            }

            $weights[] = $method['weight'];
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
        return array(
            'pickup' => array(
                'title' => $this->language->text('Pickup'),
                'description' => $this->language->text('Customer must pick up his items himself at the store'),
                'template' => array('complete' => ''),
                'image' => '',
                'status' => true,
                'weight' => 0
            )
        );
    }

}
