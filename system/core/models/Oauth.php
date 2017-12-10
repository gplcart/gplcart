<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Handler,
    gplcart\core\Hook;
use gplcart\core\helpers\Url as UrlHelper,
    gplcart\core\helpers\Request as RequestHelper,
    gplcart\core\helpers\Session as SessionHelper;
use gplcart\core\exceptions\OauthAuthorization as OauthAuthorizationException;

/**
 * Manages basic behaviors and data related to Oauth 2.0 functionality
 */
class Oauth
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Curl helper instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * URL helper instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Session helper instance
     * @var \gplcart\core\helpers\Session $session
     */
    protected $session;

    /**
     * @param Hook $hook
     * @param RequestHelper $request
     * @param SessionHelper $session
     * @param UrlHelper $url
     */
    public function __construct(Hook $hook, RequestHelper $request, SessionHelper $session,
            UrlHelper $url)
    {
        $this->url = $url;
        $this->hook = $hook;
        $this->request = $request;
        $this->session = $session;
    }

    /**
     * Returns an Oauth provider
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
     * @return array
     */
    public function getProviders(array $data = array())
    {
        $providers = &gplcart_static(gplcart_array_hash(array('oauth.providers' => $data)));

        if (isset($providers)) {
            return $providers;
        }

        $providers = array();
        $this->hook->attach('oauth.providers', $providers, $this);

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
     * Returns an array of authorization URL query
     * @param array $provider
     * @param array $params
     * @return array
     */
    public function getQueryAuth(array $provider, array $params = array())
    {
        $params += array(
            'response_type' => 'code',
            'scope' => $provider['scope'],
            'state' => $this->buildState($provider)
        );

        $params += $this->getDefaultQuery($provider);

        if (isset($provider['handlers']['auth'])) {
            $params = $this->callHandler('auth', $provider, $params);
        }

        return $params;
    }

    /**
     * Returns default query data for the user authorization process
     * @param array $provider
     * @return array
     */
    protected function getDefaultQuery(array $provider)
    {
        return array(
            'client_id' => $provider['settings']['client_id'],
            'redirect_uri' => $this->url->get('oauth', array(), true)
        );
    }

    /**
     * Returns a query for the authorization request
     * @param array $provider
     * @param array $params
     * @return array
     */
    public function getQueryToken(array $provider, array $params = array())
    {
        $default = array(
            'grant_type' => 'authorization_code',
            'client_secret' => $provider['settings']['client_secret']
        );

        $default += $this->getDefaultQuery($provider);
        return array_merge($default, $params);
    }

    /**
     * Returns an authorization URL for the given provider
     * @param array $provider
     * @param array $params
     * @return string
     */
    public function url(array $provider, array $params = array())
    {
        $query = $this->getQueryAuth($provider, $params);
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
            'url' => $this->url->get('', array(), true),
            'key' => gplcart_string_random(4), // Make resulting hash unique
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
     * Save a state code in the session
     * @param string $state
     * @param string $provider_id
     */
    public function setState($state, $provider_id)
    {
        $this->session->set("oauth.state.$provider_id", $state);
    }

    /**
     * Returns a saved state data from the session
     * @param string $provider_id
     * @return string
     */
    public function getState($provider_id)
    {
        return $this->session->get("oauth.state.$provider_id");
    }

    /**
     * Save a token data in the session
     * @param array $token
     * @param string $provider_id
     */
    public function setToken($token, $provider_id)
    {
        if (isset($token['expires_in'])) {
            $token['expires'] = GC_TIME + $token['expires_in'];
        }

        $this->session->set("oauth.token.$provider_id", $token);
    }

    /**
     * Whether a token for the given provider is valid
     * @param string $provider_id
     * @return bool
     */
    public function isValidToken($provider_id)
    {
        $token = $this->getToken($provider_id);

        return isset($token['access_token'])//
                && isset($token['expires'])//
                && GC_TIME < $token['expires'];
    }

    /**
     * Returns a saved token data from the session
     * @param string $provider_id
     * @return array
     */
    public function getToken($provider_id)
    {
        return $this->session->get("oauth.token.$provider_id");
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
     * Performs request to get access token
     * @param array $provider
     * @param array $query
     * @return array
     */
    public function requestToken(array $provider, array $query)
    {
        $result = null;
        $this->hook->attach('oauth.request.token.before', $provider, $query, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        try {
            $response = $this->request->send($provider['url']['token'], array('data' => $query));
            $result = json_decode($response['data'], true);
        } catch (\Exception $ex) {
            $result = array();
        }

        $this->hook->attach('oauth.request.token.after', $provider, $query, $result, $this);
        return $result;
    }

    /**
     * Returns an array of requested token data
     * @param array $provider
     * @param array $params
     * @return array
     */
    public function exchangeToken(array $provider, array $params = array())
    {
        if ($this->isValidToken($provider['id'])) {
            return $this->getToken($provider['id']);
        }

        if (isset($provider['handlers']['token'])) {
            $token = $this->callHandler('token', $provider, $params);
        } else {
            $token = $this->requestToken($provider, $params);
        }

        $this->setToken($token, $provider['id']);
        return $token;
    }

    /**
     * Generate and sign a JWT token
     * @param array $data
     * @return string
     * @throws \InvalidArgumentException
     * @link https://developers.google.com/accounts/docs/OAuth2ServiceAccount
     */
    public function generateJwt(array $data)
    {
        $data += array('lifetime' => 3600);

        if (empty($data['certificate_file'])) {
            throw new \InvalidArgumentException('Certificate file not set');
        }

        if (strpos($data['certificate_file'], GC_DIR_FILE) !== 0) {
            $data['certificate_file'] = gplcart_file_absolute($data['certificate_file']);
        }

        if (!is_readable($data['certificate_file']) || !is_file($data['certificate_file'])) {
            throw new \InvalidArgumentException('Private key does not exist');
        }

        $key = file_get_contents($data['certificate_file']);
        $header = array('alg' => 'RS256', 'typ' => 'JWT');

        $params = array(
            'iat' => GC_TIME,
            'scope' => $data['scope'],
            'aud' => $data['token_url'],
            'iss' => $data['service_account_id'],
            'exp' => GC_TIME + $data['lifetime']
        );

        $encodings = array(
            base64_encode(json_encode($header)),
            base64_encode(json_encode($params)),
        );

        $certs = array();
        if (!openssl_pkcs12_read($key, $certs, $data['certificate_secret'])) {
            throw new \InvalidArgumentException('Could not parse .p12 file');
        }

        if (!isset($certs['pkey'])) {
            throw new \InvalidArgumentException('Could not find private key in .p12 file');
        }

        $sig = '';
        $input = implode('.', $encodings);
        if (!openssl_sign($input, $sig, openssl_pkey_get_private($certs['pkey']), OPENSSL_ALGO_SHA256)) {
            throw new \InvalidArgumentException('Could not sign data');
        }

        $encodings[] = base64_encode($sig);
        return implode('.', $encodings);
    }

    /**
     * Does main authorization process
     * @param array $provider
     * @param array $params
     * @return bool
     */
    public function process(array $provider, $params)
    {
        $result = null;
        $this->hook->attach('oauth.process.before', $provider, $params, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $result = $this->callHandler('process', $provider, $params);
        $this->hook->attach('oauth.process.after', $provider, $params, $result, $this);
        return $result;
    }

    /**
     * Call a provider handler
     * @param string $handler
     * @param array $provider
     * @param array $params
     * @return mixed
     */
    protected function callHandler($handler, array $provider, $params)
    {
        try {
            $providers = $this->getProviders();
            return Handler::call($providers, $provider['id'], $handler, array($params, $provider, $this));
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Returns an array of requested token for "server-to-server" authorization
     * @param array $provider
     * @param array $jwt
     * @return mixed
     * @throws OauthAuthorizationException
     * @link https://developers.google.com/accounts/docs/OAuth2ServiceAccount
     */
    public function exchangeTokenServer($provider, $jwt)
    {
        if ($this->isValidToken($provider['id'])) {
            return $this->getToken($provider['id']);
        }

        $jwt += array(
            'scope' => $provider['scope'],
            'token_url' => $provider['url']['token']
        );

        try {
            $assertion = $this->generateJwt($jwt);
        } catch (\InvalidArgumentException $ex) {
            throw new OauthAuthorizationException('Failed to exchange Oauth service token: ' . $ex->getMessage());
        }

        $request = array(
            'assertion' => $assertion,
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer'
        );

        $token = $this->requestToken($provider, $request);
        $this->setToken($token, $provider['id']);
        return $token;
    }

}
