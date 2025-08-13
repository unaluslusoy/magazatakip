<?php
require __DIR__ . '/../vendor/autoload.php';

$svc = new \app\Services\TrendyolGoService();
$magModel = new \app\Models\TrendyolGoMagaza();
$urunModel = new \app\Models\TrendyolUrun();

$per = isset($argv[1]) ? max(10, (int)$argv[1]) : 500;
$maxPages = isset($argv[2]) ? max(1, (int)$argv[2]) : 50;

$stores = $magModel->getAll();
$summary = [ 'per' => $per, 'max_pages' => $maxPages, 'stores' => [] ];

foreach ($stores as $s) {
    $sid = $s['store_id'] ?? '';
    if ($sid === '') { continue; }
    $inserted = 0; $errors = 0; $pages = 0; $total = null;
    for ($page = 1; $page <= $maxPages; $page++) {
        $res = $svc->getProducts('', $page, $per, [ 'filters' => [ 'storeId' => $sid, 'status' => 'ACTIVE' ] ]);
        $pages++;
        $items = $res['items'] ?? [];
        if ($total === null) { $total = (int)($res['total'] ?? 0); }
        if (empty($items)) { break; }
        foreach ($items as $p) {
            $ok = $urunModel->upsertByStoreBarcode((string)$sid, (string)($p['barcode'] ?? ''), [
                'supplier_id' => $p['supplierId'] ?? null,
                'title' => $p['name'] ?? null,
                'description' => $p['description'] ?? null,
                'stockCode' => $p['stockCode'] ?? ($p['code'] ?? null),
                'sku' => $p['sku'] ?? ($p['code'] ?? null),
                'brand_id' => $p['brand_id'] ?? ($p['brand']['id'] ?? null),
                'brand' => $p['brand'] ?? (is_array($p['brand'] ?? null) ? ($p['brand']['name'] ?? null) : null),
                'category_id' => $p['categoryId'] ?? ($p['category']['id'] ?? null),
                'category_name' => $p['categoryName'] ?? ($p['category']['name'] ?? null),
                'stock' => $p['stock'] ?? ($p['quantity'] ?? null),
                'listPrice' => $p['listPrice'] ?? ($p['originalPrice'] ?? null),
                'sellingPrice' => $p['trendyolPrice'] ?? ($p['sellingPrice'] ?? null),
                'price' => $p['price'] ?? null,
                'status' => ($p['stock'] ?? 0) > 0 ? 'ACTIVE' : 'PASSIVE',
                'onSale' => ($p['stock'] ?? 0) > 0 ? 1 : 0,
                'imageUrl' => $p['imageUrl'] ?? null
            ]);
            if ($ok) { $inserted++; } else { $errors++; }
        }
        if ((int)($res['total'] ?? 0) <= ($page * $per)) { break; }
    }
    $summary['stores'][] = [ 'store_id' => $sid, 'name' => $s['magaza_adi'] ?? '', 'pages' => $pages, 'inserted' => $inserted, 'errors' => $errors, 'estimated_total' => $total ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([ 'success' => true, 'summary' => $summary ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;

