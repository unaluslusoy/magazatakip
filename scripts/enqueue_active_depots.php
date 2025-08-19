<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use app\Models\TamsoftStockRepo;
use app\Models\QueueRepo;
use app\Models\TamsoftStockConfig;

if (php_sapi_name() !== 'cli') { echo "CLI gerekli\n"; exit(1); }

$date = $argv[1] ?? null; // örn: 1900-01-01

// Modül aktif mi?
try {
    $cfg = new TamsoftStockConfig();
    $c = $cfg->getConfig();
    if (empty($c['sync_active'])) { echo json_encode(['success'=>false,'reason'=>'sync_inactive'], JSON_UNESCAPED_UNICODE), "\n"; exit; }
} catch (Throwable $e) { /* geç */ }

$repo = new TamsoftStockRepo();
$deps = $repo->getActiveDepots();
$qr = new QueueRepo();

// İlgili job modül bazında kapalı mı? (job_schedule.enabled)
try {
    $db = $repo->getDb();
    $stmt = $db->prepare("SELECT enabled FROM job_schedule WHERE job_key=:k LIMIT 1");
    $stmt->execute([':k'=>'tamsoft_ecommerce_stock']);
    $en = $stmt->fetchColumn();
    if ($en !== false && (int)$en === 0) { echo json_encode(['success'=>false,'reason'=>'job_disabled'], JSON_UNESCAPED_UNICODE), "\n"; exit; }
} catch (Throwable $e) { /* geç */ }

$enq = 0; $ids = [];
foreach (($deps ?: []) as $d) {
    $id = (int)($d['id'] ?? 0);
    if ($id > 0) {
        // Backpressure: global ve tip eşiklerine göre sınırlama
        $globalThreshold = 200; // önceki 1000 -> 200
        $typeThreshold = 60;   // önceki 300 -> 60
        if ($qr->isQueueOverloaded($globalThreshold)) { break; }
        if ($qr->isQueueOverloaded($typeThreshold, 'tamsoft_ecommerce_stock')) { break; }
        $qr->enqueue('tamsoft_ecommerce_stock', [ 'date' => $date, 'depo_id' => $id ]);
        $enq++; $ids[] = $id;
    }
}

echo json_encode(['success'=>true,'enqueued'=>$enq,'depots'=>$ids], JSON_UNESCAPED_UNICODE), "\n";





