<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use PDO;
use core\Container;
use core\classes\Tool;
use core\classes\Request as ClassesRequest;
use core\models\Store as ModelsStore;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to system installation
 */
class Install
{

    /**
     * Store model instance
     * @var \core\models\Store $store
     */
    protected $store;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Request class instance
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param ModelsStore $store
     * @param ModelsLanguage $language
     * @param ClassesRequest $request
     */
    public function __construct(ModelsStore $store, ModelsLanguage $language,
            ClassesRequest $request)
    {
        $this->store = $store;
        $this->request = $request;
        $this->language = $language;
    }

    /**
     * Returns an array of requirements
     * @return array
     */
    public function getRequirements()
    {
        $requirements = array();

        $requirements['extensions']['gd'] = array(
            'status' => extension_loaded('gd'),
            'severity' => 'danger',
            'message' => $this->language->text('GD image extension installed')
        );

        $requirements['extensions']['pdo'] = array(
            'status' => extension_loaded('pdo'),
            'severity' => 'danger',
            'message' => $this->language->text('PDO database driver installed')
        );

        $requirements['extensions']['curl'] = array(
            'status' => extension_loaded('curl'),
            'severity' => 'danger',
            'message' => $this->language->text('CURL extension installed')
        );

        $requirements['extensions']['fileinfo'] = array(
            'status' => extension_loaded('fileinfo'),
            'severity' => 'danger',
            'message' => $this->language->text('FileInfo extension installed')
        );

        $requirements['extensions']['openssl'] = array(
            'status' => extension_loaded('openssl'),
            'severity' => 'danger',
            'message' => $this->language->text('OpenSSL extension installed')
        );

        $requirements['php']['allow_url_fopen'] = array(
            'status' => !ini_get('allow_url_fopen'),
            'severity' => 'warning',
            'message' => $this->language->text('allow_url_fopen directive disabled')
        );

        $requirements['files']['system_directory'] = array(
            'status' => is_writable(GC_SYSTEM_DIR),
            'severity' => 'danger',
            'message' => $this->language->text('System directory exists and writable')
        );

        $requirements['files']['cache_directory'] = array(
            'status' => is_writable(GC_CACHE_DIR),
            'severity' => 'danger',
            'message' => $this->language->text('Cache directory exists and writable')
        );

        return $requirements;
    }

    /**
     * Returns an array of requirements errors
     * @param array $requirements
     * @return array
     */
    public function getRequirementsErrors(array $requirements)
    {
        $errors = array();
        foreach ($requirements as $items) {
            foreach ($items as $name => $info) {
                if (empty($info['status'])) {
                    $errors[$info['severity']][] = $name;
                }
            }
        }

        return $errors;
    }

    /**
     * Connects to the database using the given settings
     * @param array $settings
     * @return boolean
     */
    public function connect(array $settings)
    {
        extract($settings);

        try {
            $this->db = new PDO("$type:host=$host;port=$port;dbname=$name", $user, $password);

            if ($this->db->query('SHOW TABLES')->fetchColumn()) {
                return $this->language->text('The database you specified already has tables');
            }
        } catch (\PDOException $e) {
            $this->db = null;
            return $e->getMessage();
        }

        return true;
    }

    /**
     * Creates tables in the database
     * @return boolean
     */
    public function tables()
    {
        $imported = 0;
        $tables = $this->dump();

        foreach ($tables as $table => $data) {
            $fields = '';
            foreach ($data['fields'] as $name => $info) {
                $fields .= "$name $info,";
            }

            $field_list = rtrim($fields, ',');

            $engine = isset($data['engine']) ? $data['engine'] : 'InnoDB';
            $collate = isset($data['collate']) ? $data['collate'] : 'utf8_general_ci';

            $sql = "CREATE TABLE $table($field_list) ENGINE=$engine CHARACTER SET utf8 COLLATE $collate";

            if ($this->db->query($sql) !== false) {
                $imported++;
            }

            if (!empty($data['alter'])) {
                $this->db->query("ALTER TABLE $table {$data['alter']}");
            }
        }

        return ($imported == count($tables));
    }

    /**
     * Creates config.php
     * @param array $settings
     * @return boolean
     */
    public function config(array $settings)
    {
        $config = file_get_contents(GC_CONFIG_COMMON_DEFAULT);

        if (!$config) {
            return false;
        }

        $config .= '$config[\'database\'] = ' . var_export($settings['database'], true) . ';' . PHP_EOL . PHP_EOL;
        $config .= 'return $config;' . PHP_EOL;

        if (!file_put_contents(GC_CONFIG_COMMON, $config)) {
            return false;
        }

        chmod(GC_CONFIG_COMMON, 0444);
        return true;
    }

    /**
     * Installs the store
     * @param array $settings
     * @return boolean|string
     */
    public function store(array $settings)
    {
        Container::unregister();
        $config = Container::instance('core\\Config');

        $this->db = $config->getDb();

        if (empty($this->db)) {
            return $this->language->text('Unable to connect to the database');
        }

        // Default store
        $data = $this->store->defaultConfig();
        $data['title'] = $settings['store']['title'];
        $data['email'] = array($settings['user']['email']);

        $store = array(
            'name' => $settings['store']['title'],
            'domain' => $this->request->host(),
            'scheme' => $this->request->scheme(),
            'basepath' => trim($this->request->base(true), '/'),
            'status' => 1,
            'data' => serialize($data),
        );

        $store_id = $this->db->insert('store', $store);
        $config->set('store', $store_id);

        // Default user
        $user = array(
            'modified' => 0,
            'status' => 1,
            'role_id' => 0,
            'store_id' => $store_id,
            'created' => GC_TIME,
            'email' => $settings['user']['email'],
            'name' => 'Superadmin',
            'hash' => Tool::hash($settings['user']['password']),
            'data' => serialize(array())
        );

        $user_id = $this->db->insert('user', $user);
        $config->set('user_superadmin', $user_id);

        // Default roles
        $this->db->insert('role', array('name' => 'General manager', 'permissions' => serialize(array())));
        $this->db->insert('role', array('name' => 'Order manager', 'permissions' => serialize(array())));
        $this->db->insert('role', array('name' => 'Content manager', 'permissions' => serialize(array())));

        // Default country
        $country = array(
            'format' => serialize(array()),
            'status' => 1,
            'name' => $settings['store']['country_name'],
            'native_name' => $settings['store']['country_native_name'],
            'code' => $settings['store']['country'],
        );

        $this->db->insert('country', $country);
        $config->set('country', $settings['store']['country']);

        // Default language
        if (!empty($settings['store']['language'])) {
            $langcode = $settings['store']['language'];
            $this->config->set('language', $langcode);

            $languages[$langcode] = array(
                'code' => $langcode,
                'name' => $langcode,
                'native_name' => $langcode,
                'status' => 1,
                'default' => 1
            );

            $languages = $this->config->set('languages', $languages);
        }

        // Default category groups
        $this->db->insert('category_group', array(
            'store_id' => $store_id,
            'data' => serialize(array()),
            'type' => 'catalog',
            'title' => 'Catalog'
        ));

        $this->db->insert('category_group', array(
            'store_id' => $store_id,
            'data' => serialize(array()),
            'type' => 'brand',
            'title' => 'Brand'
        ));

        // Default pages and their aliases
        $page_id = $this->db->insert('page', array(
            'status' => 1,
            'user_id' => $user_id,
            'store_id' => $store_id,
            'created' => GC_TIME,
            'data' => serialize(array()),
            'title' => 'About us',
            'description' => 'Company information',
        ));

        $this->db->insert('alias', array(
            'id_key' => 'page_id',
            'id_value' => $page_id,
            'alias' => 'about'
        ));

        $page_id = $this->db->insert('page', array(
            'status' => 1,
            'user_id' => $user_id,
            'store_id' => $store_id,
            'created' => GC_TIME,
            'data' => serialize(array()),
            'title' => 'Contact us',
            'description' => 'Contact information',
        ));

        $this->db->insert('alias', array(
            'id_key' => 'page_id',
            'id_value' => $page_id,
            'alias' => 'contact'
        ));

        $page_id = $this->db->insert('page', array(
            'status' => 1,
            'user_id' => $user_id,
            'store_id' => $store_id,
            'created' => GC_TIME,
            'data' => serialize(array()),
            'title' => 'Terms and conditions',
            'description' => 'Terms and conditions',
        ));

        $this->db->insert('alias', array(
            'id_key' => 'page_id',
            'id_value' => $page_id,
            'alias' => 'terms'
        ));

        $page_id = $this->db->insert('page', array(
            'status' => 1,
            'user_id' => $user_id,
            'store_id' => $store_id,
            'created' => GC_TIME,
            'data' => serialize(array()),
            'title' => 'FAQ',
            'description' => 'Questions and answers',
        ));

        $this->db->insert('alias', array(
            'id_key' => 'page_id',
            'id_value' => $page_id,
            'alias' => 'faq'
        ));

        return true;
    }

    /**
     * Returns an array of data used to create tables in the database
     * @return array
     */
    protected function dump()
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
            )
        );

        $tables['alias'] = array(
            'fields' => array(
                'alias_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'id_value' => 'int(10) NOT NULL',
                'id_key' => 'varchar(50) NOT NULL',
                'alias' => 'varchar(255) NOT NULL'
            )
        );

        $tables['bookmark'] = array(
            'fields' => array(
                'bookmark_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'id_value' => 'int(10) NOT NULL',
                'created' => 'int(10) NOT NULL',
                'id_key' => 'varchar(50) NOT NULL',
                'user_id' => 'varchar(255) NOT NULL',
                'title' => 'varchar(255) NOT NULL DEFAULT ""',
                'url' => 'varchar(255) NOT NULL DEFAULT ""'
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
            )
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
            )
        );

        $tables['category_group'] = array(
            'fields' => array(
                'category_group_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'store_id' => 'int(10) NOT NULL',
                'type' => 'varchar(50) NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'data' => 'blob NOT NULL'
            )
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
            )
        );

        $tables['country'] = array(
            'fields' => array(
                'code' => 'varchar(2) PRIMARY KEY',
                'name' => 'varchar(255) NOT NULL',
                'native_name' => 'varchar(255) NOT NULL',
                'status' => 'int(1) NOT NULL DEFAULT 0',
                'weight' => 'int(2) NOT NULL DEFAULT 0',
                'format' => 'blob NOT NULL'
            )
        );

        $tables['field'] = array(
            'fields' => array(
                'field_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'weight' => 'int(2) NOT NULL DEFAULT 0',
                'type' => 'varchar(50) NOT NULL',
                'widget' => 'varchar(50) NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'data' => 'blob NOT NULL'
            )
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
            )
        );

        $tables['module'] = array(
            'fields' => array(
                'status' => 'int(1) NOT NULL',
                'weight' => 'int(2) NOT NULL',
                'module_id' => 'varchar(50) PRIMARY KEY',
                'settings' => 'blob NOT NULL'
            )
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
            )
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
            )
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
            )
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
            )
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
            )
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
            )
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
            )
        );

        $tables['store'] = array(
            'fields' => array(
                'store_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'status' => 'int(1) NOT NULL DEFAULT 0',
                'domain' => 'varchar(255) NOT NULL',
                'name' => 'varchar(255) NOT NULL',
                'scheme' => 'varchar(50) NOT NULL DEFAULT "http://"',
                'basepath' => 'varchar(50) NOT NULL DEFAULT ""',
                'data' => 'blob NOT NULL'
            )
        );

        $tables['transaction'] = array(
            'fields' => array(
                'transaction_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'order_id' => 'int(10) NOT NULL',
                'created' => 'int(10) NOT NULL',
                'payment_service' => 'varchar(255) NOT NULL',
                'service_transaction_id' => 'varchar(255) NOT NULL',
                'data' => 'blob NOT NULL'
            )
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
            )
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

        $tables['queue'] = array(
            'fields' => array(
                'queue_id' => 'varchar(255) PRIMARY KEY',
                'status' => 'int(1) NOT NULL DEFAULT 1',
                'created' => 'int(10) NOT NULL',
                'modified' => 'int(10) NOT NULL DEFAULT 0',
                'total' => 'int(10) NOT NULL DEFAULT 0'
            )
        );

        $tables['queue_item'] = array(
            'fields' => array(
                'queue_item_id' => 'int(10) AUTO_INCREMENT PRIMARY KEY',
                'queue_id' => 'varchar(50) NOT NULL',
                'value' => 'varchar(255) NOT NULL'
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

        return $tables;
    }
}
