<?php

namespace Instasent\RateLimitBundle\Tests\Util;

use Instasent\RateLimitBundle\Tests\TestCase;
use Instasent\RateLimitBundle\Util\PathLimitProcessor;
use Symfony\Component\HttpFoundation\Request;

class PathLimitProcessorTest extends TestCase
{
    /** @test */
    public function itReturnsNullIfThereAreNoPathLimits()
    {
        $plp = new PathLimitProcessor([]);

        $result = $plp->getRateLimit(new Request());

        $this->assertNull($result);
    }

    /** @test */
    public function itReturnARateLimitIfItMatchesPathAndMethod()
    {
        $plp = new PathLimitProcessor([
            'api' => [
                'path'    => 'api/',
                'methods' => ['GET'],
                'limit'   => 100,
                'period'  => 60,
            ],
        ]);

        $result = $plp->getRateLimit(
            Request::create('/api/', 'GET')
        );

        $this->assertInstanceOf(
            'Instasent\RateLimitBundle\Annotation\RateLimit',
            $result
        );

        $this->assertEquals(100, $result->getLimit());
        $this->assertEquals(60, $result->getPeriod());
        $this->assertEquals(['GET'], $result->getMethods());
    }

    /** @test */
    public function itReturnARateLimitIfItMatchesSubPathWithUrlEncodedString()
    {
        $plp = new PathLimitProcessor([
            'api' => [
                'path'    => 'api',
                'methods' => ['GET'],
                'limit'   => 100,
                'period'  => 60,
            ],
        ]);

        $result = $plp->getRateLimit(
            Request::create('%2Fapi%2Fusers', 'GET')
        );

        $this->assertInstanceOf(
            'Instasent\RateLimitBundle\Annotation\RateLimit',
            $result
        );

        $this->assertEquals(100, $result->getLimit());
        $this->assertEquals(60, $result->getPeriod());
        $this->assertEquals(['GET'], $result->getMethods());
    }

    /** @test */
    public function itWorksWhenMultipleMethodsAreSpecified()
    {
        $plp = new PathLimitProcessor([
            'api' => [
                'path'    => 'api/',
                'methods' => ['GET', 'POST'],
                'limit'   => 1000,
                'period'  => 600,
            ],
        ]);

        $result = $plp->getRateLimit(
            Request::create('/api/', 'POST')
        );

        $this->assertEquals(1000, $result->getLimit());
        $this->assertEquals(600, $result->getPeriod());
        $this->assertEquals(['GET', 'POST'], $result->getMethods());
    }

    /** @test */
    public function itReturnsTheCorrectRateLimitWithMultiplePathLimits()
    {
        $plp = new PathLimitProcessor([
            'api' => [
                'path'    => 'api/',
                'methods' => ['GET', 'POST'],
                'limit'   => 1000,
                'period'  => 600,
            ],
            'api2' => [
                'path'    => 'api2/',
                'methods' => ['POST'],
                'limit'   => 20,
                'period'  => 15,
            ],
        ]);

        $result = $plp->getRateLimit(
            Request::create('/api2/', 'POST')
        );

        $this->assertEquals(20, $result->getLimit());
        $this->assertEquals(15, $result->getPeriod());
        $this->assertEquals(['POST'], $result->getMethods());
    }

    /** @test */
    public function itWorksWithLimitsOnSamePathButDifferentMethods()
    {
        $plp = new PathLimitProcessor([
            'api_get' => [
                'path'    => 'api/',
                'methods' => ['GET'],
                'limit'   => 1000,
                'period'  => 600,
            ],
            'api_post' => [
                'path'    => 'api/',
                'methods' => ['POST'],
                'limit'   => 200,
                'period'  => 150,
            ],
        ]);

        $result = $plp->getRateLimit(
            Request::create('/api/', 'POST')
        );

        $this->assertEquals(200, $result->getLimit());
        $this->assertEquals(150, $result->getPeriod());
        $this->assertEquals(['POST'], $result->getMethods());
    }

    /** @test */
    public function itMatchesAstrixAsAnyMethod()
    {
        $plp = new PathLimitProcessor([
            'api' => [
                'path'    => 'api/',
                'methods' => ['*'],
                'limit'   => 100,
                'period'  => 60,
            ],
        ]);

        $result = $plp->getRateLimit(
            Request::create('/api/users/emails', 'GET')
        );

        $this->assertEquals(100, $result->getLimit());
        $this->assertEquals(60, $result->getPeriod());
        $this->assertEquals(['*'], $result->getMethods());

        $result = $plp->getRateLimit(
            Request::create('/api/users/emails', 'PUT')
        );

        $this->assertEquals(100, $result->getLimit());
        $this->assertEquals(60, $result->getPeriod());
        $this->assertEquals(['*'], $result->getMethods());

        $result = $plp->getRateLimit(
            Request::create('/api/users/emails', 'POST')
        );

        $this->assertEquals(100, $result->getLimit());
        $this->assertEquals(60, $result->getPeriod());
        $this->assertEquals(['*'], $result->getMethods());
    }

    /** @test */
    public function itMatchesWhenAccessSubPaths()
    {
        $plp = new PathLimitProcessor([
            'api' => [
                'path'    => 'api/',
                'methods' => ['GET'],
                'limit'   => 100,
                'period'  => 60,
            ],
        ]);

        $result = $plp->getRateLimit(
            Request::create('/api/users/emails', 'GET')
        );

        $this->assertEquals(100, $result->getLimit());
        $this->assertEquals(60, $result->getPeriod());
        $this->assertEquals(['GET'], $result->getMethods());
    }

    /** @test */
    public function itReturnsNullIfThereIsNoMatchingPath()
    {
        $plp = new PathLimitProcessor([
            'api' => [
                'path'    => 'api/users/emails',
                'methods' => ['GET'],
                'limit'   => 100,
                'period'  => 60,
            ],
        ]);

        $result = $plp->getRateLimit(
            Request::create('/api', 'GET')
        );

        $this->assertNull($result);
    }

    /** @test */
    public function itMatchesTheMostSpecificPathFirst()
    {
        $plp = new PathLimitProcessor([
            'api' => [
                'path'    => 'api',
                'methods' => ['GET'],
                'limit'   => 5,
                'period'  => 1,
            ],
            'api_emails' => [
                'path'    => 'api/users/emails',
                'methods' => ['GET'],
                'limit'   => 100,
                'period'  => 60,
            ],
        ]);

        $result = $plp->getRateLimit(
            Request::create('/api/users/emails', 'GET')
        );

        $this->assertEquals(100, $result->getLimit());
        $this->assertEquals(60, $result->getPeriod());
        $this->assertEquals(['GET'], $result->getMethods());
    }

    /** @test */
    public function itReturnsTheMatchedPath()
    {
        $plp = new PathLimitProcessor([
            'api' => [
                'path'    => 'api/',
                'methods' => ['GET', 'POST'],
                'limit'   => 1000,
                'period'  => 600,
            ],
        ]);

        $path = $plp->getMatchedPath(
            Request::create('/api/', 'POST')
        );

        $this->assertEquals('api', $path);
    }

    /** @test */
    public function itReturnsTheCorrectPathForADifferentSetup()
    {
        $plp = new PathLimitProcessor([
            'api' => [
                'path'    => 'api',
                'methods' => ['GET'],
                'limit'   => 5,
                'period'  => 1,
            ],
            'api_emails' => [
                'path'    => 'api/users/emails',
                'methods' => ['GET'],
                'limit'   => 100,
                'period'  => 60,
            ],
        ]);

        $path = $plp->getMatchedPath(
            Request::create('/api/users/emails', 'GET')
        );

        $this->assertEquals('api/users/emails', $path);
    }

    /** @test */
    public function itReturnsTheCorrectMatchedPathForSubPaths()
    {
        $plp = new PathLimitProcessor([
            'api' => [
                'path'    => 'api/',
                'methods' => ['GET'],
                'limit'   => 100,
                'period'  => 60,
            ],
        ]);

        $path = $plp->getMatchedPath(
            Request::create('/api/users/emails', 'GET')
        );

        $this->assertEquals('api', $path);
    }
}
