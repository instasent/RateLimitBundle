<?php

namespace Instasent\RateLimitBundle\Tests\DependencyInjection;

use Instasent\RateLimitBundle\DependencyInjection\NoxlogicRateLimitExtension;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * ConfigurationTest.
 */
class NoxlogicRateLimitExtensionTest extends WebTestCase
{
    public function testAreParametersSet()
    {
        $extension = new NoxlogicRateLimitExtension();
        $containerBuilder = new ContainerBuilder(new ParameterBag());
        $extension->load([], $containerBuilder);

        $this->assertEquals($containerBuilder->getParameter('instasent_rate_limit.rate_response_code'), 429);
        $this->assertEquals($containerBuilder->getParameter('instasent_rate_limit.display_headers'), true);
        $this->assertEquals($containerBuilder->getParameter('instasent_rate_limit.headers.reset.name'), 'X-RateLimit-Reset');
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException
     */
    public function testNoParametersWhenDisabled()
    {
        $extension = new NoxlogicRateLimitExtension();
        $containerBuilder = new ContainerBuilder(new ParameterBag());
        $extension->load(['enabled' => false], $containerBuilder);

        $containerBuilder->getParameter('instasent_rate_limit.rate_response_code');
    }

    public function testPathLimitsParameter()
    {
        $pathLimits = [
            'api' => [
                'path'    => 'api/',
                'methods' => ['GET'],
                'limit'   => 100,
                'period'  => 60,
            ],
        ];

        $extension = new NoxlogicRateLimitExtension();
        $containerBuilder = new ContainerBuilder(new ParameterBag());
        $extension->load([['path_limits' => $pathLimits]], $containerBuilder);

        $this->assertEquals($containerBuilder->getParameter('instasent_rate_limit.path_limits'), $pathLimits);
    }
}
