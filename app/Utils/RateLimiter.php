<?php

namespace app\Utils;

/**
 * Basit bir süreç içi rate limiter.
 * Not: Çoklu proses/worker durumunda kalıcı bir depo (Redis/MySQL) kullanmak gerekir.
 */
class RateLimiter
{
\tprivate int $intervalMs;
\tprivate int $lastAtMs = 0;

\tpublic function __construct(int $intervalMs)
\t{
\t\t$this->intervalMs = max(0, $intervalMs);
\t}

\tpublic function awaitNext(): void
\t{
\t\tif ($this->intervalMs <= 0) { return; }
\t\t$now = (int)floor(microtime(true) * 1000);
\t\t$next = $this->lastAtMs + $this->intervalMs;
\t\tif ($next > $now) {
\t\t\t$delay = $next - $now;
\t\t\tusleep($delay * 1000);
\t\t\t$now = (int)floor(microtime(true) * 1000);
\t\t}
\t\t$this->lastAtMs = $now;
\t}
}


