<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\mail\data;

use gplcart\core\Container;

/**
 * Base mail data handler class
 */
class Base
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
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = Container::get('gplcart\\core\\Config');
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
        $replacements = array();
        $signature = array("\r\n\r\n-------------------------------------");

        if (!empty($options['owner'])) {
            $signature[] = '@owner';
            $replacements['@owner'] = $options['owner'];
        }

        if (!empty($options['address'])) {
            $signature[] = '@address';
            $replacements['@address'] = $options['address'];
        }

        if (!empty($options['phone'])) {
            $signature[] = $this->language->text('Tel: @phone');
            $replacements['@phone'] = implode(',', $options['phone']);
        }

        if (!empty($options['fax'])) {
            $signature[] = $this->language->text('Fax: @fax');
            $replacements['@fax'] = implode(',', $options['fax']);
        }

        if (!empty($options['email'])) {
            $signature[] = $this->language->text('E-mail: @store_email');
            $replacements['@store_email'] = implode(',', $options['email']);
        }

        if (!empty($options['map'])) {
            $signature[] = $this->language->text('Find us on Google Maps: @map');
            $replacements['@map'] = 'http://maps.google.com/?q=' . implode(',', $options['map']);
        }

        if (empty($signature)) {
            return '';
        }

        return gplcart_string_format(implode("\r\n", $signature), $replacements);
    }

}
