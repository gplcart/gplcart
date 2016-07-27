<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use PDO;
use core\Model;
use core\classes\Cache;
use core\classes\Session;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to user roles
 */
class UserRole extends Model
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Session class instance
     * @var \core\classes\Session $session
     */
    protected $session;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param Session $session
     */
    public function __construct(ModelsLanguage $language, Session $session)
    {
        parent::__construct();

        $this->session = $session;
        $this->language = $language;
    }

    /**
     * Returns an array of all permissions
     * @return array
     */
    public function getPermissions()
    {
        $permissions = &Cache::memory('permissions');

        if (isset($permissions)) {
            return $permissions;
        }

        $permissions = $this->defaultPermissions();
        asort($permissions);

        $this->hook->fire('permissions', $permissions);
        return $permissions;
    }

    /**
     * Returns an array of roles or counts them
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(role_id)';
        }

        $sql .= ' FROM role WHERE role_id > 0';

        $where = array();

        if (isset($data['name'])) {
            $sql .= ' AND name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc'), true))) {
            switch ($data['sort']) {
                case 'name':
                    $sql .= " ORDER BY name {$data['order']}";
                    break;
                case 'status':
                    $sql .= " ORDER BY status {$data['order']}";
                    break;
                case 'role_id':
                    $sql .= " ORDER BY role_id {$data['order']}";
            }
        } else {
            $sql .= " ORDER BY name ASC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        $roles = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $role) {
            $role['permissions'] = unserialize($role['permissions']);
            $roles[$role['role_id']] = $role;
        }

        $this->hook->fire('roles', $roles);
        return $roles;
    }

    /**
     * Deletes a role
     * @param integer $role_id
     * @return boolean
     */
    public function delete($role_id)
    {
        $this->hook->fire('delete.role.before', $role_id);

        if (empty($role_id)) {
            return false;
        }

        if (!$this->canDelete($role_id)) {
            return false;
        }

        $result = $this->db->delete('role', array('role_id' => (int) $role_id));
        $this->hook->fire('delete.role.after', $role_id, $result);
        return (bool) $result;
    }

    /**
     * Whether the role canbe deleted
     * @param integer $role_id
     * @return boolean
     */
    public function canDelete($role_id)
    {
        $sth = $this->db->prepare('SELECT user_id FROM user WHERE role_id=:role_id');
        $sth->execute(array(':role_id' => (int) $role_id));
        return !$sth->fetchColumn();
    }

    /**
     * Adds a role to the database
     * @param array $data
     * @return integer|boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('add.role.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'name' => $data['name'],
            'status' => !empty($data['status']) ? (int) $data['status'] : 0,
            'permissions' => !empty($data['permissions']) ? serialize($data['permissions']) : serialize(array())
        );

        $role_id = $this->db->insert('role', $values);

        $this->hook->fire('add.role.after', $data, $role_id);
        return $role_id;
    }

    /**
     * Updates a role
     * @param integer $role_id
     * @param array $data
     * @return boolean
     */
    public function update($role_id, array $data)
    {
        $this->hook->fire('update.role.before', $role_id, $data);

        if (empty($role_id)) {
            return false;
        }

        $values = array();

        if (isset($data['name'])) {
            $values['name'] = $data['name'];
        }

        if (isset($data['status'])) {
            $values['status'] = (int) $data['status'];
        }

        if (isset($data['permissions'])) {
            $values['permissions'] = serialize((array) $data['permissions']);
        }

        $result = false;

        if (!empty($values)) {
            $result = $this->db->update('role', $values, array('role_id' => (int) $role_id));
            $this->hook->fire('update.role.after', $role_id, $data, $result);
        }

        return (bool) $result;
    }

    /**
     * Loads a role from the database
     * @param integer $role_id
     * @return array
     */
    public function get($role_id)
    {
        $role = &Cache::memory("role.$role_id");

        if (isset($role)) {
            return $role;
        }

        $this->hook->fire('get.role.before', $role_id);

        $sth = $this->db->prepare('SELECT * FROM role WHERE role_id=:role_id');
        $sth->execute(array(':role_id' => (int) $role_id));

        $role = $sth->fetch(PDO::FETCH_ASSOC);

        if (isset($role['permissions'])) {
            $role['permissions'] = unserialize($role['permissions']);
        }

        $this->hook->fire('get.role.after', $role_id, $role);
        return $role;
    }

    /**
     * Returns an array of default permissions
     * @return array
     */
    protected function defaultPermissions()
    {
        $permissions = array(
            'admin' => $this->language->text('Admin: access'),
            'product' => $this->language->text('Product: access'),
            'product_add' => $this->language->text('Product: add'),
            'product_edit' => $this->language->text('Product: edit'),
            'product_delete' => $this->language->text('Product: delete'),
            'product_class' => $this->language->text('Product class: access'),
            'product_class_add' => $this->language->text('Product class: add'),
            'product_class_edit' => $this->language->text('Product class: edit'),
            'product_class_delete' => $this->language->text('Product class: delete'),
            'price_rule' => $this->language->text('Price rule: access'),
            'price_rule_add' => $this->language->text('Price rule: add'),
            'price_rule_edit' => $this->language->text('Price rule: edit'),
            'price_rule_delete' => $this->language->text('Price rule: delete'),
            'page' => $this->language->text('Page: access'),
            'page_add' => $this->language->text('Page: add'),
            'page_edit' => $this->language->text('Page: edit'),
            'page_delete' => $this->language->text('Page: delete'),
            'review' => $this->language->text('Review: access'),
            'review_add' => $this->language->text('Review: add'),
            'review_edit' => $this->language->text('Review: edit'),
            'review_delete' => $this->language->text('Review: delete'),
            'category' => $this->language->text('Category: access'),
            'category_add' => $this->language->text('Category: add'),
            'category_edit' => $this->language->text('Category: edit'),
            'category_delete' => $this->language->text('Category: delete'),
            'category_group' => $this->language->text('Category group: access'),
            'category_group_add' => $this->language->text('Category group: add'),
            'category_group_edit' => $this->language->text('Category group: edit'),
            'category_group_delete' => $this->language->text('Category group: delete'),
            'wishlist_add' => $this->language->text('Wishlist: add'),
            'wishlist_delete' => $this->language->text('Wishlist: delete'),
            'user' => $this->language->text('User: access'),
            'user_add' => $this->language->text('User: add'),
            'user_edit' => $this->language->text('User: edit'),
            'user_delete' => $this->language->text('User: delete'),
            'user_role' => $this->language->text('User role: access'),
            'user_role_add' => $this->language->text('User role: add'),
            'user_role_edit' => $this->language->text('User role: edit'),
            'user_role_delete' => $this->language->text('User role: delete'),
            'field' => $this->language->text('Field: access'),
            'field_edit' => $this->language->text('Field: edit'),
            'field_add' => $this->language->text('Field: add'),
            'field_delete' => $this->language->text('Field: delete'),
            'field_value' => $this->language->text('Field value: delete'),
            'field_value_edit' => $this->language->text('Field value: edit'),
            'field_value_add' => $this->language->text('Field value: add'),
            'field_value_delete' => $this->language->text('Field value: delete'),
            'order' => $this->language->text('Order: access'),
            'order_add' => $this->language->text('Order: add'),
            'order_edit' => $this->language->text('Order: edit'),
            'order_delete' => $this->language->text('Order: delete'),
            'module' => $this->language->text('Module: access'),
            'module_edit' => $this->language->text('Module: edit'),
            'module_install' => $this->language->text('Module: install'),
            'module_uninstall' => $this->language->text('Module: uninstall'),
            'module_enable' => $this->language->text('Module: enable'),
            'module_disable' => $this->language->text('Module: disable'),
            'module_upload' => $this->language->text('Module: upload'),
            'store' => $this->language->text('Store: access'),
            'store_edit' => $this->language->text('Store: edit'),
            'store_add' => $this->language->text('Store: add'),
            'store_delete' => $this->language->text('Store: delete'),
            'dashboard' => $this->language->text('Dashboard: access'),
            'dashboard_edit' => $this->language->text('Dashboard: edit'),
            'image_style' => $this->language->text('Image style: access'),
            'image_style_edit' => $this->language->text('Image style: edit'),
            'image_style_add' => $this->language->text('Image style: add'),
            'language' => $this->language->text('Language: access'),
            'language_add' => $this->language->text('Language: add'),
            'language_edit' => $this->language->text('Language: edit'),
            'language_delete' => $this->language->text('Language: delete'),
            'currency' => $this->language->text('Currency: access'),
            'currency_add' => $this->language->text('Currency: add'),
            'currency_edit' => $this->language->text('Currency: edit'),
            'currency_delete' => $this->language->text('Currency: delete'),
            'country' => $this->language->text('Country: access'),
            'country_edit' => $this->language->text('Country: edit'),
            'country_add' => $this->language->text('Country: add'),
            'country_delete' => $this->language->text('Country: delete'),
            'country_format' => $this->language->text('Country format: access'),
            'country_format_add' => $this->language->text('Country format: add'),
            'country_format_edit' => $this->language->text('Country format: edit'),
            'country_format_delete' => $this->language->text('Country format: delete'),
            'state' => $this->language->text('Country state: access'),
            'state_add' => $this->language->text('Country state: add'),
            'state_edit' => $this->language->text('Country state: edit'),
            'state_delete' => $this->language->text('Country state: delete'),
            'city' => $this->language->text('City: access'),
            'city_add' => $this->language->text('City: add'),
            'city_edit' => $this->language->text('City: edit'),
            'city_delete' => $this->language->text('City: delete'),
            'report_system' => $this->language->text('Report: system events'),
            'report_ga' => $this->language->text('Report: Google Analytics'),
            'file' => $this->language->text('File: access'),
            'file_add' => $this->language->text('File: add'),
            'file_edit' => $this->language->text('File: edit'),
            'file_delete' => $this->language->text('File: delete'),
            'file_upload' => $this->language->text('File: upload'),
            'cron' => $this->language->text('Cron: access'),
            'notification' => $this->language->text('Notification: access'),
            'import' => $this->language->text('Import: access'),
            'export' => $this->language->text('Export: access'),
            'settings' => $this->language->text('Settings: access'),
            'search' => $this->language->text('Search: access'),
            'search_edit' => $this->language->text('Search: edit'),
            'alias' => $this->language->text('Alias: access')
        );

        return $permissions;
    }

}
