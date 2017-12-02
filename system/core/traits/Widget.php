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
     * @return \gplcart\core\Controller
     */
    protected abstract function getController();

    /**
     * Returns rendered admin menu
     * @param \gplcart\core\Route $route_class
     * @param array $options
     * @return string
     */
    public function getWidgetAdminMenu($route_class, array $options = array())
    {
        $options += array('parent_url' => 'admin');
        $controller = $this->getController();

        $items = array();
        foreach ($route_class->getList() as $path => $route) {
            if (strpos($path, "{$options['parent_url']}/") !== 0 || empty($route['menu']['admin'])) {
                continue;
            }
            if (isset($route['access']) && !$controller->access($route['access'])) {
                continue;
            }
            $items[$path] = array(
                'url' => $controller->url($path),
                'text' => $controller->text($route['menu']['admin']),
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

        return $this->getController()->render($options['template'], $options);
    }

    /**
     * Returns rendered category menu
     * @param array $categories
     * @return string
     */
    public function getWidgetCategoryMenu($categories)
    {
        return $this->getController()->getWidgetMenu(array('items' => $categories));
    }

    /**
     * Returns rendered honey pot input
     * @return string
     */
    public function getWidgetCaptcha()
    {
        return $this->getController()->render('common/honeypot');
    }

    /**
     * Returns rendered "Share this" widget
     * @param array $options
     * @return string
     */
    public function getWidgetShare(array $options = array())
    {
        $controller = $this->getController();

        $options += array(
            'url' => $controller->url('', array(), true));

        return $controller->render('common/share', $options);
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

        return $this->getController()->render('common/image', $options);
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
            'status' => true,
            'type' => 'login'
        );

        $controller = $this->getController();
        $providers = $this->getProviders($options);

        $buttons = array();
        foreach ($providers as $provider_id => $provider) {
            if (!empty($provider['template']['button'])) {
                $url = $oauth_model->url($provider);
                $buttons[$provider_id]['url'] = $url;
                $buttons[$provider_id]['provider'] = $provider;
                $data = array('provider' => $provider, 'url' => $url);
                $buttons[$provider_id]['rendered'] = $controller->render($provider['template']['button'], $data);
            }
        }

        return $controller->render('common/oauth', array('buttons' => $buttons));
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

        return $this->getController()->render('content/product/picker', $options);
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

        return $this->getController()->render($item['collection_handler']['template']['list'], $data, true);
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

        $controller = $this->getController();

        $options = array(
            'cart' => $cart,
            'limit' => $controller->config('cart_preview_limit', 5)
        );

        return $controller->render('cart/preview', $options, true);
    }

}
