<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use app\Models\TamsoftStockRepo;

if (php_sapi_name() !== 'cli') { echo "CLI required\n"; exit(1); }

try {
	$repo = new TamsoftStockRepo();
	// Kurulum constructor içinde ensurePricePropagationTriggers ile yapılır
	$db = $repo->getDb();
	$rows = $db->query("SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE() AND TRIGGER_NAME IN ('trg_tamsoft_urunler_ai_price','trg_tamsoft_urunler_au_price') ORDER BY TRIGGER_NAME")->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode(['success'=>true,'installed_triggers'=>$rows], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) . "\n";
} catch (Throwable $e) {
	echo json_encode(['success'=>false, 'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) . "\n";
	exit(2);
}



