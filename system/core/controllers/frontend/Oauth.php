<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use OutOfBoundsException,
    UnexpectedValueException;
use gplcart\core\exceptions\Authorization as AuthorizationException;
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
     * A code received from a provider
     * @var string
     */
    protected $data_code;

    /**
     * A state hash received from a provider
     * @var string
     */
    protected $data_state;

    /**
     * An array of token data
     * @var array
     */
    protected $data_token;

    /**
     * A processed authorization result
     * @var mixed
     */
    protected $data_result;

    /**
     * A URL to redirect to after authorization
     * @var string
     */
    protected $data_url;

    /**
     * @param OauthModel $oauth
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
     * @throws OutOfBoundsException
     * @throws AuthorizationException
     * @throws UnexpectedValueException
     */
    protected function setReceivedDataOauth()
    {
        $this->data_code = $this->getQuery('code', '');
        $this->data_state = $this->getQuery('state', '');

        if (empty($this->data_code) || empty($this->data_state)) {
            $this->outputHttpStatus(403);
        }

        $parsed = $this->oauth->parseState($this->data_state);

        if (empty($parsed['id']) || !isset($parsed['url'])) {
            throw new OutOfBoundsException('Invalid provider Id and/or returning URL');
        }

        if (!$this->oauth->isValidState($this->data_state, $parsed['id'])) {
            throw new AuthorizationException('Invalid state code');
        }

        $this->data_provider = $this->oauth->getProvider($parsed['id']);

        if (empty($this->data_provider)) {
            throw new UnexpectedValueException('Failed to get Oauth provider data');
        }

        $domain = parse_url($parsed['url'], PHP_URL_HOST);

        if (!empty($domain)) {
            $store = $this->store->get($domain);
        }

        if (empty($store['status'])) {
            throw new OutOfBoundsException('Invalid domain in the redirect URL');
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
     * @throws OutOfBoundsException
     */
    protected function setTokenOauth()
    {
        $query = $this->oauth->getQueryToken($this->data_provider, array('code' => $this->data_code));
        $this->data_token = $this->oauth->exchangeToken($this->data_provider, $query);

        if (empty($this->data_token['access_token'])) {
            throw new OutOfBoundsException('Empty Oauth access token');
        }
    }

    /**
     * Set authorization result
     */
    protected function setResultOauth()
    {
        $this->data_result = $this->oauth->process($this->data_provider, array(
            'token' => $this->data_token['access_token']));

        if (empty($this->data_result)) {
            $this->data_result['severity'] = 'warning';
            $this->data_result['message'] = $this->text('An error occurred');
        }
    }

}
