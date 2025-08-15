<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use app\Services\TamsoftStockService;

ini_set('display_errors', '0');
ignore_user_abort(true);
set_time_limit(0);

$lockFile = sys_get_temp_dir() . '/tamsoft_master_cron.lock';
$lock = @fopen($lockFile, 'c');
if ($lock && !@flock($lock, LOCK_EX | LOCK_NB)) {
	echo json_encode(['ok'=>false,'error'=>'Locked'], JSON_UNESCAPED_UNICODE), PHP_EOL;
	exit(0);
}

try {
	$svc = new TamsoftStockService();
	$res = $svc->monthlyProductMasterSync();
	$payload = ['ok'=>true] + $res;
	echo json_encode($payload, JSON_UNESCAPED_UNICODE), PHP_EOL;
	try { $log = __DIR__ . '/../logs/cron_master.log'; @file_put_contents($log, '['.date('Y-m-d H:i:s').'] '.json_encode($payload, JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND); } catch (Throwable $e) {}
} catch (Throwable $e) {
	$payload = ['ok'=>false,'error'=>$e->getMessage()];
	echo json_encode($payload, JSON_UNESCAPED_UNICODE), PHP_EOL;
	try { $log = __DIR__ . '/../logs/cron_master.log'; @file_put_contents($log, '['.date('Y-m-d H:i:s').'] '.json_encode($payload, JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND); } catch (Throwable $e2) {}
} finally {
	if ($lock) { @flock($lock, LOCK_UN); @fclose($lock); }
}


