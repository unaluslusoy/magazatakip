<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use app\Services\TamsoftStockService;

try {
	$svc = new TamsoftStockService();
	$db = $svc->repoDb();
	$before = (int)$db->query("SELECT COUNT(*) FROM tamsoft_depolar WHERE depo_adi IS NULL")->fetchColumn();
	$res = $svc->syncDepots();
	$after = (int)$db->query("SELECT COUNT(*) FROM tamsoft_depolar WHERE depo_adi IS NULL")->fetchColumn();
	echo json_encode([
		'ok' => true,
		'before_null_names' => $before,
		'after_null_names' => $after,
		'api_http_code' => $res['http_code'] ?? null,
		'updated_count' => $res['count'] ?? null,
	], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), PHP_EOL;
} catch (Throwable $e) {
	echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), PHP_EOL;
}
