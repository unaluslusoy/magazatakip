<?php

namespace app\Services\Tamsoft;

use app\Models\TamsoftStockRepo;

class PreviewService
{
\tprivate TamsoftStockRepo $repo;

\tpublic function __construct()
\t{
\t\t$this->repo = new TamsoftStockRepo();
\t}

\tpublic function ping(): array
\t{
\t\treturn ['success'=>true, 'service'=>'PreviewService'];
\t}
}


