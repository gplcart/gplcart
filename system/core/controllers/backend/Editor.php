<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\backend;

use core\models\Editor as EditorModel;
use core\models\Module as ModuleModel;
use core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to theme editor
 */
class Editor extends BackendController
{

    /**
     * Editor model instance
     * @var \core\models\Editor $editor
     */
    protected $editor;

    /**
     * Module model instance
     * @var \core\models\Module $module
     */
    protected $module;

    /**
     * Constructor
     * @param EditorModel $editor
     * @param ModuleModel $module
     */
    public function __construct(EditorModel $editor, ModuleModel $module)
    {
        parent::__construct();

        $this->editor = $editor;
        $this->module = $module;
    }

    /**
     * Displays the module file overview page
     * @param integer $module_id
     */
    public function listEditor($module_id)
    {
        $module = $this->getModuleEditor($module_id);
        $files = $this->getFilesEditor($module);

        $this->setData('files', $files);
        $this->setData('module', $module);

        $this->setTitleListEditor($module);
        $this->setBreadcrumbListEditor();
        $this->outputListEditor();
    }

    /**
     * Returns an array of module data
     * @param string $module_id
     * @return array
     */
    protected function getModuleEditor($module_id)
    {
        $module = $this->module->get($module_id);

        if (empty($module)) {
            $this->outputError(404);
        }

        if ($module['type'] === 'theme') {
            return $module;
        }

        $this->outputError(403);
    }

    /**
     * 
     * @param type $module
     * @return type
     */
    protected function getFilesEditor(array $module)
    {
        $files = $this->editor->getList($module);
        return $this->prepareFilesEditor($files, $module);
    }

    /**
     * 
     * @param array $data
     * @param array $module
     * @return type
     */
    protected function prepareFilesEditor(array $data, array $module)
    {
        $prepared = array();
        foreach ($data as $folder => $files) {
            foreach ($files as $file) {

                $path = trim(str_replace($module['directory'], '', $file), '/');
                $depth = substr_count($path, '/');

                $pathinfo = pathinfo($path);

                $directory = is_dir($file);
                $parent = $directory ? $path : $pathinfo['dirname'];

                $prepared[$folder][$parent][] = array(
                    'file' => $file,
                    'path' => $path,
                    'depth' => $depth,
                    'directory' => $directory,
                    'name' => $pathinfo['basename'],
                    'id' => urlencode(base64_encode($path)),
                    'indentation' => str_repeat('<span class="indentation"></span>', $depth)
                );
            }

            ksort($prepared[$folder]);
        }
        return $prepared;
    }

    /**
     * Sets title on theme files overview page
     * @param array $module
     */
    protected function setTitleListEditor(array $module)
    {
        $vars = array('%name' => $module['name']);
        $text = $this->text('Edit theme %name', $vars);
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on theme files overview page
     */
    protected function setBreadcrumbListEditor()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/module/list'),
            'text' => $this->text('Modules')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders templates of theme files overview page
     */
    protected function outputListEditor()
    {
        $this->output('tool/editor/list');
    }

    /**
     * Displays the file edit page
     * @param string $module_id
     * @param string $file_id
     */
    public function editEditor($module_id, $file_id)
    {
        $module = $this->getModuleEditor($module_id);

        $file = $this->getFilePathEditor($module, $file_id);
        $content = $this->getFileContentEditor($file);
        $lines = $this->getFileTotalLinesEditor($file);

        $can_save = $this->canSaveEditor($module);

        $this->setData('lines', $lines);
        $this->setData('module', $module);
        $this->setData('can_save', $can_save);
        $this->setData('editor.content', $content);

        $this->submitEditor($module, $file);

        $this->setTitleEditEditor($module, $file);
        $this->setBreadcrumbEditEditor($module);
        $this->setJsSettingsEditor($file);
        $this->outputEditEditor();
    }
    
    /**
     * Sets JavaScript settings on the file edit page
     * @param string $file
     */
    protected function setJsSettingsEditor($file)
    {
        $settings = array(
            'file_extension' => pathinfo($file, PATHINFO_EXTENSION));

        $this->setJsSettings('editor', $settings);
    }

    /**
     * Saves an array of submitted data
     * @param array $module
     * @param string $file
     * @return null
     */
    protected function submitEditor(array $module, $file)
    {
        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('editor', null, false);
        $this->validateEditor($module, $file);

        if ($this->hasErrors('editor')) {
            return null;
        }

        $this->saveEditor();
        return null;
    }

    /**
     * Validates a submitted data when editing a theme file
     * @param array $module
     * @param string $file
     */
    protected function validateEditor(array $module, $file)
    {
        $this->setSubmitted('file', $file);
        $this->setSubmitted('module', $module);
    }

    /**
     * Writes a submitted content to a theme file
     */
    protected function saveEditor()
    {
        $file = $this->getSubmitted('file');
        $module = $this->getSubmitted('module');
        $content = $this->getSubmitted('content');

        $this->controlAccessSaveEditor($module);

        $result = $this->editor->save($content, $file, $module);

        if ($result === true) {
            $message = $this->text('Theme file has been saved');
            $this->redirect("admin/tool/editor/{$module['id']}", $message, 'success');
        }

        $message = $this->text('An error occurred');
        $this->redirect('', $message, 'warning');
    }

    /**
     * Whether the current user can save the file
     * @param array $module
     */
    protected function canSaveEditor(array $module)
    {
        return ($this->access('editor_edit') && $this->theme !== $module['id']);
    }

    /**
     * Controls permissions to save a theme file for the current user
     * @param array $module
     */
    protected function controlAccessSaveEditor(array $module)
    {
        if (!$this->canSaveEditor($module)) {
            $this->outputError(403);
        }
    }

    /**
     * Sets titles on the file edit page
     * @param array $module
     * @param string $filepath
     */
    protected function setTitleEditEditor(array $module, $filepath)
    {
        $vars = array('%path' => $filepath);
        $text = $this->text('Edit file %path', $vars);
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the file edit page
     * @param array $module
     */
    protected function setBreadcrumbEditEditor(array $module)
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/module/list'),
            'text' => $this->text('Modules')
        );

        $breadcrumbs[] = array(
            'url' => $this->url("admin/tool/editor/{$module['id']}"),
            'text' => $this->text('Edit theme %name', array('%name' => $module['name']))
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the file edit page
     */
    protected function outputEditEditor()
    {
        $this->output('tool/editor/edit');
    }

    /**
     * Returns an array of the file to be edited
     * @param array $module
     * @param string $encoded_filename URL encoded base64 hash
     * @return array|null
     */
    protected function getFilePathEditor(array $module, $encoded_filename)
    {
        $filepath = base64_decode(urldecode($encoded_filename));
        $file = "{$module['directory']}/$filepath";

        if (!is_file($file) || !is_readable($file)) {
            $this->outputError(404);
        }

        return $file;
    }

    /**
     * Returns a content of the file
     * @param string $file
     * @return string
     */
    protected function getFileContentEditor($file)
    {
        return file_get_contents($file);
    }

    /**
     * Returns the total number of lines in the file
     * @param string $file
     * @return integer
     */
    protected function getFileTotalLinesEditor($file)
    {
        return count(file($file));
    }

}
