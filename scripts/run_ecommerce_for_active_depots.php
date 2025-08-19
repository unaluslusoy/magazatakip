<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use app\Services\TamsoftStockService;
use app\Models\TamsoftStockRepo;

ini_set('display_errors', '0');
ignore_user_abort(true);
set_time_limit(0);

$lockFile = sys_get_temp_dir() . '/run_ecommerce_active_depots.lock';
$lock = @fopen($lockFile, 'c');
if ($lock && !@flock($lock, LOCK_EX | LOCK_NB)) {
	echo json_encode(['ok'=>false,'error'=>'Locked'], JSON_UNESCAPED_UNICODE), PHP_EOL;
	exit(0);
}

try {
	$svc = new TamsoftStockService();
	$repo = new TamsoftStockRepo();
	$date = $argv[1] ?? null; // İsteğe bağlı tarih (örn: 1900-01-01)
	$depots = $repo->getActiveDepotsFromDb();
	$results = [];
	foreach (($depots ?: []) as $d) {
		$id = (int)($d['id'] ?? 0);
		if ($id <= 0) { continue; }
		$res = $svc->refreshDepotQtyFromEcommerce($date, $id);
		$results[] = ['depo'=>$id] + $res;
	}
	$payload = ['ok'=>true, 'count'=>count($results), 'results'=>$results];
	echo json_encode($payload, JSON_UNESCAPED_UNICODE), PHP_EOL;
	try { $log = __DIR__ . '/../logs/cron_stock.log'; @file_put_contents($log, '['.date('Y-m-d H:i:s').'] '.json_encode($payload, JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND); } catch (Throwable $e) {}
} catch (Throwable $e) {
	$payload = ['ok'=>false,'error'=>$e->getMessage()];
	echo json_encode($payload, JSON_UNESCAPED_UNICODE), PHP_EOL;
	try { $log = __DIR__ . '/../logs/cron_stock.log'; @file_put_contents($log, '['.date('Y-m-d H:i:s').'] '.json_encode($payload, JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND); } catch (Throwable $e2) {}
} finally {
	if ($lock) { @flock($lock, LOCK_UN); @fclose($lock); }
}


