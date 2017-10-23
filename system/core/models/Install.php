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
    gplcart\core\Handler;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to system installation
 */
class Install extends Model
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $database;

    /**
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();

        $this->language = $language;
    }

    /**
     * Whether the main config file exists
     * @return boolean
     */
    public function isInstalled()
    {
        return is_file(GC_FILE_CONFIG_COMPILED);
    }

    /**
     * Returns an array of defined handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &gplcart_static('install.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = array();
        $this->hook->attach('install.handlers', $handlers, $this);

        foreach ($handlers as $id => &$handler) {

            if (empty($handler['module'])) {
                unset($handlers[$id]);
                continue;
            }

            $info = $this->config->getModuleInfo($handler['module']);

            if (empty($info['type']) || $info['type'] !== 'installer') {
                unset($handlers[$id]);
                continue;
            }

            $handler['id'] = $id;
            $handler['weight'] = isset($handler['weight']) ? $handler['weight'] : 0;
        }

        $handlers = array_merge($handlers, $this->getDefaultHandler());

        gplcart_array_sort($handlers);
        return $handlers;
    }

    /**
     * Returns an array of default installers
     * @return array
     */
    protected function getDefaultHandler()
    {
        return array(
            'default' => array(
                'weight' => 0,
                'module' => '',
                'id' => 'default',
                'title' => $this->language->text('Default'),
                'description' => $this->language->text('Default'),
                'handlers' => array(
                    'install' => array('gplcart\\core\\handlers\\install\\DefaultProfile', 'install')
                )
            )
        );
    }

    /**
     * Returns an installer handler
     * @param string $handler_id
     * @return array
     */
    public function getHandler($handler_id)
    {
        $handlers = $this->getHandlers();
        return empty($handlers[$handler_id]) ? array() : $handlers[$handler_id];
    }

    /**
     * Process installation by calling a handler
     * @param string $handler_id
     * @param array $data
     * @return array
     */
    public function callHandler($handler_id, array $data)
    {
        $handlers = $this->getHandlers();
        $method = isset($data['step']) ? 'install_' . $data['step'] : 'install';

        try {
            $result = Handler::call($handlers, $handler_id, $method, array($data, $this->database));
        } catch (\Exception $ex) {
            $result = array();
        }

        return (array) $result;
    }

    /**
     * Returns an array of requirements
     * @return array
     */
    public function getRequirements()
    {
        $requirements = &gplcart_static('install.requirements');

        if (isset($requirements)) {
            return (array) $requirements;
        }

        $requirements = (array) gplcart_config_get(GC_FILE_CONFIG_REQUIREMENT);
        $this->hook->attach('install.requirements', $requirements);
        return (array) $requirements;
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
     * Tries to connect and validates the database
     * @param array $settings
     * @return boolean|string
     */
    public function connectDb(array $settings)
    {
        try {
            $this->database = new Database;
            $this->database->set($settings);
        } catch (\Exception $e) {
            $this->database = null;
            return $this->language->text($e->getMessage());
        }

        return $this->validateDb();
    }

    /**
     * Validate the database is ready for installation process
     * @return boolean|string
     */
    public function validateDb()
    {
        $existing = $this->database->query('SHOW TABLES')->fetchColumn();

        if (empty($existing)) {
            return true;
        }

        return $this->language->text('The database you specified already has tables');
    }

    /**
     * Performs full system installation
     * @param array $data
     * @param array $cli_route
     * @return array
     */
    public function process(array $data, array $cli_route = array())
    {
        $result = null;
        $this->hook->attach('install.before', $data, $result, $cli_route, $this);

        $default_result = array(
            'message' => '',
            'severity' => '',
            'redirect' => null
        );

        if (isset($result)) {
            return array_merge($default_result, (array) $result);
        }

        $result = $this->callHandler($data['installer'], $data);

        $this->hook->attach('install.after', $data, $result, $cli_route, $this);
        return array_merge($default_result, (array) $result);
    }

}
