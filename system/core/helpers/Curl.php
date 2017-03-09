<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

/**
 * Provides wrappers for CURL functions
 */
class Curl
{

    /**
     * A string containing the last error for the current CURL session
     * @var string
     */
    protected $error = '';

    /**
     * Array of curl response info
     * @var mixed
     */
    protected $info = array();

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

        $this->error = curl_error($ch);

        if (empty($this->error)) {
            $this->info = curl_getinfo($ch);
        }

        curl_close($ch);
        return $response;
    }

    /**
     * Returns an array of default curl options
     * @param string $url
     * @return array
     */
    protected function defaultOptions($url)
    {
        return array(
            CURLOPT_URL => $url,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'GPL Cart Agent'
        );
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

        $fields = '';
        if (isset($options['fields'])) {
            $fields = is_array($options['fields']) ? http_build_query($options['fields']) : (string) $options['fields'];
            unset($options['fields']);
        }

        $options += array(CURLOPT_POSTFIELDS => $fields, CURLOPT_POST => true);

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        $this->error = curl_error($ch);

        if (empty($this->error)) {
            $this->info = curl_getinfo($ch);
        }

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

        $this->error = curl_error($ch);

        if (empty($this->error)) {
            $this->info = $info;
        }

        curl_close($ch);
        return $info;
    }

    /**
     * Returns an array of response info
     * @return array
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Returns a string containing the last error for the current CURL session
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

}
