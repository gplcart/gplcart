<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\UserAccess as UserAccessModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to user account creation
 */
class UserRegister extends FrontendController
{

    /**
     * User access model instance
     * @var \gplcart\core\models\UserAccess $user_access
     */
    protected $user_access;

    /**
     * @param UserAccessModel $user_access
     */
    public function __construct(UserAccessModel $user_access)
    {
        parent::__construct();

        $this->user_access = $user_access;
    }

    /**
     * Displays the user registration page
     */
    public function editUserRegister()
    {
        $this->controlAccessUserRegister();

        $this->setTitleEditUserRegister();
        $this->setBreadcrumbEditUserRegister();

        $this->submitUserRegister();
        $this->setData('password_limit', $this->user->getPasswordLength());

        $this->outputEditUserRegister();
    }

    /**
     * Controls access to the register user page
     */
    protected function controlAccessUserRegister()
    {
        if (!empty($this->uid)) {
            $this->redirect("account/{$this->uid}");
        }
    }

    /**
     * Registers a user using an array of submitted values
     */
    protected function submitUserRegister()
    {
        if ($this->isPosted('register')) {
            $this->controlSpam();
            if ($this->validateUserRegister()) {
                $this->userRegister();
            }
        }
    }

    /**
     * Registers a user
     */
    protected function userRegister()
    {
        $result = $this->user_access->register($this->getSubmitted());
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Validates an array of submitted data during registration
     * @return bool
     */
    protected function validateUserRegister()
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
    protected function setTitleEditUserRegister()
    {
        $this->setTitle($this->text('Register'));
    }

    /**
     * Sets breadcrumbs on the user registration page
     */
    protected function setBreadcrumbEditUserRegister()
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
    protected function outputEditUserRegister()
    {
        $this->output('register');
    }

}
