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
 * Handles incoming requests and outputs data related to logging in users
 */
class UserLogin extends Controller
{

    /**
     * User access model instance
     * @var \gplcart\core\models\UserAction $user_action
     */
    protected $user_action;

    /**
     * @param UserActionModel $user_action
     */
    public function __construct(UserActionModel $user_action)
    {
        parent::__construct();

        $this->user_action = $user_action;
    }

    /**
     * Displays the login page
     */
    public function editUserLogin()
    {
        $this->controlAccessUserLogin();
        $this->setTitleEditUserLogin();
        $this->setBreadcrumbEditUserLogin();

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
        $user = $this->getSubmitted();
        $result = $this->user_action->login($user);

        if (empty($result['user'])) {
            $this->setData('user', $user);
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

        return !$this->hasErrors();
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
