<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\State as ModelsState;
use core\models\Language as ModelsLanguage;

/**
 * Provides methods to validate various database related data
 */
class State
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsState $state
     */
    public function __construct(ModelsLanguage $language, ModelsState $state)
    {
        $this->state = $state;
        $this->language = $language;
    }

    /**
     * Checks if a state code is unique for a given country
     * @param string $code
     * @param array $options
     * @return boolean|string
     */
    public function codeUnique($code, array $options = array())
    {
        $state = $options['data']['state'];
        $country = $options['data']['country'];

        if (isset($state['code']) && $state['code'] === $code) {
            return true;
        }

        $existing = $this->state->getByCode($code, $country['code']);

        if (empty($existing)) {
            return true;
        }

        return $this->language->text('State code %code already exists for country %country', array(
                    '%code' => $code, '%country' => $country['name']));
    }

}
