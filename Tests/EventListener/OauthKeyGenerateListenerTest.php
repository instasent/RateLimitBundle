<?php

namespace Instasent\RateLimitBundle\Tests\Annotation;

use Instasent\RateLimitBundle\EventListener\OauthKeyGenerateListener;
use Instasent\RateLimitBundle\Events\GenerateKeyEvent;
use Instasent\RateLimitBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;

class OauthKeyGenerateListenerTest extends TestCase
{
    protected $mockContext;

    public function setUp()
    {
        if (!class_exists('FOS\\OAuthServerBundle\\Security\\Authentication\\Token\\OAuthToken')) {
            $this->markTestSkipped('FOSOAuth bundle is not found');
        }
        if (interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
            $this->mockContext = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        } else {
            $this->mockContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        }
    }

    public function testListener()
    {
        $mockToken = $this->createMockToken();

        $mockContext = $this->mockContext;
        $mockContext
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($mockToken));

        $event = new GenerateKeyEvent(new Request(), 'foo');

        $listener = new OauthKeyGenerateListener($mockContext);
        $listener->onGenerateKey($event);

        $this->assertEquals('foo:mocktoken', $event->getKey());
    }

    public function testListenerWithoutOAuthToken()
    {
        $mockContext = $this->mockContext;
        $mockContext
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue(new \StdClass()));

        $event = new GenerateKeyEvent(new Request(), 'foo');

        $listener = new OauthKeyGenerateListener($mockContext);
        $listener->onGenerateKey($event);

        $this->assertEquals('foo', $event->getKey());
    }

    private function createMockToken()
    {
        $oauthToken = $this->getMock('FOS\\OAuthServerBundle\\Security\\Authentication\\Token\\OAuthToken');
        $oauthToken
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue('mocktoken'));

        return $oauthToken;
    }
}
