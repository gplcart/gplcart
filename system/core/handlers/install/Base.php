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
     * Install model instance
     * @var \gplcart\core\models\Install $install
     */
    protected $install;

    /**
     * Session helper instance
     * @var \gplcart\core\helpers\Session $session
     */
    protected $session;

    /**
     * Command line helper instance
     * @var \gplcart\core\helpers\Cli $cli
     */
    protected $cli;

    /**
     * Database class instance
     * @var \gplcart\core\Database $database
     */
    protected $db;

    /**
     * An array of data provided by a user during initial installation
     * @var array
     */
    protected $data = array();

    /**
     * An array of context data used during installation process
     * @var array
     */
    protected $context = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        set_time_limit(0);

        $this->cli = Container::get('gplcart\\core\\helpers\Cli');
        $this->session = Container::get('gplcart\\core\\helpers\Session');
        $this->install = Container::get('gplcart\\core\\models\\Install');
        $this->language = Container::get('gplcart\\core\\models\\Language');
        // Rest of models are loaded inline as they require database set up
    }

    /**
     * Creates config.php
     * @return boolean|string
     */
    protected function createConfig()
    {
        $config = file_get_contents(GC_CONFIG_COMMON_DEFAULT);

        if (empty($config)) {
            $vars = array('@path' => GC_CONFIG_COMMON_DEFAULT);
            return $this->language->text('Failed to read the source config @path', $vars);
        }

        $config .= '$config[\'database\'] = ' . var_export($this->data['database'], true) . ';';
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
     */
    protected function createPages()
    {
        /* @var $model \gplcart\core\models\Page */
        $model = Container::get('gplcart\\core\\models\\Page');

        $pages = array();

        $pages[] = array(
            'title' => 'Contact us',
            'description' => 'Contact information',
        );

        $pages[] = array(
            'title' => 'Help',
            'description' => 'Help information. Coming soon...',
        );

        foreach ($pages as $page) {

            $page += array(
                'status' => 1,
                'user_id' => $this->context['user_id'],
                'store_id' => $this->context['store_id']
            );

            $model->add($page);
        }
    }

    /**
     * Creates default languages
     */
    protected function createLanguages()
    {
        if (!empty($this->data['store']['language']) && $this->data['store']['language'] !== 'en') {
            $language = array('code' => $this->data['store']['language'], 'default' => true);
            /* @var $model \gplcart\core\models\Language */
            $model = Container::get('gplcart\\core\\models\\Language');
            $model->add($language);
        }
    }

    /**
     * Creates super admin user
     */
    protected function createSuperadmin()
    {
        /* @var $model \gplcart\core\models\User */
        $model = Container::get('gplcart\\core\\models\\User');

        $user = array(
            'status' => 1,
            'name' => 'Superadmin',
            'store_id' => $this->context['store_id'],
            'email' => $this->data['user']['email'],
            'password' => $this->data['user']['password']
        );

        $user_id = $model->add($user);

        $this->config->set('user_superadmin', $user_id);
        $this->setContext('user_id', $user_id);
    }

    /**
     * Creates default store
     */
    protected function createStore()
    {
        /* @var $model \gplcart\core\models\Store */
        $model = Container::get('gplcart\\core\\models\\Store');

        $default = $model->defaultConfig();

        $default['title'] = $this->data['store']['title'];
        $default['email'] = array($this->data['user']['email']);

        $store = array(
            'status' => 1,
            'data' => $default,
            'name' => $this->data['store']['title'],
            'domain' => $this->data['store']['host'],
            'basepath' => $this->data['store']['basepath']
        );

        $store_id = $model->add($store);

        $this->config->set('store', $store_id);
        $this->setContext('store_id', $store_id);
    }

    /**
     * Create default content for the site
     * @return boolean|string
     */
    protected function createContent()
    {
        Container::unregister();

        $this->config = Container::get('gplcart\\core\\Config');
        $this->db = $this->config->getDb();

        try {
            $this->createDbConfig();
            $this->createStore();
            $this->createSuperadmin();
            $this->createCountries();
            $this->createLanguages();
            $this->createPages();
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }

        return true;
    }

    /**
     * Create store settings in the database
     */
    protected function createDbConfig()
    {
        $this->config->set('intro', 1);
        $this->config->set('installed', GC_TIME);
        $this->config->set('cron_key', gplcart_string_random());
        $this->config->set('installer', $this->data['installer']);
        $this->config->set('timezone', $this->data['store']['timezone']);
    }

    /**
     * Sets the current context
     * @param string $key
     * @param mixed $value
     */
    protected function setContext($key, $value)
    {
        gplcart_array_set($this->context, $key, $value);
        $this->session->set("install.context.$key", $value);
    }

    /**
     * Returns a value from the current context
     * @param string $key
     * @return mixed
     */
    protected function getContext($key)
    {
        if (GC_CLI) {
            return gplcart_array_get($this->context, $key);
        }

        return $this->session->get("install.context.$key");
    }

    /**
     * Sets context error message
     * @param integer $step
     * @param string $message
     */
    protected function setContextError($step, $message)
    {
        $pos = count($this->getContext("errors.$step"));
        $pos++;
        $this->setContext("errors.$step.$pos", $message);
    }

    /**
     * Returns an array of context errors
     * @param bool $flatten
     * @return array
     */
    protected function getContextErrors($flatten = true)
    {
        $errors = $this->getContext('errors');

        if (empty($errors)) {
            return array();
        }

        if ($flatten) {
            return gplcart_array_flatten($errors);
        }

        return $errors;
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
     * Creates countries from ISO list
     */
    protected function createCountries()
    {
        $countries = require GC_CONFIG_COUNTRY;

        $rows = $placeholders = array();

        foreach ($countries as $code => $name) {
            $placeholders[] = '(?,?,?,?,?)';
            $rows = array_merge($rows, array(0, $name, $code, $name, serialize(array())));
        }

        $sql = 'INSERT INTO country (status, name, code, native_name, format) VALUES ' . implode(',', $placeholders);
        $this->db->run($sql, $rows);
    }

    /**
     * Does initial tasks before installation
     */
    protected function start()
    {
        $this->session->delete('user');
        $this->session->delete('install');
        $this->session->set('install.data', $this->data);
    }

    /**
     * Process installation
     * @return boolean|array
     */
    protected function process()
    {
        $result = array(
            'message' => '',
            'redirect' => '',
            'severity' => 'warning'
        );

        $result_db = $this->createDb();

        if ($result_db !== true) {
            $result['message'] = $result_db;
            return $result;
        }

        $result_config = $this->createConfig();

        if ($result_config !== true) {
            $result['message'] = $result_config;
            return $result;
        }

        $result_store = $this->createContent();

        if ($result_store !== true) {
            $result['message'] = $result_store;
            return $result;
        }

        return true;
    }

    /**
     * Finishes installation
     * @return array
     */
    protected function finish()
    {
        $this->session->delete('install');

        return array(
            'redirect' => 'login',
            'severity' => 'success',
            'message' => $this->getSuccessMessage()
        );
    }

    /**
     * Returns success message
     * @return string
     */
    protected function getSuccessMessage()
    {
        if (GC_CLI) {
            $vars = array('@url' => rtrim("{$this->data['store']['host']}/{$this->data['store']['basepath']}", '/'));
            return $this->language->text("Your store has been installed.\nURL: @url\nAdmin area: @url/admin\nGood luck!", $vars);
        }

        return $this->language->text('Your store has been installed. Now you can log in as superadmin');
    }

}
