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
    gplcart\core\Handler,
    gplcart\core\Cache;
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
        return is_readable(GC_CONFIG_COMMON);
    }

    /**
     * Returns an array of defined handlers
     * @return string
     */
    public function getHandlers()
    {
        $handlers = $this->getDefaultHandler();

        $this->hook->fire('install.handlers', $handlers, $this);

        array_walk($handlers, function(&$value, $key) {
            $value['id'] = $key;
        });

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
                'title' => $this->language->text('Default'),
                'description' => $this->language->text('Default system installer'),
                'handlers' => array(
                    'process' => array('gplcart\\core\\handlers\\install\\Install', 'process')
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

        try {
            $result = Handler::call($handlers, $handler_id, 'process', array($data, $this->database, $this));
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
        $requirements = &Cache::memory(__METHOD__);

        if (isset($requirements)) {
            return (array) $requirements;
        }

        $requirements = require GC_CONFIG_REQUIREMENT;
        $this->hook->fire('install.requirements', $requirements);
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
            $this->database = new Database($settings);
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
        $this->hook->fire('install.before', $data, $cli_route, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        $default_result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->language->text('An error occurred')
        );

        if (!isset($data['installer'])) {
            $data['installer'] = 'default';
        }

        $result = array_merge($default_result, $this->callHandler($data['installer'], $data));

        $this->hook->fire('install.after', $data, $cli_route, $result, $this);
        return (array) $result;
    }

}
