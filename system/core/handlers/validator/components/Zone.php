<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

// Parent
use gplcart\core\Config;
use gplcart\core\models\File as FileModel,
    gplcart\core\models\User as UserModel,
    gplcart\core\models\Store as StoreModel,
    gplcart\core\models\Alias as AliasModel,
    gplcart\core\helpers\Request as RequestHelper,
    gplcart\core\models\Language as LanguageModel;
// New
use gplcart\core\models\Zone as ZoneModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate geo zones
 */
class Zone extends ComponentValidator
{

    /**
     * Review model instance
     * @var \gplcart\core\models\Zone $zone
     */
    protected $zone;

    /**
     * @param Config $config
     * @param LanguageModel $language
     * @param FileModel $file
     * @param UserModel $user
     * @param StoreModel $store
     * @param AliasModel $alias
     * @param RequestHelper $request
     * @param ZoneModel $zone
     */
    public function __construct(Config $config, LanguageModel $language, FileModel $file,
            UserModel $user, StoreModel $store, AliasModel $alias, RequestHelper $request,
            ZoneModel $zone)
    {
        parent::__construct($config, $language, $file, $user, $store, $alias, $request);

        $this->zone = $zone;
    }

    /**
     * Performs full zone data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function zone(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateZone();
        $this->validateStatus();
        $this->validateTitle();

        return $this->getResult();
    }

    /**
     * Validates a zone to be updated
     * @return boolean|null
     */
    protected function validateZone()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->zone->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->language->text('Zone'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

}
