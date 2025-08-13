<?php

namespace app\Models;

use core\Model;

class TrendyolGoAyarlar extends Model
{
    protected $table = 'trendyolgo_ayarlar';

    public function __construct()
    {
        parent::__construct();
        $this->createTableIfNotExists();
        $this->ensureColumns();
    }

    private function createTableIfNotExists(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                base_url VARCHAR(255) NULL,
                api_key VARCHAR(255) NULL,
                supplier_id VARCHAR(100) NULL,
                webhook_secret VARCHAR(255) NULL,
                enabled TINYINT(1) NOT NULL DEFAULT 0,
                schedule_minutes INT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $this->db->exec($sql);
        } catch (\Throwable $e) {
            error_log('TrendyolGoAyarlar create table error: ' . $e->getMessage());
        }
    }

    private function ensureColumns(): void
    {
        // MySQL'de IF NOT EXISTS desteği sınırlı olduğundan her sütunu ayrı TRY/ALTER ile eklemeye çalışalım
        $columns = [
            'satici_cari_id VARCHAR(50) NULL',
            'entegrasyon_ref_kodu VARCHAR(100) NULL',
            'api_secret VARCHAR(255) NULL',
            'token VARCHAR(512) NULL',
            'default_store_id VARCHAR(100) NULL'
        ];
        foreach ($columns as $col) {
            try {
                $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN {$col}");
            } catch (\Throwable $e) {
                // sütun zaten varsa hata alırız; sessizce geç
            }
        }
    }

    public function getAyarlar(): ?array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 1");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('TrendyolGoAyarlar get error: ' . $e->getMessage());
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
                'api_key' => $data['api_key'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'webhook_secret' => $data['webhook_secret'] ?? null,
                'satici_cari_id' => $data['satici_cari_id'] ?? null,
                'entegrasyon_ref_kodu' => $data['entegrasyon_ref_kodu'] ?? null,
                'api_secret' => $data['api_secret'] ?? null,
                'token' => $data['token'] ?? null,
                'default_store_id' => $data['default_store_id'] ?? ($data['store_id'] ?? null),
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
            error_log('TrendyolGoAyarlar update error: ' . $e->getMessage());
            return false;
        }
    }
}


