<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use app\Models\QueueRepo;
use app\Services\TamsoftStockService;
use app\Utils\CronHelper;

if (php_sapi_name() !== 'cli') { echo "CLI required\n"; exit(1); }

$db = (new TamsoftStockService())->repoDb();
$qr = new QueueRepo();

$now = date('Y-m-d H:i:00');
$stmt = $db->prepare("SELECT job_key, cron_expr FROM job_schedule WHERE enabled=1 AND (next_run_at IS NULL OR next_run_at <= :now)");
$stmt->execute([':now'=>$now]);
$due = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$enq = 0; $updated = 0;
foreach ($due as $row) {
    $key = (string)$row['job_key'];
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


