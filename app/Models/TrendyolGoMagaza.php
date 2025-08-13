<?php

namespace app\Models;

use core\Model;

class TrendyolGoMagaza extends Model
{
    protected $table = 'trendyolgo_magazalar';

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
                magaza_adi VARCHAR(200) NOT NULL,
                store_id VARCHAR(100) NOT NULL,
                last_sync DATETIME NULL,
                last_success DATETIME NULL,
                adres TEXT NULL,
                telefon VARCHAR(50) NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                UNIQUE KEY uniq_store_id (store_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $this->db->exec($sql);
        } catch (\Throwable $e) {
            error_log('TrendyolGoMagaza create table error: ' . $e->getMessage());
        }
    }

	public function touchSync(string $storeId, bool $success): void
	{
		try {
			$now = date('Y-m-d H:i:s');
			if ($success) {
				$stmt = $this->db->prepare("UPDATE {$this->table} SET last_sync = :n, last_success = :n WHERE store_id = :sid");
				$stmt->execute([':n'=>$now, ':sid'=>$storeId]);
			} else {
				$stmt = $this->db->prepare("UPDATE {$this->table} SET last_sync = :n WHERE store_id = :sid");
				$stmt->execute([':n'=>$now, ':sid'=>$storeId]);
			}
			if (function_exists('apcu_delete')) { @apcu_delete('tgo_stores_all'); }
		} catch (\Throwable $e) {}
	}

    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY magaza_adi ASC");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('TrendyolGoMagaza getAll error: ' . $e->getMessage());
            return [];
        }
    }

	public function getAllCached(int $ttlSeconds = 300): array
	{
		try {
			if (function_exists('apcu_fetch')) {
				$k = 'tgo_stores_all';
				$val = apcu_fetch($k, $ok);
				if ($ok && is_array($val)) { return $val; }
				$val = $this->getAll();
				apcu_store($k, $val, $ttlSeconds);
				return $val;
			}
			return $this->getAll();
		} catch (\Throwable $e) { return $this->getAll(); }
	}

    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('TrendyolGoMagaza getById error: ' . $e->getMessage());
            return null;
        }
    }

    public function add(array $data): bool
    {
        try {
            $now = date('Y-m-d H:i:s');
            $payload = [
                'magaza_adi' => trim($data['magaza_adi'] ?? ''),
                'store_id' => trim($data['store_id'] ?? ''),
                'adres' => trim($data['adres'] ?? ''),
                'telefon' => trim($data['telefon'] ?? ''),
                'created_at' => $now,
                'updated_at' => $now
            ];
            if ($payload['magaza_adi'] === '' || $payload['store_id'] === '') { return false; }
            return $this->create($payload);
        } catch (\Throwable $e) {
            error_log('TrendyolGoMagaza add error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateById(int $id, array $data): bool
    {
        try {
            $payload = [
                'magaza_adi' => trim($data['magaza_adi'] ?? ''),
                'store_id' => trim($data['store_id'] ?? ''),
                'adres' => trim($data['adres'] ?? ''),
                'telefon' => trim($data['telefon'] ?? ''),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            if ($payload['magaza_adi'] === '' || $payload['store_id'] === '') { return false; }
            return $this->update($id, $payload);
        } catch (\Throwable $e) {
            error_log('TrendyolGoMagaza updateById error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteById(int $id): bool
    {
        try {
            return $this->delete($id);
        } catch (\Throwable $e) {
            error_log('TrendyolGoMagaza deleteById error: ' . $e->getMessage());
            return false;
        }
    }
}


