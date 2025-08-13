<?php

namespace app\Models;

use core\Model;

class TamsoftAyarlar extends Model
{
    protected $table = 'tamsoft_ayarlar';

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
                base_url VARCHAR(255) NULL,
                alt_base_url VARCHAR(255) NULL,
                username VARCHAR(150) NULL,
                password VARCHAR(255) NULL,
                enabled TINYINT(1) NOT NULL DEFAULT 0,
                schedule_minutes INT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $this->db->exec($sql);
        } catch (\Throwable $e) {
            error_log('TamsoftAyarlar create table error: ' . $e->getMessage());
        }
    }

    public function getAyarlar(): ?array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 1");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('TamsoftAyarlar get error: ' . $e->getMessage());
            return null;
        }
    }

    public function updateAyarlar(array $data): bool
    {
        try {
            $existing = $this->getAyarlar();
            $now = date('Y-m-d H:i:s');
            $payload = [
                'base_url' => $data['base_url'] ?? null,
                'alt_base_url' => $data['alt_base_url'] ?? null,
                'username' => $data['username'] ?? null,
                'password' => $data['password'] ?? null,
                'enabled' => !empty($data['enabled']) ? 1 : 0,
                'schedule_minutes' => !empty($data['schedule_minutes']) ? (int)$data['schedule_minutes'] : null,
                'updated_at' => $now
            ];
            if ($existing) {
                return $this->update($existing['id'], $payload);
            }
            $payload['created_at'] = $now;
            return $this->create($payload);
        } catch (\Throwable $e) {
            error_log('TamsoftAyarlar update error: ' . $e->getMessage());
            return false;
        }
    }
}


