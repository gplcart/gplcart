<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Zone as ZoneModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate geo zones
 */
class Zone extends BaseValidator
{

    /**
     * Review model instance
     * @var \core\models\Zone $zone
     */
    protected $zone;

    /**
     * Constructor
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
    public function zone(array &$submitted, array $options)
    {
        $this->submitted = &$submitted;

        $this->validateZone($options);
        $this->validateStatus($options);
        $this->validateTitle($options);

        return $this->getResult();
    }

    /**
     * Validates a zone to be updated
     * @param array $options
     * @return boolean|null
     */
    protected function validateZone(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->zone->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Zone'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

}
