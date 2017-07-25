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
     * @return boolean
     */
    protected function createDefaultConfig(array $settings)
    {
        $config = file_get_contents(GC_CONFIG_COMMON_DEFAULT);

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

        return $result;
    }

    /**
     * Creates default pages
     * @param integer $user_id
     * @param integer $store_id
     */
    protected function createDefaultPages($user_id, $store_id)
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
    protected function createDefaultLanguages(array $settings)
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
    protected function createDefaultStore(array $settings)
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
    protected function setDefaultStore(array $settings)
    {
        Container::unregister(); // Remove old instances to prevent conflicts

        $this->config = Container::get('gplcart\\core\\Config');
        $this->db = $this->config->getDb();

        if (!$this->db instanceof \gplcart\core\Database) {
            return $this->language->text('Could not connect to database');
        }

        if (empty($settings['store']['timezone'])) {
            $settings['store']['timezone'] = date_default_timezone_get();
        }

        $this->config->set('intro', 1);
        $this->config->set('installed', GC_TIME);
        $this->config->set('cron_key', gplcart_string_random());
        $this->config->set('timezone', $settings['store']['timezone']);

        $store_id = $this->createDefaultStore($settings);
        $user_id = $this->createSuperadmin($settings, $store_id);

        $this->createDefaultLanguages($settings);
        $this->createDefaultPages($user_id, $store_id);

        return true;
    }

    /**
     * Create default database structure
     * @return bool
     */
    protected function createDefaultDb()
    {
        return $this->db->import($this->db->getScheme());
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
