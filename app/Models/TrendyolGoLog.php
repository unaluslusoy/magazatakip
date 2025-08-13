<?php

namespace app\Models;

use core\Model;

class TrendyolGoLog extends Model
{
    protected $table = 'trendyolgo_logs';

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
                method VARCHAR(10) NOT NULL,
                url TEXT NOT NULL,
                status INT NULL,
                body_preview LONGTEXT NULL,
                message TEXT NULL,
                created_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $this->db->exec($sql);
        } catch (\Throwable $e) { error_log('TrendyolGoLog create table error: ' . $e->getMessage()); }
    }

    public function add(string $method, string $url, ?int $status, ?string $bodyPreview, ?string $message = null): bool
    {
        try {
            return $this->create([
                'method' => strtoupper($method),
                'url' => $url,
                'status' => $status,
                'body_preview' => $bodyPreview,
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) { error_log('TrendyolGoLog add error: ' . $e->getMessage()); return false; }
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
        try {
            $this->db->exec("TRUNCATE TABLE {$this->table}");
            return true;
        } catch (\Throwable $e) {
            try {
                $this->db->exec("DELETE FROM {$this->table}");
                return true;
            } catch (\Throwable $e2) { return false; }
        }
    }
}


