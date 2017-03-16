<?php

namespace Instasent\RateLimitBundle\Service\Storage;

use Instasent\RateLimitBundle\Service\RateLimitInfo;

class Memcache implements StorageInterface
{
    /** @var \Memcached */
    protected $client;

    public function __construct(\Memcached $client)
    {
        $this->client = $client;
    }

    public function getRateInfo($key)
    {
        $info = $this->client->get($key);

        $rateLimitInfo = new RateLimitInfo();
        $rateLimitInfo->setLimit($info['limit']);
        $rateLimitInfo->setCalls($info['calls']);
        $rateLimitInfo->setResetTimestamp($info['reset']);

        return $rateLimitInfo;
    }

    public function limitRate($key)
    {
        $cas = null;
        do {
            $info = $this->client->get($key, null, $cas);
            if (!$info) {
                return false;
            }

            $info['calls']++;
            $this->client->cas($cas, $key, $info);
        } while ($this->client->getResultCode() != \Memcached::RES_SUCCESS);

        return $this->getRateInfo($key);
    }

    public function createRate($key, $limit, $period)
    {
        $info = [];
        $info['limit'] = $limit;
        $info['calls'] = 1;
        $info['reset'] = time() + $period;

        $this->client->set($key, $info, $period);

        return $this->getRateInfo($key);
    }

    public function resetRate($key)
    {
        $this->client->delete($key);

        return true;
    }
}
