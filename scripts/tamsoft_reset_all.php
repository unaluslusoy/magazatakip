<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

// Sadece Tamsoft ile ilgili veri tablolarını temizler; ayar tablosu korunur

try {
	$db = core\Database::getInstance()->getConnection();
	$tables = [
		'tamsoft_depo_stok_ozet',
		'tamsoft_depo_stok_degisim_log',
		'tamsoft_stok_log',
		'tamsoft_urun_barkodlar',
		'tamsoft_urunler',
		'tamsoft_urunler_stage',
		'tamsoft_urun_barkodlar_stage',
		'tamsoft_depolar',
		'urun_entegrasyon_map',
	];
	$before = [];
	foreach ($tables as $t) {
		try { $before[$t] = (int)$db->query("SELECT COUNT(*) FROM $t")->fetchColumn(); }
		catch (\Throwable $e) { $before[$t] = null; }
	}
	foreach ($tables as $t) {
		try { $db->exec("TRUNCATE TABLE $t"); }
		catch (\Throwable $e) {}
	}
	$after = [];
	foreach ($tables as $t) {
		try { $after[$t] = (int)$db->query("SELECT COUNT(*) FROM $t")->fetchColumn(); }
		catch (\Throwable $e) { $after[$t] = null; }
	}
	echo json_encode(['ok'=>true,'before'=>$before,'after'=>$after], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), PHP_EOL;
} catch (Throwable $e) {
	echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), PHP_EOL;
}







