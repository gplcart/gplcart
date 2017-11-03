<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\install;

use gplcart\core\Config;
use gplcart\core\helpers\Cli as CliHelper,
    gplcart\core\helpers\Session as SessionHelper;
use gplcart\core\models\Install as InstallModel,
    gplcart\core\models\Language as LanguageModel;
use gplcart\core\handlers\install\Base as BaseInstall;

/**
 * Default installer
 */
class DefaultProfile extends BaseInstall
{

    /**
     * @param Config $config
     * @param InstallModel $install
     * @param LanguageModel $language
     * @param SessionHelper $session
     * @param CliHelper $cli
     */
    public function __construct(Config $config, InstallModel $install, LanguageModel $language,
            SessionHelper $session, CliHelper $cli)
    {
        parent::__construct($config, $install, $language, $session, $cli);
    }

    /**
     * Performs full system installation
     * @param array $data
     * @param \gplcart\core\Database $db
     * @return array
     */
    public function install(array $data, $db)
    {
        $this->db = $db;
        $this->data = $data;

        $this->start();
        $result = $this->process();

        if ($result !== true) {
            return $result;
        }

        return $this->finish();
    }

}
