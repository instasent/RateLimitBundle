<?php

namespace Instasent\RateLimitBundle\Tests\Service\Storage;

use Instasent\RateLimitBundle\Service\Storage\Redis;
use Instasent\RateLimitBundle\Tests\TestCase;

class RedisTest extends TestCase
{
    public function setUp()
    {
        if (!class_exists('Predis\\Client')) {
            $this->markTestSkipped('Predis client not installed');
        }
    }

    public function testgetRateInfo()
    {
        $client = $this->getMock('Predis\\Client', ['hgetall']);
        $client->expects($this->once())
              ->method('hgetall')
              ->with('foo')
              ->will($this->returnValue(['limit' => 100, 'calls' => 50, 'reset' => 1234]));

        $storage = new Redis($client);
        $rli = $storage->getRateInfo('foo');
        $this->assertInstanceOf('Instasent\\RateLimitBundle\\Service\\RateLimitInfo', $rli);
        $this->assertEquals(100, $rli->getLimit());
        $this->assertEquals(50, $rli->getCalls());
        $this->assertEquals(1234, $rli->getResetTimestamp());
    }

    public function testcreateRate()
    {
        $client = $this->getMock('Predis\\Client', ['hset', 'expire', 'hgetall']);
        $client->expects($this->once())
              ->method('expire')
              ->with('foo', 123);
        $client->expects($this->exactly(3))
              ->method('hset')
              ->withConsecutive(
                    ['foo', 'limit', 100],
                    ['foo', 'calls', 1],
                    ['foo', 'reset']
              );

        $storage = new Redis($client);
        $storage->createRate('foo', 100, 123);
    }

    public function testLimitRateNoKey()
    {
        $client = $this->getMock('Predis\\Client', ['hexists']);
        $client->expects($this->once())
              ->method('hexists')
              ->with('foo', 'limit')
              ->will($this->returnValue(false));

        $storage = new Redis($client);
        $this->assertFalse($storage->limitRate('foo'));
    }

    public function testLimitRateWithKey()
    {
        $client = $this->getMock('Predis\\Client', ['hexists', 'hincrby', 'hgetall']);
        $client->expects($this->once())
              ->method('hexists')
              ->with('foo', 'limit')
              ->will($this->returnValue(true));
        $client->expects($this->once())
              ->method('hincrby')
              ->with('foo', 'calls', 1)
              ->will($this->returnValue(true));

        $storage = new Redis($client);
        $storage->limitRate('foo');
    }

    public function testresetRate()
    {
        $client = $this->getMock('Predis\\Client', ['hdel']);
        $client->expects($this->once())
              ->method('hdel')
              ->with('foo');

        $storage = new Redis($client);
        $this->assertTrue($storage->resetRate('foo'));
    }
}
