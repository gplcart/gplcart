<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\Container as Container;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Methods to validate single elements
 */
class Element extends BaseValidator
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->language = Container::get('gplcart\\core\\models\\Language');
    }

}
