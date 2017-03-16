<?php

namespace Instasent\RateLimitBundle\Tests\Service\Storage;

use Instasent\RateLimitBundle\Service\Storage\Memcache;
use Instasent\RateLimitBundle\Tests\TestCase;

class MemcacheTest extends TestCase
{
    public function setUp()
    {
        if (!class_exists('\\MemCached')) {
            $this->markTestSkipped('MemCached extension not installed');
        }
    }

    public function testgetRateInfo()
    {
        $client = @$this->getMock('\\Memcached', ['get']);
        $client->expects($this->once())
              ->method('get')
              ->with('foo')
              ->will($this->returnValue(['limit' => 100, 'calls' => 50, 'reset' => 1234]));

        $storage = new Memcache($client);
        $rli = $storage->getRateInfo('foo');
        $this->assertInstanceOf('Instasent\\RateLimitBundle\\Service\\RateLimitInfo', $rli);
        $this->assertEquals(100, $rli->getLimit());
        $this->assertEquals(50, $rli->getCalls());
        $this->assertEquals(1234, $rli->getResetTimestamp());
    }

    public function testcreateRate()
    {
        $client = @$this->getMock('\\Memcached', ['set', 'get']);
        $client->expects($this->exactly(1))
              ->method('set');

        $storage = new Memcache($client);
        $storage->createRate('foo', 100, 123);
    }

    public function testLimitRateNoKey()
    {
        $client = @$this->getMock('\\Memcached', ['get', 'cas', 'getResultCode']);
        $client->expects($this->any())
                ->method('getResultCode')
                ->willReturn(\Memcached::RES_SUCCESS);
        $client->expects($this->atLeastOnce())
              ->method('get')
              ->with('foo')
              ->will($this->returnValue(['limit' => 100, 'calls' => 1, 'reset' => 1234]));
        $client->expects($this->atLeastOnce())
              ->method('cas')
              ->with(null, 'foo')
              ->will($this->returnValue(true));

        $storage = new Memcache($client);
        $storage->limitRate('foo');
    }

    public function testLimitRateWithKey()
    {
        $client = @$this->getMock('\\Memcached', ['get', 'cas', 'getResultCode']);
        $client->expects($this->any())
                ->method('getResultCode')
                ->willReturn(\Memcached::RES_SUCCESS);
        $client->expects($this->atLeastOnce())
              ->method('get')
              ->with('foo')
              ->willReturn(false);

        $storage = new Memcache($client);
        $storage->limitRate('foo');
    }

    public function testresetRate()
    {
        $client = @$this->getMock('\\Memcached', ['delete']);
        $client->expects($this->once())
              ->method('delete')
              ->with('foo');

        $storage = new Memcache($client);
        $this->assertTrue($storage->resetRate('foo'));
    }
}
