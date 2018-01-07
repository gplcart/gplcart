<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\mail;

use gplcart\core\Container;

/**
 * Base mail handler class
 */
class Base
{

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Store model instance
     * @var \gplcart\core\models\Store $store
     */
    protected $store;

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = Container::get('gplcart\\core\\Config');
        $this->user = Container::get('gplcart\\core\\models\\User');
        $this->store = Container::get('gplcart\\core\\models\\Store');
        $this->translation = Container::get('gplcart\\core\\models\\Translation');
    }

    /**
     * Sets a property
     * @param string $name
     * @param mixed $value
     */
    public function setProperty($name, $value)
    {
        $this->{$name} = $value;
    }

    /**
     * Returns a string containing default e-mail signature
     * @param array $options
     * @return string
     */
    protected function getSignature(array $options)
    {
        $signature = /* @text */"\r\n\r\n-------------------------------------\r\n@owner\r\n@address\r\n@phone\r\n@fax\r\n@store_email\r\n@map";

        $replacements = array();
        $replacements['@owner'] = empty($options['owner']) ? '' : $options['owner'];
        $replacements['@address'] = empty($options['address']) ? '' : $options['address'];
        $replacements['@phone'] = empty($options['phone']) ? '' : $this->translation->text('Tel: @phone', array('@phone' => implode(',', $options['phone'])));
        $replacements['@fax'] = empty($options['fax']) ? '' : $this->translation->text('Fax: @fax', array('@fax' => implode(',', $options['fax'])));
        $replacements['@store_email'] = empty($options['email']) ? '' : $this->translation->text('E-mail: @store_email', array('@store_email' => implode(',', $options['email'])));
        $replacements['@map'] = empty($options['map']) ? '' : $this->translation->text('Find us on Google Maps: @map', array('@map' => 'http://maps.google.com/?q=' . implode(',', $options['map'])));

        return rtrim(gplcart_string_format($signature, $replacements), "\t\n\r\0\x0B-");
    }

}
