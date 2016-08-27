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
    public function loginUser()
    {
        $this->controlAccessLoginUser();

        $this->submitLoginUser();

        $honeypot = $this->getHoneypot();
        $this->setData('honeypot', $honeypot);

        $this->setTitleLoginUser();
        $this->setBreadcrumbLoginUser();
        $this->outputLoginUser();
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
     */
    protected function submitLoginUser()
    {
        if (!$this->isPosted('login')) {
            return;
        }

        $this->controlSpam('login');
        $this->setSubmitted('user', null, 'raw');
        $this->validateLoginUser();

        if (!$this->hasErrors('user', false)) {
            $result = $this->getSubmitted('redirect');
            $this->redirect($result['redirect'], $result['message'], $result['severity']);
        }
    }

    /**
     * Validates submitted login credentials
     */
    protected function validateLoginUser()
    {
        $email = $this->getSubmitted('email');
        $password = $this->getSubmitted('password');
        $result = $this->user->login($email, $password);

        if (empty($result)) {
            $message = $this->text('Invalid E-mail and/or password');
            $this->setMessage($message, 'danger');
            $this->setError('login', $message);
            return;
        }

        $this->setSubmitted('redirect', $result);
    }

    /**
     * Sets titles on the login page
     */
    protected function setTitleLoginUser()
    {
        $this->setTitle($this->text('Login'));
    }

    /**
     * Sets breadcrumbs on the login page
     */
    protected function setBreadcrumbLoginUser()
    {
        $breadcrumbs[] = array(
            'text' => $this->text('Home'),
            'url' => $this->url('/'));

        $breadcrumbs[] = array(
            'text' => $this->text('Register account'),
            'url' => $this->url('register'));

        $breadcrumbs[] = array(
            'text' => $this->text('Forgot password'),
            'url' => $this->url('forgot'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the login page
     */
    protected function outputLoginUser()
    {
        $this->output('login');
    }

    /**
     * Displays the user registration page
     */
    public function registerUser()
    {
        $this->controlAccessRegisterUser();

        $this->submitRegisterUser();

        $honeypot = $this->getHoneypot();
        $limit = $this->user->getPasswordLength();

        $this->setData('honeypot', $honeypot);
        $this->setData('password_limit', $limit);

        $this->setTitleRegisterUser();
        $this->setBreadcrumbRegisterUser();
        $this->outputRegisterUser();
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
            return;
        }

        $this->controlSpam('register');
        $this->setSubmitted('user', null, 'raw');
        $this->validateRegisterUser();

        if (!$this->hasErrors('user')) {
            $submitted = $this->getSubmitted();
            $result = $this->user->register($submitted);
            $this->redirect($result['redirect'], $result['message'], $result['severity']);
        }
    }

    /**
     * Validates an array of submitted data during registration
     */
    protected function validateRegisterUser()
    {
        $this->addValidator('email', array(
            'required' => array(),
            'email' => array(),
            'user_email_unique' => array()
        ));

        $this->addValidator('name', array(
            'length' => array('min' => 1, 'max' => 255),
            'user_name_unique' => array()
        ));

        $options = $this->user->getPasswordLength();
        $options['required'] = true;

        $this->addValidator('password', array(
            'length' => $options
        ));

        $this->setValidators();
    }

    /**
     * Sets titles on the registration page
     */
    protected function setTitleRegisterUser()
    {
        $this->setTitle($this->text('Register'));
    }

    /**
     * Sets breadcrumbs on the user registration page
     */
    protected function setBreadcrumbRegisterUser()
    {
        $breadcrumbs[] = array(
            'text' => $this->text('Home'),
            'url' => $this->url('/'));

        $breadcrumbs[] = array(
            'text' => $this->text('Login'),
            'url' => $this->url('login'));

        $breadcrumbs[] = array(
            'text' => $this->text('Forgot password'),
            'url' => $this->url('forgot'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the registration page
     */
    protected function outputRegisterUser()
    {
        $this->output('register');
    }

    /**
     * Displays the password reset page
     */
    public function resetPasswordUser()
    {
        $this->controlAccessResetPasswordUser();

        $honeypot = $this->getHoneypot();
        $user = $this->getForgetfulUser();
        $limit = $this->user->getPasswordLength();

        $this->setData('honeypot', $honeypot);
        $this->setData('forgetful_user', $user);
        $this->setData('password_limit', $limit);

        $this->submitResetPasswordUser($user);

        $this->setTitleResetPasswordUser();
        $this->setBreadcrumbResetPasswordUser();
        $this->outputResetPasswordUser();
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
            return;
        }

        $this->controlSpam('reset_password');
        $this->setSubmitted('user', null, 'raw');

        $this->validateResetPasswordUser($user);

        if (!$this->hasErrors('user')) {
            $submitted = $this->getSubmitted();
            $result = $this->user->resetPassword($submitted);
            $this->redirect($result['redirect'], $result['message'], $result['severity']);
        }
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
    }

    /**
     * Sets titles on the password reset page
     */
    protected function setTitleResetPasswordUser()
    {
        $this->setTitle($this->text('Reset password'));
    }

    /**
     * Sets breadcrumbs on the password reset page
     */
    protected function setBreadcrumbResetPasswordUser()
    {
        $breadcrumbs[] = array(
            'text' => $this->text('Home'),
            'url' => $this->url('/'));

        $breadcrumbs[] = array(
            'text' => $this->text('Login'),
            'url' => $this->url('login'));

        $breadcrumbs[] = array(
            'text' => $this->text('Register account'),
            'url' => $this->url('register'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the password reset page templates
     */
    protected function outputResetPasswordUser()
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
