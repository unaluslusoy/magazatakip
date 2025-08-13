<?php

namespace app\Models;

use core\Model;

class TrendyolGoLock extends Model
{
	protected $table = 'trendyolgo_lock';

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
				lock_key VARCHAR(200) NOT NULL UNIQUE,
				locked_until DATETIME NULL,
				owner VARCHAR(200) NULL,
				updated_at DATETIME NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
			$this->db->exec($sql);
		} catch (\Throwable $e) { error_log('TrendyolGoLock create table error: ' . $e->getMessage()); }
	}

	public function acquire(string $key, int $ttlSeconds = 600, string $owner = ''): bool
	{
		try {
			$now = date('Y-m-d H:i:s');
			$until = date('Y-m-d H:i:s', time() + $ttlSeconds);
			// First, try to insert a new lock
			try {
				$ins = $this->db->prepare("INSERT INTO {$this->table} (lock_key, locked_until, owner, updated_at) VALUES (:k, :u, :o, :n)");
				$okIns = $ins->execute([':k'=>$key, ':u'=>$until, ':o'=>$owner, ':n'=>$now]);
				if ($okIns) { return true; }
			} catch (\Throwable $e) { /* ignore duplicate */ }
			// If exists, try to steal only if expired
			$upd = $this->db->prepare("UPDATE {$this->table} SET locked_until = :u, owner = :o, updated_at = :n WHERE lock_key = :k AND (locked_until IS NULL OR locked_until < :n2)");
			$upd->execute([':u'=>$until, ':o'=>$owner, ':n'=>$now, ':k'=>$key, ':n2'=>$now]);
			return $upd->rowCount() > 0;
		} catch (\Throwable $e) { return false; }
	}

	public function release(string $key): void
	{
		try { $stmt = $this->db->prepare("UPDATE {$this->table} SET locked_until = NULL, updated_at = NOW() WHERE lock_key = :k"); $stmt->execute([':k'=>$key]); } catch (\Throwable $e) {}
	}
}


