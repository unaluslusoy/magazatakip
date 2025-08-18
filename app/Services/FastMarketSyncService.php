<?php

namespace app\Services;

use app\Models\StoreDepotMap;
use app\Models\UrunEntegrasyonMap;

class FastMarketSyncService
{
    private \PDO $db;

    public function __construct()
    {
        $repo = new \app\Models\TamsoftStockRepo();
        $ref = new \ReflectionClass($repo);
        $prop = $ref->getParentClass()->getProperty('db');
        $prop->setAccessible(true);
        /** @var \PDO $pdo */
        $pdo = $prop->getValue($repo);
        $this->db = $pdo;
    }

    /**
     * TrendyolGO için tek ürün fiyat+stok hesapla ve gönder
     */
    public function pushTrendyolGoSingle(string $extUrunId, string $storeId): array
    {
        $map = new StoreDepotMap();
        $depoId = $map->getDepotFor('trendyolgo', $storeId);
        if (!$depoId) { return ['success'=>false,'error'=>'store_not_mapped']; }
        // İç ürün bilgisi + fiyat + depo stok
        $stmt = $this->db->prepare("SELECT id, ext_urun_id, barkod, urun_adi, fiyat FROM tamsoft_urunler WHERE ext_urun_id=:e LIMIT 1");
        $stmt->execute([':e'=>$extUrunId]);
        $u = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$u) { return ['success'=>false,'error'=>'product_not_found']; }
        $stockStmt = $this->db->prepare("SELECT miktar FROM tamsoft_depo_stok_ozet WHERE urun_id=:u AND depo_id=:d LIMIT 1");
        $stockStmt->execute([':u'=>(int)$u['id'], ':d'=>$depoId]);
        $stok = (float)($stockStmt->fetchColumn() ?: 0);
        // eşleşme haritası
        $mm = new UrunEntegrasyonMap();
        // Çoklu eşleştirme: mağaza bazlı öncelik, yoksa platform-genel fallback
        $row = $mm->getByKey($extUrunId, $u['barkod'] ?? null, 'trendyolgo', $storeId);
        if (!$row) { $row = $mm->getByKey($extUrunId, $u['barkod'] ?? null, 'trendyolgo', null); }
        if (!$row || empty($row['trendyolgo_sku'])) { return ['success'=>false,'error'=>'not_matched']; }
        $sku = (string)$row['trendyolgo_sku'];
        // Fiyat dönüşümü (kg/lt -> birim)
        $pms = new ProductMatchService();
        $price = $pms->computeUnitPrice(isset($u['fiyat']) ? (float)$u['fiyat'] : null, (string)($u['urun_adi'] ?? ''));
        // Ayar çarpanı uygula: yüzde ve sabit ek
        $tgo = new TrendyolGoService();
        $cfg = $tgo->getConfig();
        if ($price !== null) {
            if (isset($cfg['price_markup_percent']) && $cfg['price_markup_percent'] !== null) {
                $price = $price * (1 + ((float)$cfg['price_markup_percent'] / 100.0));
            }
            if (isset($cfg['price_add_abs']) && $cfg['price_add_abs'] !== null) {
                $price = $price + (float)$cfg['price_add_abs'];
            }
            $price = round($price, 2);
        }
        $resp = $tgo->updateProductPriceStock($sku, $price, (int)$stok, $storeId);
        return $resp;
    }

    /** toplu push (seçilebilir aralık) */
    public function pushTrendyolGoBatch(array $extUrunIdList, string $storeId): array
    {
        $ok = 0; $fail = 0; $last = null; $errors = [];
        foreach ($extUrunIdList as $ext) {
            $r = $this->pushTrendyolGoSingle((string)$ext, $storeId);
            if (!empty($r['success'])) { $ok++; } else { $fail++; $errors[] = [ 'ext'=>$ext, 'err'=>$r['error'] ?? 'unknown' ]; }
            $last = $r;
        }
        return [ 'success'=>true, 'ok'=>$ok, 'fail'=>$fail, 'last'=>$last, 'errors'=>$errors ];
    }
}


