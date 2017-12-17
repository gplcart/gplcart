<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook,
    gplcart\core\Config;
use gplcart\core\models\Mail as MailModel,
    gplcart\core\models\User as UserModel,
    gplcart\core\models\Translation as TranslationModel;
use gplcart\core\helpers\Session as SessionHelper;

/**
 * Manages basic behaviors and data related to user actions
 */
class UserAction
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Mail model instance
     * @var \gplcart\core\models\Mail $mail
     */
    protected $mail;

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Session class instance
     * @var \gplcart\core\helpers\Session $session
     */
    protected $session;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param UserModel $user
     * @param TranslationModel $translation
     * @param MailModel $mail
     * @param SessionHelper $session
     */
    public function __construct(Hook $hook, Config $config, UserModel $user,
            TranslationModel $translation, MailModel $mail, SessionHelper $session)
    {
        $this->hook = $hook;
        $this->config = $config;

        $this->mail = $mail;
        $this->user = $user;
        $this->session = $session;
        $this->translation = $translation;
    }

    /**
     * Logs in a user
     * @param array $data
     * @param bool $check_password
     * @return array
     */
    public function login(array $data, $check_password = true)
    {
        $result = array();
        $this->hook->attach('user.login.before', $data, $check_password, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $result = array(
            'redirect' => null,
            'severity' => 'warning',
            'message' => $this->translation->text('Login failed. Make sure your e-mail and password are correct')
        );

        if (empty($data['email'])) {
            return $result;
        }

        $user = $this->user->getByEmail($data['email']);

        if (empty($user['status'])) {
            return $result;
        }

        if ($check_password && !$this->user->passwordMatches($data['password'], $user)) {
            return $result;
        }

        $this->session->regenerate(true);
        $this->session->set('user', $user);

        $result = array(
            'user' => $user,
            'message' => '',
            'severity' => 'success',
            'redirect' => $this->getLoginRedirect($user),
        );

        $this->hook->attach('user.login.after', $data, $check_password, $result, $this);
        return (array) $result;
    }

    /**
     * Registers a user
     * @param array $data
     * @return array
     */
    public function register(array $data)
    {
        $result = array();
        $this->hook->attach('user.register.before', $data, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $this->filterData($data);

        $data['login'] = $this->config->get('user_registration_login', true);
        $data['status'] = $this->config->get('user_registration_status', true);
        $data['user_id'] = $this->user->add($data);

        if (empty($data['user_id'])) {
            return array(
                'redirect' => null,
                'severity' => 'warning',
                'message' => $this->translation->text('An error occurred')
            );
        }

        $this->emailRegistration($data);

        $this->session->regenerate(true);

        $result = array(
            'redirect' => '/',
            'severity' => 'success',
            'user_id' => $data['user_id'],
            'message' => $this->translation->text('Your account has been created'));

        if (!empty($data['login']) && !empty($data['status'])) {
            $result = $this->login($data);
        }

        $this->hook->attach('user.register.after', $data, $result, $this);
        return (array) $result;
    }

    /**
     * Logs out the current user
     * @return array
     */
    public function logout()
    {
        $result = array();
        $user = $this->user->get($this->user->getId());
        $this->hook->attach('user.logout.before', $user, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $this->session->delete();

        $result = array(
            'user' => $user,
            'message' => '',
            'redirect' => 'login',
            'severity' => 'success'
        );

        $this->hook->attach('user.logout.after', $user, $result, $this);
        return (array) $result;
    }

    /**
     * Performs reset password operation
     * @param array $data
     * @return array
     */
    public function resetPassword(array $data)
    {
        $result = array();
        $this->hook->attach('user.reset.password.before', $data, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        if (empty($data['user']['user_id'])) {
            return array(
                'message' => '',
                'severity' => '',
                'redirect' => null
            );
        }

        if (isset($data['password'])) {
            $result = $this->resetPasswordFinish($data['user'], $data['password']);
        } else {
            $result = $this->resetPasswordStart($data['user']);
        }

        $this->session->regenerate(true);

        $this->hook->attach('user.reset.password.after', $data, $result, $this);
        return (array) $result;
    }

    /**
     * Sends e-mails on registration event
     * @param array $data
     */
    public function emailRegistration(array $data)
    {
        if ($this->config->get('user_registration_email_customer', true)) {
            $this->mail->set('user_registered_customer', array($data));
        }

        if ($this->config->get('user_registration_email_admin', true)) {
            $this->mail->set('user_registered_admin', array($data));
        }
    }

    /**
     * Start the password reset operation
     * @param array $user
     * @return array
     */
    protected function resetPasswordStart(array $user)
    {
        $user['data']['reset_password'] = array(
            'token' => gplcart_string_random(),
            'expires' => GC_TIME + $this->user->getResetPasswordLifespan(),
        );

        $this->user->update($user['user_id'], array('data' => $user['data']));
        $this->mail->set('user_reset_password', array($user));

        return array(
            'redirect' => 'forgot',
            'severity' => 'success',
            'message' => $this->translation->text('Password reset link has been sent to your E-mail')
        );
    }

    /**
     * Finish the password reset operation
     * @param array $user
     * @param string $password
     * @return array
     */
    protected function resetPasswordFinish(array $user, $password)
    {
        $user['password'] = $password;
        unset($user['data']['reset_password']);

        $this->user->update($user['user_id'], $user);
        $this->mail->set('user_changed_password', array($user));

        return array(
            'redirect' => 'login',
            'severity' => 'success',
            'message' => $this->translation->text('Your password has been successfully changed')
        );
    }

    /**
     * Remove all but allowed keys from an array of user data
     * @param array $data
     */
    protected function filterData(array &$data)
    {
        $allowed = array('name', 'email', 'password', 'store_id');
        $data = array_intersect_key($data, array_flip($allowed));
    }

    /**
     * Returns a redirect path for the user
     * @param array $user
     * @return string
     */
    protected function getLoginRedirect(array $user)
    {
        $redirect = "account/{$user['user_id']}";

        if (!empty($user['role_redirect'])) {
            $redirect = $user['role_redirect'];
        }

        if ($this->user->isSuperadmin($user['user_id'])) {
            $redirect = 'admin';
        }

        return $redirect;
    }

}
