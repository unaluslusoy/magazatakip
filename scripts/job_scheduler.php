<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use app\Models\QueueRepo;
use app\Services\TamsoftStockService;
use app\Utils\CronHelper;
use app\Models\TamsoftStockConfig;

if (php_sapi_name() !== 'cli') { echo "CLI required\n"; exit(1); }

$db = (new TamsoftStockService())->repoDb();

// Çalıştırma izni kontrolü (veritabanına göre)
try {
    $cfg = new TamsoftStockConfig();
    $c = $cfg->getConfig();
    if (empty($c['sync_active'])) { echo json_encode(['ok'=>false,'reason'=>'sync_inactive'])."\n"; exit; }
} catch (\Throwable $e) { /* geç */ }
$qr = new QueueRepo();

$now = date('Y-m-d H:i:00');
$stmt = $db->prepare("SELECT job_key, cron_expr FROM job_schedule WHERE enabled=1 AND (next_run_at IS NULL OR next_run_at <= :now)");
$stmt->execute([':now'=>$now]);
$due = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$enq = 0; $updated = 0;
foreach ($due as $row) {
    $key = (string)$row['job_key'];
    // Backpressure: kuyruk doluysa (global veya tipe özel) enqueuing atla
    $globalThreshold = 200; // önceki 1000 -> 200
    if ($qr->isQueueOverloaded($globalThreshold)) { continue; }
    // Tip bazlı eşik (bazı iş tipleri daha düşük eşikte kısıtlanabilir)
    $typeThreshold = 60; // önceki 300 -> 60
    if ($qr->isQueueOverloaded($typeThreshold, $key)) { continue; }
    $qr->enqueue($key, []);
    $enq++;
    try {
        $next = CronHelper::nextRunAt((string)$row['cron_expr'], new DateTime($now));
        $upd = $db->prepare("UPDATE job_schedule SET next_run_at=:n, updated_at=NOW() WHERE job_key=:k");
        $upd->execute([':n'=>$next, ':k'=>$key]);
        $updated++;
    } catch (Throwable $e) {}
}

echo json_encode(['ok'=>true,'enqueued'=>$enq,'updated'=>$updated,'ts'=>$now], JSON_UNESCAPED_UNICODE), "\n";


