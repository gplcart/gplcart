<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\Config;
use gplcart\core\models\Language as LanguageModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Parent class handlers containing methods to validate single elements
 */
class Element extends BaseValidator
{

    /**
     * @param Config $config
     * @param LanguageModel $language
     */
    public function __construct(Config $config, LanguageModel $language)
    {
        parent::__construct($config, $language);
    }

}
