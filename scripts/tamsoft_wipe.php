<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use app\Models\TamsoftStockRepo;
use app\Models\TamsoftStockConfig;

if (php_sapi_name() !== 'cli') { echo "CLI gerekli\n"; exit(1); }

$repo = new TamsoftStockRepo();
$db = $repo->getDb();

function cnt(PDO $db, string $t): int {
    $q = $db->query("SELECT COUNT(*) FROM $t");
    return (int)($q ? $q->fetchColumn() : 0);
}

$tables = [
    'tamsoft_depo_stok_ozet',
    'tamsoft_depo_stok_degisim_log',
    'tamsoft_urun_barkodlar',
    'tamsoft_urunler',
    'tamsoft_urunler_stage',
    'tamsoft_urun_barkodlar_stage',
    'tamsoft_stok_log',
];

$preserve = [
    'tamsoft_stok_ayarlar',
    'tamsoft_depolar',
    'urun_entegrasyon_map',
];

$summary = [];
foreach ($tables as $t) { $summary[$t] = cnt($db, $t); }
foreach ($preserve as $t) { $summary[$t] = cnt($db, $t); }

// Silme sırası (FK nedeniyle): özet/loglar -> barkodlar -> urunler -> stage
$order = [
    'tamsoft_depo_stok_ozet',
    'tamsoft_depo_stok_degisim_log',
    'tamsoft_urun_barkodlar',
    'tamsoft_urunler',
    'tamsoft_urunler_stage',
    'tamsoft_urun_barkodlar_stage',
    'tamsoft_stok_log',
];

try {
    $db->beginTransaction();
    foreach ($order as $t) { $db->exec("TRUNCATE TABLE `$t`"); }
    // Token sıfırla
    $db->exec("UPDATE tamsoft_stok_ayarlar SET token_value=NULL, token_expires_at=NULL, token_type=NULL");
    $db->commit();
    echo json_encode(['success'=>true, 'before'=>$summary, 'preserved'=>$preserve], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), "\n";
} catch (Throwable $e) {
    try { $db->rollBack(); } catch (Throwable $e2) {}
    echo json_encode(['success'=>false, 'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), "\n";
    exit(2);
}





