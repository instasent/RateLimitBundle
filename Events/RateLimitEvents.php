<?php

namespace Instasent\RateLimitBundle\Events;

final class RateLimitEvents
{
    const PRE_CREATE_RATE_LIMIT = 'ratelimit.pre.create';

    const GENERATE_KEY = 'ratelimit.generate.key';
}
