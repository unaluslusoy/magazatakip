<?php

use app\Models\TamsoftStockRepo;

require_once __DIR__ . '/../vendor/autoload.php';

// Basit migrasyon ve temizlik aracı
// - Kullanılan tablo beyaz listesi dışındaki "tamsoft_%" tablolarını tespit eder (dry-run)
// - İstenirse gerçekten DROP eder (CLI arg: --execute)
// - Önerilen indeks/benzersiz kısıtlar/foreign key'ler/tetikleyiciler ve job tablolarını uygular

function out(string $msg): void { fwrite(STDOUT, $msg . "\n"); }

// Argümanlar
$execute = in_array('--execute', $argv, true);
$skipDrops = in_array('--skip-drops', $argv, true); // sadece DDL uygula, DROP yapma

$repo = new TamsoftStockRepo();
$db = $repo->getDb();

// Mevcut veritabanı adını tespit et
$schema = $db->query('SELECT DATABASE()')->fetchColumn();
if (!$schema) { out('HATA: Aktif veritabanı bulunamadı.'); exit(1); }

out('Aktif veritabanı: ' . $schema);
out('Çalışma modu: ' . ($execute ? 'EXECUTE' : 'DRY-RUN'));

// Kullanılan tabloların beyaz listesi
$whitelist = [
	'tamsoft_stok_ayarlar',
	'tamsoft_urunler',
	'tamsoft_urun_barkodlar',
	'tamsoft_depolar',
	'tamsoft_depo_stok_ozet',
	'tamsoft_stok_log',
	'tamsoft_depo_stok_degisim_log',
	'tamsoft_urunler_stage',
	'tamsoft_urun_barkodlar_stage',
	'urun_entegrasyon_map',
	'job_schedule',
	'job_runs',
	'job_lock',
];

// Bilinen tamsoft_ tablolarını keşfet
$stmt = $db->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema = :s AND table_name LIKE 'tamsoft\_%'");
$stmt->execute([':s' => $schema]);
$allTamsoftTables = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

$toDrop = array_values(array_diff($allTamsoftTables, $whitelist));

if (!empty($toDrop)) {
	out('Kullanılmayan gibi görünen tablolar (beyaz liste dışı):');
	foreach ($toDrop as $t) { out('  - ' . $t); }
	if ($execute && !$skipDrops) {
		out('DROP işlemleri uygulanıyor...');
		foreach ($toDrop as $t) {
			try {
				$db->exec('DROP TABLE IF EXISTS `'.$t.'`');
				out('DROPPED: ' . $t);
			} catch (Throwable $e) {
				out('DROP FAILED: ' . $t . ' => ' . $e->getMessage());
			}
		}
	} else {
		out('Not: DRY-RUN modunda veya --skip-drops ile çalışıyor. DROP uygulanmadı.');
	}
} else {
	out('Beyaz liste dışı tamsoft_ tablosu bulunamadı.');
}

// Yardımcı: güvenli ALTER/CREATE
$apply = function (string $sql, string $desc) use ($db, $execute) {
	if ($execute) {
		try { $db->exec($sql); out('OK: ' . $desc); }
		catch (Throwable $e) { out('WARN: ' . $desc . ' => ' . $e->getMessage()); }
	} else {
		out('PLAN: ' . $desc);
		out('SQL> ' . $sql);
	}
};

out('DDL uygulanıyor (indeksler, benzersiz kısıtlar, FK, tetikleyiciler, job tabloları)...');

// 1) Log indeksleri
$apply("ALTER TABLE tamsoft_stok_log ADD INDEX idx_type_created (type, created_at)", 'tamsoft_stok_log.idx_type_created');

// 2) Stok özet kompozit indeks (depo_id, urun_id)
$apply("ALTER TABLE tamsoft_depo_stok_ozet ADD INDEX idx_du (depo_id, urun_id)", 'tamsoft_depo_stok_ozet.idx_du');

// 3) Entegrasyon map benzersiz kısıtlar (çoklu eşleşmeye izin verilecek; varsa düşür)
$apply("ALTER TABLE urun_entegrasyon_map DROP INDEX uk_map_urun", 'urun_entegrasyon_map.drop_uk_map_urun');
$apply("ALTER TABLE urun_entegrasyon_map DROP INDEX uk_map_barkod", 'urun_entegrasyon_map.drop_uk_map_barkod');

// 4) Yabancı anahtarlar için veri temizlikleri (yumuşak hatalar yutulur)
// Orphan barkodlar
$apply("DELETE b FROM tamsoft_urun_barkodlar b LEFT JOIN tamsoft_urunler u ON u.id=b.urun_id WHERE u.id IS NULL", 'temizlik: orphan barkod');
// Orphan stok özet (urun)
$apply("DELETE s FROM tamsoft_depo_stok_ozet s LEFT JOIN tamsoft_urunler u ON u.id=s.urun_id WHERE u.id IS NULL", 'temizlik: orphan stok(urun)');
// Orphan stok özet (depo)
$apply("DELETE s FROM tamsoft_depo_stok_ozet s LEFT JOIN tamsoft_depolar d ON d.depo_id=s.depo_id WHERE d.depo_id IS NULL", 'temizlik: orphan stok(depo)');
// Orphan değişim log
$apply("DELETE l FROM tamsoft_depo_stok_degisim_log l LEFT JOIN tamsoft_urunler u ON u.id=l.urun_id WHERE u.id IS NULL", 'temizlik: orphan degisim(urun)');
$apply("DELETE l FROM tamsoft_depo_stok_degisim_log l LEFT JOIN tamsoft_depolar d ON d.depo_id=l.depo_id WHERE d.depo_id IS NULL", 'temizlik: orphan degisim(depo)');

// 5) Yabancı anahtarlar (varsa hata verirse uyarı ile geçilir)
$apply("ALTER TABLE tamsoft_urun_barkodlar ADD CONSTRAINT fk_barkod_urun FOREIGN KEY (urun_id) REFERENCES tamsoft_urunler(id) ON DELETE CASCADE", 'FK: barkod->urun');
$apply("ALTER TABLE tamsoft_depo_stok_ozet ADD CONSTRAINT fk_stok_urun FOREIGN KEY (urun_id) REFERENCES tamsoft_urunler(id) ON DELETE CASCADE", 'FK: stok->urun');
$apply("ALTER TABLE tamsoft_depo_stok_ozet ADD CONSTRAINT fk_stok_depo FOREIGN KEY (depo_id) REFERENCES tamsoft_depolar(depo_id) ON DELETE CASCADE", 'FK: stok->depo');
$apply("ALTER TABLE tamsoft_depo_stok_degisim_log ADD INDEX idx_ud (urun_id, depo_id)", 'degisim_log.idx_ud');

// 6) Tetikleyiciler: stok özet değişince ürün toplam miktarı güncellensin
$apply("DROP TRIGGER IF EXISTS trg_stok_ozet_ai", 'DROP TRIGGER trg_stok_ozet_ai');
$apply("DROP TRIGGER IF EXISTS trg_stok_ozet_au", 'DROP TRIGGER trg_stok_ozet_au');
$apply("DROP TRIGGER IF EXISTS trg_stok_ozet_ad", 'DROP TRIGGER trg_stok_ozet_ad');

$triggerBody = <<<SQL
CREATE TRIGGER trg_stok_ozet_ai AFTER INSERT ON tamsoft_depo_stok_ozet
FOR EACH ROW BEGIN
	UPDATE tamsoft_urunler u
	SET u.miktari = (
		SELECT COALESCE(SUM(s.miktar),0) FROM tamsoft_depo_stok_ozet s WHERE s.urun_id = NEW.urun_id
	)
	WHERE u.id = NEW.urun_id;
END
SQL;
$apply($triggerBody, 'TRIGGER trg_stok_ozet_ai');

$triggerBody = <<<SQL
CREATE TRIGGER trg_stok_ozet_au AFTER UPDATE ON tamsoft_depo_stok_ozet
FOR EACH ROW BEGIN
	UPDATE tamsoft_urunler u
	SET u.miktari = (
		SELECT COALESCE(SUM(s.miktar),0) FROM tamsoft_depo_stok_ozet s WHERE s.urun_id = NEW.urun_id
	)
	WHERE u.id = NEW.urun_id;
END
SQL;
$apply($triggerBody, 'TRIGGER trg_stok_ozet_au');

$triggerBody = <<<SQL
CREATE TRIGGER trg_stok_ozet_ad AFTER DELETE ON tamsoft_depo_stok_ozet
FOR EACH ROW BEGIN
	UPDATE tamsoft_urunler u
	SET u.miktari = (
		SELECT COALESCE(SUM(s.miktar),0) FROM tamsoft_depo_stok_ozet s WHERE s.urun_id = OLD.urun_id
	)
	WHERE u.id = OLD.urun_id;
END
SQL;
$apply($triggerBody, 'TRIGGER trg_stok_ozet_ad');

// 7) Job tabloları (panelden cron kontrolü için)
$apply("CREATE TABLE IF NOT EXISTS job_schedule (
	id INT AUTO_INCREMENT PRIMARY KEY,
	job_key VARCHAR(100) NOT NULL UNIQUE,
	description VARCHAR(255) NULL,
	cron_expr VARCHAR(50) NOT NULL,
	enabled TINYINT(1) NOT NULL DEFAULT 1,
	last_run_at DATETIME NULL,
	next_run_at DATETIME NULL,
	created_at DATETIME NULL,
	updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", 'CREATE job_schedule');

$apply("CREATE TABLE IF NOT EXISTS job_runs (
	id BIGINT AUTO_INCREMENT PRIMARY KEY,
	job_key VARCHAR(100) NOT NULL,
	started_at DATETIME NOT NULL,
	finished_at DATETIME NULL,
	status ENUM('success','error','skipped') NOT NULL,
	message VARCHAR(500) NULL,
	INDEX idx_job_time (job_key, started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", 'CREATE job_runs');

$apply("CREATE TABLE IF NOT EXISTS job_lock (
	job_key VARCHAR(100) PRIMARY KEY,
	locked_by VARCHAR(100) NOT NULL,
	locked_at DATETIME NOT NULL,
	expires_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", 'CREATE job_lock');

out('Bitti.');

out('Kullanım:');
out('  php scripts/tamsoft_migrate.php               # DRY-RUN');
out('  php scripts/tamsoft_migrate.php --execute      # UYGULA (DDL + DROP)');
out('  php scripts/tamsoft_migrate.php --execute --skip-drops   # Sadece DDL, DROP yok');


