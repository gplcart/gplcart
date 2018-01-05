<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook;
use gplcart\core\models\Translation as TranslationModel;

/**
 * Manages basic behaviors and data related to payment methods
 */
class Payment
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * @param Hook $hook
     * @param Translation $translation
     */
    public function __construct(Hook $hook, TranslationModel $translation)
    {
        $this->hook = $hook;
        $this->translation = $translation;
    }

    /**
     * Returns an array of payment methods
     * @param array $data
     * @return array
     */
    public function getList(array $data = array())
    {
        $methods = &gplcart_static(gplcart_array_hash(array('payment.methods' => $data)));

        if (isset($methods)) {
            return $methods;
        }

        $methods = $this->getDefaultList();
        $this->hook->attach('payment.methods', $methods, $this);

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
     * Returns a payment method
     * @param string $method_id
     * @return array
     */
    public function get($method_id)
    {
        $methods = $this->getList();
        return empty($methods[$method_id]) ? array() : $methods[$method_id];
    }

    /**
     * Returns an array of default payment methods
     * @return array
     */
    protected function getDefaultList()
    {
        return array(
            'cod' => array(
                'title' => $this->translation->text('Cash on delivery'),
                'description' => $this->translation->text('Payment for an order is made at the time of delivery'),
                'template' => array('complete' => ''),
                'image' => '',
                'module' => 'core',
                'status' => true,
                'weight' => 0
            )
        );
    }

}
