<?php

namespace Instasent\RateLimitBundle\Tests\EventListener;

use Instasent\RateLimitBundle\Service\RateLimitInfo;
use Instasent\RateLimitBundle\Service\Storage\StorageInterface;

class MockStorage implements StorageInterface
{
    protected $rates;

    /**
     * Get information about the current rate.
     *
     * @param string $key
     *
     * @return RateLimitInfo Rate limit information
     */
    public function getRateInfo($key)
    {
        $info = $this->rates[$key];

        $rateLimitInfo = new RateLimitInfo();
        $rateLimitInfo->setCalls($info['calls']);
        $rateLimitInfo->setResetTimestamp($info['reset']);
        $rateLimitInfo->setLimit($info['limit']);

        return $rateLimitInfo;
    }

    /**
     * Limit the rate by one.
     *
     * @param string $key
     *
     * @return RateLimitInfo Rate limit info
     */
    public function limitRate($key)
    {
        if (!isset($this->rates[$key])) {
            return;
        }

        if ($this->rates[$key]['reset'] <= time()) {
            unset($this->rates[$key]);

            return;
        }

        $this->rates[$key]['calls']++;

        return $this->getRateInfo($key);
    }

    /**
     * Create a new rate entry.
     *
     * @param string $key
     * @param int    $limit
     * @param int    $period
     *
     * @return \Instasent\RateLimitBundle\Service\RateLimitInfo
     */
    public function createRate($key, $limit, $period)
    {
        $this->rates[$key] = ['calls' => 1, 'limit' => $limit, 'reset' => (time() + $period)];

        return $this->getRateInfo($key);
    }

    /**
     * Reset the rating.
     *
     * @param $key
     */
    public function resetRate($key)
    {
        unset($this->rates[$key]);
    }

    public function createMockRate($key, $limit, $period, $calls)
    {
        $this->rates[$key] = ['calls' => $calls, 'limit' => $limit, 'reset' => (time() + $period)];

        return $this->getRateInfo($key);
    }
}
