<?php

namespace Instasent\RateLimitBundle\Tests\DependencyInjection;

use Instasent\RateLimitBundle\DependencyInjection\Configuration;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * ConfigurationTest.
 */
class ConfigurationTest extends WebTestCase
{
    /**
     * @var Processor
     */
    private $processor;

    public function setUp()
    {
        $this->processor = new Processor();
    }

    private function getConfigs(array $configArray)
    {
        $configuration = new Configuration();

        return $this->processor->processConfiguration($configuration, [$configArray]);
    }

    public function testUnconfiguredConfiguration()
    {
        $configuration = $this->getConfigs([]);

        $this->assertSame([
            'enabled'                 => true,
            'storage_engine'          => 'redis',
            'redis_client'            => 'default_client',
            'memcache_client'         => 'default',
            'doctrine_provider'       => null,
            'rate_response_code'      => 429,
            'rate_response_exception' => null,
            'rate_response_message'   => 'You exceeded the rate limit',
            'display_headers'         => true,
            'headers'                 => [
                'limit'     => 'X-RateLimit-Limit',
                'remaining' => 'X-RateLimit-Remaining',
                'reset'     => 'X-RateLimit-Reset',
            ],
            'path_limits' => [],
        ], $configuration);
    }

    public function testDisabledConfiguration()
    {
        $configuration = $this->getConfigs(['enabled' => false]);

        $this->assertArrayHasKey('enabled', $configuration);
        $this->assertFalse($configuration['enabled']);
    }

    public function testPathLimitConfiguration()
    {
        $pathLimits = [
            'api' => [
                'path'    => 'api/',
                'methods' => ['GET'],
                'limit'   => 100,
                'period'  => 60,
            ],
        ];

        $configuration = $this->getConfigs([
            'path_limits' => $pathLimits,
        ]);

        $this->assertArrayHasKey('path_limits', $configuration);
        $this->assertEquals($pathLimits, $configuration['path_limits']);
    }

    public function testMultiplePathLimitConfiguration()
    {
        $pathLimits = [
            'api' => [
                'path'    => 'api/',
                'methods' => ['GET', 'POST'],
                'limit'   => 200,
                'period'  => 10,
            ],
            'api2' => [
                'path'    => 'api2/',
                'methods' => ['*'],
                'limit'   => 1000,
                'period'  => 15,
            ],
        ];

        $configuration = $this->getConfigs([
            'path_limits' => $pathLimits,
        ]);

        $this->assertArrayHasKey('path_limits', $configuration);
        $this->assertEquals($pathLimits, $configuration['path_limits']);
    }

    public function testDefaultPathLimitMethods()
    {
        $pathLimits = [
            'api' => [
                'path'    => 'api/',
                'methods' => ['GET', 'POST'],
                'limit'   => 200,
                'period'  => 10,
            ],
            'api2' => [
                'path'   => 'api2/',
                'limit'  => 1000,
                'period' => 15,
            ],
        ];

        $configuration = $this->getConfigs([
            'path_limits' => $pathLimits,
        ]);

        $pathLimits['api2']['methods'] = ['*'];

        $this->assertArrayHasKey('path_limits', $configuration);
        $this->assertEquals($pathLimits, $configuration['path_limits']);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMustBeBasedOnExceptionClass()
    {
        $configuration = $this->getConfigs(['rate_response_exception' => '\StdClass']);
    }

    public function testMustBeBasedOnExceptionClass2()
    {
        $configuration = $this->getConfigs(['rate_response_exception' => '\InvalidArgumentException']);

        // no exception triggered is ok.
        $this->assertTrue(true);
    }
}
