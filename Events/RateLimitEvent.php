<?php

namespace Instasent\RateLimitBundle\Events;

use Instasent\RateLimitBundle\Annotation\RateLimit;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class RateLimitEvent extends Event
{

    /** @var Request */
    protected $request;

    /** @var string */
    protected $rateLimit;

    public function __construct(Request $request, RateLimit $rateLimitAnnotation)
    {
        $this->request = $request;
        $this->rateLimit = $rateLimitAnnotation;
    }

    /**
     * @return RateLimit
     */
    public function getRateLimit()
    {
        return $this->rateLimit;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

}
