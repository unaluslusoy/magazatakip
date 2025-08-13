<?php

namespace app\Models;

use core\Model;

class GetirLog extends Model
{
    protected $table = 'getir_logs';

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
                type VARCHAR(50) NULL,
                direction VARCHAR(10) NULL,
                method VARCHAR(10) NULL,
                url TEXT NULL,
                status INT NULL,
                body_preview LONGTEXT NULL,
                message TEXT NULL,
                created_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $this->db->exec($sql);
        } catch (\Throwable $e) { error_log('GetirLog create table error: ' . $e->getMessage()); }
    }

    public function add(array $data): bool
    {
        try {
            $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
            return $this->create($data);
        } catch (\Throwable $e) { return false; }
    }

    public function latest(int $limit = 100): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT :lim");
            $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { return []; }
    }

    public function clear(): bool
    {
        try { $this->db->exec("TRUNCATE TABLE {$this->table}"); return true; }
        catch (\Throwable $e) { try { $this->db->exec("DELETE FROM {$this->table}"); return true; } catch (\Throwable $e2) { return false; } }
    }
}


