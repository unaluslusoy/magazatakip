<?php

namespace app\Services;

use app\Models\TamsoftStockConfig;
use app\Models\TamsoftStockRepo;
use app\Http\TamsoftHttpClient;
use app\Utils\RateLimiter;

class TamsoftStockService
{
	private TamsoftStockConfig $cfg;
	private TamsoftStockRepo $repo;
	private TamsoftHttpClient $httpClient;
	private RateLimiter $rateLimiter;

	public function __construct()
	{
		$this->cfg = new TamsoftStockConfig();
		$this->repo = new TamsoftStockRepo();
		$this->httpClient = new TamsoftHttpClient();
		$intervalMs = (int)($this->cfg->getConfig()['throttle_ms'] ?? 75);
		$this->rateLimiter = new RateLimiter(max(0, $intervalMs));
	}

	public function getConfig(): array { return $this->cfg->getConfig(); }
	public function saveConfig(array $data): bool { return $this->cfg->saveConfig($data); }

	public function repoDb(): \PDO { return $this->repo->getDb(); }

	public function listProducts(int $limit = 500): array { return $this->repo->listProducts($limit); }

	public function listByPrefix(string $prefix, int $limit = 500, int $offset = 0): array
	{
		return $this->repo->listByPrefix($prefix, $limit, $offset);
	}

	public function listProductsWithMap(int $limit = 500): array
	{
		return $this->repo->listProductsWithMap($limit);
	}

	/**
	 * Aylık tam ürün listesi senkronu (staging + master'a aktarım)
	 */
	public function monthlyProductMasterSync(?int $preferredDepotId = null): array
	{
		$cfg = $this->cfg->getConfig();
		$token = $this->getToken();
		$base = rtrim((string)($cfg['api_url'] ?? ''), '/');
		$this->repo->ensureStageTables();
		$this->repo->truncateStage();

		// Çekilecek depo: tercihen ayarlardaki, yoksa veritabanındaki ilk aktif depo; DB boşsa API'den
		$depoId = $preferredDepotId ?? (isset($cfg['default_depo_id']) ? (int)$cfg['default_depo_id'] : null);
		if ($depoId === null) {
			$active = $this->repo->getActiveDepotsFromDb();
			if (!empty($active)) { $depoId = (int)$active[0]['id']; }
		}
		if ($depoId === null) {
			$dl = $this->getDepotList($base, $token);
			foreach ($dl as $d) { $depoId = (int)($d['Depoid'] ?? ($d['DepoID'] ?? 0)); if ($depoId>0) break; }
		}
		if (!$depoId) { throw new \RuntimeException('Depo bulunamadı'); }

		$imported = 0; $offset = 0; $batch = max(50, min(2000, (int)($cfg['master_batch'] ?? 500)));
		// Geniş tarih ile tüm seti çekmeye çalış
		$paramsBase = [
			'tarih' => (string)($cfg['default_date'] ?? '1900-01-01'),
			'depoid' => $depoId,
			'miktarsifirdanbuyukstoklarlistelensin' => !empty($cfg['default_only_positive']) ? 'True' : 'False',
			'urununsonbarkodulistelensin' => !empty($cfg['default_last_barcode_only']) ? 'True' : 'False',
			'sadeceeticaretstoklarigetir' => !empty($cfg['default_only_ecommerce']) ? 'True' : 'False',
		];
		while (true) {
			$q = $paramsBase + ['offset'=>$offset, 'limit'=>$batch];
			$url = $base . '/api/Integration/EticaretStokListesi?' . http_build_query($q);
			$meta = $this->httpGetMeta($url, $token);
			$data = is_array($meta['json'] ?? null) ? $meta['json'] : [];
			$cnt = is_array($data) ? count($data) : 0;
			if ($cnt === 0) { break; }
			// Sayfa işlemlerini tek transaction'da uygula ve bulk insert kullan (IO'yu ciddi azaltır)
			$__db = $this->repoDb(); $__inTx = false; try { $__db->beginTransaction(); $__inTx = true; } catch (\Throwable $e) {}
			$prodRows = [];
			$bcRows = [];
			foreach ($data as $row) {
				$norm = $this->parseProductRow($row, (int)$depoId);
				if ($norm === null) { continue; }
				$prodRows[] = [
					'ext_urun_id' => $norm['ext_urun_id'],
					'barkod' => $norm['barkod'] ?? null,
					'urun_adi' => $norm['urun_adi'] ?? null,
					'kdv' => $norm['kdv'] ?? null,
					'birim' => $norm['birim'] ?? null,
					'fiyat' => $norm['fiyat'] ?? null,
				];
				foreach (($norm['alt_barkodlar'] ?? []) as $ab) {
					$bcRows[] = [
						'ext_urun_id' => $norm['ext_urun_id'],
						'barkod' => (string)$ab['barkod'],
						'birim' => $ab['birim'] ?? null,
						'fiyat' => isset($ab['fiyat']) ? (float)$ab['fiyat'] : null
					];
				}
			}
			if (!empty($prodRows)) { $this->repo->stageInsertProductBulk($prodRows); $imported += count($prodRows); }
			if (!empty($bcRows)) { $this->repo->stageInsertBarcodeBulk($bcRows); }
			if ($__inTx) { try { $__db->commit(); } catch (\Throwable $e) { try { $__db->rollBack(); } catch (\Throwable $e2) {} } }
			$offset += $batch;
		}
		$this->repo->syncMasterFromStage();
		$this->repo->logEvent('product_master_sync', 'Master ürün senk tamamlandı. Kaydedilen: '.$imported);
		return ['success'=>true, 'imported'=>$imported];
	}

	/**
	 * Periyodik stok senkron (config değerleriyle)
	 */
	public function intervalStockSync(): array
	{
		$cfg = $this->cfg->getConfig();
		// Sessiz saatler: 22:30 - 09:00 arası otomatik durdur
		$quietEnabled = (int)($cfg['quiet_enabled'] ?? 1) === 1;
		if ($quietEnabled) {
			$now = new \DateTime('now');
			$qs = $cfg['quiet_start'] ?? '22:30:00';
			$qe = $cfg['quiet_end'] ?? '09:00:00';
			$start = \DateTime::createFromFormat('H:i:s', $qs) ?: new \DateTime('22:30:00');
			$end = \DateTime::createFromFormat('H:i:s', $qe) ?: new \DateTime('09:00:00');
			$inQuiet = false;
			if ($start <= $end) {
				// Aynı gün penceresi
				$inQuiet = ($now >= $start && $now <= $end);
			} else {
				// Gece yarısını aşan pencere (örn 22:30-09:00)
				$inQuiet = ($now >= $start || $now <= $end);
			}
			if ($inQuiet) {
				return ['success'=>true, 'skipped'=>true, 'reason'=>'quiet_hours'];
			}
		}
		$date = (string)($cfg['default_date'] ?? '1900-01-01');
		// 30 dakikada bir tüm aktif depolar (depo id bağımsız)
		$depo = null;
		$onlyPos = (bool)($cfg['default_only_positive'] ?? 1);
		$lastBarcode = (bool)($cfg['default_last_barcode_only'] ?? 0);
		$onlyEcom = (bool)($cfg['default_only_ecommerce'] ?? 0);
		return $this->refreshStocks($date, $depo, $onlyPos, $lastBarcode, $onlyEcom);
	}

	public function dashboardSummary(): array
	{
		$cfg = $this->cfg->getConfig();
		$urunSayisi = $this->repo->countActiveProductsExcludingPrefixes();
		$depoSayisi = $this->repo->countDepotsActive();
		$iptSayisi = $this->repo->countByPrefix('IPT');
		$bkSayisi = $this->repo->countByPrefix('BK');
		$logs = $this->repo->latestLogs(5);
		$lastSyncAt = $this->repo->getLastSyncAt();
		$lastMasterAt = $this->repo->getLastMasterSyncAt();
		return [
			'success' => true,
			'urun_sayisi' => $urunSayisi,
			'depo_sayisi' => $depoSayisi,
			'ipt_urun_sayisi' => $iptSayisi,
			'bk_urun_sayisi' => $bkSayisi,
			'sync_active' => (bool)($cfg['sync_active'] ?? 0),
			'request_interval_sec' => (int)($cfg['request_interval_sec'] ?? 0),
			'sync_by_depot' => (bool)($cfg['sync_by_depot'] ?? 0),
			'latest_logs' => $logs,
			'last_sync_at' => $lastSyncAt,
			'last_master_sync_at' => $lastMasterAt,
		];
	}

	public function listProductsServerSide(int $start, int $length, ?string $search, string $orderBy, string $orderDir, ?string $filterPrefix = null, ?int $hasIntegration = null, ?int $onlyPositive = null, ?int $depoId = null, ?int $orderDepotId = null): array
	{
		$total = $this->repo->countProductsTotal($filterPrefix, $hasIntegration, $onlyPositive, $depoId);
		$filtered = $this->repo->countProductsFiltered($search, $filterPrefix, $hasIntegration, $onlyPositive, $depoId);
		$rows = $this->repo->listProductsWithDepotsPage($start, $length, $search, $orderBy, $orderDir, $filterPrefix, $hasIntegration, $onlyPositive, $depoId, $orderDepotId);
		$depots = $this->repo->getActiveDepots();
		return [ 'total' => $total, 'filtered' => $filtered, 'rows' => $rows, 'depots' => $depots ];
	}

	private function httpGet(string $url, ?string $bearer = null): array
	{
		return $this->httpWithRetry('GET', $url, null, $bearer);
	}

	private function httpPost(string $url, array $fields, ?string $bearer = null): array
	{
		return $this->httpWithRetry('POST', $url, $fields, $bearer);
	}

	private function httpWithRetry(string $method, string $url, ?array $fields, ?string $bearer): array
	{
		$cfg = $this->cfg->getConfig();
		$maxRetries = max(0, (int)($cfg['max_retries'] ?? 3));
		$throttleMs = max(0, (int)($cfg['throttle_ms'] ?? 75));
		$failThresh = max(1, (int)($cfg['breaker_fail_threshold'] ?? 5));
		$cooldown = max(60, (int)($cfg['breaker_cooldown_sec'] ?? 300));
		static $failCount = 0; static $breakerUntil = 0;
		$now = time();
		if ($breakerUntil > $now) { throw new \RuntimeException('Servis yoğun, lütfen daha sonra deneyiniz.'); }
		$attempt = 0; $lastErr = null;
		while ($attempt <= $maxRetries) {
			if ($attempt > 0) { $this->rateLimiter->awaitNext(); usleep(($throttleMs + rand(25,150)) * 1000); }
			$attempt++;
			$headers = ['Accept: application/json'];
			if ($bearer) { $headers[] = 'Authorization: Bearer ' . $bearer; }
			$timeoutSec = max(5, (int)($cfg['http_timeout_sec'] ?? 20));
			$resp = $method === 'POST'
				? $this->httpClient->postForm($url, $fields ?? [], $headers, $timeoutSec)
				: $this->httpClient->get($url, $headers, $timeoutSec);
			$code = (int)($resp['http_code'] ?? 0);
			$err = $resp['error'] ?? null;
			$body = $resp['raw_body'] ?? '';
			if ($err) { $lastErr = 'HTTP error: '.$err; }
			else if ($code >= 429 || $code >= 500) { $lastErr = 'HTTP '.$code.' body: '.$body; }
			else if ($code >= 400) { throw new \RuntimeException('HTTP '.$code.' body: '.$body); }
			else {
				$trimmed = is_string($body) ? trim($body) : '';
				if ($trimmed === '' || $trimmed === 'null') { return []; }
				$failCount = 0; return $this->decodeFlexible($trimmed);
			}
		}
		$failCount++;
		if ($failCount >= $failThresh) { $breakerUntil = $now + $cooldown; }
		throw new \RuntimeException($lastErr ?? 'İstek başarısız');
	}

	private function decodeFlexible(string $raw): array
	{
		$decoded = json_decode($raw, true);
		if (is_array($decoded)) { return $decoded; }
		if (is_string($decoded)) {
			$decoded2 = json_decode($decoded, true);
			if (is_array($decoded2)) { return $decoded2; }
		}
		// Bazı durumlarda tüm çıktı çift tırnak içine alınmış olabilir
		$trim = trim($raw);
		if (strlen($trim) > 2 && (($trim[0] === '"' && substr($trim, -1) === '"') || ($trim[0] === "'" && substr($trim, -1) === "'"))) {
			$inner = substr($trim, 1, -1);
			$decoded3 = json_decode($inner, true);
			if (is_array($decoded3)) { return $decoded3; }
		}
		return [];
	}

	private function httpGetMeta(string $url, ?string $bearer = null): array
	{
		$cfg = $this->cfg->getConfig();
		$headers = ['Accept: application/json'];
		if ($bearer) { $headers[] = 'Authorization: Bearer ' . $bearer; }
		$resp = $this->httpClient->get($url, $headers, (int)($cfg['http_timeout_sec'] ?? 60));
		$trimmed = is_string($resp['raw_body'] ?? '') ? trim((string)$resp['raw_body']) : '';
		$decoded = $this->decodeFlexible($trimmed);
		return [
			'http_code' => $resp['http_code'] ?? null,
			'error' => $resp['error'] ?? null,
			'raw_body' => $trimmed,
			'json' => is_array($decoded) ? $decoded : null,
		];
	}

	// Yeni: Sadece fiyat çekme servisi (StokListesi)
	public function refreshPricesOnly(?string $date = null, ?int $depoId = null, int $maxPages = 200, int $batch = 100): array
	{
		$cfg = $this->cfg->getConfig();
		$date = $date ?: (string)($cfg['default_date'] ?? '1900-01-01');
		// Config tabanlı zaman/sayfa/batch limitleri
		$maxPagesCfg = (int)($cfg['price_max_pages'] ?? 0);
		$batchCfg = (int)($cfg['price_batch'] ?? 0);
		$maxSeconds = (int)($cfg['price_max_seconds'] ?? 0);
		if ($maxPagesCfg > 0) { $maxPages = $maxPagesCfg; }
		if ($batchCfg > 0) { $batch = $batchCfg; }
		$token = $this->getToken();
		$base = rtrim((string)($cfg['api_url'] ?? ''), '/');
		$updated = 0; $skipped = 0; $pages = 0;
		$startedAt = time();
		$this->repo->logEvent('price_refresh', 'Fiyat güncelleme başladı (date='.$date.', depo='.(string)($depoId ?? 'hepsi').', batch='.$batch.', maxPages='.$maxPages.')');
		// Tek depo üzerinden fiyat: varsayılan depo veya 1
		$did = $depoId ?? (int)($cfg['default_depo_id'] ?? 1);
		if ($did <= 0) { $did = 1; }
		$depots = [ ['id'=>$did] ];
		foreach ($depots as $d) {
			$did = (int)($d['id'] ?? $d['DepoID'] ?? $d['Depoid'] ?? 0);
			if ($did <= 0) continue;
			$offset = 0; $pages = 0; $prevHash = null;
			$this->repo->logEvent('price_refresh', 'Depo '.$did.' fiyat güncelleme başlatıldı');
			while ($pages < $maxPages) {
				// global timebox
				if ($maxSeconds > 0 && (time() - $startedAt) > $maxSeconds) {
					$this->repo->logEvent('price_refresh', 'Zaman limiti aşıldı, işlem sonlandırılıyor');
					break 2;
				}
				$params = [
					'tarih' => $date,
					'depoid' => $did,
					'urununsonbarkodulistelensin' => !empty($cfg['default_last_barcode_only']) ? 'True' : 'False',
					'miktarsifirdanbuyukstoklarlistelensin' => !empty($cfg['default_only_positive']) ? 'True' : 'False',
					'sadeceeticaretstoklarigetir' => !empty($cfg['default_only_ecommerce']) ? 'True' : 'False',
					'offset' => $offset,
					'limit' => $batch,
				];
				$url = $base . '/api/Integration/StokListesi?' . http_build_query($params);
				$meta = $this->httpGetMeta($url, $token);
				$data = is_array($meta['json'] ?? null) ? $meta['json'] : [];
				$cnt = is_array($data) ? count($data) : 0;
				if ($cnt === 0) break;
				$hash = md5(json_encode($data)); if ($prevHash !== null && $hash === $prevHash) break; $prevHash = $hash;
				foreach ($data as $row) {
					$ext = trim((string)($row['UrunKodu'] ?? ($row['Kod'] ?? '')));
					$barcode = trim((string)($row['Barkod'] ?? ($row['barkod'] ?? '')));
					$ind = $this->normalizeDecimal($row['IndirimliTutar'] ?? null);
					$basePrice = $this->extractPriceFromRow($row);
					$price = ($ind !== null && $ind > 0) ? $ind : $basePrice;
					if ($ext === '' && $barcode === '') { $skipped++; continue; }
					$ok = $this->repo->updatePriceByExtOrBarcode($ext !== '' ? $ext : null, $barcode !== '' ? $barcode : null, $price);
					if ($ok) { $updated++; }
					// Debug: fiyat yakalanamadıysa örnek bir log satırı bırak
					if ($price === null) {
						try{ $rootLog = dirname(__DIR__, 2) . '/logs/tamsoft.log'; @file_put_contents($rootLog, json_encode(['price_missing'=>['ext'=>$ext,'barcode'=>$barcode,'keys'=>array_keys($row)]], JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND); }catch(\Throwable $e){}
					}
				}
				$offset += $batch; $pages++;
				if ($cnt < $batch) break;
			}
			$this->repo->logEvent('price_refresh', 'Depo '.$did.' tamamlandı (pages='.$pages.')');
		}
		$this->repo->logEvent('price_refresh', 'Fiyat güncelleme bitti (updated='.$updated.', skipped='.$skipped.')');
		return ['success'=>true,'updated'=>$updated,'skipped'=>$skipped];
	}

	/**
	 * EticaretStokListesi üzerinden depo bazlı sadece miktar güncellemesi yapar (fiyatı değiştirmez)
	 */
	public function refreshDepotQtyFromEcommerce(?string $date = null, ?int $depoId = null, int $maxPages = 200, int $batch = 100): array
	{
		$cfg = $this->cfg->getConfig();
		$date = $date ?: (string)($cfg['default_date'] ?? '1900-01-01');
		$token = $this->getToken();
		$base = rtrim((string)($cfg['api_url'] ?? ''), '/');
		$onlyPositive = (bool)($cfg['default_only_positive'] ?? 1);
		$lastBarcodeOnly = (bool)($cfg['default_last_barcode_only'] ?? 0);
		$onlyEcommerce = (bool)($cfg['default_only_ecommerce'] ?? 0);
		$updated = 0; $skipped = 0; $pages = 0;
		// Depo seçimi: param > config > 1
		$did = $depoId ?? (int)($cfg['default_depo_id'] ?? 1);
		if ($did <= 0) { $did = 1; }
		while ($pages < $maxPages) {
			$params = [
				'tarih' => $date,
				'depoid' => $did,
				'urununsonbarkodulistelensin' => $lastBarcodeOnly ? 'True' : 'False',
				'miktarsifirdanbuyukstoklarlistelensin' => $onlyPositive ? 'True' : 'False',
				'sadeceeticaretstoklarigetir' => $onlyEcommerce ? 'True' : 'False',
				'offset' => $pages * $batch,
				'limit' => $batch,
			];
			$url = $base . '/api/Integration/EticaretStokListesi?' . http_build_query($params);
			$meta = $this->httpGetMeta($url, $token);
			$data = is_array($meta['json'] ?? null) ? $meta['json'] : [];
			$cnt = is_array($data) ? count($data) : 0;
			if ($cnt === 0) break;
			// Sayfa işlemleri tek transaction
			$db = $this->repoDb(); $inTx = false; try { $db->beginTransaction(); $inTx = true; } catch (\Throwable $e) {}
			foreach ($data as $row) {
				$norm = $this->parseProductRow($row, (int)$did);
				if ($norm === null) { $skipped++; continue; }
				$urunId = $this->repo->upsertProduct(
					$norm['ext_urun_id'],
					$norm['barkod'] ?? null,
					$norm['urun_adi'] ?? null,
					$norm['kdv'] ?? null,
					$norm['birim'] ?? null,
					null // fiyatı güncellemiyoruz
				);
				$this->repo->upsertDepot($norm['depo_id'], null);
				$this->repo->upsertStockSummary($urunId, $norm['depo_id'], (float)($norm['miktar'] ?? 0), null);
				$updated++;
			}
			if ($inTx) { try { $db->commit(); } catch (\Throwable $e) { try { $db->rollBack(); } catch (\Throwable $e2) {} } }
			$pages++;
			if ($cnt < $batch) break;
		}
		$this->repo->updateProductQuantityFromSummary();
		return ['success'=>true, 'updated'=>$updated, 'skipped'=>$skipped];
	}

	private function normalizeDecimal($value): ?float
	{
		if ($value === null) { return null; }
		if (is_float($value) || is_int($value)) { return (float)$value; }
		if (!is_string($value)) { return null; }
		$raw = trim($value);
		// Para birimi ve yazı karakterlerini temizle
		$raw = preg_replace('/[^0-9,\.-]/u', '', $raw ?? '');
		if ($raw === '') { return null; }
		// Türkçe biçimler: 1.234,56 → 1234.56, 12,34 → 12.34
		// Strateji: eğer virgül varsa ve sondan 3. karakter değilse onu ondalık ayırıcı kabul edip virgülü noktaya çevir, noktaları kaldır
		$hasComma = strpos($raw, ',') !== false; $hasDot = strpos($raw, '.') !== false;
		if ($hasComma && !$hasDot) {
			$raw = str_replace(['.',' '], '', $raw); // olası binlik ayırıcıyı temizle
			$raw = str_replace(',', '.', $raw);
		}
		else if ($hasComma && $hasDot) {
			// Her ikisi de varsa ve son virgül noktadan sonra ise, virgül ondalık olabilir: 1.234,56
			$lastComma = strrpos($raw, ','); $lastDot = strrpos($raw, '.');
			if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
				$raw = str_replace('.', '', $raw);
				$raw = str_replace(',', '.', $raw);
			}
		}
		return is_numeric($raw) ? (float)$raw : null;
	}

	private function extractPriceFromRow(array $row): ?float
	{
		$priceCandidates = ['Tutar','Fiyat','SatisFiyati','SatisFiyati1','SatisFiyatiKDVli','SatisFiyatiKDVHaric'];
		foreach ($priceCandidates as $pc) {
			if (array_key_exists($pc, $row)) {
				$val = $this->normalizeDecimal($row[$pc]);
				if ($val !== null) { return $val; }
			}
		}
		return null;
	}

	/**
	 * Tamsoft ürün satırını normalize eder
	 * - ext_urun_id: UrunKodu
	 * - miktar: Envanter
	 * - fiyat: IndirimliTutar > 0 ? IndirimliTutar : Tutar
	 * - barkod/birim: Barkodlar[0] -> Barkodu/Birim
	 */
	private function parseProductRow(array $row, int $depoId): ?array
	{
		$extUrunId = trim((string)($row['UrunKodu'] ?? ($row['Kod'] ?? '')));
		if ($extUrunId === '') { return null; }

		$urunAdi = trim((string)($row['UrunAdi'] ?? ($row['urun_adi'] ?? '')));
		$kdvOrani = (int)($row['KDVOrani'] ?? ($row['KDV'] ?? 0));
		$indirimliTutar = (float)($row['IndirimliTutar'] ?? 0);
		$tutar = (float)($row['Tutar'] ?? ($row['Fiyat'] ?? 0));
		$fiyat = $indirimliTutar > 0 ? $indirimliTutar : $tutar;

		$envanter = (float)($row['Envanter'] ?? ($row['Miktar'] ?? 0));

		$barkod = null; $birim = null; $altBarkodlar = [];
		if (!empty($row['Barkodlar']) && is_array($row['Barkodlar'])) {
			foreach ($row['Barkodlar'] as $bc) {
				$bk = trim((string)($bc['Barkodu'] ?? ($bc['Barkod'] ?? '')));
				$brm = trim((string)($bc['Birim'] ?? ($bc['birim'] ?? '')));
				$bfy = isset($bc['Fiyat']) ? (float)$bc['Fiyat'] : (isset($bc['fiyat']) ? (float)$bc['fiyat'] : null);
				if ($bk !== '') { $altBarkodlar[] = ['barkod'=>$bk,'birim'=>$brm ?: null,'fiyat'=>$bfy]; }
			}
			if (!empty($altBarkodlar)) {
				$barkod = $altBarkodlar[0]['barkod'];
				$birim = $altBarkodlar[0]['birim'] ?? 'ADET';
			}
		}

		return [
			'ext_urun_id' => $extUrunId,
			'urun_adi' => $urunAdi !== '' ? $urunAdi : null,
			'kdv' => $kdvOrani,
			'fiyat' => $fiyat,
			'miktar' => $envanter,
			'barkod' => $barkod,
			'birim' => $birim,
			'depo_id' => $depoId,
			'alt_barkodlar' => $altBarkodlar,
		];
	}

	private function getToken(): string
	{
		$cfg = $this->cfg->getConfig();
		$now = time();
		// Geçerli token varsa kullan
		if (!empty($cfg['token_value']) && !empty($cfg['token_expires_at'])) {
			$exp = strtotime((string)$cfg['token_expires_at']);
			if ($exp && $exp > ($now + 5)) {
				return (string)$cfg['token_value'];
			}
		}

		$tokenUrl = rtrim((string)($cfg['api_url'] ?? ''), '/') . '/token';
		$fields = [
			'grant_type' => 'password',
			'username' => (string)($cfg['kullanici'] ?? ''),
			'password' => (string)($cfg['sifre'] ?? ''),
		];
		$resp = $this->httpPost($tokenUrl, $fields);
		$token = (string)($resp['access_token'] ?? '');
		if ($token === '') {
			// Fallback GET dene
			$resp = $this->httpGet($tokenUrl . '?' . http_build_query($fields));
			$token = (string)($resp['access_token'] ?? '');
		}
		if ($token !== '') {
			$expiresIn = isset($resp['expires_in']) ? (int)$resp['expires_in'] : null;
			$tokenType = isset($resp['token_type']) ? (string)$resp['token_type'] : 'Bearer';
			$this->cfg->saveToken($token, $expiresIn, $tokenType);
		}
		return $token;
	}

	private function getDepotList(string $baseUrl, string $token): array
	{
		$url = rtrim($baseUrl, '/') . '/api/Integration/DepoListesi';
		$meta = $this->httpGetMeta($url, $token);
		$rows = is_array($meta['json'] ?? null) ? $meta['json'] : [];
		$clean = [];
		foreach ($rows as $d) {
			$id = (int)($d['Depoid'] ?? ($d['DepoID'] ?? 0));
			$ad = (string)($d['Adi'] ?? ($d['Ad'] ?? ''));
			// Sadece DB'de aktif olan depoları kullan
			$clean[] = ['id'=>$id, 'adi'=>$ad];
		}
		// DB aktif filtrelemesi
		$active = $this->repo->getActiveDepotsFromDb();
		if (!empty($active)) {
			$ids = array_column($active, 'id');
			$clean = array_values(array_filter($clean, fn($x)=> in_array($x['id'], $ids, true)));
		}
		return $clean;
	}

	public function syncDepots(): array
	{
		$cfg = $this->cfg->getConfig();
		$token = $this->getToken();
		$base = rtrim((string)($cfg['api_url'] ?? ''), '/');
		$endpoint = $base . '/api/Integration/DepoListesi';
		$meta = $this->httpGetMeta($endpoint, $token);
		$list = is_array($meta['json'] ?? null) ? $meta['json'] : [];
		$count = 0;
		foreach ($list as $d) {
			$id = (int)($d['Depoid'] ?? ($d['DepoID'] ?? 0));
			$adi = (string)($d['Adi'] ?? ($d['Ad'] ?? ''));
			if ($id > 0) { $this->repo->upsertDepot($id, $adi !== '' ? $adi : null); $count++; }
		}
		return [
			'success'=>true,
			'count'=>$count,
			'endpoint'=>$endpoint,
			'http_code' => $meta['http_code'] ?? null,
			'raw_total'=> is_array($list) ? count($list) : 0,
			'raw_sample'=> array_slice(is_array($list)?$list:[], 0, 5),
			'raw_body' => substr((string)($meta['raw_body'] ?? ''), 0, 4000),
			'error' => $meta['error'] ?? null,
		];
	}

	public function previewDepots(): array
	{
		$cfg = $this->cfg->getConfig();
		$token = $this->getToken();
		$base = rtrim((string)($cfg['api_url'] ?? ''), '/');
		$endpoint = $base . '/api/Integration/DepoListesi';
		$meta = $this->httpGetMeta($endpoint, $token);
		$list = is_array($meta['json'] ?? null) ? $meta['json'] : [];
		$rows = [];
		foreach ($list as $d) {
			$rows[] = [
				'ID' => (int)($d['Depoid'] ?? ($d['DepoID'] ?? 0)),
				'Adi' => (string)($d['Adi'] ?? ($d['Ad'] ?? '')),
			];
		}
		return [
			'success'=>true,
			'count'=>count($rows),
			'rows'=>$rows,
			'endpoint'=>$endpoint,
			'http_code' => $meta['http_code'] ?? null,
			'raw_total'=> is_array($list) ? count($list) : 0,
			'raw_sample'=> array_slice(is_array($list)?$list:[], 0, 5),
			'raw_body' => substr((string)($meta['raw_body'] ?? ''), 0, 4000),
			'error' => $meta['error'] ?? null,
			'health' => [ 'service' => ($meta['http_code'] ?? 500) === 200 ? 'online' : 'offline' ]
		];
	}

	public function previewStocks(?string $date, ?int $depoId, ?bool $onlyPositive, ?bool $lastBarcodeOnly, ?bool $onlyEcommerce, int $limit = 50): array
	{
		$cfg = $this->cfg->getConfig();
		$date = $date ?: (string)($cfg['default_date'] ?? '1900-01-01');
		$onlyPositive = $onlyPositive ?? (bool)($cfg['default_only_positive'] ?? 1);
		$lastBarcodeOnly = $lastBarcodeOnly ?? (bool)($cfg['default_last_barcode_only'] ?? 0);
		$onlyEcommerce = $onlyEcommerce ?? (bool)($cfg['default_only_ecommerce'] ?? 0);
		$token = $this->getToken();
		$base = rtrim((string)($cfg['api_url'] ?? ''), '/');
		// depo yoksa: önce DB'den ilk aktif depoyu seç, yoksa API'den
		if ($depoId === null) {
			$active = $this->repo->getActiveDepotsFromDb();
			if (!empty($active)) {
				$depoId = (int)$active[0]['id'];
			} else {
				$depots = $this->getDepotList($base, $token);
				foreach ($depots as $d) { $depoId = (int)($d['Depoid'] ?? ($d['DepoID'] ?? 0)); if ($depoId>0) break; }
			}
		}
		$params = [ 'tarih' => $date ];
		if ($depoId !== null) { $params['depoid'] = $depoId; }
		$params['miktarsifirdanbuyukstoklarlistelensin'] = $onlyPositive ? 'True' : 'False';
		$params['urununsonbarkodulistelensin'] = $lastBarcodeOnly ? 'True' : 'False';
		$params['sadeceeticaretstoklarigetir'] = $onlyEcommerce ? 'True' : 'False';
		$url = $base . '/api/Integration/StokListesi?' . http_build_query($params);
		$meta = $this->httpGetMeta($url, $token);
		$data = is_array($meta['json'] ?? null) ? $meta['json'] : [];
		$total = is_array($data) ? count($data) : 0;
		$rows = [];
		foreach (array_slice($data ?: [], 0, $limit) as $r) {
			$norm = $this->parseProductRow($r, (int)($depoId ?? 0));
			if ($norm === null) { continue; }
			$rows[] = [
				'Kod' => (string)$norm['ext_urun_id'],
				'Barkod' => (string)($norm['barkod'] ?? ''),
				'UrunAdi' => (string)($norm['urun_adi'] ?? ''),
				'Birim' => (string)($norm['birim'] ?? ''),
				'KDV' => (int)($norm['kdv'] ?? 0),
				'Fiyat' => (float)($norm['fiyat'] ?? 0),
				'Miktar' => (float)($norm['miktar'] ?? 0),
				'DepoID' => (int)($norm['depo_id'] ?? ($depoId ?? 0)),
			];
		}
		return [
			'success'=>true,
			'total'=>$total,
			'preview_count'=>count($rows),
			'rows'=>$rows,
			'params'=>$params,
			'endpoint'=>$url,
			'http_code' => $meta['http_code'] ?? null,
			'raw_sample'=> array_slice(is_array($data)?$data:[], 0, 5),
			'raw_body' => substr((string)($meta['raw_body'] ?? ''), 0, 4000),
			'error' => $meta['error'] ?? null,
		];
	}

	public function refreshStocks(?string $date = null, ?int $depoId = null, bool $onlyPositive = true, ?bool $lastBarcodeOnly = null, ?bool $onlyEcommerce = null): array
	{
		$cfg = $this->cfg->getConfig();
		$date = $date ?: (string)($cfg['default_date'] ?? '1900-01-01');
		$onlyPositive = (bool)$onlyPositive;
		$lastBarcodeOnly = $lastBarcodeOnly ?? (bool)($cfg['default_last_barcode_only'] ?? 0);
		$onlyEcommerce = $onlyEcommerce ?? (bool)($cfg['default_only_ecommerce'] ?? 0);
		$token = $this->getToken();
		$base = rtrim((string)($cfg['api_url'] ?? ''), '/');

		$imported = 0; $skipped = 0; $total = 0;
		$depolar = [];
		if ($depoId !== null) {
			$depolar = [ ['id' => $depoId, 'adi' => null] ];
		} else {
			// Tek depo: varsayılan depo ya da 1
			$did = isset($cfg['default_depo_id']) ? (int)$cfg['default_depo_id'] : 0;
			if ($did <= 0) { $did = 1; }
			$depolar = [ ['id' => $did, 'adi' => null] ];
		}

		foreach ($depolar as $dep) {
			// Depo veri bütünlüğü: envanterde görünmesi için en az 0 miktarlı satırları hazırla
			try { $this->repo->ensureDepotRows((int)$dep['id']); } catch (\Throwable $e) {}
			$params = [ 'tarih' => $date, 'depoid' => $dep['id'] ];
			$params['miktarsifirdanbuyukstoklarlistelensin'] = $onlyPositive ? 'True' : 'False';
			$params['urununsonbarkodulistelensin'] = $lastBarcodeOnly ? 'True' : 'False';
			$params['sadeceeticaretstoklarigetir'] = $onlyEcommerce ? 'True' : 'False';
			// Sayfalı çekim: 100'er
			$offset = 0; $batch = 100;
			$depotStartTs = time();
			$maxDepotSeconds = (int)($cfg['max_seconds_per_depot'] ?? 180);
			$maxPagesPerDepot = (int)($cfg['max_pages_per_depot'] ?? 200);
			$pagesDone = 0;
			$prevPageHash = null; $prevCnt = null;
			while (true) {
				// Timebox: depo bazında maksimum süre/sayfa
				if ($maxDepotSeconds > 0 && (time() - $depotStartTs) > $maxDepotSeconds) {
					$this->repo->logEvent('stok_update', 'Depo '.($dep['id']).' timebox aşıldı, sıradaki depoya geçiliyor');
					break;
				}
				if ($maxPagesPerDepot > 0 && $pagesDone >= $maxPagesPerDepot) {
					$this->repo->logEvent('stok_update', 'Depo '.($dep['id']).' sayfa limiti aşıldı, sıradaki depoya geçiliyor');
					break;
				}
				// Debug log: sayfa başlangıcı (ana dizindeki log dosyasına)
				try {
					$rootLog = dirname(__DIR__, 2) . '/logs/tamsoft.log';
					@file_put_contents($rootLog, json_encode(['page_start'=>['depo'=>$dep['id'],'offset'=>$offset,'ts'=>date('c')]], JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
				} catch (\Throwable $e) {}
				$q = $params + ['offset' => $offset, 'limit' => $batch];
				$url = $base . '/api/Integration/StokListesi?' . http_build_query($q);
				$meta = $this->httpGetMeta($url, $token);
				$data = is_array($meta['json'] ?? null) ? $meta['json'] : [];
				$cnt = is_array($data) ? count($data) : 0;
				// Bazı uçlarda offset/limit yok sayılıyor (aynı sayfa tekrar döner). Aynı sayfayı yakalayınca kır.
				$pageHash = $cnt > 0 ? md5(json_encode($data)) : null;
				if ($pagesDone > 0 && ($pageHash !== null && $pageHash === $prevPageHash || ($prevCnt !== null && $cnt === $prevCnt))) {
					try { $rootLog = dirname(__DIR__, 2) . '/logs/tamsoft.log'; @file_put_contents($rootLog, json_encode(['page_repeat'=>['depo'=>$dep['id'],'offset'=>$offset,'cnt'=>$cnt,'ts'=>date('c')]], JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND); } catch (\Throwable $e) {}
					break;
				}
				// Debug log: sayfa bitti (ana dizindeki log dosyasına)
				try {
					$rootLog = dirname(__DIR__, 2) . '/logs/tamsoft.log';
					@file_put_contents($rootLog, json_encode(['page_done'=>['depo'=>$dep['id'],'offset'=>$offset,'cnt'=>$cnt,'http_code'=>$meta['http_code'] ?? null,'ts'=>date('c')]], JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
				} catch (\Throwable $e) {}
				if ($cnt === 0) { break; }
				$total += $cnt;
				$bulkMode = !empty($cfg['bulk_stock_summary']);
				$prodRows = [];
				$bcRows = [];
				$sumRows = [];
				foreach ($data as $row) {
					$norm = $this->parseProductRow($row, (int)$dep['id']);
					if ($norm === null) { $skipped++; continue; }
					if ($bulkMode) {
						$prodRows[] = [
							'ext_urun_id' => $norm['ext_urun_id'],
							'barkod' => $norm['barkod'] ?? null,
							'urun_adi' => $norm['urun_adi'] ?? null,
							'kdv' => $norm['kdv'] ?? null,
							'birim' => $norm['birim'] ?? null,
							'fiyat' => $norm['fiyat'] ?? null,
						];
						foreach (($norm['alt_barkodlar'] ?? []) as $ab) {
							$bcRows[] = [
								'ext_urun_id' => $norm['ext_urun_id'],
								'barkod' => (string)$ab['barkod'],
								'birim' => $ab['birim'] ?? null,
								'fiyat' => isset($ab['fiyat']) ? (float)$ab['fiyat'] : null,
							];
						}
						$sumRows[] = [
							'ext_urun_id' => $norm['ext_urun_id'],
							'barcode' => $norm['barkod'] ?? null,
							'urun_adi' => $norm['urun_adi'] ?? null,
							'kdv' => $norm['kdv'] ?? null,
							'birim' => $norm['birim'] ?? null,
							'fiyat' => $norm['fiyat'] ?? null,
							'depo_id' => (int)$norm['depo_id'],
							'miktar' => (float)($norm['miktar'] ?? 0),
						];
						$imported++;
					} else {
						// Tek tek (mevcut davranış)
						$prefix = strtoupper(substr($norm['ext_urun_id'], 0, 3));
						$urunId = $this->repo->upsertProduct(
							$norm['ext_urun_id'], $norm['barkod'] ?? null, $norm['urun_adi'] ?? null, $norm['kdv'] ?? null, $norm['birim'] ?? null, $norm['fiyat'] ?? null
						);
						if (in_array($prefix, ['IPT','\u0130PT','BK ']) || str_starts_with(strtoupper($norm['ext_urun_id']), 'BK')) {
							$this->repo->setProductActive($urunId, 0);
						}
						foreach (($norm['alt_barkodlar'] ?? []) as $ab) {
							$this->repo->upsertBarcode($urunId, (string)$ab['barkod'], $ab['birim'] ?? null, isset($ab['fiyat']) ? (float)$ab['fiyat'] : null);
						}
						$this->repo->upsertDepot($norm['depo_id'], $dep['adi'] ?? null);
						$this->repo->upsertStockSummary($urunId, $norm['depo_id'], (float)($norm['miktar'] ?? 0), $norm['fiyat'] ?? null);
						if (!empty($cfg['verbose_stock_log'])) {
							$this->repo->logEvent('stok_update', 'Ürün '.$norm['ext_urun_id'].' depo '.$norm['depo_id'].' stok='.$norm['miktar'].' fiyat='.((string)($norm['fiyat'] ?? '')));
						}
						$imported++;
					}
				}
				// Bulk modunda: stage + pivot üzerinden upsert
				if ($bulkMode && !empty($prodRows)) {
					try { $this->repo->stageInsertProductBulk($prodRows); } catch (\Throwable $e) {}
					try { if (!empty($bcRows)) { $this->repo->stageInsertBarcodeBulk($bcRows); } } catch (\Throwable $e) {}
					// Stage'den master'a aktar
					try { $this->repo->syncMasterFromStage(); } catch (\Throwable $e) {}
					// Depo özetini toplu güncelle: önce ext->id map, sonra bulk upsert
					$exts = array_column($sumRows, 'ext_urun_id');
					$map = $this->repo->getProductIdsByExt($exts);
					$sumPayload = [];
					foreach ($sumRows as $s) {
						$uid = isset($map[$s['ext_urun_id']]) ? (int)$map[$s['ext_urun_id']] : 0;
						if ($uid > 0) {
							$this->repo->upsertDepot((int)$s['depo_id'], $dep['adi'] ?? null);
							$sumPayload[] = [ 'urun_id'=>$uid, 'depo_id'=>(int)$s['depo_id'], 'miktar'=>(float)$s['miktar'], 'fiyat'=>$s['fiyat'] ];
						}
					}
					if (!empty($sumPayload)) { $this->repo->upsertStockSummaryBulk($sumPayload); }
				}
				$offset += $batch;
				$pagesDone++;
				$prevPageHash = $pageHash; $prevCnt = $cnt;
				// Son sayfayı hızlı bitir: batch'ten küçükse veri bitti
				if ($cnt < $batch) { break; }
			}
		}
		$this->repo->updateProductQuantityFromSummary();
		// Eski değişim kayıtlarını 7 günden sonra sil
		try { $this->repoDb()->exec("DELETE FROM tamsoft_depo_stok_degisim_log WHERE changed_at < (NOW() - INTERVAL 1 DAY)"); } catch (\Throwable $e) {}
		return ['success'=>true, 'total'=>$total, 'imported'=>$imported, 'skipped'=>$skipped];
	}

	public function importRows(array $rows): array
	{
		$imported = 0; $skipped = 0;
		foreach ($rows as $row) {
			$ext = trim((string)($row['ext_urun_id'] ?? ($row['Kod'] ?? ($row['UrunKodu'] ?? ''))));
			if ($ext === '') { $skipped++; continue; }
			$depo = (int)($row['depo_id'] ?? ($row['DepoID'] ?? 0));
			$miktar = (float)($row['miktar'] ?? ($row['Miktar'] ?? 0));
			$fiyat = isset($row['fiyat']) ? (float)$row['fiyat'] : (isset($row['Fiyat']) ? (float)$row['Fiyat'] : null);
			$ad = isset($row['urun_adi']) ? (string)$row['urun_adi'] : (isset($row['UrunAdi']) ? (string)$row['UrunAdi'] : null);
			$kdv = isset($row['kdv']) ? (int)$row['kdv'] : (isset($row['KDV']) ? (int)$row['KDV'] : null);
			$birim = isset($row['birim']) ? (string)$row['birim'] : (isset($row['Birim']) ? (string)$row['Birim'] : null);
			$barkod = isset($row['barkod']) ? (string)$row['barkod'] : (isset($row['Barkod']) ? (string)$row['Barkod'] : null);
			$urunId = $this->repo->upsertProduct($ext, $barkod, $ad, $kdv, $birim, $fiyat);
			if ($depo > 0) { $this->repo->upsertDepot($depo, null); $this->repo->upsertStockSummary($urunId, $depo, $miktar, $fiyat); $imported++; }
		}
		$this->repo->updateProductQuantityFromSummary();
		return ['imported'=>$imported, 'skipped'=>$skipped, 'total'=>count($rows)];
	}

	public function testToken(): array
	{
		try {
			$token = $this->getToken();
			if ($token === '') { return ['success'=>false, 'message'=>'Token alınamadı']; }
			return [
				'success' => true,
				'token_preview' => substr($token, 0, 10) . '...'
			];
		} catch (\Throwable $e) {
			return ['success'=>false, 'error'=>$e->getMessage()];
		}
	}
}



