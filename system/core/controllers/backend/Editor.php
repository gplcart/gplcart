<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Editor as EditorModel;
use gplcart\core\models\Module as ModuleModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to theme editor
 */
class Editor extends BackendController
{

    /**
     * Editor model instance
     * @var \gplcart\core\models\Editor $editor
     */
    protected $editor;

    /**
     * Module model instance
     * @var \gplcart\core\models\Module $module
     */
    protected $module;

    /**
     * The current module
     * @var array
     */
    protected $data_module = array();

    /**
     * The current module file
     * @var string
     */
    protected $data_file;

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
        $this->setModuleEditor($module_id);

        $this->setTitleListEditor();
        $this->setBreadcrumbListEditor();

        $this->setData('module', $this->data_module);
        $this->setData('files', $this->getFilesEditor());

        $this->outputListEditor();
    }

    /**
     * Returns an array of module data
     * @param string $module_id
     * @return array
     */
    protected function setModuleEditor($module_id)
    {
        $module = $this->module->get($module_id);

        if (empty($module)) {
            $this->outputHttpStatus(404);
        }

        if ($module['type'] !== 'theme') {
            $this->outputHttpStatus(403);
        }

        $this->data_module = $module;
        return $module;
    }

    /**
     * Returns an array of files to edit
     * @return array
     */
    protected function getFilesEditor()
    {
        $data = $this->editor->getList($this->data_module);
        return $this->prepareFilesEditor($data);
    }

    /**
     * Prepares an array of files to be edited
     * @param array $data
     * @return array
     */
    protected function prepareFilesEditor(array $data)
    {
        $prepared = array();
        foreach ($data as $folder => $files) {
            foreach ($files as $file) {

                $path = trim(str_replace($this->data_module['directory'], '', $file), '/');
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
     */
    protected function setTitleListEditor()
    {
        $vars = array('%name' => $this->data_module['name']);
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
        $this->setModuleEditor($module_id);
        $this->setFilePathEditor($file_id);

        $this->setTitleEditEditor();
        $this->setBreadcrumbEditEditor();

        $this->setMessageEditEditor();

        $this->setData('module', $this->data_module);
        $this->setData('can_save', $this->canSaveEditor());
        $this->setData('lines', $this->getFileTotalLinesEditor());
        $this->setData('editor.content', $this->getFileContentEditor());

        $this->submitEditor();

        $this->setJsSettingsEditor();
        $this->outputEditEditor();
    }

    /**
     * Sets messages on the file edit page
     */
    protected function setMessageEditEditor()
    {
        if ($this->canSaveEditor()) {
            $message = $this->text('Before saving changes make sure you have a <a href="@url">backup</a> of the current version', array('@url' => $this->url('admin/tool/backup')));
            $this->setMessage($message, 'warning');
        }

        if ($this->current_theme['id'] == $this->data_module['id']) {
            $message = $this->text('You cannot edit the current theme');
            $this->setMessage($message, 'warning');
        }
    }

    /**
     * Sets JavaScript settings on the file edit page
     */
    protected function setJsSettingsEditor()
    {
        $settings = array(
            'file_extension' => pathinfo($this->data_file, PATHINFO_EXTENSION),
            'readonly' => !$this->canSaveEditor()
        );

        $this->setJsSettings('editor', $settings);
    }

    /**
     * Saves an array of submitted data
     * @return null
     */
    protected function submitEditor()
    {
        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('editor', null, false);
        $this->validateEditor();

        if ($this->hasErrors('editor')) {
            return null;
        }

        $this->saveEditor();
    }

    /**
     * Validates a submitted data when editing a theme file
     */
    protected function validateEditor()
    {
        $this->setSubmitted('user_id', $this->uid);
        $this->setSubmitted('path', $this->data_file);
        $this->setSubmitted('module', $this->data_module);

        $this->validate('editor');
    }

    /**
     * Writes a submitted content to a theme file
     */
    protected function saveEditor()
    {
        $this->controlAccessSaveEditor();

        $submitted = $this->getSubmitted();
        $result = $this->editor->save($submitted);

        if ($result === true) {
            $message = $this->text('Theme file has been saved');
            $this->redirect("admin/tool/editor/{$submitted['module']['id']}", $message, 'success');
        }

        $message = $this->text('An error occurred');
        $this->redirect('', $message, 'warning');
    }

    /**
     * Whether the current user can save the file
     */
    protected function canSaveEditor()
    {
        return $this->access('editor_edit')//
                && $this->current_theme['id'] != $this->data_module['id'];
    }

    /**
     * Controls permissions to save a theme file for the current user
     */
    protected function controlAccessSaveEditor()
    {
        if (!$this->canSaveEditor()) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Sets titles on the file edit page
     */
    protected function setTitleEditEditor()
    {
        $vars = array('%path' => substr($this->data_file, strlen(GC_MODULE_DIR . '/')));
        $text = $this->text('Edit file %path', $vars);
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the file edit page
     */
    protected function setBreadcrumbEditEditor()
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
            'url' => $this->url("admin/tool/editor/{$this->data_module['id']}"),
            'text' => $this->text('Edit theme %name', array('%name' => $this->data_module['name']))
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
     * Returns a path to the file to be edited
     * @param string $encoded_filename URL encoded base64 hash
     * @return string
     */
    protected function setFilePathEditor($encoded_filename)
    {
        $filepath = base64_decode(urldecode($encoded_filename));
        $file = "{$this->data_module['directory']}/$filepath";

        if (!is_file($file) || !is_readable($file)) {
            $this->outputHttpStatus(404);
        }

        $this->data_file = $file;
        return $file;
    }

    /**
     * Returns a content of the file
     * @return string
     */
    protected function getFileContentEditor()
    {
        return file_get_contents($this->data_file);
    }

    /**
     * Returns the total number of lines in the file
     * @return integer
     */
    protected function getFileTotalLinesEditor()
    {
        return count(file($this->data_file));
    }

}
