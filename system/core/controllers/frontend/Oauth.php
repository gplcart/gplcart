<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Oauth as OauthModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to Oauth functionality
 */
class Oauth extends FrontendController
{

    /**
     * Oauth model instance
     * @var \gplcart\core\models\Oauth $oauth
     */
    protected $oauth;

    /**
     * The current Oauth provider
     * @var array
     */
    protected $data_provider;

    /**
     * The current received code from provider
     * @var string
     */
    protected $data_code;

    /**
     * The current received state hash from provider
     * @var type 
     */
    protected $data_state;

    /**
     * The current token data
     * @var array
     */
    protected $data_token;

    /**
     * Processed authorization result
     * @var mixed 
     */
    protected $data_result;

    /**
     * URL to redirect to after authorization
     * @var string
     */
    protected $data_url;

    /**
     * @param OauthModuleModel $oauth
     */
    public function __construct(OauthModel $oauth)
    {
        parent::__construct();

        $this->oauth = $oauth;
    }

    /**
     * Callback for Oauth returning URL
     */
    public function callbackOauth()
    {
        $this->setReceivedDataOauth();
        $this->setTokenOauth();
        $this->setResultOauth();
        $this->redirectOauth();
    }

    /**
     * Set and validates received data from Oauth provider
     */
    protected function setReceivedDataOauth()
    {
        $this->data_code = $this->request->get('code');
        $this->data_state = $this->request->get('state');

        if (empty($this->data_code) || empty($this->data_state)) {
            $this->outputHttpStatus(403);
        }

        $parsed = $this->oauth->parseState($this->data_state);

        if (empty($parsed['id']) || !isset($parsed['url'])) {
            throw new \InvalidArgumentException('Invalid provider Id and/or returning URL');
        }

        if (!$this->oauth->isValidState($this->data_state, $parsed['id'])) {
            throw new \InvalidArgumentException('Invalid state code');
        }

        $this->data_provider = $this->oauth->getProvider($parsed['id']);

        if (empty($this->data_provider)) {
            throw new \InvalidArgumentException('Unknown Oauth provider');
        }

        // Be sure that URL domain belongs to our enabled store
        $store = $this->store->get(parse_url($parsed['url'], PHP_URL_HOST));

        if (empty($store['status'])) {
            throw new \InvalidArgumentException('Invalid domain in redirect URL');
        }

        $this->data_url = $parsed['url'];
    }

    /**
     * Does final redirect after authorization
     */
    protected function redirectOauth()
    {
        if (isset($this->data_result['message'])) {
            $this->setMessage($this->data_result['message'], $this->data_result['severity'], true);
        }

        if (isset($this->data_result['redirect'])) {
            $this->redirect($this->data_result['redirect']);
        }

        $this->redirect($this->data_url);
    }

    /**
     * Set received token data
     * @return array
     */
    protected function setTokenOauth()
    {
        $query = $this->oauth->getQueryToken($this->data_provider, array('code' => $this->data_code));
        $this->data_token = $this->oauth->exchangeToken($this->data_provider, $query);

        if (empty($this->data_token['access_token'])) {
            throw new \InvalidArgumentException('Failed to get access token');
        }

        $this->oauth->setToken($this->data_token, $this->data_provider['id']);
        return $this->data_token;
    }

    /**
     * Set authorization result
     * @return array
     */
    protected function setResultOauth()
    {
        $this->data_result = $this->oauth->process($this->data_provider, array(
            'token' => $this->data_token['access_token']));

        if (empty($this->data_result)) {
            $this->data_result['severity'] = 'warning';
            $this->data_result['message'] = $this->text('An error occurred');
        }

        return $this->data_result;
    }

}
