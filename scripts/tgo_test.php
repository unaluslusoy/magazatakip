<?php
require __DIR__ . '/../vendor/autoload.php';

try {
	$svc = new \app\Services\TrendyolGoService();
	$page = isset($argv[1]) ? max(1, (int)$argv[1]) : 1;
	$size = isset($argv[2]) ? max(1, (int)$argv[2]) : 10;
	$mode = $argv[3] ?? 'products'; // products|orders|all

    if ($mode === 'orders') {
		$res = $svc->getOrders('ACTIVE', $page, $size, null, null, null);
	} elseif ($mode === 'all') {
		$res = $svc->getAllProducts($size, 1);
    } elseif ($mode === 'stores') {
        $m = new \app\Models\TrendyolGoMagaza();
        $stores = $m->getAll();
        $out = [];
        foreach ($stores as $s) {
            $sid = $s['store_id'] ?? '';
            if ($sid === '') { continue; }
            $r = $svc->getProducts('', 1, $size, ['filters' => ['storeId' => $sid, 'status' => 'ACTIVE']]);
            $out[] = [
                'store_id' => $sid,
                'magaza_adi' => $s['magaza_adi'] ?? '',
                'count' => count(($r['items'] ?? [])),
                'last_request' => $r['last_request'] ?? null,
                'attempts' => $r['attempts'] ?? []
            ];
        }
        $res = [ 'success' => true, 'per' => $size, 'stores' => $out ];
	} else {
		$res = $svc->getProducts('', $page, $size, ['filters' => ['status' => 'ACTIVE']]);
	}

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
} catch (\Throwable $e) {
	$err = [ 'error' => true, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString() ];
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($err, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
}


