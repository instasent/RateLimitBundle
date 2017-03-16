<?php

namespace Instasent\RateLimitBundle\Tests;

use Instasent\RateLimitBundle\NoxlogicRateLimitBundle;

class NoxlogicRateLimitBundleTest extends TestCase
{
    public function testBuild()
    {
        //        $container = $this->getMock('\\Symfony\\Component\\DependencyInjection\\ContainerBuilder');
//        $container->expects($this->exactly(0))
//            ->method('addCompilerPass')
//            ->with($this->isInstanceOf('\\Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface'));
//
        $bundle = new NoxlogicRateLimitBundle();
        $this->assertInstanceOf('Instasent\\RateLimitBundle\\NoxlogicRateLimitBundle', $bundle);
//        $bundle->build($container);
    }
}
