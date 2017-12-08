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
 * Handles incoming requests and outputs data related to logging in users
 */
class UserLogin extends FrontendController
{

    /**
     * Oauth model instance
     * @var \gplcart\core\models\Oauth $oauth
     */
    protected $oauth;

    /**
     * @param OauthModel $oauth
     */
    public function __construct(OauthModel $oauth)
    {
        parent::__construct();

        $this->oauth = $oauth;
    }

    /**
     * Displays the login page
     */
    public function editUserLogin()
    {
        $this->controlAccessUserLogin();

        $this->setTitleEditUserLogin();
        $this->setBreadcrumbEditUserLogin();

        $this->setData('oauth_buttons', $this->getWidgetOauthButtons($this->oauth));

        $this->submitUserLogin();
        $this->outputEditUserLogin();
    }

    /**
     * Controls access to the login form
     */
    protected function controlAccessUserLogin()
    {
        if (!empty($this->uid)) {
            $this->redirect("account/{$this->uid}");
        }
    }

    /**
     * Logs in a user
     */
    protected function submitUserLogin()
    {
        if ($this->isPosted('login')) {
            $this->controlSpam();
            if ($this->validateUserLogin()) {
                $this->loginUser();
            }
        }
    }

    /**
     * Log in a user
     */
    protected function loginUser()
    {
        $result = $this->user->login($this->getSubmitted());

        if (empty($result['user'])) {
            $this->setMessage($result['message'], $result['severity']);
        } else {
            $this->redirect($result['redirect'], $result['message'], $result['severity']);
        }
    }

    /**
     * Validates submitted login credentials
     * @return bool
     */
    protected function validateUserLogin()
    {
        $this->setSubmitted('user', null, false);
        $this->filterSubmitted(array('email', 'password'));
        $this->validateComponent('user_login');

        return !$this->hasErrors(false);
    }

    /**
     * Sets titles on the login page
     */
    protected function setTitleEditUserLogin()
    {
        $this->setTitle($this->text('Login'));
    }

    /**
     * Sets breadcrumbs on the login page
     */
    protected function setBreadcrumbEditUserLogin()
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the login page
     */
    protected function outputEditUserLogin()
    {
        $this->output('login');
    }

}
