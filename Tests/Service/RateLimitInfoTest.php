<?php

namespace Instasent\RateLimitBundle\Tests\Annotation;

use Instasent\RateLimitBundle\EventListener\OauthKeyGenerateListener;
use Instasent\RateLimitBundle\Events\GenerateKeyEvent;
use Instasent\RateLimitBundle\Service\RateLimitInfo;
use Instasent\RateLimitBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RateLimitInfoTest extends TestCase
{

    public function testRateInfoSetters()
    {
        $rateInfo = new RateLimitInfo();

        $rateInfo->setLimit(1234);
        $this->assertEquals(1234, $rateInfo->getLimit());

        $rateInfo->setCalls(5);
        $this->assertEquals(5, $rateInfo->getCalls());

        $rateInfo->setResetTimestamp(100000);
        $this->assertEquals(100000, $rateInfo->getResetTimestamp());
    }

}
