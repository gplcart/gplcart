<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Controller methods to render various widgets like share button etc
 */
trait ControllerWidget
{

    /**
     * Returns rendered honeypot input
     * @param \gplcart\core\Controller $controller
     * @return string
     */
    public function renderHoneyPotTrait($controller)
    {
        return $controller->render('common/honeypot');
    }

    /**
     * Returns rendered "Share this" widget
     * @param \gplcart\core\Controller $controller
     * @param array $options
     * @return string
     */
    public function renderShareWidgetTrait($controller, array $options = array())
    {
        $options += array(
            'title' => $controller->ptitle(),
            'url' => $controller->url('', array(), true)
        );

        return $controller->render('common/share', $options);
    }

    /**
     * Returns rendered menu
     * @param \gplcart\core\Controller $controller
     * @param array $options
     * @return string
     */
    protected function renderMenuTrait($controller, array $options = array())
    {
        if (empty($options['items'])) {
            return '';
        }

        $options += array('depth' => 0, 'template' => 'common/menu');
        return $controller->render($options['template'], $options);
    }

    /**
     * Returns a rendered job widget
     * @param \gplcart\core\Controller $controller
     * @param array $job
     * @return string
     */
    public function renderJobTrait($controller, array $job)
    {
        if (empty($job['status'])) {
            return '';
        }

        $job += array('widget' => 'common/job/widget');
        return $controller->render($job['widget'], array('job' => $job));
    }

}
