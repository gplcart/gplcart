<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\helpers\Socket as SocketHelper;
use gplcart\core\Hook;

/**
 * Manages basic behaviors and data related to HTTP requests
 */
class Http
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Socket helper class instance
     * @var \gplcart\core\helpers\Socket $socket
     */
    protected $socket;

    /**
     * @param Hook $hook
     * @param SocketHelper $socket
     */
    public function __construct(Hook $hook, SocketHelper $socket)
    {
        $this->hook = $hook;
        $this->socket = $socket;
    }

    /**
     * Perform an HTTP request
     * @param string $url
     * @param array $options
     * @return mixed
     */
    public function request($url, $options = array())
    {
        $result = null;
        $this->hook->attach('http.request.before', $url, $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $result = $this->socket->request($url, $options);
        $this->hook->attach('http.request.after', $url, $options, $result, $this);
        return $result;
    }
}
