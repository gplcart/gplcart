<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Container;
use core\classes\Tool;
use core\classes\Request;
use core\classes\Database;
use core\models\Store as ModelsStore;
use core\models\Language as ModelsLanguage;
use core\exceptions\DatabaseException;

/**
 * Manages basic behaviors and data related to system installation
 */
class Install extends Model
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
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Constructor
     * @param ModelsStore $store
     * @param ModelsLanguage $language
     * @param ClassesRequest $request
     */
    public function __construct(ModelsStore $store, ModelsLanguage $language,
            Request $request)
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

        $requirements['extensions']['zip'] = array(
            'status' => class_exists('ZipArchive'),
            'severity' => 'danger',
            'message' => $this->language->text('Supports ZIP files')
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
    public function getRequirementErrors(array $requirements)
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
     * @return boolean|string
     */
    public function connect(array $settings)
    {
        try {
            $this->db = new Database($settings);
        } catch (DatabaseException $e) {
            $this->db = null;
            return $e->getMessage();
        }

        $existing = $this->db->query('SHOW TABLES')->fetchColumn();
        if (!empty($existing)) {
            return $this->language->text('The database you specified already has tables');
        }

        return true;
    }

    /**
     * Creates tables in the database
     * @return boolean
     */
    public function tables()
    {
        $scheme = $this->db->getScheme();
        return $this->db->import($scheme);
    }

    /**
     * Creates config.php
     * @param array $settings
     * @return boolean
     */
    public function config(array $settings)
    {
        $config = file_get_contents(GC_CONFIG_COMMON_DEFAULT);

        if (empty($config)) {
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
        Container::unregister(); // Remove old instances to prevent conflicts

        $this->config = Container::instance('core\\Config');
        $this->db = $this->config->getDb();

        if (empty($this->db)) {
            return $this->language->text('Unable to connect to the database');
        }

        $this->config->set('intro', 1);

        $store_id = $this->createStore($settings);
        $user_id = $this->createSuperadmin($settings, $store_id);

        $this->createRoles();
        $this->createLanguages($settings);
        $this->createCollections($store_id);
        $this->createCategoryGroups($store_id);
        $this->createPages($user_id, $store_id);

        return true;
    }

    /**
     * Creates default collections
     * @param integer $store_id
     */
    protected function createCollections($store_id)
    {
        $collections = array();

        $collections['front_slideshow'] = array(
            'type' => 'file',
            'title' => 'Front slideshow',
            'description' => 'Block with rotating banners on the front page',
        );

        foreach ($collections as $name => $collection) {
            $collection += array('status' => 1, 'store_id' => $store_id);
            $collection_id = $this->db->insert('collection', $collection);
            $this->config->set("collection_$name", $collection_id);
        }
    }

    /**
     * Creates default category groups
     * @param integer $store_id
     */
    protected function createCategoryGroups($store_id)
    {
        $groups = array();

        $groups[] = array('type' => 'brand', 'title' => 'Brand');
        $groups[] = array('type' => 'catalog', 'title' => 'Catalog');

        foreach ($groups as $group) {
            $group += array('store_id' => $store_id);
            $this->db->insert('category_group', $group);
        }
    }

    /**
     * Creates default store
     * @param array $settings
     * @return integer
     */
    protected function createStore(array $settings)
    {
        $data = $this->store->defaultConfig();

        $data['title'] = $settings['store']['title'];
        $data['email'] = array($settings['user']['email']);

        $store = array(
            'status' => 1,
            'data' => $data,
            'created' => GC_TIME,
            'domain' => $this->request->host(),
            'name' => $settings['store']['title'],
            'basepath' => trim($this->request->base(true), '/')
        );

        $store_id = $this->db->insert('store', $store);
        $this->config->set('store', $store_id);

        return $store_id;
    }

    /**
     * Creates superadmin user
     * @param array $settings
     * @param integer $store_id
     * @return integer
     */
    protected function createSuperadmin(array $settings, $store_id)
    {
        $user = array(
            'status' => 1,
            'created' => GC_TIME,
            'name' => 'Superadmin',
            'store_id' => $store_id,
            'email' => $settings['user']['email'],
            'hash' => Tool::hash($settings['user']['password'])
        );

        $user_id = $this->db->insert('user', $user);
        $this->config->set('user_superadmin', $user_id);

        return $user_id;
    }

    /**
     * Creates default languages
     * @param array $settings
     * @return boolean
     */
    protected function createLanguages(array $settings)
    {
        if (empty($settings['store']['language'])) {
            return false;
        }

        $code = key($settings['store']['language']);
        $this->config->set('language', $code);

        $native_name = $name = $settings['store']['language'][$code][0];
        if (isset($settings['store']['language'][$code][1])) {
            $native_name = $settings['store']['language'][$code][1];
        }

        $languages = array();

        $languages[$code] = array(
            'status' => 1,
            'default' => 1,
            'code' => $code,
            'name' => $name,
            'native_name' => $native_name
        );

        return $this->config->set('languages', $languages);
    }

    /**
     * Create default roles
     */
    protected function createRoles()
    {
        $roles = array();

        $roles[] = array('name' => 'Content manager');
        $roles[] = array('name' => 'Order manager');
        $roles[] = array('name' => 'Content manager');

        foreach ($roles as $role) {
            $this->db->insert('role', $role);
        }
    }

    /**
     * Creates default pages
     * @param integer $user_id
     * @param integer $store_id
     */
    protected function createPages($user_id, $store_id)
    {
        $pages = array();

        $pages['about.html'] = array(
            'title' => 'About us',
            'description' => 'Company information',
        );

        $pages['contact.html'] = array(
            'title' => 'Contact us',
            'description' => 'Contact information',
        );

        $pages['terms.html'] = array(
            'title' => 'Terms and conditions',
            'description' => 'Terms and conditions',
        );

        $pages['faq.html'] = array(
            'title' => 'FAQ',
            'description' => 'Questions and answers',
        );

        foreach ($pages as $alias => $data) {

            $data += array(
                'status' => 1,
                'created' => GC_TIME,
                'user_id' => $user_id,
                'store_id' => $store_id
            );

            $page_id = $this->db->insert('page', $data);

            $alias = array(
                'alias' => $alias,
                'id_key' => 'page_id',
                'id_value' => $page_id
            );

            $this->db->insert('alias', $alias);
        }
    }

}
