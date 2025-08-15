<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use app\Services\TamsoftStockService;

ignore_user_abort(true);
set_time_limit(0);

function j($x){ echo json_encode($x, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), "\n"; @ob_flush(); @flush(); }

try {
	$svc = new TamsoftStockService();
	$db = $svc->repoDb();
	$cfg = $svc->getConfig();
	$onlyPos = (bool)($cfg['default_only_positive'] ?? 1);
	$lastBarcode = (bool)($cfg['default_last_barcode_only'] ?? 0);
	$onlyEcom = (bool)($cfg['default_only_ecommerce'] ?? 0);
	$rows = $db->query("SELECT depo_id FROM tamsoft_depolar WHERE aktif=1 ORDER BY depo_id")->fetchAll(PDO::FETCH_COLUMN);
	j(['ok'=>true,'active_depots'=>count($rows),'order'=>$rows]);

	$maxParallel = (int)($cfg['max_parallel_depots'] ?? 3);
	if ($maxParallel < 1) { $maxParallel = 1; }
	$chunks = array_chunk($rows, $maxParallel);
	foreach ($chunks as $chunk) {
		// shell_exec kapalı olabilir; bu durumda ardışık olarak çalıştır
		if (function_exists('shell_exec')) {
			foreach ($chunk as $did) {
				$did = (int)$did;
				$cmd = 'php ' . escapeshellarg(__DIR__.'/tamsoft_sync_one.php') . ' ' . $did . ' >/dev/null 2>&1 &';
				j(['depo_start'=>$did, 'ts'=>date('c'), 'spawned'=>true]);
				@pclose(@popen($cmd, 'r'));
			}
			usleep(500000);
		} else {
			foreach ($chunk as $did) {
				$did = (int)$did;
				j(['depo_start'=>$did, 'ts'=>date('c'), 'spawned'=>false]);
				$res = $svc->refreshStocks(null, $did, $onlyPos, $lastBarcode, $onlyEcom);
				j(['depo_done'=>$did] + $res);
			}
		}
	}
	j(['ok'=>true,'all_spawned'=>true,'ts'=>date('c')]);
} catch (Throwable $e) {
	j(['ok'=>false,'error'=>$e->getMessage()]);
}


