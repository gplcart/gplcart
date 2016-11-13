<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\classes\Tool;
use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to frontend users
 */
class User extends FrontendController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Displays the login page
     */
    public function editLoginUser()
    {
        $this->controlAccessLoginUser();

        $this->submitLoginUser();

        $honeypot = $this->getHoneypot();
        $this->setData('honeypot', $honeypot);

        $this->setTitleEditLoginUser();
        $this->setBreadcrumbEditLoginUser();
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

        $this->controlSpam('login');
        $this->setSubmitted('user', null, 'raw');
        $this->validateLoginUser();

        if (!$this->hasErrors('user', false)) {
            $this->loginUser();
        }

        return null;
    }

    /**
     * Logs in a user
     * @return null
     */
    protected function loginUser()
    {
        $data = $this->getSubmitted();
        $result = $this->user->login($data);

        if (empty($result['user'])) {
            $this->setMessage($result['message'], $result['severity']);
            return null;
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
        return null;
    }

    /**
     * Validates submitted login credentials
     */
    protected function validateLoginUser()
    {
        $this->validate('user_login');
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
            'url' => $this->url('/'));

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

        $this->submitRegisterUser();

        $honeypot = $this->getHoneypot();
        $limit = $this->user->getPasswordLength();

        $this->setData('honeypot', $honeypot);
        $this->setData('password_limit', $limit);

        $this->setTitleEditRegisterUser();
        $this->setBreadcrumbEditRegisterUser();
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

        $this->controlSpam('register');
        $this->setSubmitted('user', null, 'raw');
        $this->validateRegisterUser();

        if (!$this->hasErrors('user')) {
            $this->registerUser();
        }

        return null;
    }

    /**
     * Registers a user
     */
    protected function registerUser()
    {
        $submitted = $this->getSubmitted();
        $result = $this->user->register($submitted);
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Validates an array of submitted data during registration
     */
    protected function validateRegisterUser()
    {
        $this->setSubmitted('store_id', $this->store_id);
        $this->validate('user');
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
            'text' => $this->text('Home'),
            'url' => $this->url('/'));

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
    public function EditResetPasswordUser()
    {
        $this->controlAccessResetPasswordUser();

        $honeypot = $this->getHoneypot();
        $user = $this->getForgetfulUser();
        $limit = $this->user->getPasswordLength();

        $this->setData('honeypot', $honeypot);
        $this->setData('forgetful_user', $user);
        $this->setData('password_limit', $limit);

        $this->submitResetPasswordUser($user);

        $this->setTitleEditResetPasswordUser();
        $this->setBreadcrumbEditResetPasswordUser();
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
     * @return boolean|array
     */
    protected function getForgetfulUser()
    {
        $token = (string) $this->request->get('key', '');
        $user_id = (string) $this->request->get('user_id', '');

        if (empty($token) || empty($user_id)) {
            return array();
        }

        $user = $this->user->get($user_id);

        // User blocked or not found
        if (empty($user['status'])) {
            return array();
        }

        $data = $user['data'];

        // No recovery data is set
        if (empty($data['reset_password'])) {
            $this->redirect('forgot');
        }

        // Invalid token
        if (!Tool::hashEquals($data['reset_password']['token'], $token)) {
            $this->outputError(403);
        }

        // Expired
        if ((int) $data['reset_password']['expires'] < GC_TIME) {
            return $this->redirect('forgot');
        }

        return $user;
    }

    /**
     * Restores forgotten password
     * @param array $user
     * @return null
     */
    protected function submitResetPasswordUser(array $user)
    {
        if (!$this->isPosted('reset')) {
            return null;
        }

        $this->controlSpam('reset_password');
        $this->setSubmitted('user', null, 'raw');

        $this->validateResetPasswordUser($user);

        if (!$this->hasErrors('user')) {
            $this->resetPasswordUser();
        }

        return null;
    }

    /**
     * Restores user password
     */
    protected function resetPasswordUser()
    {
        $submitted = $this->getSubmitted();
        $result = $this->user->resetPassword($submitted);
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Validates an array of submitted data to restore forgotten password
     * @param array $user
     * @return boolean
     */
    protected function validateResetPasswordUser(array $user)
    {
        if ($this->isSubmitted('password')) {

            $options = $this->user->getPasswordLength();
            $options['required'] = true;

            $this->addValidator('password', array(
                'length' => $options
            ));
        }

        if ($this->isSubmitted('email')) {
            $this->addValidator('email', array(
                'required' => array(),
                'user_email_exists' => array('status' => true)
            ));
        }

        $errors = $this->setValidators($user);

        if (empty($errors)) {
            $email_user = $this->getValidatorResult('email');

            if (isset($email_user)) {
                $user = $email_user;
            }

            $this->setSubmitted('user', $user);
        }

        $this->validate('user_reset_password');
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
            'url' => $this->url('/'));

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
