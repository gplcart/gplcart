<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains Oauth controller methods
 */
trait ControllerOauth
{

    /**
     * Returns an array of Oauth login buttons
     * @param \gplcart\core\Controller $controller
     */
    protected function getOauthButtonsTrait($controller)
    {
        if (!$controller instanceof \gplcart\core\Controller) {
            throw new \InvalidArgumentException("Object is not instance of \gplcart\core\Controller");
        }

        /* @var $model \gplcart\core\models\Oauth */
        $model = \gplcart\core\Container::get('gplcart\\core\\models\\Oauth');

        $options = array('type' => 'login', 'status' => true);
        $providers = $model->getProviders($options);

        $buttons = array();
        foreach ($providers as $provider_id => $provider) {
            if (isset($provider['template']['button'])) {
                $url = $model->url($provider);
                $buttons[$provider_id]['url'] = $url;
                $buttons[$provider_id]['provider'] = $provider;
                $data = array('provider' => $provider, 'url' => $url);
                $buttons[$provider_id]['rendered'] = $controller->render($provider['template']['button'], $data);
            }
        }

        return $buttons;
    }

}
