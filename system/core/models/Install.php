<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use PDOException;
use core\Model;
use core\Container;
use core\classes\Tool;
use core\classes\Request;
use core\classes\Database;
use core\models\Store as ModelsStore;
use core\models\Language as ModelsLanguage;

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
     * @return boolean|string
     */
    public function connect(array $settings)
    {
        try {
            $this->db = new Database($settings);
            $existing = $this->db->query('SHOW TABLES')->fetchColumn();
            if (!empty($existing)) {
                return $this->language->text('The database you specified already has tables');
            }
        } catch (PDOException $e) {
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
        $scheme = $this->getDbScheme();
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
            'basepath' => trim($this->request->base(true), '/'),
            'status' => 1,
            'data' => serialize($data),
        );

        $store_id = $this->db->insert('store', $store);
        $config->set('store', $store_id);
        $config->set('intro', 1);

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


        $collection_id = $this->db->insert('collection', array(
            'status' => 1,
            'type' => 'file',
            'store_id' => $store_id,
            'title' => 'Front slideshow',
            'description' => 'Block with rotating banners on the front page',
        ));

        $config->set('collection_front_slideshow', $collection_id);

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
            'alias' => 'about.html'
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
            'alias' => 'contact.html'
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
            'alias' => 'terms.html'
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
            'alias' => 'faq.html'
        ));

        return true;
    }

}
