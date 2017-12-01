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
     * @see \gplcart\core\Controller::render()
     */
    abstract function render($file, $data = array(), $merge = true, $default = '');

    /**
     * @see \gplcart\core\Controller::access()
     */
    abstract function access($permission);

    /**
     * @see \gplcart\core\Controller::url()
     */
    abstract function url($path = '', array $query = array(), $absolute = false, $exclude = false);

    /**
     * @see \gplcart\core\Controller::config()
     */
    abstract function config($key = null, $default = null);

    /**
     * @see \gplcart\core\Controller::text()
     */
    abstract function text($string = null, array $arguments = array());
    

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
     * Returns rendered category menu
     * @param array $categories
     * @return string
     */
    public function getWidgetCategoryMenu($categories)
    {
        return $this->getWidgetMenu(array('items' => $categories));
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
            'languages' => $language_model->getList(false, true)
        );

        return $this->render('common/image', $options);
    }

    /**
     * Returns render widget with buttons from Oauth providers
     * @param \gplcart\core\models\Oauth $oauth_model
     * @param array $options
     * @return string
     */
    public function getWidgetOauthButtons($oauth_model, array $options = array())
    {
        $options += array(
            'type' => 'login',
            'status' => true
        );

        $providers = $this->getProviders($options);

        $buttons = array();
        foreach ($providers as $provider_id => $provider) {
            if (!empty($provider['template']['button'])) {
                $url = $oauth_model->url($provider);
                $buttons[$provider_id]['url'] = $url;
                $buttons[$provider_id]['provider'] = $provider;
                $data = array('provider' => $provider, 'url' => $url);
                $buttons[$provider_id]['rendered'] = $this->render($provider['template']['button'], $data);
            }
        }

        return $this->render('common/oauth', array('buttons' => $buttons));
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
