<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to payment methods
 */
class Payment extends Model
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();

        $this->language = $language;
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
                'title' => $this->language->text('Cash on delivery'),
                'description' => $this->language->text('Payment for an order is made at the time of delivery'),
                'template' => array('complete' => ''),
                'image' => '',
                'status' => true,
                'weight' => 0
            )
        );
    }

}
