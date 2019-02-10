<?php

namespace Disc\Zabbix;

class Sender
{
    /**
     * Zabbix host
     *
     * @var string
     */
    protected $server;

    /**
     * Zabbix port
     *
     * @var int
     */
    protected $port;

    /**
    * Request data
    *
    * @var array
    */
    protected $data = [];

    /**
     * Last response body
     *
     * @var array
     */
    protected $response = [];

    /**
     * Timeout in seconds
     *
     * @var float
     */
    protected $timeout;

    /**
     * Zabbix constructor.
     *
     * @param string $server  Zabbix host
     * @param int    $port    Zabbix port
     * @param float  $timeout Connection timeout 1 second by default
     */
    public function __construct($server, $port = 10051, $timeout = 0.0)
    {
        $this->server  = $server;
        $this->port    = $port;
        $this->timeout = (float)$timeout;
    }

    /**
     * Send data to Zabbix
     *
     * @param string $host Host
     * @param string $key Key
     * @param mixed $value Value
     * @param int $clock Timestamp
     * @return \Disc\Zabbix\Sender
     */
    public function addData(
        $host,
        $key,
        $value,
        $clock = null
    ) {
        $data = [
            'host' => $host,
            'key' => $key,
            'value' => $value,
        ];

        if ($clock) {
            $data['clock'] = $clock;
        }

        $this->data[] = $data;

        return $this;
    }

    /**
     * Send data to Zabbix
     *
     * @return \Disc\Zabbix\Sender
     */
    public function send()
    {
        $this->sendData(
            $this->buildRequestBody()
        );

        $this->clearData();

        return $this;
    }

    /**
     * Returns response array
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns array of data
     *
     * @return array
     */
    protected function getData()
    {
        return $this->data;
    }

    /**
     * Returns json encoded request

     * @return string
     */
    protected function buildRequestBody()
    {
        $data = json_encode([
            'request' => 'sender data',
            'data' => $this->getData(),
        ]);

        return "ZBXD\1" . pack('V', strlen($data)) . "\0\0\0\0" . $data;
    }

    /**
     * Send data to zabbix by socket
     *
     * @param string $body Request body
     * @return void
     */
    protected function sendData($body)
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$socket) {
            throw new \RuntimeException(socket_strerror(socket_last_error()));
        }

        // Set send and receive timeout
        $timeoutSettings = ['sec' => floor($this->timeout), 'usec' => ($this->timeout - floor($this->timeout)) * 1e6];
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $timeoutSettings);
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeoutSettings);

        $result = socket_connect($socket, $this->server, $this->port);
        if (!$result) {
            throw new \RuntimeException(socket_strerror(socket_last_error($socket)));
        }

        $result = socket_send($socket, $body, strlen($body), 0);
        if (false === $result) {
            throw new \RuntimeException(socket_strerror(socket_last_error($socket)));
        }

        $this->parseResponse($socket);

        socket_close($socket);
    }

    /**
     * Parse response from socket
     *
     * @param resource $socket
     * @return \Disc\Zabbix\Sender
     */
    protected function parseResponse($socket)
    {
        $response = $this->socketReceive($socket);

        // Length of header in response 13 bytes
        $headerLength = 13;

        if ($response) {
            $this->response = json_decode(mb_substr($response, $headerLength), true);
        }

        return $this;
    }

    /**
     * Socket receive wrapper
     * Returns first 1024 bytes of data from socket
     *
     * @param resource $socket
     * @return string
     */
    protected function socketReceive($socket)
    {
        socket_recv($socket, $response, 1024, 0);

        return $response;
    }

    /**
     * Clear request data
     *
     * @return \Disc\Zabbix\Sender
     */
    protected function clearData()
    {
        $this->data = [];

        return $this;
    }
}
