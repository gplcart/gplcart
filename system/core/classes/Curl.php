<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\classes;

/**
 * Provides wrappers for CURL functions
 */
class Curl
{

    /**
     * Performs a GET query
     * @param string $url
     * @param array $options
     * @return string
     */
    public function get($url, array $options = array())
    {
        $options += $this->defaultOptions($url);

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * Performs a POST query
     * @param string $url
     * @param array $options
     * @return string
     */
    public function post($url, array $options = array())
    {
        $options += $this->defaultOptions($url);
        $options += array(CURLOPT_POSTFIELDS => '', CURLOPT_POST => true);

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * Returns an array of header data
     * @param string $url
     * @param array $options
     * @return string
     */
    public function header($url, array $options = array())
    {
        $options += $this->defaultOptions($url);
        $options += array(CURLOPT_HEADER => true, CURLOPT_NOBODY => true);

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $info;
    }

    /**
     * Returns an array of default curl options
     * @param string $url
     * @return array
     */
    protected function defaultOptions($url)
    {
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'GPL Cart Agent');

        return $options;
    }
}
