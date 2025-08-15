<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use app\Services\TamsoftStockService;

if (php_sapi_name() !== 'cli') { echo "CLI required\n"; exit(1); }

$depoId = isset($argv[1]) ? (int)$argv[1] : 0;
$onlyPos = isset($argv[2]) ? (bool)$argv[2] : true;
$lastBarcode = isset($argv[3]) ? (bool)$argv[3] : false;
$onlyEcom = isset($argv[4]) ? (bool)$argv[4] : false;

try {
    $svc = new TamsoftStockService();
    $res = $svc->refreshStocks(null, $depoId > 0 ? $depoId : null, $onlyPos, $lastBarcode, $onlyEcom);
    echo json_encode(['ok'=>true, 'depo_id'=>$depoId] + $res, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), "\n";
} catch (Throwable $e) {
    echo json_encode(['ok'=>false, 'depo_id'=>$depoId, 'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), "\n";
    exit(2);
}


