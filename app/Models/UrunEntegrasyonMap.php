<?php

namespace app\Models;

use core\Model;

class UrunEntegrasyonMap extends Model
{
    protected $table = 'urun_entegrasyon_map';

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
                urun_kodu VARCHAR(150) NULL,
                barkod VARCHAR(150) NULL,
                trendyolgo_sku VARCHAR(190) NULL,
                getir_code VARCHAR(190) NULL,
                yemeksepeti_code VARCHAR(190) NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                INDEX idx_barkod (barkod),
                INDEX idx_urun_kodu (urun_kodu)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $this->db->exec($sql);
        } catch (\Throwable $e) {
            error_log('UrunEntegrasyonMap create table error: ' . $e->getMessage());
        }
    }

    public function getByKey(?string $urunKodu, ?string $barkod): ?array
    {
        try {
            if ($urunKodu) {
                $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE urun_kodu = :k LIMIT 1");
                $stmt->execute([':k' => $urunKodu]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row) { return $row; }
            }
            if ($barkod) {
                $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE barkod = :b LIMIT 1");
                $stmt->execute([':b' => $barkod]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row) { return $row; }
            }
        } catch (\Throwable $e) {
            error_log('UrunEntegrasyonMap get error: ' . $e->getMessage());
        }
        return null;
    }

    public function upsert(array $data): bool
    {
        try {
            $now = date('Y-m-d H:i:s');
            $urunKodu = $data['urun_kodu'] ?? null;
            $barkod = $data['barkod'] ?? null;
            $existing = $this->getByKey($urunKodu, $barkod);
            $payload = [
                'urun_kodu' => $urunKodu,
                'barkod' => $barkod,
                'trendyolgo_sku' => $data['trendyolgo_sku'] ?? null,
                'getir_code' => $data['getir_code'] ?? null,
                'yemeksepeti_code' => $data['yemeksepeti_code'] ?? null,
                'updated_at' => $now
            ];
            if ($existing) {
                return $this->update((int)$existing['id'], $payload);
            }
            $payload['created_at'] = $now;
            return $this->create($payload);
        } catch (\Throwable $e) {
            error_log('UrunEntegrasyonMap upsert error: ' . $e->getMessage());
            return false;
        }
    }
}




