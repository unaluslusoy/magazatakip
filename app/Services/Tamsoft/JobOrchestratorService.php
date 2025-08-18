<?php

namespace app\Services\Tamsoft;

use app\Models\QueueRepo;

class JobOrchestratorService
{
\tprivate ?QueueRepo $queue = null;

\tpublic function __construct()
\t{
\t\tif (class_exists('app\\Models\\QueueRepo')) {
\t\t\t$this->queue = new QueueRepo();
\t\t}
\t}

\tpublic function ping(): array
\t{
\t\treturn ['success'=>true, 'service'=>'JobOrchestratorService'];
\t}
}


