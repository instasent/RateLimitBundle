<?php

namespace Instasent\RateLimitBundle\Tests\Annotation;

use Instasent\RateLimitBundle\Events\RateLimitEvents;
use Instasent\RateLimitBundle\Tests\TestCase;

class RateLimitEventsTest extends TestCase
{
    public function testConstants()
    {
        $this->assertEquals('ratelimit.generate.key', RateLimitEvents::GENERATE_KEY);
    }
}
