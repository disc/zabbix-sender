<?php

namespace Disc\Zabbix\Tests;

use AspectMock\Kernel;
use AspectMock\Test;
use Disc\Zabbix\Sender;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\Mock;
use PHPUnit_Framework_TestCase;

/**
 * Class SenderTest
 * @package \Disc\Zabbix\Tests
 *
 * @covers \Disc\Zabbix\Sender
 * @coversDefaultClass \Disc\Zabbix\Sender
 */
class SenderTest extends PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Prepare
     */
    public function setUp()
    {
        $kernel = Kernel::getInstance();
        $kernel->init([
            'debug' => true,
            'includePaths' => [__DIR__.'/../src'],
            'cacheDir' => sys_get_temp_dir(),
        ]);
    }

    /**
     * Test for getData
     *
     * @covers ::getData
     * @covers ::send
     */
    public function testAddData()
    {
        /** @var Mock|Sender $sender */
        
        $sender = \Mockery::mock(Sender::class . '[sendData]', ['localhost']);
        $sender->shouldAllowMockingProtectedMethods();
        $data = json_encode([
            'request' => 'sender data',
            'data' => [],
        ]);
        $sender->shouldReceive('sendData')->once()->with("ZBXD\1" . pack('V', strlen($data)) . "\0\0\0\0" . $data);

        $sender->send();

        $sender->addData('Host', 'test.key', 'some value');
        $sender->addData('Host', 'another.key', 123);
        $expectedTime = time();
        $sender->addData('Host', 'one.more.key', 0.001, $expectedTime);
        $data = json_encode([
            'request' => 'sender data',
            'data' => [
                [
                    'host' => 'Host',
                    'key' => 'test.key',
                    'value' => 'some value',
                ],
                [
                    'host' => 'Host',
                    'key' => 'another.key',
                    'value' => 123,
                ],
                [
                    'host' => 'Host',
                    'key' => 'one.more.key',
                    'value' => 0.001,
                    'clock' => $expectedTime,
                ],
            ],
        ]);
        $sender->shouldReceive('sendData')->once()->with("ZBXD\1" . pack('V', strlen($data)) . "\0\0\0\0" . $data);
        $sender->send();
    }

    /**
     * Test for clear data
     *
     * @covers ::getData
     * @covers ::clearData
     * @covers ::send
     */
    public function testClearData()
    {
        /** @var Mock|Sender $sender */
        $sender = \Mockery::mock(Sender::class . '[sendData]', ['localhost']);
        $sender->shouldAllowMockingProtectedMethods();

        $sender->addData('Host', 'test.key', 'some value');
        $data = json_encode([
            'request' => 'sender data',
            'data' => [
                [
                    'host' => 'Host',
                    'key' => 'test.key',
                    'value' => 'some value',
                ]
            ],
        ]);
        $sender->shouldReceive('sendData')->once()->with("ZBXD\1" . pack('V', strlen($data)) . "\0\0\0\0" . $data);
        $sender->send();
        $data = json_encode([
            'request' => 'sender data',
            'data' => [],
        ]);
        $sender->shouldReceive('sendData')->once()->with("ZBXD\1" . pack('V', strlen($data)) . "\0\0\0\0" . $data);
        $sender->send();
    }

    /**
     * Test for getResponse
     *
     * @covers ::getResponse
     */
    public function testGetResponse()
    {
        test::func('Disc\Zabbix', 'socket_create', function () { return true; });
        test::func('Disc\Zabbix', 'socket_set_option', '');
        test::func('Disc\Zabbix', 'socket_connect', function () { return true; });
        test::func('Disc\Zabbix', 'socket_send', '');
        test::func('Disc\Zabbix', 'socket_close', '');

        /** @var Mock|Sender $sender */
        $sender = \Mockery::mock(Sender::class)->makePartial();
        $sender->shouldAllowMockingProtectedMethods();
        $sender->shouldReceive('socketReceive')->andReturn('header       {"code": 100}');
        $sender->send();
        $this->assertSame(["code" => 100], $sender->getResponse());
    }

    /**
     * Test for getResponse
     *
     * @covers ::getResponse
     */
    public function testGetResponseFailedCreation()
    {
        test::clean();
        test::func('Disc\Zabbix', 'socket_create', function () { return false; });

        /** @var Mock|Sender $sender */
        $sender = \Mockery::mock(Sender::class)->makePartial();
        $this->expectException('RuntimeException');
        $sender->send();
    }

    /**
     * Test for getResponse
     *
     * @covers ::getResponse
     */
    public function testGetResponseFailedConnection()
    {
        test::clean();
        test::func('Disc\Zabbix', 'socket_connect', function () { return false; });

        /** @var Mock|Sender $sender */
        $sender = \Mockery::mock(Sender::class)->makePartial();
        $this->expectException('RuntimeException');
        $sender->send();
    }

    /**
     * Test for getResponse
     *
     * @covers ::getResponse
     */
    public function testGetResponseFailedSend()
    {
        test::clean();
        test::func('Disc\Zabbix', 'socket_connect', function () { return true; });
        test::func('Disc\Zabbix', 'socket_send', function () { return false; });

        /** @var Mock|Sender $sender */
        $sender = \Mockery::mock(Sender::class)->makePartial();
        $this->expectException('RuntimeException');
        $sender->send();
    }
}
