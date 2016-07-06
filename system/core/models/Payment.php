<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Hook;
use core\Config;
use core\Container;
use core\classes\Cache;
use core\classes\Url as ClassesUrl;
use core\models\Module as ModelsModule;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to payment services and modules
 */
class Payment
{

    /**
     * Module model instance
     * @var \core\models\Module $module
     */
    protected $module;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Url model instance
     * @var \core\classes\Url $url
     */
    protected $url;

    /**
     * Config class instance
     * @var type \core\Config $config
     */
    protected $config;

    /**
     * Constructor
     * @param ModelsModule $module
     * @param ModelsLanguage $language
     * @param ClassesUrl $url
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(ModelsModule $module, ModelsLanguage $language,
                                ClassesUrl $url, Hook $hook, Config $config)
    {
        $this->url = $url;
        $this->hook = $hook;
        $this->module = $module;
        $this->config = $config;
        $this->language = $language;
    }

    /**
     * Returns an array of shipping services
     * @param array $cart
     * @param array $order
     * @param boolean $enabled
     * @return array
     */
    public function getServices(array $cart = array(), array $order = array(),
                                $enabled = true)
    {
        $services = &Cache::memory('payment.services');

        if (isset($services)) {
            return $services;
        }

        $services = $this->defaultServices();

        foreach ($this->module->getByType('payment', true) as $module_id => $info) {
            $object = Container::instance($info['class']);

            if (!method_exists($object, 'services')) {
                $services["$module_id|$module_id"] = $this->defaultService($info);
                continue;
            }

            foreach ($object->services($cart, $order) as $service_id => $service) {
                $services["$module_id|$service_id"] = $service + $this->defaultService($info);
            }
        }

        $this->hook->fire('payment.services', $cart, $order, $services);

        if ($enabled) {
            return array_filter($services, function ($service) {
                return ($service['price'] !== false);
            });
        }

        return $services;
    }

    /**
     * Returns a single shipping service
     * @param string $service_id
     * @param array $cart
     * @param array $order
     * @return array
     */
    public function getService($service_id, array $cart = array(),
                               array $order = array())
    {
        $services = $this->getServices($cart, $order, false);
        return empty($services[$service_id]) ? array() : $services[$service_id];
    }

    /**
     * Returns an array of default payment services
     * @return array
     */
    protected function defaultServices()
    {
        return array(
            'cod|cod' => array(
                'name' => $this->language->text('Cash on delivery'),
                'description' => $this->language->text('Payment for an order is made at the time of delivery'),
                'image' => '',
                'price' => $this->config->get('payment_cod_price', 0),
                'currency' => $this->config->get('currency', 'USD')
            )
        );
    }

    /**
     * Returns an array of default service values
     * @param array $module
     * @return array
     */
    protected function defaultService(array $module)
    {
        return array(
            'name' => $module['name'],
            'description' => $module['description'],
            'image' => $module['image'],
            'price' => false,
            'currency' => $this->config->get('currency', 'USD')
        );
    }
}
