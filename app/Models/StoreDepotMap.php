<?php

namespace app\Models;

use core\Model;

class StoreDepotMap extends Model
{
    protected $table = 'fast_market_store_map';

    public function __construct()
    {
        parent::__construct();
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            platform VARCHAR(20) NOT NULL,
            store_id VARCHAR(64) NOT NULL,
            depo_id INT NOT NULL,
            enabled TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            UNIQUE KEY uk_platform_store (platform, store_id),
            KEY idx_depo (depo_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        try { $this->db->exec($sql); } catch (\Throwable $e) { /* ignore */ }
    }

    public function getDepotFor(string $platform, string $storeId): ?int
    {
        $stmt = $this->db->prepare("SELECT depo_id FROM {$this->table} WHERE platform=:p AND store_id=:s AND enabled=1 LIMIT 1");
        $stmt->execute([':p'=>$platform, ':s'=>$storeId]);
        $val = $stmt->fetchColumn();
        return $val ? (int)$val : null;
    }

    public function setMapping(string $platform, string $storeId, int $depoId, bool $enabled = true): bool
    {
        $now = date('Y-m-d H:i:s');
        $sql = "INSERT INTO {$this->table}(platform, store_id, depo_id, enabled, created_at, updated_at)
                VALUES(:p,:s,:d,:e,:c,:u)
                ON DUPLICATE KEY UPDATE depo_id=VALUES(depo_id), enabled=VALUES(enabled), updated_at=VALUES(updated_at)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':p'=>$platform, ':s'=>$storeId, ':d'=>$depoId, ':e'=>$enabled?1:0, ':c'=>$now, ':u'=>$now]);
    }

    public function listMappings(string $platform): array
    {
        $stmt = $this->db->prepare("SELECT platform, store_id, depo_id, enabled, created_at, updated_at FROM {$this->table} WHERE platform=:p ORDER BY store_id ASC");
        $stmt->execute([':p'=>$platform]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}


