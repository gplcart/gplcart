<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\Container;

/**
 * Parent class for models
 */
class Model
{

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hook = Container::instance('core\\Hook');
        $this->config = Container::instance('core\\Config');
        $this->db = $this->config->getDb();
    }

    /**
     * Returns config instance
     * @return \core\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns PDO database instance
     * @return \core\classes\Database
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Returns an array of data used to create tables in the database
     * @param string|null $table
     * @return array
     */
    protected function getDbScheme($table = null)
    {
        $tables['address'] = array(
            'fields' => array(
                'address_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'state_id' => 'int(10) NOT NULL',
                'created' => 'int(10) NOT NULL',
                'country' => 'varchar(2) NOT NULL',
                'city_id' => 'varchar(255) NOT NULL',
                'address_1' => 'varchar(255) NOT NULL',
                'address_2' => 'varchar(255) NOT NULL DEFAULT ""',
                'phone' => 'varchar(255) NOT NULL',
                'type' => 'varchar(255) NOT NULL DEFAULT "shipping"',
                'user_id' => 'varchar(255) NOT NULL',
                'middle_name' => 'varchar(255) NOT NULL DEFAULT ""',
                'last_name' => 'varchar(255) NOT NULL',
                'first_name' => 'varchar(255) NOT NULL',
                'postcode' => 'varchar(50) NOT NULL',
                'company' => 'varchar(255) NOT NULL',
                'fax' => 'varchar(50) NOT NULL',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['alias'] = array(
            'fields' => array(
                'alias_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'id_value' => 'int(10) NOT NULL',
                'id_key' => 'varchar(50) NOT NULL',
                'alias' => 'varchar(255) NOT NULL'
            )
        );

        $tables['wishlist'] = array(
            'fields' => array(
                'wishlist_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'product_id' => 'int(10) NOT NULL',
                'created' => 'int(10) NOT NULL',
                'user_id' => 'varchar(255) NOT NULL'
            )
        );

        $tables['cart'] = array(
            'fields' => array(
                'cart_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'store_id' => 'int(10) NOT NULL',
                'created' => 'int(10) NOT NULL',
                'modified' => 'int(10) NOT NULL',
                'product_id' => 'int(10) NOT NULL',
                'quantity' => 'int(2) NOT NULL DEFAULT 1',
                'order_id' => 'int(10) NOT NULL DEFAULT "0"',
                'user_id' => 'varchar(255) NOT NULL',
                'sku' => 'varchar(255) NOT NULL',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['category'] = array(
            'fields' => array(
                'category_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'weight' => 'int(2) NOT NULL DEFAULT 0',
                'status' => 'int(1) NOT NULL',
                'category_group_id' => 'int(10) NOT NULL',
                'parent_id' => 'int(10) NOT NULL',
                'meta_title' => 'varchar(255) NOT NULL DEFAULT ""',
                'title' => 'varchar(255) NOT NULL',
                'meta_description' => 'varchar(255) NOT NULL DEFAULT ""',
                'description_1' => 'text NOT NULL',
                'description_2' => 'text NOT NULL',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['category_group'] = array(
            'fields' => array(
                'category_group_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'store_id' => 'int(10) NOT NULL',
                'type' => 'varchar(50) NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['category_group_translation'] = array(
            'fields' => array(
                'translation_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'category_group_id' => 'int(10) NOT NULL',
                'language' => 'varchar(2) NOT NULL',
                'title' => 'varchar(255) NOT NULL'
            )
        );

        $tables['category_translation'] = array(
            'fields' => array(
                'translation_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'category_id' => 'int(10) NOT NULL',
                'language' => 'varchar(4) NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'meta_title' => 'varchar(255) NOT NULL',
                'description_1' => 'text NOT NULL',
                'meta_description' => 'text NOT NULL',
                'description_2' => 'text NOT NULL'
            )
        );

        $tables['city'] = array(
            'fields' => array(
                'city_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'name' => 'varchar(255) NOT NULL',
                'state_id' => 'int(10) NOT NULL',
                'country' => 'varchar(2) NOT NULL',
                'status' => 'int(1) NOT NULL DEFAULT 0',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['country'] = array(
            'fields' => array(
                'code' => 'varchar(2) PRIMARY KEY',
                'name' => 'varchar(255) NOT NULL',
                'native_name' => 'varchar(255) NOT NULL',
                'status' => 'int(1) NOT NULL DEFAULT 0',
                'weight' => 'int(2) NOT NULL DEFAULT 0',
                'format' => 'blob NOT NULL'
            ),
            'serialize' => array('format')
        );

        $tables['collection'] = array(
            'fields' => array(
                'collection_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'title' => 'varchar(255) NOT NULL',
                'description' => 'text NOT NULL',
                'type' => 'varchar(50) NOT NULL',
                'store_id' => 'int(10) NOT NULL',
                'status' => 'int(1) NOT NULL DEFAULT 0',
            )
        );

        $tables['collection_translation'] = array(
            'fields' => array(
                'translation_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'collection_id' => 'int(10) NOT NULL',
                'language' => 'varchar(4) NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'description' => 'text NOT NULL',
            )
        );

        $tables['collection_item'] = array(
            'fields' => array(
                'collection_item_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'collection_id' => 'int(10) NOT NULL',
                'id_value' => 'int(10) NOT NULL',
                'id_key' => 'varchar(50) NOT NULL',
                'weight' => 'int(2) NOT NULL DEFAULT 0',
                'status' => 'int(1) NOT NULL DEFAULT 0',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['field'] = array(
            'fields' => array(
                'field_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'weight' => 'int(2) NOT NULL DEFAULT 0',
                'type' => 'varchar(50) NOT NULL',
                'widget' => 'varchar(50) NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['field_translation'] = array(
            'fields' => array(
                'translation_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'field_id' => 'int(10) NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'language' => 'varchar(4) NOT NULL'
            )
        );

        $tables['field_value'] = array(
            'fields' => array(
                'field_value_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'field_id' => 'int(10) NOT NULL',
                'weight' => 'int(2) NOT NULL DEFAULT 0',
                'file_id' => 'int(10) NOT NULL',
                'color' => 'varchar(10) NOT NULL',
                'title' => 'varchar(255) NOT NULL'
            )
        );

        $tables['field_value_translation'] = array(
            'fields' => array(
                'translation_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'field_id' => 'int(10) NOT NULL',
                'field_value_id' => 'int(10) NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'language' => 'varchar(4) NOT NULL'
            )
        );

        $tables['file'] = array(
            'fields' => array(
                'file_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'id_value' => 'int(10) NOT NULL',
                'created' => 'int(10) NOT NULL',
                'weight' => 'int(2) NOT NULL DEFAULT 0',
                'id_key' => 'varchar(50) NOT NULL',
                'file_type' => 'varchar(50) NOT NULL',
                'title' => 'varchar(255) NOT NULL DEFAULT ""',
                'mime_type' => 'varchar(255) NOT NULL DEFAULT ""',
                'path' => 'text NOT NULL',
                'description' => 'text NOT NULL'
            )
        );

        $tables['file_translation'] = array(
            'fields' => array(
                'translation_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'file_id' => 'int(10) NOT NULL',
                'language' => 'varchar(4) NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'description' => 'text NOT NULL'
            )
        );

        $tables['history'] = array(
            'fields' => array(
                'user_id' => 'int(10) NOT NULL',
                'id_value' => 'int(10) NOT NULL',
                'time' => 'int(10) NOT NULL',
                'history_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'id_key' => 'varchar(32) NOT NULL'
            )
        );

        $tables['log'] = array(
            'fields' => array(
                'time' => 'int(10) NOT NULL',
                'text' => 'text NOT NULL',
                'log_id' => 'varchar(50) PRIMARY KEY',
                'type' => 'varchar(255) NOT NULL',
                'severity' => 'varchar(255) NOT NULL',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['module'] = array(
            'fields' => array(
                'status' => 'int(1) NOT NULL',
                'weight' => 'int(2) NOT NULL',
                'module_id' => 'varchar(50) PRIMARY KEY',
                'settings' => 'blob NOT NULL'
            ),
            'serialize' => array('settings')
        );

        $tables['option_combination'] = array(
            'fields' => array(
                'product_id' => 'int(10) NOT NULL',
                'stock' => 'int(10) NOT NULL',
                'file_id' => 'int(10) NOT NULL',
                'price' => 'int(10) NOT NULL',
                'combination_id' => 'varchar(255) PRIMARY KEY'
            )
        );

        $tables['orders'] = array(
            'fields' => array(
                'order_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'store_id' => 'int(10) NOT NULL DEFAULT 0',
                'shipping_address' => 'int(10) NOT NULL',
                'payment_address' => 'int(10) NOT NULL',
                'created' => 'int(10) NOT NULL',
                'modified' => 'int(10) NOT NULL',
                'total' => 'int(10) NOT NULL',
                'creator' => 'int(10) NOT NULL DEFAULT 0',
                'currency' => 'varchar(4) NOT NULL',
                'user_id' => 'varchar(255) NOT NULL',
                'payment' => 'varchar(255) NOT NULL',
                'shipping' => 'varchar(255) NOT NULL',
                'status' => 'varchar(50) NOT NULL',
                'comment' => 'text NOT NULL',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['page'] = array(
            'fields' => array(
                'page_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'user_id' => 'int(10) NOT NULL',
                'status' => 'int(1) NOT NULL DEFAULT 1',
                'front' => 'int(1) NOT NULL DEFAULT 0',
                'store_id' => 'int(10) NOT NULL',
                'category_id' => 'int(10) NOT NULL DEFAULT 0',
                'created' => 'int(10) NOT NULL',
                'modified' => 'int(10) NOT NULL DEFAULT 0',
                'title' => 'varchar(255) NOT NULL',
                'meta_title' => 'varchar(255) NOT NULL DEFAULT ""',
                'meta_description' => 'text(255) NOT NULL DEFAULT ""',
                'description' => 'text NOT NULL',
                'data' => 'blob NOT NULL',
            ),
            'serialize' => array('data')
        );

        $tables['page_translation'] = array(
            'fields' => array(
                'translation_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'page_id' => 'int(10) NOT NULL',
                'language' => 'varchar(4) NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'meta_description' => 'varchar(255) NOT NULL',
                'meta_title' => 'varchar(255) NOT NULL',
                'description' => 'text NOT NULL'
            )
        );

        $tables['price_rule'] = array(
            'fields' => array(
                'price_rule_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'value' => 'int(10) NOT NULL',
                'store_id' => 'int(10) NOT NULL',
                'status' => 'int(1) NOT NULL DEFAULT 0',
                'weight' => 'int(2) NOT NULL DEFAULT 0',
                'used' => 'int(10) NOT NULL DEFAULT 0',
                'name' => 'varchar(255) NOT NULL',
                'code' => 'varchar(255) NOT NULL',
                'value_type' => 'varchar(50) NOT NULL',
                'type' => 'varchar(50) NOT NULL',
                'currency' => 'varchar(4) NOT NULL',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['product'] = array(
            'fields' => array(
                'product_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'status' => 'int(1) NOT NULL',
                'front' => 'int(1) NOT NULL DEFAULT 0',
                'subtract' => 'int(1) NOT NULL',
                'product_class_id' => 'int(10) NOT NULL',
                'price' => 'int(10) NOT NULL DEFAULT 0',
                'created' => 'int(10) NOT NULL',
                'modified' => 'int(10) NOT NULL',
                'stock' => 'int(10) NOT NULL DEFAULT 0',
                'store_id' => 'int(10) NOT NULL DEFAULT 0',
                'brand_category_id' => 'int(10) NOT NULL',
                'user_id' => 'int(10) NOT NULL',
                'category_id' => 'int(10) NOT NULL',
                'length' => 'int(10) NOT NULL DEFAULT 0',
                'width' => 'int(10) NOT NULL DEFAULT 0',
                'height' => 'int(10) NOT NULL DEFAULT 0',
                'weight' => 'int(10) NOT NULL DEFAULT 0',
                'meta_title' => 'varchar(255) NOT NULL DEFAULT ""',
                'meta_description' => 'varchar(255) NOT NULL',
                'currency' => 'varchar(4) NOT NULL',
                'volume_unit' => 'varchar(2) NOT NULL',
                'weight_unit' => 'varchar(2) NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'description' => 'text NOT NULL',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['product_class'] = array(
            'fields' => array(
                'product_class_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'status' => 'int(1) NOT NULL DEFAULT 0',
                'title' => 'varchar(255) NOT NULL'
            )
        );

        $tables['product_class_field'] = array(
            'fields' => array(
                'product_class_field_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'product_class_id' => 'int(10) NOT NULL',
                'field_id' => 'int(10) NOT NULL',
                'required' => 'int(1) NOT NULL',
                'multiple' => 'int(1) NOT NULL',
                'weight' => 'int(2) NOT NULL DEFAULT 0'
            )
        );

        $tables['product_field'] = array(
            'fields' => array(
                'product_field_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'field_id' => 'int(10) NOT NULL',
                'field_value_id' => 'int(10) NOT NULL',
                'product_id' => 'int(10) NOT NULL',
                'type' => 'varchar(50) NOT NULL'
            )
        );

        $tables['product_sku'] = array(
            'fields' => array(
                'sku' => 'varchar(255) NOT NULL',
                'product_id' => 'int(10) NOT NULL',
                'combination_id' => 'varchar(255) NOT NULL DEFAULT ""',
                'store_id' => 'int(10) NOT NULL',
                'product_sku_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY'
            )
        );

        $tables['product_translation'] = array(
            'fields' => array(
                'translation_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'product_id' => 'int(10) NOT NULL',
                'language' => 'varchar(4) NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'meta_description' => 'varchar(255) NOT NULL',
                'meta_title' => 'varchar(255) NOT NULL',
                'description' => 'text NOT NULL'
            )
        );

        $tables['product_related'] = array(
            'fields' => array(
                'product_id' => 'int(10) NOT NULL PRIMARY KEY',
                'related_product_id' => 'int(10) NOT NULL',
            )
        );

        $tables['review'] = array(
            'fields' => array(
                'review_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'user_id' => 'int(10) NOT NULL',
                'status' => 'int(1) NOT NULL DEFAULT 0',
                'created' => 'int(10) NOT NULL',
                'modified' => 'int(10) NOT NULL DEFAULT 0',
                'product_id' => 'int(10) NOT NULL',
                'text' => 'text NOT NULL',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['rating'] = array(
            'fields' => array(
                'product_id' => 'int(10) NOT NULL PRIMARY KEY',
                'votes' => 'int(10) NOT NULL',
                'rating' => 'float DEFAULT 0',
            )
        );

        $tables['rating_user'] = array(
            'fields' => array(
                'id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'product_id' => 'int(10) NOT NULL',
                'user_id' => 'varchar(255) NOT NULL',
                'rating' => 'float DEFAULT 0',
            )
        );

        $tables['role'] = array(
            'fields' => array(
                'role_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'status' => 'int(1) NOT NULL DEFAULT 0',
                'name' => 'varchar(255) NOT NULL',
                'permissions' => 'blob NOT NULL'
            ),
            'serialize' => array('permissions')
        );

        $tables['settings'] = array(
            'fields' => array(
                'id' => 'varchar(255) PRIMARY KEY',
                'value' => 'blob NOT NULL',
                'created' => 'int(10) NOT NULL',
                'serialized' => 'int(1) NOT NULL DEFAULT 0'
            )
        );

        $tables['state'] = array(
            'fields' => array(
                'state_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'status' => 'int(1) NOT NULL DEFAULT 0',
                'code' => 'varchar(255) NOT NULL',
                'name' => 'varchar(255) NOT NULL',
                'country' => 'varchar(2) NOT NULL',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['store'] = array(
            'fields' => array(
                'store_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'status' => 'int(1) NOT NULL DEFAULT 0',
                'domain' => 'varchar(255) NOT NULL',
                'name' => 'varchar(255) NOT NULL',
                'basepath' => 'varchar(50) NOT NULL DEFAULT ""',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['transaction'] = array(
            'fields' => array(
                'transaction_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'order_id' => 'int(10) NOT NULL',
                'created' => 'int(10) NOT NULL',
                'payment_service' => 'varchar(255) NOT NULL',
                'service_transaction_id' => 'varchar(255) NOT NULL',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['user'] = array(
            'fields' => array(
                'user_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'created' => 'int(10) NOT NULL',
                'modified' => 'int(10) NOT NULL DEFAULT 0',
                'status' => 'int(1) NOT NULL DEFAULT 1',
                'role_id' => 'int(10) NOT NULL DEFAULT 0',
                'store_id' => 'int(10) NOT NULL DEFAULT 0',
                'email' => 'varchar(255) NOT NULL',
                'name' => 'varchar(255) NOT NULL',
                'hash' => 'text NOT NULL',
                'data' => 'blob NOT NULL'
            ),
            'serialize' => array('data')
        );

        $tables['zone'] = array(
            'fields' => array(
                'zone_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'state_id' => 'int(10) NOT NULL',
                'status' => 'int(1) NOT NULL DEFAULT 1',
                'name' => 'varchar(255) NOT NULL',
                'country' => 'varchar(2) NOT NULL'
            )
        );

        $tables['search_index'] = array(
            'fields' => array(
                'search_index_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'id_value' => 'int(10) NOT NULL',
                'id_key' => 'varchar(50) NOT NULL',
                'language' => 'varchar(4) NOT NULL',
                'text' => 'longtext NOT NULL',
            ),
            'engine' => 'MyISAM',
            'alter' => 'ADD FULLTEXT(text)',
        );

        if (isset($table)) {
            return empty($tables[$table]) ? array() : $tables[$table];
        }

        return $tables;
    }

    /**
     * Filters an array of data according to existing columns for the given table
     * @param string $table
     * @param array $data
     * @return array
     */
    protected function getDbSchemeValues($table, array $data)
    {
        $scheme = $this->getDbScheme($table);

        if (empty($scheme['fields'])) {
            return array();
        }

        $values = array_intersect_key($data, $scheme['fields']);

        if (empty($values) || empty($scheme['serialize'])) {
            return $values;
        }

        foreach ($values as $field => &$value) {
            if (in_array($field, $scheme['serialize'])) {
                $value = serialize((array) $value);
            }
        }

        return $values;
    }

}
