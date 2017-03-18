<?php
namespace Disc\Zabbix\Tests;

use Disc\Zabbix\Sender;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;

/**
 * Class SenderTest
 * @package Disc\Zabbix\Tests
 *
 * @covers Disc\Zabbix\Sender
 * @coversDefaultClass Disc\Zabbix\Sender
 */
class SenderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|\Disc\Zabbix\Sender
     */
    protected $sender;

    public function setUp()
    {
        $this->sender = new Sender('localhost', 10051);

        $this->sender = $this->getMockBuilder('Sender')
            ->disableOriginalConstructor()
            ->setMethods(['send','getResponse'])
            ->getMock();

        $this->sender
            ->expects($this->any())
            ->method('send')
            ->willReturn($this->sender);

        $this->sender
            ->expects($this->any())
            ->method('getResponse')
            ->willReturn([
                'response' => 'success',
                'info' => 'processed: 1; failed: 1; total: 2; seconds spent: 0.000021',
            ]);
    }

    /**
     * Test for getData
     *
     * @covers ::getData
     */
    public function testAddData()
    {
        $method = new ReflectionMethod('Sender', 'getData');
        $method->setAccessible(true);

        $this->assertCount(0, $method->invoke($this->sender));
        $this->sender->addData('Host', 'test.key', 'some value');
        $this->sender->addData('Host', 'another.key', 123);
        $this->sender->addData('Host', 'one.more.key', 0.001, time());
        $this->assertCount(3, $method->invoke($this->sender));
    }

    /**
     * Test for clear data
     *
     * @covers ::getData
     * @covers ::clearData
     */
    public function testClearData()
    {
        $methodGetData = new ReflectionMethod(Sender::class, 'getData');
        $methodGetData->setAccessible(true);

        $methodClearData = new ReflectionMethod(Sender::class, 'clearData');
        $methodClearData->setAccessible(true);

        $this->sender->addData('Host', 'test.key', 'some value');
        $this->sender->addData('Host', 'another.key', 123);
        $this->assertCount(2, $methodGetData->invoke($this->sender));

        $methodClearData->invoke($this->sender);
        $this->assertCount(0, $methodGetData->invoke($this->sender));
    }

    /**
     * Test for send
     *
     * @covers ::send
     */
    public function testSend()
    {
        $this->sender->addData('host', 'some.key', 'test value');
        $this->sender->addData('host', 'some.key.2', 134);
        $this->sender->send();
        $this->assertNotEmpty($this->sender->getResponse());
    }

    /**
     * Test for getResponse
     *
     * @covers ::getResponse
     */
    public function testGetResponse()
    {
        $this->sender->addData('host', 'some.key.2', 134);
        $this->sender->send();
        $response = $this->sender->getResponse();
        $this->assertArrayHasKey('response', $response);
        $this->assertArrayHasKey('info', $response);
    }
}