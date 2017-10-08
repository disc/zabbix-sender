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
     * Zabbix constructor.
     *
     * @param string $server Zabbix host
     * @param int $port Zabbix port
     */
    public function __construct($server, $port = 10051)
    {
        $this->server = $server;
        $this->port = $port;
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
        return json_encode([
            'request' => 'sender data',
            'data' => $this->getData(),
        ]);
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

        socket_connect($socket, $this->server, $this->port);

        socket_send($socket, $body, strlen($body), 0);

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
        // Get 1024 bytes from socket
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
     *
     * @param resource $socket
     * @return int
     */
    protected function socketReceive($socket)
    {
        return socket_recv($socket, $response, 1024, 0);
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
