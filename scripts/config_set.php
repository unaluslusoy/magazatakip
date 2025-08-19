<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

$key = $argv[1] ?? '';
$val = $argv[2] ?? '';
if ($key === '') {
    fwrite(STDERR, "Usage: php scripts/config_set.php <column> <value>\n");
    exit(1);
}

$db = (new \app\Models\TamsoftStockRepo())->getDb();
$allowed = [
    'sync_active','sync_by_depot','request_interval_sec','throttle_ms','max_retries','breaker_fail_threshold','breaker_cooldown_sec',
    'max_parallel_depots','master_batch','price_batch','price_max_pages','price_max_seconds','max_seconds_per_depot','max_pages_per_depot','bulk_stock_summary',
    'default_date','default_depo_id','default_only_positive','default_last_barcode_only','default_only_ecommerce',
];
if (!in_array($key, $allowed, true)) {
    fwrite(STDERR, "Invalid key: $key\n");
    exit(2);
}

$sql = "UPDATE tamsoft_stok_ayarlar SET `$key` = :v";
$stmt = $db->prepare($sql);
$ok = $stmt->execute([':v'=>$val]);
echo json_encode(['success'=>$ok,'key'=>$key,'value'=>$val], JSON_UNESCAPED_UNICODE),"\n";



