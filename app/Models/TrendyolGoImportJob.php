<?php

namespace app\Models;

use core\Model;

class TrendyolGoImportJob extends Model
{
    protected $table = 'trendyolgo_import_jobs';

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
                status VARCHAR(20) NOT NULL DEFAULT 'queued',
                total INT NULL,
                processed INT NOT NULL DEFAULT 0,
                preview LONGTEXT NULL,
                error_message TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $this->db->exec($sql);
        } catch (\Throwable $e) { error_log('ImportJob create table error: ' . $e->getMessage()); }
    }

    public function createJob(): int
    {
        $now = date('Y-m-d H:i:s');
        $this->create(['status' => 'queued', 'created_at' => $now, 'updated_at' => $now]);
        return (int)$this->db->lastInsertId();
    }

    public function updateJob(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    public function getJob(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}


