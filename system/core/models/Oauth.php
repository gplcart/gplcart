<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Cache,
    gplcart\core\Handler;
use gplcart\core\helpers\Url as UrlHelper,
    gplcart\core\helpers\Curl as CurlHelper,
    gplcart\core\helpers\Session as SessionHelper;

/**
 * Manages basic behaviors and data related to Oauth functionality
 */
class Oauth extends Model
{

    /**
     * Curl helper instance
     * @var \gplcart\core\helpers\Curl $curl
     */
    protected $curl;

    /**
     * Url helper instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Session helper instance
     * @var \gplcart\core\helpers\Session $session
     */
    protected $session;

    /**
     * @param CurlHelper $curl
     * @param SessionHelper $session
     * @param UrlHelper $url
     */
    public function __construct(CurlHelper $curl, SessionHelper $session,
            UrlHelper $url)
    {
        parent::__construct();

        $this->url = $url;
        $this->curl = $curl;
        $this->session = $session;
    }

    /**
     * Returns an array of an Oauth provider
     * @param string $id
     * @return array
     */
    public function getProvider($id)
    {
        $providers = $this->getProviders();

        if (empty($providers[$id])) {
            return array();
        }

        return $providers[$id];
    }

    /**
     * Returns an array of Oauth providers
     * @param array $data
     * @return string
     */
    public function getProviders(array $data = array())
    {
        $providers = &Cache::memory(array(__METHOD__ => $data));

        if (isset($providers)) {
            return $providers;
        }

        $providers = array();
        $this->hook->fire('oauth.providers', $providers);

        foreach ($providers as $provider_id => &$provider) {
            $provider += array('type' => '', 'id' => $provider_id, 'status' => true);
            if (isset($data['type']) && $data['type'] !== $provider['type']) {
                unset($providers[$provider_id]);
                continue;
            }

            if (isset($data['status']) && $data['status'] != $provider['status']) {
                unset($providers[$provider_id]);
            }
        }

        return $providers;
    }

    /**
     * Returns an array of provider query data
     * @param array $provider
     * @param array $params
     * @return array
     */
    public function getQuery(array $provider, array $params = array())
    {
        $params += array(
            'client_id' => $provider['settings']['client_id'],
            'redirect_uri' => $this->url->get('oauth', array(), true)
        );

        return array_merge($provider['query'], $params);
    }

    /**
     * Returns an array of query used to exchange token
     * @param array $provider
     * @param array $params
     */
    public function getQueryToken(array $provider, array $params)
    {
        $query = $this->getQuery($provider);

        $params += array(
            'grant_type' => 'authorization_code',
            'client_secret' => $provider['settings']['client_secret']
        );

        return array_merge($query, $params);
    }

    /**
     * Returns an authorization URL for a given provider
     * @param array $provider
     * @param array $params
     * @return string
     */
    public function url(array $provider, array $params = array())
    {
        $params += array(
            'response_type' => 'code',
            'state' => $this->buildState($provider)
        );

        $query = $this->getQuery($provider, $params);
        return $this->url->get($provider['url']['auth'], $query, true);
    }

    /**
     * Build state code
     * @param array $provider
     * @return string
     */
    protected function buildState(array $provider)
    {
        $data = array(
            'id' => $provider['id'],
            'url' => $this->url->path(),
            'key' => gplcart_string_random(4), // More security
        );

        $state = gplcart_string_encode(json_encode($data));
        $this->setState($state, $provider['id']);
        return $state;
    }

    /**
     * Returns an array of data from encoded state code
     * @param string $string
     * @return array
     */
    public function parseState($string)
    {
        return json_decode(gplcart_string_decode($string), true);
    }

    /**
     * Set the current state
     * @param string $state
     * @param string $provider_id
     */
    public function setState($state, $provider_id)
    {
        $this->session->set("oauth_state.$provider_id", $state);
    }

    /**
     * Returns a saved state data from the session
     * @param string $provider_id
     * @return string
     */
    public function getState($provider_id)
    {
        return $this->session->get("oauth_state.$provider_id");
    }

    /**
     * Whether the state is actual
     * @param string $state
     * @param string $provider_id
     * @return bool
     */
    public function isValidState($state, $provider_id)
    {
        return gplcart_string_equals($state, $this->getState($provider_id));
    }

    /**
     * Returns an array of token data
     * @param array $provider
     * @param array $params
     * @return array
     */
    public function getToken(array $provider, array $params = array())
    {
        $this->hook->fire('oauth.get.token.before', $provider, $params);
        $token = $this->requestToken($provider, $params);
        $this->hook->fire('oauth.get.token.after', $provider, $params, $token);
        return $token;
    }

    /**
     * Request a new token
     * @param array $provider
     * @param array $params
     * @return mixed
     */
    protected function requestToken(array $provider, array $params)
    {
        if (isset($provider['handlers']['token'])) {
            return $this->call('token', $provider, $params);
        }

        $post = array('fields' => $this->getQueryToken($provider, $params));
        $response = $this->curl->post($provider['url']['token'], $post);

        return json_decode($response, true);
    }

    /**
     * Does main authorization process
     * @param array $provider
     * @param string $token
     * @return bool
     */
    public function process(array $provider, $token)
    {
        $this->hook->fire('oauth.process.before', $provider, $token);
        $result = $this->call('process', $provider, array('token' => $token));
        $this->hook->fire('oauth.process.after', $provider, $token, $result);

        return $result;
    }

    /**
     * Call a provider handler
     * @param string $handler
     * @param array $provider
     * @param array $params
     * @return mixed
     */
    protected function call($handler, array $provider, $params)
    {
        $providers = $this->getProviders();
        return Handler::call($providers, $provider['id'], $handler, array($params, $provider, $this));
    }

}
