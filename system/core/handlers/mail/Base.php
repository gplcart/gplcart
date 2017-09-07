<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\mail;

use gplcart\core\Container,
    gplcart\core\Handler;

/**
 * Base mail data handler class
 */
class Base extends Handler
{

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

        $this->user = Container::get('gplcart\\core\\models\\User');
        $this->store = Container::get('gplcart\\core\\models\\Store');
        $this->language = Container::get('gplcart\\core\\models\\Language');
    }

    /**
     * Returns a string containing default e-mail signature
     * @param array $options Store settings
     * @return string
     */
    protected function getSignature(array $options)
    {
        $signature = /* @text */"\r\n\r\n-------------------------------------\r\n@owner\r\n@address\r\n@phone\r\n@fax\r\n@store_email\r\n@map";

        $replacements = array();
        $replacements['@owner'] = empty($options['owner']) ? '' : $options['owner'];
        $replacements['@address'] = empty($options['address']) ? '' : $options['address'];
        $replacements['@phone'] = empty($options['phone']) ? '' : $this->language->text('Tel: @phone', array('@phone' => implode(',', $options['phone'])));
        $replacements['@fax'] = empty($options['fax']) ? '' : $this->language->text('Fax: @fax', array('@fax' => implode(',', $options['fax'])));
        $replacements['@store_email'] = empty($options['email']) ? '' : $this->language->text('E-mail: @store_email', array('@store_email' => implode(',', $options['email'])));
        $replacements['@map'] = empty($options['map']) ? '' : $this->language->text('Find us on Google Maps: @map', array('@map' => 'http://maps.google.com/?q=' . implode(',', $options['map'])));

        return rtrim(gplcart_string_format($signature, $replacements), "\t\n\r\0\x0B-");
    }

}
