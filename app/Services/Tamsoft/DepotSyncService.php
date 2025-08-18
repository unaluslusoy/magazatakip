<?php

namespace app\Services\Tamsoft;

use app\Models\TamsoftStockRepo;
use app\Models\TamsoftStockConfig;
use app\Http\TamsoftHttpClient;
use app\Utils\RateLimiter;

/**
 * Depo bazlı stok senkron servisi (iskele).
 */
class DepotSyncService
{
\tprivate TamsoftStockRepo $repo;
\tprivate TamsoftStockConfig $cfg;
\tprivate TamsoftHttpClient $http;
\tprivate RateLimiter $limiter;

\tpublic function __construct()
\t{
\t\t$this->repo = new TamsoftStockRepo();
\t\t$this->cfg = new TamsoftStockConfig();
\t\t$this->http = new TamsoftHttpClient();
\t\t$intervalMs = (int)($this->cfg->getConfig()['throttle_ms'] ?? 75);
\t\t$this->limiter = new RateLimiter(max(0, $intervalMs));
\t}

\tpublic function ping(): array
\t{
\t\treturn ['success'=>true, 'service'=>'DepotSyncService'];
\t}
}


