<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\install;

use gplcart\core\Container,
    gplcart\core\Handler;

/**
 * Base installer handlers class
 */
class Base extends Handler
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Store model instance
     * @var \gplcart\core\models\Store $store
     */
    protected $store;

    /**
     * Session class instance
     * @var \gplcart\core\helpers\Session $session
     */
    protected $session;

    /**
     * Database class instance
     * @var \gplcart\core\Database $database
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->store = Container::get('gplcart\\core\\models\\Store');
        $this->language = Container::get('gplcart\\core\\models\\Language');
        $this->session = Container::get('gplcart\\core\\helpers\Session');
    }

    /**
     * Creates config.php
     * @param array $settings
     * @return boolean|string
     */
    protected function createConfig(array $settings)
    {
        $config = file_get_contents(GC_CONFIG_COMMON_DEFAULT);

        if (empty($config)) {
            return $this->language->text('Failed to read the source config @path', array(
                        '@path' => GC_CONFIG_COMMON_DEFAULT));
        }

        $config .= '$config[\'database\'] = ' . var_export($settings['database'], true) . ';';
        $config .= PHP_EOL . PHP_EOL;
        $config .= 'return $config;';
        $config .= PHP_EOL;

        if (file_put_contents(GC_CONFIG_COMMON, $config)) {
            chmod(GC_CONFIG_COMMON, 0444);
            return true;
        }

        return $this->language->text('Failed to create config.php');
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

            $page_id = $this->db->insert('page', $data);

            $alias = array(
                'alias' => $alias,
                'id_key' => 'page_id',
                'id_value' => $page_id
            );

            $this->db->insert('alias', $alias);
        }
    }

    /**
     * Creates default languages
     * @param array $settings
     */
    protected function createLanguages(array $settings)
    {
        if (empty($settings['store']['language'])) {
            return null;
        }

        $code = key($settings['store']['language']);

        if ($code === 'en') {
            return null;
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

        $this->config->set('languages', $languages);
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

        $user_id = (int) $this->db->insert('user', $user);
        $this->config->set('user_superadmin', $user_id);

        return $user_id;
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

        $store_id = (int) $this->db->insert('store', $store);
        $this->config->set('store', $store_id);

        return $store_id;
    }

    /**
     * Sets the default store
     * @param array $settings
     * @return boolean|string
     */
    protected function setStore(array $settings)
    {
        // Remove old instances to prevent conflicts
        Container::unregister();

        // Re-instantiate Config and set fresh database object
        $this->config = Container::get('gplcart\\core\\Config');
        $this->db = $this->config->getDb();

        if (!$this->db instanceof \gplcart\core\Database) {
            return $this->language->text('Could not connect to database');
        }

        if (empty($settings['store']['timezone'])) {
            $settings['store']['timezone'] = date_default_timezone_get();
        }

        try {

            $this->config->set('intro', 1);
            $this->config->set('installed', GC_TIME);
            $this->config->set('cron_key', gplcart_string_random());
            $this->config->set('timezone', $settings['store']['timezone']);

            $store_id = $this->createStore($settings);
            $user_id = $this->createSuperadmin($settings, $store_id);

            $this->createCountries();
            $this->createLanguages($settings);
            $this->createPages($user_id, $store_id);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }

        return true;
    }

    /**
     * Create default database structure
     * @return bool|string
     */
    protected function createDb()
    {
        try {
            $result = $this->db->import($this->db->getScheme());
        } catch (\Exception $ex) {
            $result = $ex->getMessage();
        }

        if (empty($result)) {
            return $this->language->text('Failed to import database tables');
        }

        return $result;
    }

    /**
     * Creates countries fron ISO list
     */
    protected function createCountries()
    {
        $countries = require GC_CONFIG_COUNTRY;

        $rows = $placeholders = array();

        foreach ($countries as $code => $name) {
            $placeholders[] = '(?,?,?,?,?)';
            $rows = array_merge($rows, array(0, $name, $code, $name, 'a:0:{}'));
        }

        $sql = 'INSERT INTO country (status, name, code, native_name, format) VALUES ' . implode(',', $placeholders);
        $this->db->run($sql, $rows);
    }

    /**
     * Does initial tasks before installation
     * @param array $settings
     */
    protected function start(array $settings)
    {
        if (!GC_CLI) {

            set_time_limit(0);

            $this->session->delete('user');
            $this->session->delete('install');

            $this->session->set('install.processing', true);
            $this->session->set('install.settings', $settings);
        }
    }

    /**
     * Finishes installation
     */
    protected function finish()
    {
        if (!GC_CLI) {
            $this->session->delete('install');
        }
    }

}
