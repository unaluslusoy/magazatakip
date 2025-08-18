<?php

namespace app\Models;

use core\Model;

class TamsoftStockRepo extends Model
{
	protected $table = 'tamsoft_depo_stok_ozet';

	public function __construct()
	{
		parent::__construct();
		$this->createTables();
		$this->ensurePricePropagationTriggers();
	}

	private function createTables(): void
	{
		try {
			$this->db->exec("CREATE TABLE IF NOT EXISTS tamsoft_urunler (
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				ext_urun_id VARCHAR(120) NOT NULL,
				barkod VARCHAR(120) NULL,
				urun_adi VARCHAR(255) NULL,
				birim VARCHAR(50) NULL,
				kdv INT NULL,
				fiyat DECIMAL(14,4) NULL,
				miktari DECIMAL(14,4) NOT NULL DEFAULT 0,
				aktif TINYINT(1) NOT NULL DEFAULT 1,
				UNIQUE KEY uk_ext (ext_urun_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			// Performans için ek indeksler (varsa hata vermeden geç)
			try { $this->db->exec("ALTER TABLE tamsoft_urunler ADD INDEX idx_urun_adi (urun_adi)"); } catch (\Throwable $e) {}
			try { $this->db->exec("ALTER TABLE tamsoft_urunler ADD INDEX idx_barkod (barkod)"); } catch (\Throwable $e) {}
			try { $this->db->exec("ALTER TABLE tamsoft_urunler ADD INDEX idx_aktif (aktif)"); } catch (\Throwable $e) {}
			$this->db->exec("CREATE TABLE IF NOT EXISTS tamsoft_urun_barkodlar (
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				urun_id BIGINT UNSIGNED NOT NULL,
				barkod VARCHAR(120) NOT NULL,
				birim VARCHAR(50) NULL,
				fiyat DECIMAL(14,4) NULL,
				UNIQUE KEY uk_barkod (barkod),
				KEY idx_urun (urun_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			$this->db->exec("CREATE TABLE IF NOT EXISTS tamsoft_depolar (
				id INT UNSIGNED NOT NULL AUTO_INCREMENT,
				depo_id INT NOT NULL,
				depo_adi VARCHAR(200) NULL,
				aktif TINYINT(1) NOT NULL DEFAULT 1,
				PRIMARY KEY (id),
				UNIQUE KEY uk_depo_id (depo_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			// Eski şemadan geçiş: kolon ekle / index ekle / id autoinc
			try { $this->db->exec("ALTER TABLE tamsoft_depolar ADD COLUMN depo_id INT NULL AFTER id"); } catch (\Throwable $e) {}
			try { $this->db->exec("UPDATE tamsoft_depolar SET depo_id = id WHERE depo_id IS NULL"); } catch (\Throwable $e) {}
			try { $this->db->exec("ALTER TABLE tamsoft_depolar ADD UNIQUE KEY uk_depo_id (depo_id)"); } catch (\Throwable $e) {}
			try { $this->db->exec("ALTER TABLE tamsoft_depolar MODIFY COLUMN id INT UNSIGNED NOT NULL AUTO_INCREMENT"); } catch (\Throwable $e) {}
			$this->db->exec("CREATE TABLE IF NOT EXISTS tamsoft_depo_stok_ozet (
				urun_id BIGINT UNSIGNED NOT NULL,
				depo_id INT NOT NULL,
				miktar DECIMAL(14,4) NOT NULL DEFAULT 0,
				fiyat DECIMAL(14,4) NULL,
				PRIMARY KEY (urun_id, depo_id),
				KEY idx_depo (depo_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			try { $this->db->exec("ALTER TABLE tamsoft_depo_stok_ozet ADD INDEX idx_urun (urun_id)"); } catch (\Throwable $e) {}
			$this->db->exec("CREATE TABLE IF NOT EXISTS tamsoft_stok_log (
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				type VARCHAR(50) NULL,
				message VARCHAR(500) NOT NULL,
				created_at DATETIME NOT NULL,
				KEY idx_created (created_at)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			$this->db->exec("CREATE TABLE IF NOT EXISTS tamsoft_depo_stok_degisim_log (
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				urun_id BIGINT UNSIGNED NOT NULL,
				depo_id INT NOT NULL,
				old_miktar DECIMAL(14,4) NULL,
				new_miktar DECIMAL(14,4) NULL,
				old_fiyat DECIMAL(14,4) NULL,
				new_fiyat DECIMAL(14,4) NULL,
				changed_at DATETIME NOT NULL,
				KEY idx_changed (changed_at),
				KEY idx_ud (urun_id, depo_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			// Entegrasyon map tablosu yoksa oluştur
			$this->db->exec("CREATE TABLE IF NOT EXISTS urun_entegrasyon_map (
				id INT AUTO_INCREMENT PRIMARY KEY,
				urun_kodu VARCHAR(150) NULL,
				barkod VARCHAR(150) NULL,
				trendyolgo_sku VARCHAR(190) NULL,
				getir_code VARCHAR(190) NULL,
				yemeksepeti_code VARCHAR(190) NULL,
				created_at DATETIME NULL,
				updated_at DATETIME NULL,
				INDEX idx_barkod (barkod),
				INDEX idx_urun_kodu (urun_kodu)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		} catch (\Throwable $e) { error_log('tamsoft tables create error: '.$e->getMessage()); }
	}

	private function ensurePricePropagationTriggers(): void
	{
		// Ürün fiyatı INSERT/UPDATE olduğunda barkod ve depo özet fiyatlarını da güncelle
		try { $this->db->exec("DROP TRIGGER IF EXISTS trg_tamsoft_urunler_ai_price"); } catch (\Throwable $e) {}
		try {
			$this->db->exec(
				"CREATE TRIGGER trg_tamsoft_urunler_ai_price AFTER INSERT ON tamsoft_urunler FOR EACH ROW\n"
				."BEGIN\n"
				."\tIF NEW.fiyat IS NOT NULL THEN\n"
				."\t\tUPDATE tamsoft_urun_barkodlar SET fiyat = NEW.fiyat WHERE urun_id = NEW.id;\n"
				."\t\tINSERT INTO tamsoft_depo_stok_degisim_log(urun_id,depo_id,old_miktar,new_miktar,old_fiyat,new_fiyat,changed_at)\n"
				."\t\tSELECT s.urun_id, s.depo_id, s.miktar, s.miktar, s.fiyat, NEW.fiyat, NOW() FROM tamsoft_depo_stok_ozet s WHERE s.urun_id = NEW.id AND NOT (s.fiyat <=> NEW.fiyat);\n"
				."\t\tUPDATE tamsoft_depo_stok_ozet SET fiyat = NEW.fiyat WHERE urun_id = NEW.id;\n"
				."\tEND IF;\n"
				."END"
			);
		} catch (\Throwable $e) { /* olabilir */ }

		try { $this->db->exec("DROP TRIGGER IF EXISTS trg_tamsoft_urunler_au_price"); } catch (\Throwable $e) {}
		try {
			$this->db->exec(
				"CREATE TRIGGER trg_tamsoft_urunler_au_price AFTER UPDATE ON tamsoft_urunler FOR EACH ROW\n"
				."BEGIN\n"
				."\tIF NOT (NEW.fiyat <=> OLD.fiyat) THEN\n"
				."\t\tUPDATE tamsoft_urun_barkodlar SET fiyat = NEW.fiyat WHERE urun_id = NEW.id;\n"
				."\t\tINSERT INTO tamsoft_depo_stok_degisim_log(urun_id,depo_id,old_miktar,new_miktar,old_fiyat,new_fiyat,changed_at)\n"
				."\t\tSELECT s.urun_id, s.depo_id, s.miktar, s.miktar, s.fiyat, NEW.fiyat, NOW() FROM tamsoft_depo_stok_ozet s WHERE s.urun_id = NEW.id AND NOT (s.fiyat <=> NEW.fiyat);\n"
				."\t\tUPDATE tamsoft_depo_stok_ozet SET fiyat = NEW.fiyat WHERE urun_id = NEW.id;\n"
				."\tEND IF;\n"
				."END"
			);
		} catch (\Throwable $e) { /* olabilir */ }
	}

	public function upsertProduct(string $extId, ?string $barkod, ?string $ad, ?int $kdv, ?string $birim, ?float $fiyat): int
	{
		$sql = "INSERT INTO tamsoft_urunler(ext_urun_id, barkod, urun_adi, kdv, birim, fiyat)
			VALUES(:e,:b,:a,:k,:bi,:f)
			ON DUPLICATE KEY UPDATE barkod=VALUES(barkod), urun_adi=VALUES(urun_adi), kdv=VALUES(kdv), birim=VALUES(birim), fiyat=VALUES(fiyat)";
		$stmt = $this->db->prepare($sql);
		$stmt->execute([':e'=>$extId, ':b'=>$barkod, ':a'=>$ad, ':k'=>$kdv, ':bi'=>$birim, ':f'=>$fiyat]);
		// return id
		$sel = $this->db->prepare("SELECT id FROM tamsoft_urunler WHERE ext_urun_id=:e");
		$sel->execute([':e'=>$extId]);
		$urunId = (int)($sel->fetchColumn() ?: 0);
		// Fiyat verildiyse alt tablolara yay
		if ($urunId > 0 && $fiyat !== null) {
			try {
				$this->updateAllBarcodesPriceByProductId($urunId, $fiyat);
				$this->updateDepotSummaryPriceByProduct($urunId, $fiyat);
			} catch (\Throwable $e) { /* sessizce yut */ }
		}
		return $urunId;
	}

	public function setProductActive(int $urunId, int $aktif): void
	{
		$this->db->prepare("UPDATE tamsoft_urunler SET aktif=:a WHERE id=:id")
			->execute([':a'=>$aktif, ':id'=>$urunId]);
	}

	public function upsertBarcode(int $urunId, string $barkod, ?string $birim, ?float $fiyat): void
	{
		if ($barkod === '') { return; }
		$sql = "INSERT INTO tamsoft_urun_barkodlar(urun_id, barkod, birim, fiyat) VALUES(:u,:b,:bi,:f)
			ON DUPLICATE KEY UPDATE urun_id=VALUES(urun_id), birim=VALUES(birim), fiyat=VALUES(fiyat)";
		$this->db->prepare($sql)->execute([':u'=>$urunId, ':b'=>$barkod, ':bi'=>$birim, ':f'=>$fiyat]);
	}

	public function upsertDepot(int $depoId, ?string $depoAdi): void
	{
		// depo_adi asla NULL'a düşmesin: NULL veya '' gelirse mevcut adı koru
		$sql = "INSERT INTO tamsoft_depolar(depo_id, depo_adi, aktif) VALUES(:did,:ad,1)
			ON DUPLICATE KEY UPDATE depo_adi=CASE WHEN VALUES(depo_adi) IS NULL OR VALUES(depo_adi) = '' THEN tamsoft_depolar.depo_adi ELSE VALUES(depo_adi) END,
			aktif=1";
		$this->db->prepare($sql)->execute([':did'=>$depoId, ':ad'=>$depoAdi]);
	}

	public function upsertStockSummary(int $urunId, int $depoId, float $miktar, ?float $fiyat): void
	{
		// Eğer değişiklik yoksa yazma, değişiklik varsa değişim logla
		$sel = $this->db->prepare("SELECT miktar, fiyat FROM tamsoft_depo_stok_ozet WHERE urun_id=:u AND depo_id=:d");
		$sel->execute([':u'=>$urunId, ':d'=>$depoId]);
		$row = $sel->fetch(\PDO::FETCH_ASSOC);
		if ($row) {
			$oldM = (float)$row['miktar'];
			$oldF = isset($row['fiyat']) ? (float)$row['fiyat'] : null;
			if (abs($oldM - $miktar) < 0.0001 && (($oldF === null && $fiyat === null) || ($oldF !== null && $fiyat !== null && abs($oldF - (float)$fiyat) < 0.0001))) {
				return;
			}
			// değişimi logla
			try {
				$stmt = $this->db->prepare("INSERT INTO tamsoft_depo_stok_degisim_log(urun_id,depo_id,old_miktar,new_miktar,old_fiyat,new_fiyat,changed_at) VALUES(:u,:d,:om,:nm,:of,:nf,:c)");
				$stmt->execute([':u'=>$urunId, ':d'=>$depoId, ':om'=>$oldM, ':nm'=>$miktar, ':of'=>$oldF, ':nf'=>$fiyat, ':c'=>date('Y-m-d H:i:s')]);
			} catch (\Throwable $e) {}
		}
		$sql = "INSERT INTO tamsoft_depo_stok_ozet(urun_id, depo_id, miktar, fiyat) VALUES(:u,:d,:m,:f)
			ON DUPLICATE KEY UPDATE miktar=VALUES(miktar), fiyat=VALUES(fiyat)";
		$this->db->prepare($sql)->execute([':u'=>$urunId, ':d'=>$depoId, ':m'=>$miktar, ':f'=>$fiyat]);
	}

	// Staging yardımcıları
	public function ensureStageTables(): void
	{
		$this->db->exec("CREATE TABLE IF NOT EXISTS tamsoft_urunler_stage (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			ext_urun_id VARCHAR(120) NOT NULL,
			barkod VARCHAR(120) NULL,
			urun_adi VARCHAR(255) NULL,
			birim VARCHAR(50) NULL,
			kdv INT NULL,
			fiyat DECIMAL(14,4) NULL,
			UNIQUE KEY uk_ext (ext_urun_id, barkod)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$this->db->exec("CREATE TABLE IF NOT EXISTS tamsoft_urun_barkodlar_stage (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			ext_urun_id VARCHAR(120) NOT NULL,
			barkod VARCHAR(120) NOT NULL,
			birim VARCHAR(50) NULL,
			fiyat DECIMAL(14,4) NULL,
			UNIQUE KEY uk_barkod (barkod)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	}

	public function truncateStage(): void
	{
		$this->db->exec("TRUNCATE TABLE tamsoft_urunler_stage");
		$this->db->exec("TRUNCATE TABLE tamsoft_urun_barkodlar_stage");
	}

	public function stageInsertProduct(string $extId, ?string $barkod, ?string $ad, ?int $kdv, ?string $birim, ?float $fiyat): void
	{
		$sql = "INSERT INTO tamsoft_urunler_stage(ext_urun_id, barkod, urun_adi, kdv, birim, fiyat)
			VALUES(:e,:b,:a,:k,:bi,:f)
			ON DUPLICATE KEY UPDATE urun_adi=VALUES(urun_adi), kdv=VALUES(kdv), birim=VALUES(birim), fiyat=VALUES(fiyat)";
		$this->db->prepare($sql)->execute([':e'=>$extId, ':b'=>$barkod, ':a'=>$ad, ':k'=>$kdv, ':bi'=>$birim, ':f'=>$fiyat]);
	}

	public function stageInsertBarcode(string $extId, string $barkod, ?string $birim, ?float $fiyat): void
	{
		if ($barkod === '') { return; }
		$sql = "INSERT INTO tamsoft_urun_barkodlar_stage(ext_urun_id, barkod, birim, fiyat)
			VALUES(:e,:b,:bi,:f)
			ON DUPLICATE KEY UPDATE birim=VALUES(birim), fiyat=VALUES(fiyat)";
		$this->db->prepare($sql)->execute([':e'=>$extId, ':b'=>$barkod, ':bi'=>$birim, ':f'=>$fiyat]);
	}

	/**
	 * Çoklu ürün satırını stage tablosuna ekler (bulk insert)
	 * rows: [ [ext_urun_id, barkod, urun_adi, kdv, birim, fiyat], ... ]
	 */
	public function stageInsertProductBulk(array $rows, int $chunkSize = 500): void
	{
		$chunk = [];
		foreach ($rows as $r) {
			$ext = (string)($r['ext_urun_id'] ?? ''); if ($ext === '') { continue; }
			$chunk[] = [
				$ext,
				$r['barkod'] ?? null,
				$r['urun_adi'] ?? null,
				isset($r['kdv']) ? (int)$r['kdv'] : null,
				$r['birim'] ?? null,
				isset($r['fiyat']) ? (float)$r['fiyat'] : null,
			];
			if (count($chunk) >= $chunkSize) { $this->flushStageProductChunk($chunk); $chunk = []; }
		}
		if (!empty($chunk)) { $this->flushStageProductChunk($chunk); }
	}

	private function flushStageProductChunk(array $chunk): void
	{
		if (empty($chunk)) return;
		$values = [];
		$params = [];
		$idx = 0;
		foreach ($chunk as $row) {
			$values[] = "(:e$idx,:b$idx,:a$idx,:k$idx,:bi$idx,:f$idx)";
			$params[":e$idx"] = $row[0];
			$params[":b$idx"] = $row[1];
			$params[":a$idx"] = $row[2];
			$params[":k$idx"] = $row[3];
			$params[":bi$idx"] = $row[4];
			$params[":f$idx"] = $row[5];
			$idx++;
		}
		$sql = "INSERT INTO tamsoft_urunler_stage(ext_urun_id, barkod, urun_adi, kdv, birim, fiyat) VALUES ".implode(',', $values)
			." ON DUPLICATE KEY UPDATE urun_adi=VALUES(urun_adi), kdv=VALUES(kdv), birim=VALUES(birim), fiyat=VALUES(fiyat)";
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
	}

	/**
	 * Çoklu barkod satırını stage tablosuna ekler (bulk insert)
	 * rows: [ [ext_urun_id, barkod, birim, fiyat], ... ]
	 */
	public function stageInsertBarcodeBulk(array $rows, int $chunkSize = 1000): void
	{
		$chunk = [];
		foreach ($rows as $r) {
			$ext = (string)($r['ext_urun_id'] ?? '');
			$bc = (string)($r['barkod'] ?? '');
			if ($ext === '' || $bc === '') { continue; }
			$chunk[] = [ $ext, $bc, $r['birim'] ?? null, isset($r['fiyat']) ? (float)$r['fiyat'] : null ];
			if (count($chunk) >= $chunkSize) { $this->flushStageBarcodeChunk($chunk); $chunk = []; }
		}
		if (!empty($chunk)) { $this->flushStageBarcodeChunk($chunk); }
	}

	private function flushStageBarcodeChunk(array $chunk): void
	{
		if (empty($chunk)) return;
		$values = [];
		$params = [];
		$idx = 0;
		foreach ($chunk as $row) {
			$values[] = "(:e$idx,:b$idx,:bi$idx,:f$idx)";
			$params[":e$idx"] = $row[0];
			$params[":b$idx"] = $row[1];
			$params[":bi$idx"] = $row[2];
			$params[":f$idx"] = $row[3];
			$idx++;
		}
		$sql = "INSERT INTO tamsoft_urun_barkodlar_stage(ext_urun_id, barkod, birim, fiyat) VALUES ".implode(',', $values)
			." ON DUPLICATE KEY UPDATE birim=VALUES(birim), fiyat=VALUES(fiyat)";
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
	}

	/**
	 * Verilen ext_urun_id listesi için id eşlemesi döndürür
	 * return: [ ext_urun_id => id ]
	 */
	public function getProductIdsByExt(array $extList): array
	{
		$extList = array_values(array_unique(array_filter(array_map('strval', $extList))));
		if (empty($extList)) { return []; }
		$map = [];
		$chunkSize = 1000; $total = count($extList);
		for ($i = 0; $i < $total; $i += $chunkSize) {
			$chunk = array_slice($extList, $i, $chunkSize);
			$placeholders = [];
			$params = [];
			foreach ($chunk as $idx => $ext) { $ph = ":e$idx"; $placeholders[] = $ph; $params[$ph] = $ext; }
			$sql = "SELECT id, ext_urun_id FROM tamsoft_urunler WHERE ext_urun_id IN (".implode(',', $placeholders).")";
			$stmt = $this->db->prepare($sql);
			$stmt->execute($params);
			foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) { $map[(string)$row['ext_urun_id']] = (int)$row['id']; }
		}
		return $map;
	}

	/**
	 * Depo stok özetlerini toplu upsert eder (log yazmaz)
	 * rows: [ [urun_id, depo_id, miktar, fiyat], ... ]
	 */
	public function upsertStockSummaryBulk(array $rows, int $chunkSize = 1000): void
	{
		$chunk = [];
		foreach ($rows as $r) {
			$uid = isset($r['urun_id']) ? (int)$r['urun_id'] : 0;
			$did = isset($r['depo_id']) ? (int)$r['depo_id'] : 0;
			if ($uid <= 0 || $did <= 0) { continue; }
			$chunk[] = [ $uid, $did, (float)($r['miktar'] ?? 0), isset($r['fiyat']) ? (float)$r['fiyat'] : null ];
			if (count($chunk) >= $chunkSize) { $this->flushStockSummaryChunk($chunk); $chunk = []; }
		}
		if (!empty($chunk)) { $this->flushStockSummaryChunk($chunk); }
	}

	private function flushStockSummaryChunk(array $chunk): void
	{
		if (empty($chunk)) return;
		$values = [];
		$params = [];
		$idx = 0;
		foreach ($chunk as $row) {
			$values[] = "(:u$idx,:d$idx,:m$idx,:f$idx)";
			$params[":u$idx"] = $row[0];
			$params[":d$idx"] = $row[1];
			$params[":m$idx"] = $row[2];
			$params[":f$idx"] = $row[3];
			$idx++;
		}
		$sql = "INSERT INTO tamsoft_depo_stok_ozet(urun_id, depo_id, miktar, fiyat) VALUES ".implode(',', $values)
			." ON DUPLICATE KEY UPDATE miktar=VALUES(miktar), fiyat=VALUES(fiyat)";
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
	}

	public function syncMasterFromStage(): void
	{
		// 1) Master ürünleri pasifle
		$this->db->exec("UPDATE tamsoft_urunler SET aktif=0");
		// 2) Stage'den master'a upsert (ürün)
		$this->db->exec("INSERT INTO tamsoft_urunler(ext_urun_id, barkod, urun_adi, birim, kdv, fiyat, aktif)
			SELECT s.ext_urun_id,
				MAX(s.barkod) as barkod,
				MAX(s.urun_adi) as urun_adi,
				MAX(s.birim) as birim,
				MAX(s.kdv) as kdv,
				MAX(s.fiyat) as fiyat,
				1 as aktif
			FROM tamsoft_urunler_stage s
			GROUP BY s.ext_urun_id
			ON DUPLICATE KEY UPDATE urun_adi=VALUES(urun_adi), birim=VALUES(birim), kdv=VALUES(kdv), fiyat=VALUES(fiyat), aktif=1");
		// 3) Barkodları upsert
		$this->db->exec("INSERT INTO tamsoft_urun_barkodlar(urun_id, barkod, birim, fiyat)
			SELECT u.id, bs.barkod, bs.birim, bs.fiyat
			FROM tamsoft_urun_barkodlar_stage bs
			JOIN tamsoft_urunler u ON u.ext_urun_id = bs.ext_urun_id
			ON DUPLICATE KEY UPDATE birim=VALUES(birim), fiyat=VALUES(fiyat)");
	}

	public function updateProductQuantityFromSummary(): void
	{
		$this->db->exec("UPDATE tamsoft_urunler u
			SET u.miktari = COALESCE((SELECT SUM(s.miktar) FROM tamsoft_depo_stok_ozet s WHERE s.urun_id = u.id), 0)");
	}

	public function listProducts(int $limit = 1000): array
	{
		$sql = "SELECT id, ext_urun_id, barkod, urun_adi, birim, kdv, fiyat, miktari FROM tamsoft_urunler ORDER BY urun_adi ASC LIMIT :lim";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function listProductsWithMap(int $limit = 1000): array
	{
		$sql = "SELECT u.id, u.ext_urun_id, u.barkod, u.urun_adi, u.birim, u.kdv, u.fiyat, u.miktari,
			COALESCE(m1.trendyolgo_sku, m2.trendyolgo_sku) AS trendyolgo_sku,
			COALESCE(m1.getir_code, m2.getir_code) AS getir_code,
			COALESCE(m1.yemeksepeti_code, m2.yemeksepeti_code) AS yemeksepeti_code
			FROM tamsoft_urunler u
			LEFT JOIN urun_entegrasyon_map m1 ON m1.urun_kodu = u.ext_urun_id
			LEFT JOIN urun_entegrasyon_map m2 ON m2.barkod = u.barkod
			ORDER BY u.urun_adi ASC LIMIT :lim";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function countProductsTotal(?string $filterPrefix = null, ?int $hasIntegration = null, ?int $onlyPositive = null, ?int $depoId = null): int
	{
		$where = [];
		if ($filterPrefix === 'IPT') { $where[] = "(UPPER(u.ext_urun_id) LIKE 'IPT%' OR u.ext_urun_id LIKE 'İPT%')"; }
		if ($filterPrefix === 'BK') { $where[] = "(UPPER(u.ext_urun_id) LIKE 'BK%')"; }
		if ($hasIntegration !== null) {
			$where[] = $hasIntegration ? '(m1.trendyolgo_sku IS NOT NULL OR m1.getir_code IS NOT NULL OR m1.yemeksepeti_code IS NOT NULL OR m2.trendyolgo_sku IS NOT NULL OR m2.getir_code IS NOT NULL OR m2.yemeksepeti_code IS NOT NULL)'
				: '(m1.trendyolgo_sku IS NULL AND m1.getir_code IS NULL AND m1.yemeksepeti_code IS NULL AND m2.trendyolgo_sku IS NULL AND m2.getir_code IS NULL AND m2.yemeksepeti_code IS NULL)';
		}
		if ($depoId !== null || $onlyPositive !== null) {
			$sw = [];
			if ($depoId !== null) { $sw[] = 's.depo_id = '.(int)$depoId; }
			if ($onlyPositive !== null) { $sw[] = $onlyPositive ? 's.miktar > 0' : '1=1'; }
			$where[] = 'EXISTS (SELECT 1 FROM tamsoft_depo_stok_ozet s WHERE s.urun_id = u.id'.(empty($sw)?'':' AND '.implode(' AND ', $sw)).')';
		}
		$sql = 'SELECT COUNT(*) FROM tamsoft_urunler u LEFT JOIN urun_entegrasyon_map m1 ON m1.urun_kodu = u.ext_urun_id LEFT JOIN urun_entegrasyon_map m2 ON m2.barkod = u.barkod';
		if (!empty($where)) { $sql .= ' WHERE '.implode(' AND ', $where); }
		return (int)$this->db->query($sql)->fetchColumn();
	}

	public function countProductsFiltered(?string $search, ?string $filterPrefix = null, ?int $hasIntegration = null, ?int $onlyPositive = null, ?int $depoId = null): int
	{
		$search = trim((string)$search);
		$where = [];
		$params = [];
		if ($filterPrefix === 'IPT') { $where[] = "(UPPER(u.ext_urun_id) LIKE 'IPT%' OR u.ext_urun_id LIKE 'İPT%')"; }
		if ($filterPrefix === 'BK') { $where[] = "(UPPER(u.ext_urun_id) LIKE 'BK%')"; }
		if ($hasIntegration !== null) {
			$where[] = $hasIntegration ? '(m1.trendyolgo_sku IS NOT NULL OR m1.getir_code IS NOT NULL OR m1.yemeksepeti_code IS NOT NULL OR m2.trendyolgo_sku IS NOT NULL OR m2.getir_code IS NOT NULL OR m2.yemeksepeti_code IS NOT NULL)'
				: '(m1.trendyolgo_sku IS NULL AND m1.getir_code IS NULL AND m1.yemeksepeti_code IS NULL AND m2.trendyolgo_sku IS NULL AND m2.getir_code IS NULL AND m2.yemeksepeti_code IS NULL)';
		}
		if ($depoId !== null || $onlyPositive !== null) {
			$sw = [];
			if ($depoId !== null) { $sw[] = 's.depo_id = '.(int)$depoId; }
			if ($onlyPositive !== null) { $sw[] = $onlyPositive ? 's.miktar > 0' : '1=1'; }
			$where[] = 'EXISTS (SELECT 1 FROM tamsoft_depo_stok_ozet s WHERE s.urun_id = u.id'.(empty($sw)?'':' AND '.implode(' AND ', $sw)).')';
		}
		if ($search !== '') {
			$where[] = '(u.ext_urun_id LIKE :s OR u.urun_adi LIKE :s)';
			$params[':s'] = '%'.$search.'%';
		}
		$sql = 'SELECT COUNT(*) FROM tamsoft_urunler u LEFT JOIN urun_entegrasyon_map m1 ON m1.urun_kodu = u.ext_urun_id LEFT JOIN urun_entegrasyon_map m2 ON m2.barkod = u.barkod';
		if (!empty($where)) { $sql .= ' WHERE '.implode(' AND ', $where); }
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
		return (int)$stmt->fetchColumn();
	}

	public function listProductsWithMapPage(int $start, int $length, ?string $search, string $orderBy, string $orderDir, ?string $filterPrefix = null): array
	{
		$search = trim((string)$search);
		$allowed = ['u.urun_adi','u.ext_urun_id','u.barkod','u.fiyat','u.miktari'];
		if (!in_array($orderBy, $allowed, true)) { $orderBy = 'u.urun_adi'; }
		$orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
		$where = [];
		$params = [':lim' => $length, ':off' => $start];
		if ($filterPrefix === 'IPT') { $where[] = "(UPPER(u.ext_urun_id) LIKE 'IPT%' OR u.ext_urun_id LIKE 'İPT%')"; }
		if ($filterPrefix === 'BK') { $where[] = "(UPPER(u.ext_urun_id) LIKE 'BK%')"; }
		if ($search !== '') { $where[] = '(u.ext_urun_id LIKE :s OR u.urun_adi LIKE :s)'; $params[':s'] = '%'.$search.'%'; }
		$sql = "SELECT u.id, u.ext_urun_id, u.barkod, u.urun_adi, u.birim, u.kdv, u.fiyat, u.miktari,
			COALESCE(m1.trendyolgo_sku, m2.trendyolgo_sku) AS trendyolgo_sku,
			COALESCE(m1.getir_code, m2.getir_code) AS getir_code,
			COALESCE(m1.yemeksepeti_code, m2.yemeksepeti_code) AS yemeksepeti_code
			FROM tamsoft_urunler u
			LEFT JOIN urun_entegrasyon_map m1 ON m1.urun_kodu = u.ext_urun_id
			LEFT JOIN urun_entegrasyon_map m2 ON m2.barkod = u.barkod";
		if (!empty($where)) { $sql .= ' WHERE '.implode(' AND ', $where); }
		$sql .= " ORDER BY $orderBy $orderDir LIMIT :lim OFFSET :off";
		$stmt = $this->db->prepare($sql);
		foreach ($params as $k=>$v) {
			if ($k === ':lim' || $k === ':off') { $stmt->bindValue($k, $v, \PDO::PARAM_INT); } else { $stmt->bindValue($k, $v); }
		}
		$stmt->execute();
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function getActiveDepots(): array
	{
		$stmt = $this->db->query("SELECT depo_id AS id, depo_adi FROM tamsoft_depolar WHERE aktif=1 ORDER BY depo_id ASC");
		return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
	}

	public function listProductsWithDepotsPage(int $start, int $length, ?string $search, string $orderBy, string $orderDir, ?string $filterPrefix = null, ?int $hasIntegration = null, ?int $onlyPositive = null, ?int $depoId = null, ?int $orderDepotId = null): array
	{
		$depots = $this->getActiveDepots();
		$search = trim((string)$search);
		$allowed = ['u.urun_adi','u.ext_urun_id','u.barkod','u.fiyat','u.miktari'];
		if (!in_array($orderBy, $allowed, true)) { $orderBy = 'u.urun_adi'; }
		// Order by map to outer alias P
		$obMap = [
			'u.urun_adi' => 'P.urun_adi',
			'u.ext_urun_id' => 'P.ext_urun_id',
			'u.barkod' => 'P.barkod',
			'u.fiyat' => 'P.fiyat',
			'u.miktari' => 'P.miktari',
		];
		$orderByOut = $obMap[$orderBy] ?? 'P.urun_adi';
		$orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
		$where = [];
		$params = [':lim' => $length, ':off' => $start];
		if ($filterPrefix === 'IPT') { $where[] = "(UPPER(u.ext_urun_id) LIKE 'IPT%' OR u.ext_urun_id LIKE 'İPT%')"; }
		if ($filterPrefix === 'BK') { $where[] = "(UPPER(u.ext_urun_id) LIKE 'BK%')"; }
		if ($search !== '') { $where[] = '(u.ext_urun_id LIKE :s OR u.urun_adi LIKE :s)'; $params[':s'] = '%'.$search.'%'; }
		if ($hasIntegration !== null) {
			$where[] = $hasIntegration ? '(m1.trendyolgo_sku IS NOT NULL OR m1.getir_code IS NOT NULL OR m1.yemeksepeti_code IS NOT NULL OR m2.trendyolgo_sku IS NOT NULL OR m2.getir_code IS NOT NULL OR m2.yemeksepeti_code IS NOT NULL)'
				: '(m1.trendyolgo_sku IS NULL AND m1.getir_code IS NULL AND m1.yemeksepeti_code IS NULL AND m2.trendyolgo_sku IS NULL AND m2.getir_code IS NULL AND m2.yemeksepeti_code IS NULL)';
		}
		// Pivot alt sorgusu: depo toplamları
		$pivotCols = [];
		foreach ($depots as $d) {
			$did = (int)$d['id'];
			$pivotCols[] = "SUM(CASE WHEN depo_id = $did THEN miktar ELSE 0 END) AS dp_$did";
		}
		$pivotSelect = empty($pivotCols) ? 'NULL' : implode(', ', $pivotCols);
		$stockWhere = [];
		if ($depoId !== null) { $stockWhere[] = 'depo_id = '.(int)$depoId; }
		if ($onlyPositive !== null) { $stockWhere[] = $onlyPositive ? 'miktar > 0' : '1=1'; }
		$pivotSql = "SELECT urun_id, $pivotSelect FROM tamsoft_depo_stok_ozet" . (empty($stockWhere)?'':' WHERE '.implode(' AND ',$stockWhere)) . " GROUP BY urun_id";
		// Ürün + map temel alt sorgu
		$baseSql = "SELECT u.id, u.ext_urun_id, u.barkod, u.urun_adi, u.birim, u.kdv, u.fiyat, u.miktari,\n\t\tCOALESCE(m1.trendyolgo_sku, m2.trendyolgo_sku) AS trendyolgo_sku,\n\t\tCOALESCE(m1.getir_code, m2.getir_code) AS getir_code,\n\t\tCOALESCE(m1.yemeksepeti_code, m2.yemeksepeti_code) AS yemeksepeti_code\n\t\tFROM tamsoft_urunler u\n\t\tLEFT JOIN urun_entegrasyon_map m1 ON m1.urun_kodu = u.ext_urun_id\n\t\tLEFT JOIN urun_entegrasyon_map m2 ON m2.barkod = u.barkod";
		if (!empty($where)) { $baseSql .= ' WHERE '.implode(' AND ', $where); }
		if ($orderDepotId !== null) {
			$sql = "SELECT P.*, S.* FROM (".$baseSql.") P LEFT JOIN (".$pivotSql.") S ON S.urun_id = P.id ORDER BY COALESCE(S.dp_".(int)$orderDepotId.",0) $orderDir, $orderByOut $orderDir LIMIT :lim OFFSET :off";
		} else {
			$sql = "SELECT P.*, S.* FROM (".$baseSql.") P LEFT JOIN (".$pivotSql.") S ON S.urun_id = P.id ORDER BY $orderByOut $orderDir LIMIT :lim OFFSET :off";
		}
		$stmt = $this->db->prepare($sql);
		foreach ($params as $k=>$v) {
			if ($k === ':lim' || $k === ':off') { $stmt->bindValue($k, $v, \PDO::PARAM_INT); } else { $stmt->bindValue($k, $v); }
		}
		$stmt->execute();
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function getLastSyncAt(): ?string
	{
		try {
			$ts = $this->db->query("SELECT MAX(created_at) FROM tamsoft_stok_log WHERE type='stok_update'")->fetchColumn();
			return $ts ? (string)$ts : null;
		} catch (\Throwable $e) { return null; }
	}

	public function getLastMasterSyncAt(): ?string
	{
		try {
			$ts = $this->db->query("SELECT MAX(created_at) FROM tamsoft_stok_log WHERE type='product_master_sync'")->fetchColumn();
			return $ts ? (string)$ts : null;
		} catch (\Throwable $e) { return null; }
	}

	public function getActiveDepotsFromDb(): array
	{
		try {
			$stmt = $this->db->query("SELECT depo_id AS id, depo_adi FROM tamsoft_depolar WHERE aktif=1 ORDER BY depo_id ASC");
			return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
		} catch (\Throwable $e) { return []; }
	}

	public function setDepotActive(int $depoId, bool $active): bool
	{
		try {
			$stmt = $this->db->prepare("UPDATE tamsoft_depolar SET aktif=:a WHERE depo_id=:id");
			return $stmt->execute([':a'=>$active?1:0, ':id'=>$depoId]);
		} catch (\Throwable $e) { return false; }
	}

	public function ensureDepotRows(int $depoId): void
	{
		// İlgili depo için tüm ürünler adına en az 0 miktarlı satır oluştur
		$sql = "INSERT INTO tamsoft_depo_stok_ozet(urun_id, depo_id, miktar, fiyat)
			SELECT u.id AS urun_id, :d AS depo_id, 0 AS miktar, NULL AS fiyat
			FROM tamsoft_urunler u
			ON DUPLICATE KEY UPDATE miktar=tamsoft_depo_stok_ozet.miktar";
		$this->db->prepare($sql)->execute([':d'=>$depoId]);
	}

	public function listByPrefix(string $prefix, int $limit = 1000, int $offset = 0): array
	{
		$prefix = strtoupper($prefix);
		if ($prefix === 'IPT') {
			$sql = "SELECT id, ext_urun_id, barkod, urun_adi, birim, kdv, fiyat, miktari, aktif FROM tamsoft_urunler WHERE UPPER(ext_urun_id) LIKE 'IPT%' OR ext_urun_id LIKE 'İPT%' ORDER BY urun_adi ASC LIMIT :lim OFFSET :off";
		} else {
			$sql = "SELECT id, ext_urun_id, barkod, urun_adi, birim, kdv, fiyat, miktari, aktif FROM tamsoft_urunler WHERE UPPER(ext_urun_id) LIKE 'BK%' ORDER BY urun_adi ASC LIMIT :lim OFFSET :off";
		}
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
		$stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function countActiveProductsExcludingPrefixes(): int
	{
		$sql = "SELECT COUNT(*) FROM tamsoft_urunler WHERE aktif=1 AND UPPER(ext_urun_id) NOT LIKE 'IPT%' AND UPPER(ext_urun_id) NOT LIKE 'BK%' AND ext_urun_id NOT LIKE 'İPT%'";
		return (int)$this->db->query($sql)->fetchColumn();
	}

	public function countDepotsActive(): int
	{
		$sql = "SELECT COUNT(*) FROM tamsoft_depolar WHERE aktif=1";
		return (int)$this->db->query($sql)->fetchColumn();
	}

	public function countByPrefix(string $prefix): int
	{
		$prefix = strtoupper($prefix);
		if ($prefix === 'IPT') {
			$stmt = $this->db->query("SELECT COUNT(*) FROM tamsoft_urunler WHERE UPPER(ext_urun_id) LIKE 'IPT%' OR ext_urun_id LIKE 'İPT%'");
			return (int)$stmt->fetchColumn();
		}
		if ($prefix === 'BK') {
			$stmt = $this->db->query("SELECT COUNT(*) FROM tamsoft_urunler WHERE UPPER(ext_urun_id) LIKE 'BK%'");
			return (int)$stmt->fetchColumn();
		}
		return 0;
	}

	public function logEvent(string $type, string $message): void
	{
		try {
			$stmt = $this->db->prepare("INSERT INTO tamsoft_stok_log(type, message, created_at) VALUES(:t,:m,:c)");
			$stmt->execute([':t'=>$type, ':m'=>$message, ':c'=>date('Y-m-d H:i:s')]);
		} catch (\Throwable $e) { /* yut */ }
	}

	public function latestLogs(int $limit = 5): array
	{
		$stmt = $this->db->prepare("SELECT type, message, created_at FROM tamsoft_stok_log ORDER BY created_at DESC LIMIT :lim");
		$stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	// Fiyat güncelleme yardımcıları
	public function findProductIdByExtOrBarcode(?string $extId, ?string $barcode): ?int
	{
		if ($barcode) {
			$stmt = $this->db->prepare("SELECT id FROM tamsoft_urunler WHERE barkod=:b LIMIT 1");
			$stmt->execute([':b'=>$barcode]);
			$id = $stmt->fetchColumn(); if ($id) return (int)$id;
		}
		if ($extId) {
			$stmt = $this->db->prepare("SELECT id FROM tamsoft_urunler WHERE ext_urun_id=:e LIMIT 1");
			$stmt->execute([':e'=>$extId]);
			$id = $stmt->fetchColumn(); if ($id) return (int)$id;
		}
		return null;
	}

	public function updateProductPriceById(int $urunId, ?float $price): void
	{
		$this->db->prepare("UPDATE tamsoft_urunler SET fiyat=:f WHERE id=:id")
			->execute([':f'=>$price, ':id'=>$urunId]);
	}

	public function updateBarcodePrice(?string $barcode, ?float $price): void
	{
		if (!$barcode) return;
		$this->db->prepare("UPDATE tamsoft_urun_barkodlar SET fiyat=:f WHERE barkod=:b")
			->execute([':f'=>$price, ':b'=>$barcode]);
	}

	public function updateAllBarcodesPriceByProductId(int $urunId, ?float $price): void
	{
		$this->db->prepare("UPDATE tamsoft_urun_barkodlar SET fiyat=:f WHERE urun_id=:u")
			->execute([':f'=>$price, ':u'=>$urunId]);
	}

	public function updateDepotSummaryPriceByProduct(int $urunId, ?float $price): void
	{
		$this->db->prepare("UPDATE tamsoft_depo_stok_ozet SET fiyat=:f WHERE urun_id=:u")
			->execute([':f'=>$price, ':u'=>$urunId]);
	}

	public function updatePriceByExtOrBarcode(?string $extId, ?string $barcode, ?float $price): bool
	{
		$urunId = $this->findProductIdByExtOrBarcode($extId, $barcode);
		if (!$urunId) { return false; }
		if ($price === null) { return false; }
		$this->updateProductPriceById($urunId, $price);
		// İlgili ürünün TÜM barkod fiyatlarını güncelle (tek barkodla sınırlı kalma)
		$this->updateAllBarcodesPriceByProductId($urunId, $price);
		$this->updateDepotSummaryPriceByProduct($urunId, $price);
		return true;
	}
}



