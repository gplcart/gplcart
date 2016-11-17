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
use core\helpers\Tool;
use core\helpers\Database;
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
     * PDO instance
     * @var \core\helpers\Database $db
     */
    protected $database;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Constructor
     * @param ModelsStore $store
     * @param ModelsLanguage $language
     */
    public function __construct(ModelsStore $store, ModelsLanguage $language)
    {
        parent::__construct();

        $this->store = $store;
        $this->language = $language;
    }

    /**
     * Whether the main config file exists
     * @return boolean
     */
    public function isInstalled()
    {
        return is_readable(GC_CONFIG_COMMON);
    }

    /**
     * Returns an array of defined installers
     * @return string
     */
    public function getList()
    {
        $installers = array();
        $this->hook->fire('installer', $installers);

        // Default installer definition goes after the hook 
        // to prevent changing form a module
        $installers['default'] = array(
            'weight' => 0,
            'path' => 'install',
            'title' => $this->language->text('Install'),
            'description' => $this->language->text('Default system installer'),
        );

        // Append installer ID
        array_walk($installers, function(&$value, $key) {
            $value['id'] = $key;
        });

        Tool::sortWeight($installers);
        return $installers;
    }

    /**
     * Returns an installer
     * @param string $installer
     * @return array
     */
    public function get($installer)
    {
        $installers = $this->getList();
        return empty($installers[$installer]) ? array() : $installers[$installer];
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
            $this->database = new Database($settings);
        } catch (DatabaseException $e) {
            $this->database = null;
            return $e->getMessage();
        }

        $existing = $this->database->query('SHOW TABLES')->fetchColumn();
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
        $scheme = $this->database->getScheme();
        return $this->database->import($scheme);
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
        $this->database = $this->config->getDb();

        if (empty($this->database)) {
            return $this->language->text('Unable to connect to the database');
        }

        $this->config->set('intro', 1);
        $this->config->set('timezone', $settings['store']['timezone']);

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

        $collections[] = array(
            'type' => 'file',
            'title' => 'Banners',
            'description' => 'Block with banners on the front page',
        );

        $collections[] = array(
            'type' => 'product',
            'title' => 'Featured products',
            'description' => 'Block with featured products on the front page',
        );

        $collections[] = array(
            'type' => 'page',
            'title' => 'News/articles',
            'description' => 'Block with news/articles on the front page',
        );

        foreach ($collections as $collection) {
            $collection += array('status' => 1, 'store_id' => $store_id);
            $this->database->insert('collection', $collection);
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
            $this->database->insert('category_group', $group);
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
            'name' => $settings['store']['title'],
            'domain' => $settings['store']['host'],
            'basepath' => $settings['store']['basepath']
        );

        $store_id = $this->database->insert('store', $store);
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

        $user_id = $this->database->insert('user', $user);
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
            $this->database->insert('role', $role);
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

            $page_id = $this->database->insert('page', $data);

            $alias = array(
                'alias' => $alias,
                'id_key' => 'page_id',
                'id_value' => $page_id
            );

            $this->database->insert('alias', $alias);
        }
    }

    /**
     * Performs full system installation
     * @param array $data
     * @return boolean|string Either TRUE on success or a error message 
     */
    public function full(array $data)
    {
        if ($this->tables() !== true) {
            return $this->language->text('Failed to create all necessary tables in the database');
        }

        if (!$this->config($data)) {
            return $this->language->text('Failed to create config.php');
        }

        $result = $this->store($data);

        if ($result !== true) {
            return (string) $result;
        }

        return true;
    }

}
