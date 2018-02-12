<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\UserAction as UserActionModel;

/**
 * Handles incoming requests and outputs data related to resetting user passwords
 */
class UserForgot extends Controller
{

    /**
     * User access model instance
     * @var \gplcart\core\models\UserAction $user_action
     */
    protected $user_action;

    /**
     * The current user
     * @var array
     */
    protected $data_user = array();

    /**
     * @param UserActionModel $user_action
     */
    public function __construct(UserActionModel $user_action)
    {
        parent::__construct();

        $this->user_action = $user_action;
    }

    /**
     * Displays the password reset page
     */
    public function editUserForgot()
    {
        $this->controlAccessUserForgot();

        $this->setUserForgot();
        $this->setTitleEditUserForgot();
        $this->setBreadcrumbEditUserForgot();

        $this->setData('forgetful_user', $this->data_user);
        $this->setData('password_limit', $this->user->getPasswordLength());

        $this->submitUserForgot();
        $this->outputEditUserForgot();
    }

    /**
     * Controls access to the password reset page
     */
    protected function controlAccessUserForgot()
    {
        if (!empty($this->uid)) {
            $this->redirect("account/{$this->uid}");
        }
    }

    /**
     * Returns a user from the current reset password URL
     */
    protected function setUserForgot()
    {
        $token = $this->getQuery('key');
        $user_id = $this->getQuery('user_id');

        $this->data_user = array();

        if (!empty($token) && is_numeric($user_id)) {
            $this->data_user = $this->user->get($user_id);
            $this->controlTokenUserForgot($this->data_user, $token);
        }
    }

    /**
     * Validates the token and its expiration time set for the user
     * @param array $user
     * @param string $token
     */
    protected function controlTokenUserForgot($user, $token)
    {
        if (!empty($user['status'])) {

            if (empty($user['data']['reset_password']['token'])) {
                $this->redirect('forgot');
            }

            if (!gplcart_string_equals($user['data']['reset_password']['token'], $token)) {
                $this->outputHttpStatus(403);
            }

            if (empty($user['data']['reset_password']['expires'])
                || $user['data']['reset_password']['expires'] < GC_TIME) {
                $this->redirect('forgot');
            }
        }
    }

    /**
     * Handles a submitted data when a user wants to reset its password
     */
    protected function submitUserForgot()
    {
        if ($this->isPosted('reset')) {

            $this->controlSpam();

            if ($this->validateUserForgot()) {
                $this->resetPasswordUser();
            }
        }
    }

    /**
     * Reset user's password
     */
    protected function resetPasswordUser()
    {
        $result = $this->user_action->resetPassword($this->getSubmitted());
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Validates a submitted data when a user wants to reset its password
     * @return boolean
     */
    protected function validateUserForgot()
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
    protected function setTitleEditUserForgot()
    {
        $this->setTitle($this->text('Reset password'));
    }

    /**
     * Sets breadcrumbs on the password reset page
     */
    protected function setBreadcrumbEditUserForgot()
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the password reset page templates
     */
    protected function outputEditUserForgot()
    {
        $this->output('forgot');
    }

}
