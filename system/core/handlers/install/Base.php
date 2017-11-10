<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\install;

use gplcart\core\Config,
    gplcart\core\Container;
use gplcart\core\helpers\Cli as CliHelper,
    gplcart\core\helpers\Session as SessionHelper;
use gplcart\core\models\Install as InstallModel,
    gplcart\core\models\Language as LanguageModel;

/**
 * Base installer handlers class
 */
class Base
{
    
    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

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
     * @param Config $config
     * @param InstallModel $install
     * @param LanguageModel $language
     * @param SessionHelper $session
     * @param CliHelper $cli
     */
    public function __construct(Config $config, InstallModel $install, LanguageModel $language,
            SessionHelper $session, CliHelper $cli)
    {
        set_time_limit(0);

        $this->cli = $cli;
        $this->config = $config;
        $this->session = $session;
        $this->install = $install;
        $this->language = $language;
    }

    /**
     * Creates config.php
     * @return boolean|string
     */
    protected function createConfig()
    {
        $config = file_get_contents(GC_FILE_CONFIG);

        if (empty($config)) {
            $vars = array('@path' => GC_FILE_CONFIG);
            return $this->language->text('Failed to read the source config @path', $vars);
        }

        $config .= '$config[\'database\'] = ' . var_export($this->data['database'], true) . ';';
        $config .= PHP_EOL . PHP_EOL;
        $config .= 'return $config;';
        $config .= PHP_EOL;

        if (file_put_contents(GC_FILE_CONFIG_COMPILED, $config) !== false) {
            chmod(GC_FILE_CONFIG_COMPILED, 0444);
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
            /* @var $model \gplcart\core\models\Language */
            $model = Container::get('gplcart\\core\\models\\Language');
            $model->update($this->data['store']['language'], array('default' => true));
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
        $this->config->init();
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
        $pos = count($this->getContext("errors.$step")) + 1;
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
        /* @var $model \gplcart\core\models\Country */
        $model = Container::get('gplcart\\core\\models\\Country');
        $template = $model->getDefaultAddressTemplate();

        $rows = $placeholders = array();
        foreach ((array) $model->getIso() as $code => $country) {
            $placeholders[] = '(?,?,?,?,?,?)';
            $native_name = isset($country['native_name']) ? $country['native_name'] : $country['name'];
            $rows = array_merge($rows, array(0, $country['name'], $code, $native_name, $template, serialize(array())));
        }

        $values = implode(',', $placeholders);
        $sql = "INSERT INTO country (status, name, code, native_name, template, format) VALUES $values";
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
