<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use app\Models\TamsoftStockRepo;
use app\Models\QueueRepo;

if (php_sapi_name() !== 'cli') { echo "CLI gerekli\n"; exit(1); }

$date = $argv[1] ?? null; // örn: 1900-01-01

$repo = new TamsoftStockRepo();
$deps = $repo->getActiveDepots();
$qr = new QueueRepo();

$enq = 0; $ids = [];
foreach (($deps ?: []) as $d) {
    $id = (int)($d['id'] ?? 0);
    if ($id > 0) {
        $qr->enqueue('tamsoft_ecommerce_stock', [ 'date' => $date, 'depo_id' => $id ]);
        $enq++; $ids[] = $id;
    }
}

echo json_encode(['success'=>true,'enqueued'=>$enq,'depots'=>$ids], JSON_UNESCAPED_UNICODE), "\n";


