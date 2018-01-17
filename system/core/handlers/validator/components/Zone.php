<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\Zone as ZoneModel;
use gplcart\core\handlers\validator\Component as BaseComponentValidator;

/**
 * Provides methods to validate geo zones
 */
class Zone extends BaseComponentValidator
{

    /**
     * Review model instance
     * @var \gplcart\core\models\Zone $zone
     */
    protected $zone;

    /**
     * @param ZoneModel $zone
     */
    public function __construct(ZoneModel $zone)
    {
        parent::__construct();

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
            $this->setErrorUnavailable('update', $this->translation->text('Zone'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

}
