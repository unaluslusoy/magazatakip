<?php

namespace app\Models;

use core\Model;

class QueueRepo extends Model
{
    protected $table = 'job_queue';

    public function __construct()
    {
        parent::__construct();
        $this->createTables();
    }

    private function createTables(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS job_queue (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            job_type VARCHAR(100) NOT NULL,
            payload JSON NULL,
            status ENUM('pending','reserved','done','failed') NOT NULL DEFAULT 'pending',
            attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
            available_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            reserved_at DATETIME NULL,
            done_at DATETIME NULL,
            worker_id VARCHAR(100) NULL,
            error TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            INDEX idx_status_available (status, available_at),
            INDEX idx_type_status (job_type, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function enqueue(string $type, array $payload = [], ?string $availableAt = null): int
    {
        $stmt = $this->db->prepare("INSERT INTO job_queue(job_type, payload, status, attempts, available_at, created_at) VALUES(:t, :p, 'pending', 0, :a, NOW())");
        $stmt->execute([':t'=>$type, ':p'=>json_encode($payload, JSON_UNESCAPED_UNICODE), ':a'=>$availableAt ?: date('Y-m-d H:i:s')]);
        return (int)$this->db->lastInsertId();
    }

    public function reserveNext(string $workerId): ?array
    {
        // Atomik rezervasyon: pending durumdaki en eski işi rezerve et
        $this->db->beginTransaction();
        try {
            $sel = $this->db->prepare("SELECT id FROM job_queue WHERE status='pending' AND available_at <= NOW() ORDER BY id ASC LIMIT 1 FOR UPDATE");
            $sel->execute();
            $id = $sel->fetchColumn();
            if (!$id) { $this->db->commit(); return null; }
            $upd = $this->db->prepare("UPDATE job_queue SET status='reserved', reserved_at=NOW(), worker_id=:w, updated_at=NOW() WHERE id=:id");
            $upd->execute([':w'=>$workerId, ':id'=>$id]);
            $get = $this->db->prepare("SELECT * FROM job_queue WHERE id=:id");
            $get->execute([':id'=>$id]);
            $row = $get->fetch(\PDO::FETCH_ASSOC) ?: null;
            $this->db->commit();
            return $row ?: null;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return null;
        }
    }

    public function markDone(int $id): void
    {
        $this->db->prepare("UPDATE job_queue SET status='done', done_at=NOW(), updated_at=NOW() WHERE id=:id")->execute([':id'=>$id]);
    }

    public function markFailed(int $id, string $error): void
    {
        $stmt = $this->db->prepare("UPDATE job_queue SET status='failed', error=:e, updated_at=NOW() WHERE id=:id");
        $stmt->execute([':e'=>$error, ':id'=>$id]);
    }
}


