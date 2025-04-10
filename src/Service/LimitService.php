<?php

namespace App\Service;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class LimitService
{
    private RedisAdapter $cache;
    private int $ttl = 600; // 10 минут
    private int $blockedUserTime = 3600;  // 1 час
    private int $attemptsLimit = 3;

    public function __construct()
    {
        $this->cache = new RedisAdapter(
            RedisAdapter::createConnection('redis://localhost:6379')
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function checkRequestLimit(string $phone): bool
    {
        $key = "rate_limit_$phone";
        $currentRequests = $this->cache->getItem($key);

        if ($currentRequests->isHit() && $currentRequests->get() >= $this->attemptsLimit) {
            return false;
        }

        $this->incrementRequestCount($key);

        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function incrementRequestCount(string $key): void
    {
        $currentRequests = $this->cache->getItem($key);
        $currentRequests->set($currentRequests->get() + 1);

        $currentRequests->expiresAfter($this->ttl);

        $this->cache->save($currentRequests);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function blockUser(string $phone): void
    {
        $key = "blocked_$phone";

        $blocked = $this->cache->getItem($key);
        $blocked->set(true);
        $blocked->expiresAfter($this->blockedUserTime);

        $this->cache->save($blocked);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function isBlocked(string $phone): bool
    {
        $key = "blocked_$phone";
        $blocked = $this->cache->getItem($key);

        return $blocked->isHit();
    }
}