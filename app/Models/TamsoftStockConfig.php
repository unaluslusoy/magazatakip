<?php

namespace app\Models;

use core\Model;

class TamsoftStockConfig extends Model
{
	protected $table = 'tamsoft_stok_ayarlar';

	public function __construct()
	{
		parent::__construct();
		$this->createTableIfNotExists();
	}

	private function createTableIfNotExists(): void
	{
		$sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
			id TINYINT UNSIGNED PRIMARY KEY DEFAULT 1,
			api_url VARCHAR(255) NOT NULL DEFAULT 'http://tamsoftintegration.camlica.com.tr',
			kullanici VARCHAR(120) NULL,
			sifre VARCHAR(120) NULL,
			default_date DATE NULL,
			default_depo_id INT NULL,
			default_only_positive TINYINT(1) NOT NULL DEFAULT 1,
			default_last_barcode_only TINYINT(1) NOT NULL DEFAULT 0,
			default_only_ecommerce TINYINT(1) NOT NULL DEFAULT 0,
			sync_active TINYINT(1) NOT NULL DEFAULT 1,
			sync_by_depot TINYINT(1) NOT NULL DEFAULT 1,
			request_interval_sec INT NULL,
			quiet_enabled TINYINT(1) NOT NULL DEFAULT 1,
			quiet_start TIME NULL,
			quiet_end TIME NULL,
			verbose_stock_log TINYINT(1) NOT NULL DEFAULT 0,
			bulk_stock_summary TINYINT(1) NOT NULL DEFAULT 0,
			-- yeni ayarlar
			master_batch INT NULL,
			price_batch INT NULL,
			price_max_pages INT NULL,
			price_max_seconds INT NULL,
			max_seconds_per_depot INT NULL,
			max_pages_per_depot INT NULL,
			max_parallel_depots TINYINT NOT NULL DEFAULT 3,
			throttle_ms INT NOT NULL DEFAULT 75,
			max_retries TINYINT NOT NULL DEFAULT 3,
			breaker_fail_threshold TINYINT NOT NULL DEFAULT 5,
			breaker_cooldown_sec INT NOT NULL DEFAULT 300,
			token_value TEXT NULL,
			token_expires_at DATETIME NULL,
			token_type VARCHAR(50) NULL,
			created_at DATETIME NULL,
			updated_at DATETIME NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
		try { $this->db->exec($sql); } catch (\Throwable $e) { error_log($e->getMessage()); }

		// Eski tablolar için eksik kolonları ekle
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN default_last_barcode_only TINYINT(1) NOT NULL DEFAULT 0"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN default_only_ecommerce TINYINT(1) NOT NULL DEFAULT 0"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN token_value TEXT NULL"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN token_expires_at DATETIME NULL"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN token_type VARCHAR(50) NULL"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN sync_active TINYINT(1) NOT NULL DEFAULT 1"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN sync_by_depot TINYINT(1) NOT NULL DEFAULT 1"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN request_interval_sec INT NULL"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN throttle_ms INT NOT NULL DEFAULT 75"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN max_retries TINYINT NOT NULL DEFAULT 3"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN breaker_fail_threshold TINYINT NOT NULL DEFAULT 5"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN breaker_cooldown_sec INT NOT NULL DEFAULT 300"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN quiet_enabled TINYINT(1) NOT NULL DEFAULT 1"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN quiet_start TIME NULL"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN quiet_end TIME NULL"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN verbose_stock_log TINYINT(1) NOT NULL DEFAULT 0"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN bulk_stock_summary TINYINT(1) NOT NULL DEFAULT 0"); } catch (\Throwable $e) {}
		// yeni kolonlar (varsa hata vermeden geç)
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN master_batch INT NULL"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN price_batch INT NULL"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN price_max_pages INT NULL"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN price_max_seconds INT NULL"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN max_seconds_per_depot INT NULL"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN max_pages_per_depot INT NULL"); } catch (\Throwable $e) {}
		try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN max_parallel_depots TINYINT NOT NULL DEFAULT 3"); } catch (\Throwable $e) {}
	}

	public function getConfig(): array
	{
		$stmt = $this->db->query("SELECT * FROM {$this->table} WHERE id=1");
		$cfg = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
		if (!$cfg) {
			$now = date('Y-m-d H:i:s');
			$sql = "INSERT INTO {$this->table}(id, created_at, updated_at) VALUES (1, :created_at, :updated_at)";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':created_at'=>$now, ':updated_at'=>$now]);
			$cfg = [ 'api_url' => 'http://tamsoftintegration.camlica.com.tr', 'default_only_positive' => 1, 'default_last_barcode_only' => 0, 'default_only_ecommerce' => 0, 'sync_active'=>1, 'sync_by_depot'=>1, 'request_interval_sec'=>900, 'quiet_enabled'=>1, 'quiet_start'=>'22:00:00', 'quiet_end'=>'09:00:00', 'verbose_stock_log'=>0, 'bulk_stock_summary'=>0, 'max_parallel_depots'=>3, 'throttle_ms'=>75, 'max_retries'=>3, 'breaker_fail_threshold'=>5, 'breaker_cooldown_sec'=>300 ];
		}
		return $cfg;
	}

	public function saveConfig(array $data): bool
	{
		$now = date('Y-m-d H:i:s');
		$sql = "UPDATE {$this->table}
			SET api_url=:api_url, kullanici=:kullanici, sifre=:sifre,
			default_date=:default_date, default_depo_id=:default_depo_id,
			default_only_positive=:default_only_positive,
			default_last_barcode_only=:default_last_barcode_only,
			default_only_ecommerce=:default_only_ecommerce,
			sync_active=:sync_active,
			sync_by_depot=:sync_by_depot,
			request_interval_sec=:request_interval_sec,
			quiet_enabled=:quiet_enabled,
			quiet_start=:quiet_start,
			quiet_end=:quiet_end,
			bulk_stock_summary=:bulk_stock_summary,
			master_batch=:master_batch,
			price_batch=:price_batch,
			price_max_pages=:price_max_pages,
			price_max_seconds=:price_max_seconds,
			max_seconds_per_depot=:max_seconds_per_depot,
			max_pages_per_depot=:max_pages_per_depot,
			throttle_ms=:throttle_ms,
			max_retries=:max_retries,
			breaker_fail_threshold=:breaker_fail_threshold,
			breaker_cooldown_sec=:breaker_cooldown_sec,
			updated_at=:updated_at
			WHERE id=1";
		$stmt = $this->db->prepare($sql);
		return $stmt->execute([
			':api_url' => (string)($data['api_url'] ?? 'http://tamsoftintegration.camlica.com.tr'),
			':kullanici' => $data['kullanici'] ?? null,
			':sifre' => $data['sifre'] ?? null,
			':default_date' => $data['default_date'] ?? null,
			':default_depo_id' => isset($data['default_depo_id']) && $data['default_depo_id'] !== '' ? (int)$data['default_depo_id'] : null,
			':default_only_positive' => !empty($data['default_only_positive']) ? 1 : 0,
			':default_last_barcode_only' => !empty($data['default_last_barcode_only']) ? 1 : 0,
			':default_only_ecommerce' => !empty($data['default_only_ecommerce']) ? 1 : 0,
			':sync_active' => !empty($data['sync_active']) ? 1 : 0,
			':sync_by_depot' => !empty($data['sync_by_depot']) ? 1 : 0,
			':request_interval_sec' => isset($data['request_interval_sec']) && $data['request_interval_sec'] !== '' ? (int)$data['request_interval_sec'] : null,
			':quiet_enabled' => isset($data['quiet_enabled']) ? (int)(!empty($data['quiet_enabled'])) : 1,
			':quiet_start' => $data['quiet_start'] ?? '22:30:00',
			':quiet_end' => $data['quiet_end'] ?? '09:00:00',
			':bulk_stock_summary' => isset($data['bulk_stock_summary']) ? (int)(!empty($data['bulk_stock_summary'])) : 0,
			':throttle_ms' => isset($data['throttle_ms']) && $data['throttle_ms'] !== '' ? (int)$data['throttle_ms'] : 75,
			':max_retries' => isset($data['max_retries']) && $data['max_retries'] !== '' ? (int)$data['max_retries'] : 3,
			':breaker_fail_threshold' => isset($data['breaker_fail_threshold']) && $data['breaker_fail_threshold'] !== '' ? (int)$data['breaker_fail_threshold'] : 5,
			':breaker_cooldown_sec' => isset($data['breaker_cooldown_sec']) && $data['breaker_cooldown_sec'] !== '' ? (int)$data['breaker_cooldown_sec'] : 300,
			':master_batch' => isset($data['master_batch']) && $data['master_batch'] !== '' ? (int)$data['master_batch'] : null,
			':price_batch' => isset($data['price_batch']) && $data['price_batch'] !== '' ? (int)$data['price_batch'] : null,
			':price_max_pages' => isset($data['price_max_pages']) && $data['price_max_pages'] !== '' ? (int)$data['price_max_pages'] : null,
			':price_max_seconds' => isset($data['price_max_seconds']) && $data['price_max_seconds'] !== '' ? (int)$data['price_max_seconds'] : null,
			':max_seconds_per_depot' => isset($data['max_seconds_per_depot']) && $data['max_seconds_per_depot'] !== '' ? (int)$data['max_seconds_per_depot'] : null,
			':max_pages_per_depot' => isset($data['max_pages_per_depot']) && $data['max_pages_per_depot'] !== '' ? (int)$data['max_pages_per_depot'] : null,
			':updated_at' => $now,
		]);
	}

	public function saveToken(string $accessToken, ?int $expiresInSeconds, ?string $tokenType = null): void
	{
		$now = time();
		$expiresAt = $expiresInSeconds !== null ? date('Y-m-d H:i:s', $now + max(0, $expiresInSeconds - 60)) : null; // 60 sn tampon
		$sql = "UPDATE {$this->table}
			SET token_value=:tv, token_expires_at=:te, token_type=:tt, updated_at=:ua
			WHERE id=1";
		$stmt = $this->db->prepare($sql);
		$stmt->execute([
			':tv' => $accessToken,
			':te' => $expiresAt,
			':tt' => $tokenType,
			':ua' => date('Y-m-d H:i:s'),
		]);
	}
}



