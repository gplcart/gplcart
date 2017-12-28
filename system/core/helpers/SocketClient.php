<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

use OutOfBoundsException,
    InvalidArgumentException,
    UnexpectedValueException;

/**
 * Provides wrappers for PHP's stream socket client
 */
class SocketClient
{

    /**
     * An array of parsed URL parameters
     * @var mixed
     */
    protected $uri;

    /**
     * The current stream resource
     * @var resource
     */
    protected $stream;

    /**
     * Address to the socket to connect to
     * @var string
     */
    protected $socket;

    /**
     * Number of seconds until the connect() system call should timeout
     * @var float
     */
    protected $timeout = 30.0;

    /**
     * An array of connection header data
     * @var array
     */
    protected $headers = array('User-Agent' => 'GPLCart');

    /**
     * The request method type
     * @var string
     */
    protected $method = 'GET';

    /**
     * A request data
     * @var mixed
     */
    protected $data;

    /**
     * Raw response data
     * @var string
     */
    protected $response;

    /**
     * A context resource
     * @var resource
     */
    protected $context;

    /**
     * Destructor
     */
    public function __destruct()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }

        $this->stream = null;
    }

    /**
     * Returns the request data
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns the request method
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns headers
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Returns the request URI
     * @return array
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Returns the request timeout
     * @return float
     */
    public function getTimeOut()
    {
        return $this->timeout;
    }

    /**
     * Returns an address of the socket
     * @return string
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * Returns socket stream resource
     * @return resource
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Returns a string containing raw response data
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns the current context resource
     * @return resource
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets the context resource
     * @param resource|array $context
     * @param array $options
     * @return $this
     * @throws UnexpectedValueException
     */
    public function setContext($context, array $options = array())
    {
        if (isset($context) && !is_resource($context)) {
            $context = stream_context_create((array) $context, $options);
        }

        if (!is_resource($context)) {
            throw new UnexpectedValueException('Stream context is not a valid resource');
        }

        $this->context = $context;
        return $this;
    }

    /**
     * Set the request data
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Sets the request method
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Sets the request header
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Sets a single header item
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Sets the request URI
     * @param string|array $uri
     * @return $this
     */
    public function setUri($uri)
    {
        if (!is_array($uri)) {
            $uri = parse_url((string) $uri);
        }

        $this->uri = $uri;
        return $this;
    }

    /**
     * Sets the request timeout
     * @param int|string $timeout
     * @return $this
     */
    public function setTimeOut($timeout)
    {
        $this->timeout = (float) $timeout;
        return $this;
    }

    /**
     * Sets a socket depending on the current URI parameters
     * @return $this
     * @throws OutOfBoundsException
     * @throws InvalidArgumentException
     */
    public function setSocket()
    {
        if (empty($this->uri['scheme'])) {
            throw new OutOfBoundsException('Unknown URL scheme');
        }

        if ($this->uri['scheme'] === 'http') {
            $port = 80;
            $protocol = 'tcp';
        } else if ($this->uri['scheme'] === 'https') {
            $port = 443;
            $protocol = 'ssl';
        } else {
            throw new InvalidArgumentException("Unsupported URL scheme: {$this->uri['scheme']}");
        }

        if (isset($this->uri['port'])) {
            $port = $this->uri['port'];
        }

        $this->socket = "$protocol://{$this->uri['host']}:$port";

        if (!isset($this->headers['Host'])) {
            $this->headers['Host'] = "{$this->uri['host']}:$port";
        }

        return $this;
    }

    /**
     * Open Internet or Unix domain socket connection and execute a query
     * @throws UnexpectedValueException
     * @return $this
     */
    public function exec()
    {
        $errno = $errstr = null;

        if (isset($this->context)) {
            $this->stream = stream_socket_client($this->socket, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $this->context);
        } else {
            $this->stream = stream_socket_client($this->socket, $errno, $errstr, $this->timeout);
        }

        if (!empty($errstr)) {
            throw new UnexpectedValueException($errstr);
        }

        fwrite($this->stream, $this->getRequestBody());

        $this->response = '';
        while (!feof($this->stream)) {
            $this->response .= fgets($this->stream, 1024);
        }

        fclose($this->stream);
        $this->stream = null;

        return $this;
    }

    /**
     * Returns the request body
     * @return string
     */
    protected function getRequestBody()
    {
        $this->prepareHeaders();

        $path = isset($this->uri['path']) ? $this->uri['path'] : '/';

        if (isset($this->uri['query'])) {
            $path .= "?{$this->uri['query']}";
        }

        $body = "{$this->method} $path HTTP/1.0\r\n";

        foreach ($this->headers as $name => $value) {
            $body .= "$name: " . trim($value) . "\r\n";
        }

        if (is_array($this->data)) {
            $this->data = http_build_query($this->data);
        }

        $body .= "\r\n{$this->data}";
        return $body;
    }

    /**
     * Prepare request headers
     */
    protected function prepareHeaders()
    {
        if (!isset($this->headers['Content-Length'])) {
            $content_length = strlen($this->data);
            if ($content_length > 0 || $this->method === 'POST' || $this->method === 'PUT') {
                $this->headers['Content-Length'] = $content_length;
            }
        }

        if (isset($this->uri['user']) && !isset($this->headers['Authorization'])) {
            $pass = isset($this->uri['pass']) ? ':' . $this->uri['pass'] : ':';
            $this->headers['Authorization'] = 'Basic ' . base64_encode($this->uri['user'] . $pass);
        }
    }

    /**
     * Returns an array of formatted response
     * @return array
     */
    public function getFormattedResponse()
    {
        list($header, $data) = preg_split("/\r\n\r\n|\n\n|\r\r/", $this->response, 2);

        $headers = preg_split("/\r\n|\n|\r/", $header);
        $status = explode(' ', trim(reset($headers)), 3);

        $result = array(
            'status' => '',
            'http' => $status[0],
            'code' => $status[1]
        );

        if (isset($status[2])) {
            $result['status'] = $status[2];
        }

        return array('data' => $data, 'status' => $result);
    }

    /**
     * Shortcut method to perform an HTTP request
     * @param string $url
     * @param array $options
     * @return array
     */
    public function request($url, array $options = array())
    {
        $options += array(
            'headers' => array(),
            'method' => 'GET',
            'data' => null,
            'timeout' => 30,
            'context' => null,
        );

        if (!empty($options['query'])) {
            $url = strtok($url, '?') . '?' . http_build_query($options['query']);
        }

        return $this->setUri($url)
                        ->setHeaders($options['headers'])
                        ->setMethod($options['method'])
                        ->setData($options['data'])
                        ->setTimeOut($options['timeout'])
                        ->setContext($options['context'])
                        ->setSocket()
                        ->exec()
                        ->getFormattedResponse();
    }

}
