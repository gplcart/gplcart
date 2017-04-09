<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\modules\twig\Twig as TwigModule,
    gplcart\core\models\Module as ModuleModel,
    gplcart\core\Controller as BaseController;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate editing theme templates
 */
class Editor extends ComponentValidator
{

    /**
     * Library instance
     * @var \gplcart\modules\twig\Twig $twig
     */
    protected $twig_module;

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
     * @param ModuleModel $module
     * @param TwigModule $twig_module
     * @param BaseController $controller
     */
    public function __construct(ModuleModel $module, TwigModule $twig_module,
            BaseController $controller)
    {
        parent::__construct();

        $this->module = $module;
        $this->controller = $controller;
        $this->twig_module = $twig_module;
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
            $module = $this->module->get($module);
        }

        if (empty($module['type']) || $module['type'] !== 'theme') {
            $this->setErrorUnavailable('module', $this->language->text('Module'));
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

        if (strpos($path, $module['directory']) === false) {
            $path = "{$module['directory']}/$path";
        }

        if (is_file($path) && is_writable($path)) {
            $this->setSubmitted('path', $path);
            return true;
        }

        $this->setErrorUnavailable('path', $this->language->text('File'));
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

        $info = pathinfo($this->getSubmitted('path'));

        if ($info['extension'] !== 'twig') {
            return null;
        }

        $content = $this->getSubmitted('content');

        if (empty($content)) {
            return null;
        }

        $this->twig_module->initTwig();
        $twig = $this->twig_module->getTwigInstance($info['dirname'], $this->controller);

        try {
            $twig->parse($twig->tokenize(new \Twig_Source($content, $info['basename'])));
            return true;
        } catch (\Twig_Error_Syntax $e) {
            $this->setError('content', $e->getMessage());
        }

        return false;
    }

}
