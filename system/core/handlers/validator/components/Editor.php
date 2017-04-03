<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\helpers\Twig as TwigHelper,
    gplcart\core\models\Module as ModuleModel,
    gplcart\core\Controller as BaseController;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate editing theme templates
 */
class Editor extends ComponentValidator
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
    public function editor(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateModuleEditor();
        $this->validateFileEditor();
        $this->validateTwigEditor();
        $this->validateUserIdComponent();

        return $this->getResult();
    }

    /**
     * Validates a theme module
     * @return boolean
     */
    protected function validateModuleEditor()
    {
        $module = $this->getSubmitted('module');

        if (!is_array($module)) {
            // We have only module ID, so load the module
            $module = $this->module->get($module);
        }

        // Invalid module ID or it's not a theme module
        if (empty($module['type']) || $module['type'] !== 'theme') {
            $vars = array('@name' => $this->language->text('Module'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('module', $error);
            return false;
        }

        $this->setSubmitted('module', $module);
        return true;
    }

    /**
     * Validates a template file
     * @return boolean
     */
    protected function validateFileEditor()
    {
        if ($this->isError('module')) {
            return null;
        }

        $path = $this->getSubmitted('path');
        $module = $this->getSubmitted('module');

        // Make the path absolute if it's not
        if (strpos($path, $module['directory']) === false) {
            $path = "{$module['directory']}/$path";
        }

        if (is_file($path) && is_writable($path)) {
            $this->setSubmitted('path', $path);
            return true;
        }

        $vars = array('@name' => $this->language->text('File'));
        $error = $this->language->text('@name is unavailable', $vars);
        $this->setError('path', $error);
        return false;
    }

    /**
     * Validates a Twig source code
     * @return boolean|null
     */
    protected function validateTwigEditor()
    {
        if ($this->isError('path')) {
            return null;
        }

        $path = $this->getSubmitted('path');

        // Twig templates always have .twig extension
        if (pathinfo($path, PATHINFO_EXTENSION) !== 'twig') {
            return null;
        }

        $content = $this->getSubmitted('content');

        if (empty($content)) {
            return null;
        }

        $result = $this->twig->validate($content, $path, $this->controller);

        if ($result === true) {
            return true;
        }

        $this->setError('content', (string) $result);
        return false;
    }

}
