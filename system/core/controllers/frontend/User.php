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
 * Handles incoming requests and outputs data related to frontend users
 */
class User extends FrontendController
{

    use \gplcart\core\traits\OauthTrait;

    /**
     * Oauth model instance
     * @var \gplcart\core\models\Oauth $oauth
     */
    protected $oauth;

    /**
     * The current user
     * @var array
     */
    protected $data_user = array();

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
    public function editLoginUser()
    {
        $this->controlAccessLoginUser();

        $this->setTitleEditLoginUser();
        $this->setBreadcrumbEditLoginUser();

        $this->setData('oauth_buttons', $this->getOauthButtonsTrait($this, $this->oauth));

        $this->submitLoginUser();
        $this->outputEditLoginUser();
    }

    /**
     * Controls access to the login form
     */
    protected function controlAccessLoginUser()
    {
        if (!empty($this->uid)) {
            $this->redirect("account/{$this->uid}");
        }
    }

    /**
     * Logs in a user
     * @return null
     */
    protected function submitLoginUser()
    {
        if (!$this->isPosted('login')) {
            return null;
        }

        $this->controlSpam();

        if ($this->validateLoginUser()) {
            $this->loginUser();
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
    protected function validateLoginUser()
    {
        $this->setSubmitted('user', null, false);
        $this->filterSubmitted(array('email', 'password'));

        $this->validateComponent('user_login');
        return !$this->hasErrors(false);
    }

    /**
     * Sets titles on the login page
     */
    protected function setTitleEditLoginUser()
    {
        $this->setTitle($this->text('Login'));
    }

    /**
     * Sets breadcrumbs on the login page
     */
    protected function setBreadcrumbEditLoginUser()
    {
        $breadcrumb = array(
            'text' => $this->text('Home'),
            'url' => $this->url('/')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the login page
     */
    protected function outputEditLoginUser()
    {
        $this->output('login');
    }

    /**
     * Displays the user registration page
     */
    public function editRegisterUser()
    {
        $this->controlAccessRegisterUser();

        $this->setTitleEditRegisterUser();
        $this->setBreadcrumbEditRegisterUser();

        $this->submitRegisterUser();
        $this->setData('password_limit', $this->user->getPasswordLength());

        $this->outputEditRegisterUser();
    }

    /**
     * Controls acccess to the register user page
     */
    protected function controlAccessRegisterUser()
    {
        if (!empty($this->uid)) {
            $this->url->redirect("account/{$this->uid}");
        }
    }

    /**
     * Registers a user using an array of submitted values
     * @return null
     */
    protected function submitRegisterUser()
    {
        if (!$this->isPosted('register')) {
            return null;
        }

        $this->controlSpam();

        if ($this->validateRegisterUser()) {
            $this->registerUser();
        }
    }

    /**
     * Registers a user
     */
    protected function registerUser()
    {
        $result = $this->user->register($this->getSubmitted());
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Validates an array of submitted data during registration
     * @return bool
     */
    protected function validateRegisterUser()
    {
        $this->setSubmitted('user', null, false);
        $this->filterSubmitted(array('email', 'password', 'name'));

        $this->setSubmitted('store_id', $this->store_id);

        $this->validateComponent('user');

        return !$this->hasErrors();
    }

    /**
     * Sets titles on the registration page
     */
    protected function setTitleEditRegisterUser()
    {
        $this->setTitle($this->text('Register'));
    }

    /**
     * Sets breadcrumbs on the user registration page
     */
    protected function setBreadcrumbEditRegisterUser()
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the registration page
     */
    protected function outputEditRegisterUser()
    {
        $this->output('register');
    }

    /**
     * Displays the password reset page
     */
    public function editResetPasswordUser()
    {
        $this->controlAccessResetPasswordUser();

        $this->setForgetfulUser();
        $this->setTitleEditResetPasswordUser();
        $this->setBreadcrumbEditResetPasswordUser();

        $this->setData('forgetful_user', $this->data_user);
        $this->setData('password_limit', $this->user->getPasswordLength());

        $this->submitResetPasswordUser();
        $this->outputEditResetPasswordUser();
    }

    /**
     * Controls access to the password reset page
     */
    protected function controlAccessResetPasswordUser()
    {
        if (!empty($this->uid)) {
            $this->url->redirect("account/{$this->uid}");
        }
    }

    /**
     * Returns a user from the current reset password URL
     * @return array
     */
    protected function setForgetfulUser()
    {
        $token = $this->getQuery('key', '', 'string');
        $user_id = $this->getQuery('user_id', '', 'string');

        if (empty($token) || !is_numeric($user_id)) {
            return array();
        }

        $user = $this->user->get($user_id);

        if (empty($user['status'])) {
            return array();
        }

        $data = $user['data'];

        if (empty($data['reset_password']['token'])) {
            $this->redirect('forgot');
        }

        if (!gplcart_string_equals($data['reset_password']['token'], $token)) {
            $this->outputHttpStatus(403);
        }

        if (empty($data['reset_password']['expires'])//
                || $data['reset_password']['expires'] < GC_TIME) {
            $this->redirect('forgot');
        }

        return $this->data_user = $user;
    }

    /**
     * Restores forgotten password
     * @return null
     */
    protected function submitResetPasswordUser()
    {
        if (!$this->isPosted('reset')) {
            return null;
        }

        $this->controlSpam();

        if ($this->validateResetPasswordUser()) {
            $this->resetPasswordUser();
        }
    }

    /**
     * Restores user password
     */
    protected function resetPasswordUser()
    {
        $result = $this->user->resetPassword($this->getSubmitted());
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Validates an array of submitted data to restore forgotten password
     * @return boolean
     */
    protected function validateResetPasswordUser()
    {
        $this->setSubmitted('user', null, false);
        $this->filterSubmitted(array('email', 'password'));

        $this->setSubmitted('user', $this->data_user);
        $this->validateComponent('user_reset_password');

        return !$this->hasErrors();
    }

    /**
     * Sets titles on the password reset page
     */
    protected function setTitleEditResetPasswordUser()
    {
        $this->setTitle($this->text('Reset password'));
    }

    /**
     * Sets breadcrumbs on the password reset page
     */
    protected function setBreadcrumbEditResetPasswordUser()
    {
        $breadcrumb = array(
            'text' => $this->text('Home'),
            'url' => $this->url('/')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the password reset page templates
     */
    protected function outputEditResetPasswordUser()
    {
        $this->output('forgot');
    }

    /**
     * Logs out a user
     */
    public function logoutUser()
    {
        $result = $this->user->logout();
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

}
