<?php

namespace app\Services\Tamsoft;

use app\Models\TamsoftStockRepo;
use app\Models\TamsoftStockConfig;
use app\Http\TamsoftHttpClient;

/**
 * Ürün master senkron servisi (iskele). Mevcut TamsoftStockService ile çakışmayacak şekilde kurgulandı.
 * İlk aşamada yalnızca bağımsız çalışabilir yardımcı metotlar içerir.
 */
class MasterSyncService
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
\t\treturn ['success'=>true, 'service'=>'MasterSyncService'];
\t}
}


