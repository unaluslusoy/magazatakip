<?php

namespace app\Utils;

/**
 * Basit bir süreç içi rate limiter.
 * Not: Çoklu proses/worker durumunda kalıcı bir depo (Redis/MySQL) kullanmak gerekir.
 */
class RateLimiter
{
    private int $intervalMs;
    private int $lastAtMs = 0;

    public function __construct(int $intervalMs)
    {
        $this->intervalMs = max(0, $intervalMs);
    }

    public function awaitNext(): void
    {
        if ($this->intervalMs <= 0) { return; }
        $now = (int)floor(microtime(true) * 1000);
        $next = $this->lastAtMs + $this->intervalMs;
        if ($next > $now) {
            $delay = $next - $now;
            usleep($delay * 1000);
            $now = (int)floor(microtime(true) * 1000);
        }
        $this->lastAtMs = $now;
    }
}


