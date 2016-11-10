<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Zone as ModelsZone;
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
     * @param ModelsZone $zone
     */
    public function __construct(ModelsZone $zone)
    {
        parent::__construct();

        $this->zone = $zone;
    }

    /**
     * Performs full zone data validation
     * @param array $submitted
     */
    public function zone(array &$submitted)
    {
        $this->validateZone($submitted);
        $this->validateStatus($submitted);
        $this->validateTitle($submitted);

        return empty($this->errors) ? true : $this->errors;
    }

    /**
     * Validates a zone to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateZone(array &$submitted)
    {
        if (empty($submitted['update']) || !is_numeric($submitted['update'])) {
            return null;
        }

        $data = $this->zone->get($submitted['update']);

        if (empty($data)) {
            $options = array('@name' => $this->language->text('Zone'));
            $this->errors['zone_id'] = $this->language->text('Object @name does not exist', $options);
            return false;
        }

        $submitted['update'] = $data;
        return true;
    }

}
