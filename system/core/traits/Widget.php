<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Collection of widgets
 */
trait Widget
{

    /**
     * @see \gplcart\core\Controller::access()
     * @param $permission
     * @return
     */
    abstract public function access($permission);

    /**
     * @see \gplcart\core\Controller::config()
     * @param null $key
     * @param null $default
     * @return
     */
    abstract public function config($key = null, $default = null);

    /**
     * @see \gplcart\core\Controller::text()
     * @param string $string
     * @param array $arguments
     * @return
     */
    abstract public function text($string, array $arguments = array());

    /**
     * @see \gplcart\core\Controller::render()
     * @param $file
     * @param array $data
     * @param bool $merge
     * @param string $default
     * @return
     */
    abstract public function render($file, $data = array(), $merge = true, $default = '');

    /**
     * @see \gplcart\core\Controller::url()
     * @param string $path
     * @param array $query
     * @param bool $abs
     * @param bool $exclude
     * @return
     */
    abstract public function url($path = '', array $query = array(), $abs = false, $exclude = false);

    /**
     * Returns rendered admin menu
     * @param \gplcart\core\Route $route_class
     * @param array $options
     * @return string
     */
    public function getWidgetAdminMenu($route_class, array $options = array())
    {
        $options += array('parent_url' => 'admin');

        $items = array();
        foreach ($route_class->getList() as $path => $route) {
            if (strpos($path, "{$options['parent_url']}/") !== 0 || empty($route['menu']['admin'])) {
                continue;
            }
            if (isset($route['access']) && !$this->access($route['access'])) {
                continue;
            }
            $items[$path] = array(
                'url' => $this->url($path),
                'text' => $this->text($route['menu']['admin']),
                'depth' => substr_count(substr($path, strlen("{$options['parent_url']}/")), '/'),
            );
        }

        ksort($items);
        $options += array('items' => $items);
        return $this->getWidgetMenu($options);
    }

    /**
     * Returns rendered menu
     * @param array $options
     * @return string
     */
    public function getWidgetMenu(array $options)
    {
        $options += array(
            'depth' => 0,
            'template' => 'common/menu'
        );

        return $this->render($options['template'], $options);
    }

    /**
     * Returns rendered honey pot input
     * @return string
     */
    public function getWidgetCaptcha()
    {
        return $this->render('common/honeypot');
    }

    /**
     * Returns rendered "Share this" widget
     * @param array $options
     * @return string
     */
    public function getWidgetShare(array $options = array())
    {
        $options += array(
            'url' => $this->url('', array(), true));

        return $this->render('common/share', $options);
    }

    /**
     * Returns rendered image widget
     * @param \gplcart\core\models\Language $language_model
     * @param array $options
     * @return string
     */
    public function getWidgetImages($language_model, array $options)
    {
        $options += array(
            'single' => false,
            'languages' => $language_model->getList(array('in_database' => true))
        );

        return $this->render('common/image', $options);
    }

    /**
     * Returns rendered product picker widget
     * @param array $options
     * @return string
     */
    public function getWidgetProductPicker(array $options = array())
    {
        $options += array(
            'name' => '',
            'key' => 'product_id',
            'store_id' => null,
            'multiple' => false,
            'products' => array()
        );

        return $this->render('content/product/picker', $options);
    }

    /**
     * Returns a rendered collection
     * @param array $items
     * @return string
     */
    protected function getWidgetCollection(array $items)
    {
        if (empty($items)) {
            return '';
        }

        $item = reset($items);

        $data = array(
            'items' => $items,
            'title' => $item['collection_item']['collection_title'],
            'collection_id' => $item['collection_item']['collection_id']
        );

        return $this->render($item['collection_handler']['template']['list'], $data, true);
    }

    /**
     * Returns rendered cart preview
     * @param array $cart
     * @return string
     */
    protected function getWidgetCartPreview(array $cart)
    {
        if (empty($cart['items'])) {
            return '';
        }

        $options = array(
            'cart' => $cart,
            'limit' => $this->config('cart_preview_limit', 5)
        );

        return $this->render('cart/preview', $options, true);
    }

}
