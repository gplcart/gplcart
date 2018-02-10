<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\install;

use gplcart\core\Container;
use UnexpectedValueException;

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
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

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
     * Construct
     */
    public function __construct()
    {
        $this->config = Container::get('gplcart\\core\\Config');
        $this->cli = Container::get('gplcart\\core\\helpers\\Cli');
        $this->install = Container::get('gplcart\\core\\models\\Install');
        $this->session = Container::get('gplcart\\core\\helpers\\Session');
        $this->translation = Container::get('gplcart\\core\\models\\Translation');
    }

    /**
     * Set a property
     * @param string $name
     * @param mixed $value
     */
    public function setProperty($name, $value)
    {
        $this->{$name} = $value;
    }

    /**
     * Create the configuration file using data from a default file
     * @return boolean|string
     * @throws UnexpectedValueException
     */
    protected function createConfig()
    {
        $this->setCliMessage('Creating configuration file...');

        $config = file_get_contents(GC_FILE_CONFIG);

        if (empty($config)) {
            $error = $this->translation->text('Failed to read the source config @path', array('@path' => GC_FILE_CONFIG));
            throw new UnexpectedValueException($error);
        }

        $config .= '$config[\'database\'] = ' . var_export($this->data['database'], true) . ';';
        $config .= PHP_EOL . PHP_EOL;
        $config .= 'return $config;';
        $config .= PHP_EOL;

        if (file_put_contents(GC_FILE_CONFIG_COMPILED, $config) === false) {
            throw new UnexpectedValueException($this->translation->text('Failed to create config.php'));
        }

        chmod(GC_FILE_CONFIG_COMPILED, 0444);
        return true;
    }

    /**
     * Creates default pages
     */
    protected function createPages()
    {
        $this->setCliMessage('Creating pages...');

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

            $this->getPageModel()->add($page);
        }
    }

    /**
     * Creates default languages
     */
    protected function createLanguages()
    {
        $this->setCliMessage('Configuring language...');

        if (!empty($this->data['store']['language']) && $this->data['store']['language'] !== 'en') {
            $this->getLanguageModel()->update($this->data['store']['language'], array('default' => true));
        }
    }

    /**
     * Creates user #1 (super-admin)
     */
    protected function createSuperadmin()
    {
        $this->setCliMessage('Creating superadmin...');

        $user = array(
            'status' => 1,
            'name' => 'Superadmin',
            'store_id' => $this->context['store_id'],
            'email' => $this->data['user']['email'],
            'password' => $this->data['user']['password']
        );

        $user_id = $this->getUserModel()->add($user);

        $this->config->set('user_superadmin', $user_id);
        $this->setContext('user_id', $user_id);
    }

    /**
     * Creates default store
     */
    protected function createStore()
    {
        $this->setCliMessage('Creating store...');

        $default = $this->getStoreModel()->getDefaultData();

        $default['title'] = $this->data['store']['title'];
        $default['email'] = array($this->data['user']['email']);

        $store = array(
            'status' => 0,
            'data' => $default,
            'name' => $this->data['store']['title'],
            'domain' => $this->data['store']['host'],
            'basepath' => $this->data['store']['basepath']
        );

        $store_id = $this->getStoreModel()->add($store);

        $this->config->set('store', $store_id);
        $this->setContext('store_id', $store_id);
    }

    /**
     * Create default content for the site
     */
    protected function createContent()
    {
        $this->setCliMessage('Creating content...');

        $this->initConfig();
        $this->createDbConfig();
        $this->createStore();
        $this->createSuperadmin();
        $this->createCountries();
        $this->createLanguages();
        $this->createPages();
    }

    /**
     * Create store settings in the database
     */
    protected function createDbConfig()
    {
        $this->setCliMessage('Configuring database...');

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
     */
    protected function createDb()
    {
        $this->setCliMessage('Creating database tables...');
        $this->db->import($this->db->getScheme());
    }

    /**
     * Creates countries from ISO list
     */
    protected function createCountries()
    {
        $rows = $placeholders = array();

        foreach ((array) $this->getCountryModel()->getIso() as $code => $country) {
            $placeholders[] = '(?,?,?,?,?)';
            $native_name = empty($country['native_name']) ? $country['name'] : $country['native_name'];
            $rows = array_merge($rows, array(0, $country['name'], $code, $native_name, serialize(array())));
        }

        $values = implode(',', $placeholders);
        $sql = "INSERT INTO country (status, name, code, native_name, format) VALUES $values";
        $this->db->run($sql, $rows);
    }

    /**
     * Does initial tasks before installation
     */
    protected function start()
    {
        set_time_limit(0);

        $this->session->delete('user');
        $this->session->delete('install');
        $this->session->set('install.data', $this->data);
    }

    /**
     * Process installation
     */
    protected function process()
    {
        $this->createDb();
        $this->createConfig();
        $this->createContent();
    }

    /**
     * Finishes installation
     * @return array
     */
    protected function finish()
    {
        $this->session->delete('install');
        $this->config->set('cli_status', 0);

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
            return $this->translation->text("Your store has been installed.\nURL: @url\nAdmin area: @url/admin\nGood luck!", $vars);
        }

        return $this->translation->text('Your store has been installed. Now you can log in as superadmin');
    }

    /**
     * Sets a message line in CLI mode
     * @param string $message
     */
    protected function setCliMessage($message)
    {
        if (GC_CLI) {
            $this->cli->line($this->translation->text($message));
        }
    }

    /**
     * Init configuration
     * It makes sure that we're using the database connection defined in the configuration file
     */
    protected function initConfig()
    {
        Container::unregister();

        $this->config = Container::get('gplcart\\core\\Config');
        $this->config->init();
        $this->db = $this->config->getDb();
    }

    /**
     * Returns Page model class instance
     * @return \gplcart\core\models\Page
     */
    protected function getPageModel()
    {
        /** @var \gplcart\core\models\Page $instance */
        $instance = Container::get('gplcart\\core\\models\\Page');
        return $instance;
    }

    /**
     * Returns Language model instance
     * @return \gplcart\core\models\Language
     */
    protected function getLanguageModel()
    {
        /** @var \gplcart\core\models\Language $instance */
        $instance = Container::get('gplcart\\core\\models\\Language');
        return $instance;
    }

    /**
     * Returns User model instance
     * @return \gplcart\core\models\User
     */
    protected function getUserModel()
    {
        /** @var \gplcart\core\models\User $instance */
        $instance = Container::get('gplcart\\core\\models\\User');
        return $instance;
    }

    /**
     * Returns Store model instance
     * @return \gplcart\core\models\Store
     */
    protected function getStoreModel()
    {
        /** @var \gplcart\core\models\Store $instance */
        $instance = Container::get('gplcart\\core\\models\\Store');
        return $instance;
    }

    /**
     * Returns Country model instance
     * @return \gplcart\core\models\Country
     */
    protected function getCountryModel()
    {
        /** @var \gplcart\core\models\Country $instance */
        $instance = Container::get('gplcart\\core\\models\\Country');
        return $instance;
    }

}
