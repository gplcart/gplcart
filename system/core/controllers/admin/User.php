<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\UserRole;

class User extends Controller
{

    /**
     * User role model instance
     * @var \core\models\UserRole $role
     */
    protected $role;

    /**
     * Constructor
     * @param UserRole $role
     */
    public function __construct(UserRole $role)
    {
        parent::__construct();
        $this->role = $role;
    }

    /**
     * Displays the users overview page
     */
    public function users()
    {

        $action = $this->request->post('action');
        $value = $this->request->post('value');
        $selected = $this->request->post('selected', array());

        if ($action) {
            $this->action($selected, $action, $value);
        }

        $query = $this->getFilterQuery();
        $total = $this->setPager($this->getTotalUsers($query), $query);

        $this->data['users'] = $this->getUsers($total, $query);
        $this->data['superadmin'] = $this->user->superadmin();
        $this->data['stores'] = $this->store->getNames();
        $this->data['roles'] = $this->role->getList();

        $filters = array('name', 'email', 'role_id', 'store_id', 'status', 'created');
        $this->setFilter($filters, $query);

        $this->setTitleUsers();
        $this->setBreadcrumbUsers();
        $this->outputUsers();
    }

    /**
     * Returns total numbers of users
     * @param array $query
     * @return integer
     */
    protected function getTotalUsers($query)
    {
        return $this->user->getList(array('count' => true) + $query);
    }

    /**
     * Renders the users overview page
     */
    protected function outputUsers()
    {
        $this->output('user/list');
    }

    /**
     * Sets breadcrumbs on the users overview page
     */
    protected function setBreadcrumbUsers()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
    }

    /**
     * Sets titles on the users overview page
     */
    protected function setTitleUsers()
    {
        $this->setTitle($this->text('Users'));
    }

    /**
     * Returns an array of users
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getUsers($limit, $query)
    {
        $stores = $this->store->getList();
        $users = $this->user->getList(array('limit' => $limit) + $query);

        foreach ($users as &$user) {
            $user['url'] = '';
            if (isset($stores[$user['store_id']])) {
                $store = $stores[$user['store_id']];
                $user['url'] = rtrim("{$store['scheme']}{$store['domain']}/{$store['basepath']}", "/") . "/account/{$user['user_id']}";
            }
        }

        return $users;
    }

    /**
     * Applies an action to users
     * @param array $selected
     * @param string $action
     * @param string $value
     * @return boolean
     */
    protected function action($selected, $action, $value)
    {
        $deleted = $updated = 0;
        foreach ($selected as $uid) {

            if ($this->user->isSuperadmin($uid)) {
                continue;
            }

            if ($action == 'status' && $this->access('user_edit')) {
                $updated += (int) $this->user->update($uid, array('status' => $value));
            }

            if ($action == 'delete' && $this->access('user_delete')) {
                $deleted += (int) $this->user->delete($uid);
            }
        }

        if ($updated) {
            $this->session->setMessage($this->text('Updated %num users', array('%num' => $updated)), 'success');
            return true;
        }

        if ($deleted) {
            $this->session->setMessage($this->text('Deleted %num users', array('%num' => $deleted)), 'success');
            return true;
        }

        return false;
    }

}
