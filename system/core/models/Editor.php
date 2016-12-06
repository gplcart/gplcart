<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\helpers\File as FileHelper;
use core\models\Backup as BackupModel;
use core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to theme editor
 */
class Editor extends Model
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Backup model instance
     * @var \core\models\Backup $backup
     */
    protected $backup;

    /**
     * Constructor
     * @param LanguageModel $language
     * @param BackupModel $backup
     */
    public function __construct(LanguageModel $language, BackupModel $backup)
    {
        parent::__construct();

        $this->backup = $backup;
        $this->language = $language;
    }

    /**
     * Returns an array of editable files
     * @param array $module
     * @return array
     */
    public function getList(array $module)
    {
        $list = array();
        foreach (array('templates', 'css', 'js') as $folder) {
            $pattern = "{$module['directory']}/$folder/*";
            $files = FileHelper::scanRecursive($pattern);
            sort($files);
            $list[$folder] = $files;
        }

        return $list;
    }

    /**
     * Saves an edited file
     * @param string $content
     * @param string $file
     * @param array $module
     * @return boolean
     */
    public function save($content, $file, array $module)
    {
        $this->hook->fire('save.editor.before', $content, $file, $module);

        if (empty($module)) {
            return false;
        }

        $has_backup = true;
        if (!$this->hasBackup($module)) {
            $has_backup = $this->backup($module);
        }

        if ($has_backup !== true) {
            return false;
        }

        $result = $this->write($content, $file);

        $this->hook->fire('save.editor.after', $content, $file, $module, $result);
        return $result;
    }

    /**
     * Writes a content to a file
     * @param string $content
     * @param string $file
     * @return boolean
     */
    protected function write($content, $file)
    {
        if (!file_exists($file)) {
            return false; // Do not create a new file
        }

        $result = file_put_contents($file, $content);
        return ($result !== false);
    }

    /**
     * Whether a module ID has a backup
     * @param array $module
     * @return boolean
     */
    public function hasBackup(array $module)
    {
        $existing = $this->backup->getList(array('module_id' => $module['id']));
        return !empty($existing);
    }

    /**
     * Creates a module backup
     * @param array $module
     * @return boolean|string
     */
    protected function backup(array $module)
    {
        $vars = array('@name' => $module['name'], '@date' => date("D M j G:i:s"));
        $name = $this->language->text('Theme @name. Automatically backed up on @date', $vars);

        $data = array(
            'name' => $name,
            'module' => $module,
            'module_id' => $module['id']
        );

        $result = $this->backup->backup('module', $data);

        // On success the result must contain a numeric ID of inserted database record
        return (is_numeric($result) && $result > 0);
    }

}
