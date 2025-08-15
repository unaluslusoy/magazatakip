<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use app\Models\TamsoftStockRepo;

ini_set('display_errors', '0');

try {
	$repo = new TamsoftStockRepo();
	$db = (new TamsoftStockRepo())->getDb();
	$rows = $db->query("SELECT id FROM tamsoft_depolar WHERE aktif=1 ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
	$done = 0;
	foreach ($rows as $did) {
		$repo->ensureDepotRows((int)$did);
		$done++;
	}
	echo json_encode(['ok'=>true,'active_depots'=>count($rows),'processed'=>$done], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), PHP_EOL;
} catch (Throwable $e) {
	echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), PHP_EOL;
}


