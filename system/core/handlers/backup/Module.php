<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\backup;

use gplcart\core\helpers\Zip as ZipHelper;
use gplcart\core\models\Language as LanguageModel;

/**
 * Provides methods to backup modules
 */
class Module
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Zip helper class instance
     * @var \gplcart\core\helpers\Zip $zip
     */
    protected $zip;

    /**
     * @param LanguageModel $language
     * @param ZipHelper $zip
     */
    public function __construct(LanguageModel $language, ZipHelper $zip)
    {
        $this->zip = $zip;
        $this->language = $language;
    }

    /**
     * Creates a module backup
     * @param array $data
     * @param \gplcart\core\models\Backup $model
     * @return boolean
     */
    public function backup(array $data, $model)
    {
        $data['type'] = 'module';

        $vars = array('@name' => $data['name'], '@date' => date("D M j G:i:s"));
        $data['name'] = $this->language->text('Module @name. Saved @date', $vars);

        $time = date('d-m-Y--G-i');
        $path = GC_PRIVATE_BACKUP_DIR . "/module_{$data['module_id']}_{$time}.zip";
        $destination = gplcart_file_unique($path);

        $success = $this->zip->folder($data['directory'], $destination, $data['module_id']);

        if ($success) {
            $data['path'] = gplcart_file_relative_path($destination);
            return (bool) $model->add($data);
        }

        return false;
    }

}
