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
                platform VARCHAR(50) NULL,
                store_id VARCHAR(100) NULL,
                match_confidence FLOAT NULL,
                match_source VARCHAR(20) NULL,
                manual_override TINYINT(1) NOT NULL DEFAULT 0,
                last_matched_at DATETIME NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                INDEX idx_barkod (barkod),
                INDEX idx_urun_kodu (urun_kodu),
                INDEX idx_platform_store (platform, store_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $this->db->exec($sql);
        } catch (\Throwable $e) {
            error_log('UrunEntegrasyonMap create table error: ' . $e->getMessage());
        }
        // Çoklu eşleştirme için eski benzersiz kısıtları kaldır (varsa)
        try { $this->db->exec("ALTER TABLE {$this->table} DROP INDEX uk_map_urun"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE {$this->table} DROP INDEX uk_map_barkod"); } catch (\Throwable $e) {}
        // Eski tablolara yeni kolonlar
        try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN match_confidence FLOAT NULL"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN match_source VARCHAR(20) NULL"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN manual_override TINYINT(1) NOT NULL DEFAULT 0"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN last_matched_at DATETIME NULL"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN platform VARCHAR(50) NULL"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN store_id VARCHAR(100) NULL"); } catch (\Throwable $e) {}
        try { $this->db->exec("CREATE INDEX idx_platform_store ON {$this->table} (platform, store_id)"); } catch (\Throwable $e) {}
    }

    public function getByKey(?string $urunKodu, ?string $barkod, ?string $platform = null, ?string $storeId = null): ?array
    {
        try {
            $conds = [];
            $params = [];
            if ($urunKodu) { $conds[] = 'urun_kodu = :k'; $params[':k'] = $urunKodu; }
            if ($barkod) { $conds[] = 'barkod = :b'; $params[':b'] = $barkod; }
            if ($platform) { $conds[] = 'platform = :p'; $params[':p'] = $platform; }
            if ($storeId) { $conds[] = 'store_id = :s'; $params[':s'] = $storeId; }
            if (empty($conds)) { return null; }
            $where = implode(' AND ', $conds);
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$where} ORDER BY manual_override DESC, updated_at DESC LIMIT 1");
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) { return $row; }
        } catch (\Throwable $e) {
            error_log('UrunEntegrasyonMap get error: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Aynı Tamsoft ürünü için (platform + opsiyonel store bazında) tüm eşleşmeler
     */
    public function listByUrun(string $urunKodu, ?string $platform = null, ?string $storeId = null): array
    {
        try {
            $conds = ['urun_kodu = :k'];
            $params = [':k' => $urunKodu];
            if ($platform) { $conds[] = 'platform = :p'; $params[':p'] = $platform; }
            if ($storeId) { $conds[] = 'store_id = :s'; $params[':s'] = $storeId; }
            $where = implode(' AND ', $conds);
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$where} ORDER BY manual_override DESC, updated_at DESC");
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function upsert(array $data): bool
    {
        try {
            $now = date('Y-m-d H:i:s');
            $urunKodu = $data['urun_kodu'] ?? null;
            $barkod = $data['barkod'] ?? null;
            $platform = $data['platform'] ?? null;
            $storeId = $data['store_id'] ?? null;
            $trendySku = $data['trendyolgo_sku'] ?? null;
            // Çoklu eşleştirme: aynısından varsa güncelle, yoksa ekle
            $existing = null;
            if ($urunKodu) {
                $conds = ['urun_kodu = :k'];
                $params = [':k' => $urunKodu];
                if ($platform) { $conds[] = 'platform = :p'; $params[':p'] = $platform; }
                if ($storeId) { $conds[] = 'store_id = :s'; $params[':s'] = $storeId; }
                if ($trendySku) { $conds[] = 'trendyolgo_sku = :ts'; $params[':ts'] = $trendySku; }
                if ($barkod) { $conds[] = 'barkod <=> :b'; $params[':b'] = $barkod; }
                $where = implode(' AND ', $conds);
                $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$where} LIMIT 1");
                $stmt->execute($params);
                $existing = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
            }
            $payload = [
                'urun_kodu' => $urunKodu,
                'barkod' => $barkod,
                'trendyolgo_sku' => $data['trendyolgo_sku'] ?? null,
                'getir_code' => $data['getir_code'] ?? null,
                'yemeksepeti_code' => $data['yemeksepeti_code'] ?? null,
                'platform' => $platform,
                'store_id' => $storeId,
                'match_confidence' => isset($data['match_confidence']) ? (float)$data['match_confidence'] : null,
                'match_source' => $data['match_source'] ?? null,
                'manual_override' => !empty($data['manual_override']) ? 1 : 0,
                'last_matched_at' => $data['last_matched_at'] ?? $now,
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




