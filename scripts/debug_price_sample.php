<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use app\Services\TamsoftStockService;
use app\Models\TamsoftStockRepo;

if (php_sapi_name() !== 'cli') { echo "CLI required\n"; exit(1); }

function http_post_form(string $url, array $fields): array {
	$ch = curl_init($url);
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => http_build_query($fields),
		CURLOPT_HTTPHEADER => ['Accept: application/json','Content-Type: application/x-www-form-urlencoded'],
		CURLOPT_TIMEOUT => 60,
	]);
	$body = curl_exec($ch);
	$err = curl_error($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return ['code'=>$code, 'err'=>$err ?: null, 'raw'=>$body, 'json'=>json_decode((string)$body,true)];
}

function http_get_json(string $url, string $bearer): array {
	$ch = curl_init($url);
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => ['Accept: application/json','Authorization: Bearer '.$bearer],
		CURLOPT_TIMEOUT => 60,
	]);
	$body = curl_exec($ch);
	$err = curl_error($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	$trim = is_string($body) ? trim($body) : '';
	$decoded = json_decode($trim, true);
	if (!is_array($decoded) && is_string($decoded)) { $decoded2 = json_decode($decoded, true); if (is_array($decoded2)) { $decoded = $decoded2; } }
	return ['code'=>$code,'err'=>$err ?: null,'raw'=>$trim,'json'=>is_array($decoded)?$decoded:null];
}

$svc = new TamsoftStockService();
$cfg = $svc->getConfig();

// CLI argümanları: --limit=100 --offset=0 --depoid=1 --rows=10
$cli = ['limit'=>100,'offset'=>0,'depoid'=>null,'rows'=>10];
foreach ($argv as $arg) {
	if (strpos($arg, '--') === 0 && strpos($arg, '=') !== false) {
		list($k,$v) = explode('=', substr($arg, 2), 2);
		if ($k === 'limit') { $cli['limit'] = max(1, (int)$v); }
		if ($k === 'offset') { $cli['offset'] = max(0, (int)$v); }
		if ($k === 'depoid') { $cli['depoid'] = (int)$v; }
		if ($k === 'rows') { $cli['rows'] = max(1, (int)$v); }
	}
}

$base = rtrim((string)($cfg['api_url'] ?? ''), '/');
$tokenUrl = $base . '/token';
$fields = [
	'grant_type' => 'password',
	'username' => (string)($cfg['kullanici'] ?? ''),
	'password' => (string)($cfg['sifre'] ?? ''),
];
$tok = http_post_form($tokenUrl, $fields);
$token = (string)($tok['json']['access_token'] ?? '');
if ($token === '') { echo "Token alınamadı\n"; fwrite(STDERR, json_encode($tok, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)."\n"); exit(2); }

// Depo: varsayılan depo ya da 1
$depoId = (int)($cli['depoid'] ?? 0);
if ($depoId <= 0) { $depoId = (int)($cfg['default_depo_id'] ?? 0); }
if ($depoId <= 0) { $depoId = 1; }
$date = (string)($cfg['default_date'] ?? '1900-01-01');

$params = [
	'tarih'=>$date,
	'depoid'=>$depoId,
	'offset'=>$cli['offset'],
	'limit'=>$cli['limit'],
	'urununsonbarkodulistelensin'=>'True',
	'miktarsifirdanbuyukstoklarlistelensin'=>'False',
	'sadeceeticaretstoklarigetir'=>'False',
];
$url = $base . '/api/Integration/StokListesi?' . http_build_query($params);
$t0 = microtime(true);
$res = http_get_json($url, $token);
$elapsed = microtime(true) - $t0;
$rows = is_array($res['json']) ? $res['json'] : [];

$priceKeys = ['IndirimliTutar','Tutar','Fiyat','SatisFiyati','SatisFiyati1','SatisFiyatiKDVli','SatisFiyatiKDVHaric','KDVDahilTutar','KDVDahilFiyat','Fiyat1','NetTutar','NetFiyat','BrutFiyat','BrutTutar'];
$out = [];
$keysUnion = [];
$take = min((int)$cli['rows'], is_array($rows) ? count($rows) : 0);
foreach (array_slice($rows, 0, $take) as $idx => $r) {
	if (!is_array($r)) { continue; }
	foreach (array_keys($r) as $k) { $keysUnion[$k] = true; }
	$item = [
		'UrunKodu' => $r['UrunKodu'] ?? ($r['Kod'] ?? null),
		'Barkod' => $r['Barkod'] ?? ($r['barkod'] ?? null),
	];
	foreach ($priceKeys as $k) { if (array_key_exists($k, $r)) { $item[$k] = $r[$k]; } }
	$heur = [];
	foreach ($r as $k=>$v) { if (is_string($k)) { $lk = strtolower($k); if (strpos($lk,'fiyat')!==false || strpos($lk,'tutar')!==false) { $heur[$k] = $v; } } }
	if (!empty($heur)) { $item['heuristic_price_like'] = $heur; }
	$out[] = $item;
}
ksort($keysUnion);

echo json_encode([
	'endpoint'=>$url,
	'http_code'=>$res['code'],
	'count'=>is_array($rows)?count($rows):0,
	'elapsed_ms'=>round($elapsed*1000),
	'keys_union'=>array_keys($keysUnion),
	'raw_rows'=>array_slice($rows, 0, $take),
	'sample_compact'=>$out
], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) . "\n";


