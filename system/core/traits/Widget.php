<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains methods to get rendered widgets
 */
trait Widget
{

    /**
     * Returns the rendered admin menu
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\Route $route_class
     * @param array $options
     * @return string
     */
    public function getWidgetAdminMenu($controller, $route_class, array $options = array())
    {
        $options += array('parent_url' => 'admin');

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
        return $this->getWidgetMenu($controller, $options);
    }

    /**
     * Returns the rendered menu
     * @param \gplcart\core\Controller $controller
     * @param array $options
     * @return string
     */
    public function getWidgetMenu($controller, array $options)
    {
        $options += array(
            'depth' => 0,
            'template' => 'common/menu'
        );

        return $controller->render($options['template'], $options);
    }

    /**
     * Returns rendered category menu
     * @param \gplcart\core\Controller $controller
     * @param array $categories
     * @return string
     */
    public function getWidgetCategoryMenu($controller, $categories)
    {
        return $this->getWidgetMenu($controller, array('items' => $categories));
    }

    /**
     * Returns rendered honey pot input
     * @param \gplcart\core\Controller $controller
     * @return string
     */
    public function getWidgetCaptcha($controller)
    {
        return $controller->render('common/honeypot');
    }

    /**
     * Returns rendered "Share this" widget
     * @param \gplcart\core\Controller $controller
     * @param array $options
     * @return string
     */
    public function getWidgetShare($controller, array $options = array())
    {
        $options += array(
            'url' => $controller->url('', array(), true));

        return $controller->render('common/share', $options);
    }

    /**
     * Returns rendered image widget
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Language $language
     * @param array $options
     * @return string
     */
    public function getWidgetImages($controller, $language, array $options)
    {
        $options += array(
            'languages' => $language->getList(false, true)
        );

        return $controller->render('common/image', $options);
    }

}
