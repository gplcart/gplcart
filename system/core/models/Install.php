<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use Exception;
use gplcart\core\Hook,
    gplcart\core\Handler,
    gplcart\core\Database,
    gplcart\core\Module as ModuleCore;
use gplcart\core\models\Translation as TranslationModel;

/**
 * Manages basic behaviors and data related to system installation
 */
class Install
{

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Module class instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * @param Hook $hook
     * @param ModuleCore $module
     * @param Translation $translation
     */
    public function __construct(Hook $hook, ModuleCore $module, TranslationModel $translation)
    {
        $this->hook = $hook;
        $this->module = $module;
        $this->translation = $translation;
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

            $info = $this->module->getInfo($handler['module']);

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
                'title' => $this->translation->text('Default'),
                'description' => '',
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
        try {
            $handlers = $this->getHandlers();
            $method = isset($data['step']) ? 'install_' . $data['step'] : 'install';
            $result = Handler::call($handlers, $handler_id, $method, array($data, $this->db));
        } catch (Exception $ex) {
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
     * Tries to connect to the database
     * @param array $settings
     * @return boolean|string
     */
    public function connectDb(array $settings)
    {
        try {
            $this->db = new Database;
            $this->db->init($settings);
        } catch (Exception $ex) {
            $this->db = null;
            return $this->translation->text($ex->getMessage());
        }

        return $this->validateDb();
    }

    /**
     * Validate the database is ready for installation process
     * @return boolean|string
     */
    public function validateDb()
    {
        $existing = $this->db->query('SHOW TABLES')->fetchColumn();

        if (empty($existing)) {
            return true;
        }

        return $this->translation->text('The database you specified already has tables');
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
