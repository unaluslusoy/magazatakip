<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use app\Models\QueueRepo;
use app\Services\TamsoftStockService;
use app\Models\TamsoftStockConfig;

if (php_sapi_name() !== 'cli') { echo "CLI required\n"; exit(1); }

$workerId = gethostname() . '-' . getmypid();
$repo = new QueueRepo();

// Çalıştırma izni kontrolü (veritabanına göre)
function isAllowed(string $jobType): bool {
    try {
        $cfg = new TamsoftStockConfig();
        $c = $cfg->getConfig();
        // Global anahtar: sync_active kapalı ise hiçbir Tamsoft işi çalışmasın
        if (empty($c['sync_active'])) { return false; }
        // job_schedule.enabled kontrolü
        $db = (new TamsoftStockService())->repoDb();
        $stmt = $db->prepare("SELECT enabled FROM job_schedule WHERE job_key=:k LIMIT 1");
        $stmt->execute([':k'=>$jobType]);
        $en = $stmt->fetchColumn();
        if ($en !== false && (int)$en === 0) { return false; }
        return true;
    } catch (\Throwable $e) { return true; }
}

// Sonsuz döngü: pending iş var oldukça tüket
while (true) {
    $job = $repo->reserveNext($workerId);
    if (!$job) { usleep(500000); continue; }
    $ok = false; $err = null;
    try {
        $type = (string)$job['job_type'];
        $payload = json_decode((string)($job['payload'] ?? '[]'), true) ?: [];
        if (!isAllowed($type)) { $ok = true; }
        switch ($type) {
            case 'tamsoft_stock_sync':
                $svc = new TamsoftStockService();
                $svc->intervalStockSync();
                $ok = true;
                break;
            case 'tamsoft_monthly_master':
                $svc = new TamsoftStockService();
                $svc->monthlyProductMasterSync();
                $ok = true;
                break;
            case 'tamsoft_price_refresh':
                $svc = new TamsoftStockService();
                $date = isset($payload['date']) ? (string)$payload['date'] : null;
                $depo = isset($payload['depo_id']) ? (int)$payload['depo_id'] : null;
                $svc->refreshPricesOnly($date, $depo);
                $ok = true;
                break;
            case 'tamsoft_ecommerce_stock':
                $svc = new TamsoftStockService();
                $date = isset($payload['date']) ? (string)$payload['date'] : null;
                $depo = isset($payload['depo_id']) ? (int)$payload['depo_id'] : null;
                $svc->refreshDepotQtyFromEcommerce($date, $depo);
                $ok = true;
                break;
            default:
                throw new \RuntimeException('Unknown job type: ' . $type);
        }
    } catch (\Throwable $e) { $ok = false; $err = $e->getMessage(); }
    if ($ok) { $repo->markDone((int)$job['id']); }
    else { $repo->markFailed((int)$job['id'], (string)$err); }
    // Sistem basıncını azaltmak için küçük bekleme
    usleep(75000);
}


