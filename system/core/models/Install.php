<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Database,
    gplcart\core\Container;
use gplcart\core\models\Store as StoreModel,
    gplcart\core\models\Language as LanguageModel;
use gplcart\core\exceptions\DatabaseException;

/**
 * Manages basic behaviors and data related to system installation
 */
class Install extends Model
{

    /**
     * Store model instance
     * @var \gplcart\core\models\Store $store
     */
    protected $store;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * PDO instance
     * @var \gplcart\core\Database $db
     */
    protected $database;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * @param StoreModel $store
     * @param LanguageModel $language
     */
    public function __construct(StoreModel $store, LanguageModel $language)
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
        $installers = $this->getDefault();

        $this->hook->fire('install.modules', $installers, $this);

        array_walk($installers, function(&$value, $key) {
            $value['id'] = $key;
        });

        gplcart_array_sort($installers);

        return $installers;
    }

    /**
     * Returns an array of default installers
     * @return array
     */
    protected function getDefault()
    {
        return array(
            'default' => array(
                'weight' => 0,
                'path' => 'install',
                'title' => $this->language->text('Default'),
                'description' => $this->language->text('Default system installer'),
            )
        );
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
        $requirements = require GC_CONFIG_REQUIREMENT;
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
            return $this->language->text($e->getMessage());
        }

        $existing = $this->database->query('SHOW TABLES')->fetchColumn();

        if (empty($existing)) {
            return true;
        }

        return $this->language->text('The database you specified already has tables');
    }

    /**
     * Creates tables in the database
     * @return boolean
     */
    public function tables()
    {
        $result = null;
        $scheme = $this->database->getScheme();
        $this->hook->fire('install.tables.before', $scheme, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $result = $this->database->import($scheme);
        $this->hook->fire('install.tables.after', $scheme, $result, $this);

        return $result;
    }

    /**
     * Creates config.php
     * @param array $settings
     * @return boolean
     */
    public function config(array $settings)
    {
        $config = file_get_contents(GC_CONFIG_COMMON_DEFAULT);

        $result = null;
        $this->hook->fire('install.config.before', $settings, $config, $result, $this);

        if (isset($result)) {
            return $result;
        }

        if (empty($config)) {
            return false;
        }

        $config .= '$config[\'database\'] = ' . var_export($settings['database'], true) . ';';
        $config .= PHP_EOL . PHP_EOL;
        $config .= 'return $config;';
        $config .= PHP_EOL;

        $result = false;
        if (file_put_contents(GC_CONFIG_COMMON, $config)) {
            chmod(GC_CONFIG_COMMON, 0444);
            $result = true;
        }

        $this->hook->fire('install.config.after', $settings, $config, $result, $this);

        return $result;
    }

    /**
     * Installs the store
     * @param array $settings
     * @return boolean|string
     */
    public function store(array $settings)
    {
        Container::unregister(); // Remove old instances to prevent conflicts

        $result = null;
        $this->hook->fire('install.store.before', $settings, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $this->config = Container::get('gplcart\\core\\Config');
        $this->database = $this->config->getDb();

        if (empty($this->database)) {
            return $this->language->text('Could not connect to database');
        }

        if (empty($settings['store']['timezone'])) {
            $settings['store']['timezone'] = date_default_timezone_get();
        }

        $this->config->set('intro', 1);
        $this->config->set('installed', GC_TIME);
        $this->config->set('cron_key', gplcart_string_random());
        $this->config->set('timezone', $settings['store']['timezone']);

        $store_id = $this->createStore($settings);
        $user_id = $this->createSuperadmin($settings, $store_id);

        $this->createLanguages($settings);
        $this->createPages($user_id, $store_id);

        $result = true;
        $this->hook->fire('install.store.after', $settings, $result, $this);

        return $result;
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

        return (int) $store_id;
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
            'hash' => gplcart_string_hash($settings['user']['password'])
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

        if ($code === 'en') {
            return false;
        }

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
     * Creates default pages
     * @param integer $user_id
     * @param integer $store_id
     */
    protected function createPages($user_id, $store_id)
    {
        $pages = array();

        $pages['contact.html'] = array(
            'title' => 'Contact us',
            'description' => 'Contact information',
        );

        $pages['help.html'] = array(
            'title' => 'Help',
            'description' => 'Help information. Coming soon...',
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
        set_time_limit(0);

        $result = null;
        $this->hook->fire('install.full.before', $data, $result, $this);

        if (isset($result)) {
            return $result;
        }

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

        $this->hook->fire('install.full.after', $data, $result, $this);
        return $result;
    }

}
