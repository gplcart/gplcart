<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Checkout methods
 */
trait Checkout
{

    /**
     * @see \gplcart\core\Controller::render()
     * @param $file
     * @param array $data
     * @param bool $merge
     * @param string $default
     */
    abstract public function render($file, $data = array(), $merge = true, $default = '');

    /**
     * @see \gplcart\core\Controller::getModuleSettings()
     * @param $module_id
     * @param null $key
     * @param null $default
     */
    abstract public function getModuleSettings($module_id, $key = null, $default = null);

    /**
     * Returns a rendered template for the shipping/payment method
     * @param string $template_name
     * @param array $order
     * @param array $method
     * @return string
     */
    protected function getMethodTemplate($template_name, array $order, array $method)
    {
        if (empty($method['status']) || empty($method['template'][$template_name])) {
            return '';
        }

        $settings = array();
        $template = $method['template'][$template_name];

        if (!empty($method['module'])) {
            $template = "{$method['module']}|$template";
            $settings = $this->getModuleSettings($method['module']);
        }

        $data = array(
            'order' => $order,
            'method' => $method,
            'settings' => $settings
        );

        return $this->render($template, $data);
    }

    /**
     * Returns a template for a shipping method
     * @param string $template
     * @param array $order
     * @param \gplcart\core\models\Shipping $shipping_model
     * @return string
     */
    public function getShippingMethodTemplate($template, array $order, $shipping_model)
    {
        if (empty($order['shipping'])) {
            return '';
        }

        $method = $shipping_model->get($order['shipping']);
        return $this->getMethodTemplate($template, $order, $method);
    }

    /**
     * Returns a template for a payment method
     * @param string $template
     * @param array $order
     * @param \gplcart\core\models\Payment $payment_model
     * @return string
     */
    public function getPaymentMethodTemplate($template, array $order, $payment_model)
    {
        if (empty($order['payment'])) {
            return '';
        }

        $method = $payment_model->get($order['payment']);
        return $this->getMethodTemplate($template, $order, $method);
    }

}
