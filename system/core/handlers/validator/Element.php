<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Parent class for element validators
 */
class Element extends BaseValidator
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

}
