<?php

namespace app\Services\Tamsoft;

use app\Models\TamsoftStockRepo;
use app\Models\TamsoftStockConfig;
use app\Http\TamsoftHttpClient;

class PriceSyncService
{
\tprivate TamsoftStockRepo $repo;
\tprivate TamsoftStockConfig $cfg;
\tprivate TamsoftHttpClient $http;

\tpublic function __construct()
\t{
\t\t$this->repo = new TamsoftStockRepo();
\t\t$this->cfg = new TamsoftStockConfig();
\t\t$this->http = new TamsoftHttpClient();
\t}

\tpublic function ping(): array
\t{
\t\treturn ['success'=>true, 'service'=>'PriceSyncService'];
\t}
}


