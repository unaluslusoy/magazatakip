<?php

namespace app\Models;

use core\Model;

class TrendyolGoRate extends Model
{
	protected $table = 'trendyolgo_rate';

	public function __construct()
	{
		parent::__construct();
		$this->createTableIfNotExists();
	}

	private function createTableIfNotExists(): void
	{
		try {
			$sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
				id INT AUTO_INCREMENT PRIMARY KEY,
				endpoint_key VARCHAR(255) NOT NULL,
				window_start INT NOT NULL,
				cnt INT NOT NULL DEFAULT 0,
				updated_at DATETIME NULL,
				UNIQUE KEY uniq_endpoint_window (endpoint_key, window_start)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
			$this->db->exec($sql);
		} catch (\Throwable $e) {
			error_log('TrendyolGoRate create table error: ' . $e->getMessage());
		}
	}

	/**
	 * Bloklayarak slot alır; 10 sn pencerede 50 isteği aşmaz.
	 */
	public function acquireSlot(string $endpointKey, int $budget = 50): void
	{
		$endpointKey = substr($endpointKey, 0, 255);
		while (true) {
			$now = time();
			$win = (int)floor($now / 10) * 10;
			try {
				// Önce yeni pencere için insert dene
				$stmt = $this->db->prepare("INSERT INTO {$this->table} (endpoint_key, window_start, cnt, updated_at) VALUES (:k, :w, 1, NOW()) ON DUPLICATE KEY UPDATE cnt = IF(cnt < :b, cnt+1, cnt), updated_at = NOW()");
				$stmt->bindValue(':k', $endpointKey);
				$stmt->bindValue(':w', $win, \PDO::PARAM_INT);
				$stmt->bindValue(':b', $budget, \PDO::PARAM_INT);
				$stmt->execute();
				// Kontrol et
				$chk = $this->db->prepare("SELECT cnt FROM {$this->table} WHERE endpoint_key = :k AND window_start = :w");
				$chk->execute([':k' => $endpointKey, ':w' => $win]);
				$cnt = (int)($chk->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);
				if ($cnt <= $budget) { return; }
			} catch (\Throwable $e) {
				// Hata durumunda kısa bekle ve yinele
				usleep(150000);
			}
			$remain = ($win + 10) - time();
			if ($remain > 0) { usleep(($remain * 1000 + 100) * 1000); } else { usleep(120000); }
		}
	}
}



