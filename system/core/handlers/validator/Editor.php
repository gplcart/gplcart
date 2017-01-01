<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\helpers\Twig as TwigHelper;
use gplcart\core\models\Module as ModuleModel;
use gplcart\core\Controller as BaseController;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate editing theme templates
 */
class Editor extends BaseValidator
{

    /**
     * Twig helper instance
     * @var \gplcart\core\helpers\Twig $twig
     */
    protected $twig;

    /**
     * Module model instance
     * @var \gplcart\core\models\Module $module
     */
    protected $module;

    /**
     * Base controller class instance
     * Needed to correctly validate Twig markup
     * @var \gplcart\core\Controller $controller
     */
    protected $controller;

    /**
     * Constructor
     * @param ModuleModel $module
     * @param TwigHelper $twig
     * @param BaseController $controller
     */
    public function __construct(ModuleModel $module, TwigHelper $twig,
            BaseController $controller)
    {
        parent::__construct();

        $this->twig = $twig;
        $this->module = $module;
        $this->controller = $controller;
    }

    /**
     * Performs validation of submitted data when editing templates
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function editor(array &$submitted, array $options)
    {
        $this->submitted = &$submitted;

        $this->validateModuleEditor($options);
        $this->validateFileEditor($options);
        $this->validateTwigEditor($options);
        $this->validateUserId($options);

        return $this->getResult();
    }

    /**
     * Validates a theme module
     * @param array $options
     * @return boolean
     */
    protected function validateModuleEditor(array $options)
    {
        $module = $this->getSubmitted('module', $options);

        if (!is_array($module)) {
            // We have only module ID, so load the module
            $module = $this->module->get($module);
        }

        // Invalid module ID or it's not a theme module
        if (empty($module['type']) || $module['type'] !== 'theme') {
            $vars = array('@name' => $this->language->text('Module'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('module', $error, $options);
            return false;
        }

        $this->setSubmitted('module', $module, $options);
        return true;
    }

    /**
     * Validates a template file
     * @param array $options
     * @return boolean
     */
    protected function validateFileEditor(array $options)
    {
        if ($this->isError('module', $options)) {
            return null;
        }

        $path = $this->getSubmitted('path', $options);
        $module = $this->getSubmitted('module', $options);

        // Make the path absolute if it's not
        if (strpos($path, $module['directory']) === false) {
            $path = "{$module['directory']}/$path";
        }

        if (is_file($path) && is_writable($path)) {
            $this->setSubmitted('path', $path, $options);
            return true;
        }

        $vars = array('@name' => $this->language->text('File'));
        $error = $this->language->text('@name is unavailable', $vars);
        $this->setError('path', $error, $options);
        return false;
    }

    /**
     * Validates a Twig source code
     * @param array $options
     * @return boolean|null
     */
    protected function validateTwigEditor(array $options)
    {
        if ($this->isError('path', $options)) {
            return null;
        }

        $path = $this->getSubmitted('path', $options);

        // Twig templates always have .twig extension
        if (pathinfo($path, PATHINFO_EXTENSION) !== 'twig') {
            return null;
        }

        $content = $this->getSubmitted('content', $options);

        if (empty($content)) {
            return null;
        }

        $result = $this->twig->validate($content, $path, $this->controller);

        if ($result === true) {
            return true;
        }

        $this->setError('content', $result, $options);
        return false;
    }

}
