<?php

namespace app\Services;

use app\Models\UrunEntegrasyonMap;

class ProductMatchService
{
    private \PDO $db;

    public function __construct()
    {
        // Basit DB erişimi için herhangi bir model üzerinden DB alalım
        $repo = new \app\Models\TamsoftStockRepo();
        $ref = new \ReflectionClass($repo);
        $prop = $ref->getParentClass()->getProperty('db');
        $prop->setAccessible(true);
        /** @var \PDO $pdo */
        $pdo = $prop->getValue($repo);
        $this->db = $pdo;
    }

    /**
     * Trendyol GO ürünleri ile iç ürünleri otomatik eşleştirir
     * Kural + bulanık mantık: barkod, kod, ad/marka/gramaj uyumu
     */
    public function autoMatchTrendyolGo(int $maxPages = 20, int $perPage = 200, float $threshold = 0.82): array
    {
        $svc = new TrendyolGoService();
        $ext = $svc->getAllProducts($perPage, $maxPages);
        $externalItems = $ext['items'] ?? [];
        // Hızlı index: barkod->item, kod->item listesi, normalleştirilmiş ad sözlüğü
        $barcodeMap = [];
        $codeMap = [];
        $normTitleMap = [];
        foreach ($externalItems as $it) {
            $barcode = trim((string)($it['barcode'] ?? ''));
            if ($barcode !== '') { $barcodeMap[$barcode] = $it; }
            $code = trim((string)($it['code'] ?? ''));
            if ($code !== '') { $codeMap[$code][] = $it; }
            $normTitle = $this->normalizeTitle((string)($it['brand'] ?? '').' '.(string)($it['name'] ?? ''));
            $normTitleMap[] = [ 'norm' => $normTitle, 'raw' => $it ];
        }

        $products = $this->fetchInternalProducts();
        $map = new UrunEntegrasyonMap();
        $auto = 0; $skipped = 0; $suggestions = 0; $checked = 0;

        foreach ($products as $p) {
            $checked++;
            $extCode = (string)$p['ext_urun_id'];
            $barcode = (string)($p['barkod'] ?? '');
            $title = (string)($p['urun_adi'] ?? '');
            $brandGuess = $this->guessBrand($title);
            $qtyGuess = $this->extractQuantity($title);
            // 1) Barkod tam eşleşme
            if ($barcode !== '' && isset($barcodeMap[$barcode])) {
                $best = $barcodeMap[$barcode];
                $conf = 0.99;
                $map->upsert([
                    'urun_kodu' => $extCode,
                    'barkod' => $barcode,
                    'trendyolgo_sku' => $best['code'] ?? null,
                    'match_confidence' => $conf,
                    'match_source' => 'rule',
                    'manual_override' => 0,
                ]);
                $auto++;
                continue;
            }
            // 2) Kod eşleşmesi (ürün kodu ~ code)
            $candidates = [];
            if ($extCode !== '' && isset($codeMap[$extCode])) {
                foreach ($codeMap[$extCode] as $cand) { $candidates[] = $cand; }
            }
            // 3) Ad benzerliği (token overlap + levenshtein oranı + marka ve gramaj çakışması)
            if (empty($candidates)) {
                $normTitle = $this->normalizeTitle($title);
                $best = null; $bestScore = 0.0;
                foreach ($normTitleMap as $row) {
                    $score = $this->similarityScore($normTitle, (string)$row['norm']);
                    // Marka/gramaj bonusu
                    if ($brandGuess && stripos((string)$row['norm'], $brandGuess) !== false) { $score += 0.08; }
                    if ($qtyGuess && stripos((string)$row['norm'], $qtyGuess) !== false) { $score += 0.05; }
                    if ($score > $bestScore) { $bestScore = $score; $best = $row['raw']; }
                }
                if ($best && $bestScore >= $threshold) {
                    $map->upsert([
                        'urun_kodu' => $extCode,
                        'barkod' => $barcode ?: ($best['barcode'] ?? null),
                        'trendyolgo_sku' => $best['code'] ?? null,
                        'match_confidence' => $bestScore,
                        'match_source' => 'rule',
                        'manual_override' => 0,
                    ]);
                    $auto++;
                    continue;
                } else {
                    $suggestions++;
                    continue;
                }
            } else {
                // Koddan gelen adaylar arasında isim skoru ile seçim
                $normTitle = $this->normalizeTitle($title);
                $best = null; $bestScore = 0.0;
                foreach ($candidates as $cand) {
                    $score = $this->similarityScore($normTitle, $this->normalizeTitle((string)($cand['brand'] ?? '').' '.(string)($cand['name'] ?? '')));
                    if ($brandGuess && stripos($this->normalizeTitle((string)($cand['brand'] ?? '')), $brandGuess) !== false) { $score += 0.08; }
                    if ($qtyGuess && stripos($this->normalizeTitle((string)($cand['name'] ?? '')), $qtyGuess) !== false) { $score += 0.05; }
                    if ($score > $bestScore) { $bestScore = $score; $best = $cand; }
                }
                if ($best && $bestScore >= $threshold) {
                    $map->upsert([
                        'urun_kodu' => $extCode,
                        'barkod' => $barcode ?: ($best['barcode'] ?? null),
                        'trendyolgo_sku' => $best['code'] ?? null,
                        'match_confidence' => $bestScore,
                        'match_source' => 'rule',
                        'manual_override' => 0,
                    ]);
                    $auto++;
                    continue;
                } else { $suggestions++; continue; }
            }
            $skipped++;
        }
        return [ 'success' => true, 'checked' => $checked, 'auto' => $auto, 'suggestions' => $suggestions, 'skipped' => $skipped ];
    }

    /**
     * Öneri üretir: skor aralığı [minScore, threshold) olan adayları döndürür
     */
    public function generateSuggestionsTrendyolGo(float $minScore = 0.70, float $threshold = 0.82, int $maxPages = 10, int $perPage = 200, ?string $storeId = null): array
    {
        // 1) Dış ürünleri getir (mağaza bazlı istek öncelikli)
        $svc = new TrendyolGoService();
        $externalItems = [];
        if ($storeId) {
            $page = 1; $collected = 0;
            while ($page <= $maxPages) {
                $res = $svc->getProducts('', $page, $perPage, [ 'filters' => [ 'storeId' => $storeId, 'status' => 'ACTIVE' ] ]);
                $items = $res['items'] ?? [];
                if (empty($items)) break;
                $externalItems = array_merge($externalItems, $items);
                $collected += count($items);
                $total = (int)($res['total'] ?? $collected);
                if ($collected >= $total) break;
                $page++;
            }
        } else {
            $all = $svc->getAllProducts($perPage, $maxPages);
            $externalItems = $all['items'] ?? [];
        }

        // 2) Dış ürün indeksleri
        $extByBarcode = [];
        $extByCode = [];
        $extTitleNorms = [];
        $barcodeCountsExt = [];
        foreach ($externalItems as $row) {
            $bc = trim((string)($row['barcode'] ?? ''));
            $cd = trim((string)($row['code'] ?? ($row['sku'] ?? '')));
            if ($bc !== '') { $extByBarcode[$bc][] = $row; $barcodeCountsExt[$bc] = ($barcodeCountsExt[$bc] ?? 0) + 1; }
            if ($cd !== '') { $extByCode[$cd][] = $row; }
            $extTitleNorms[] = [ 'norm' => $this->normalizeTitle((string)($row['brand'] ?? '').' '.(string)($row['name'] ?? ( $row['title'] ?? ''))), 'raw' => $row ];
        }

        // 3) İç ürünler ve iç barkod sayıları (1-1 kontrolü için)
        $products = $this->fetchInternalProducts();
        $barcodeCountsInt = [];
        foreach ($products as $p) {
            $b = trim((string)($p['barkod'] ?? ''));
            if ($b !== '') { $barcodeCountsInt[$b] = ($barcodeCountsInt[$b] ?? 0) + 1; }
        }

        $suggestions = [];
        foreach ($products as $p) {
            $extCode = (string)($p['ext_urun_id'] ?? '');
            $barcode = trim((string)($p['barkod'] ?? ''));
            $title = (string)($p['urun_adi'] ?? '');

            // Öncelik 1: Barkod 1-1 eşleşme (hem içte hem dışta tekil)
            if ($barcode !== '' && ($barcodeCountsInt[$barcode] ?? 0) === 1 && ($barcodeCountsExt[$barcode] ?? 0) === 1) {
                $cand = $extByBarcode[$barcode][0] ?? null;
                if ($cand) {
                    $suggestions[] = [
                        'ext_urun_id' => $extCode,
                        'barkod' => $barcode,
                        'urun_adi' => $title,
                        'candidate_code' => $cand['code'] ?? ($cand['sku'] ?? null),
                        'candidate_barcode' => $cand['barcode'] ?? null,
                        'candidate_title' => trim(((string)($cand['brand'] ?? '')).' '.((string)($cand['name'] ?? ($cand['title'] ?? '')))),
                        'candidate_brand' => $cand['brand'] ?? null,
                        'score' => 1.00,
                        'reason' => 'barcode_1to1'
                    ];
                    continue;
                }
            }

            // Öncelik 2: Kod (ext_urun_id ↔ code/sku) tekil eşleşme
            if ($extCode !== '' && !empty($extByCode[$extCode]) && count($extByCode[$extCode]) === 1) {
                $cand = $extByCode[$extCode][0];
                $suggestions[] = [
                    'ext_urun_id' => $extCode,
                    'barkod' => $barcode,
                    'urun_adi' => $title,
                    'candidate_code' => $cand['code'] ?? ($cand['sku'] ?? null),
                    'candidate_barcode' => $cand['barcode'] ?? null,
                    'candidate_title' => trim(((string)($cand['brand'] ?? '')).' '.((string)($cand['name'] ?? ($cand['title'] ?? '')))),
                    'candidate_brand' => $cand['brand'] ?? null,
                    'score' => 0.95,
                    'reason' => 'code_unique'
                ];
                continue;
            }

            // Öncelik 3: İsim tabanlı – en az 3 ortak kelime şartı
            $normTitle = $this->normalizeTitle($title);
            $tokensInt = array_values(array_filter(preg_split('~\s+~', $normTitle)));
            $best = null; $bestCommon = 0; $bestScore = 0.0;
            foreach ($extTitleNorms as $row) {
                $tokensExt = array_values(array_filter(preg_split('~\s+~', (string)$row['norm'])));
                $common = count(array_intersect($tokensInt, $tokensExt));
                if ($common >= 3) {
                    // ikincil ölçüt: önce ortak kelime sayısı, sonra yumuşak benzerlik
                    $sim = $this->similarityScore($normTitle, (string)$row['norm']);
                    if ($common > $bestCommon || ($common === $bestCommon && $sim > $bestScore)) {
                        $bestCommon = $common; $bestScore = $sim; $best = $row['raw'];
                    }
                }
            }
            if ($best) {
                $suggestions[] = [
                    'ext_urun_id' => $extCode,
                    'barkod' => $barcode,
                    'urun_adi' => $title,
                    'candidate_code' => $best['code'] ?? ($best['sku'] ?? null),
                    'candidate_barcode' => $best['barcode'] ?? null,
                    'candidate_title' => trim(((string)($best['brand'] ?? '')).' '.((string)($best['name'] ?? ($best['title'] ?? '')))),
                    'candidate_brand' => $best['brand'] ?? null,
                    'score' => round(max($minScore, min(0.89, $bestScore)), 4),
                    'reason' => 'name_tokens>=3'
                ];
            }
        }
        return [ 'success' => true, 'items' => $suggestions, 'count' => count($suggestions) ];
    }

    /**
     * Yalnızca birebir eşleşmeler: barkod 1-1 ve kod (ext_urun_id ↔ code/sku) tekil
     */
    public function generateExactMatchesTrendyolGo(?string $storeId = null, int $maxPages = 10, int $perPage = 200): array
    {
        $svc = new TrendyolGoService();
        $externalItems = [];
        if ($storeId) {
            $page = 1; $collected = 0;
            while ($page <= $maxPages) {
                $res = $svc->getProducts('', $page, $perPage, [ 'filters' => [ 'storeId' => $storeId, 'status' => 'ACTIVE' ] ]);
                $items = $res['items'] ?? [];
                if (empty($items)) break;
                $externalItems = array_merge($externalItems, $items);
                $collected += count($items);
                $total = (int)($res['total'] ?? $collected);
                if ($collected >= $total) break;
                $page++;
            }
        } else {
            $all = $svc->getAllProducts($perPage, $maxPages);
            $externalItems = $all['items'] ?? [];
        }
        // dış indeksler
        $extByBarcode = [];
        $barcodeCountsExt = [];
        $extByCode = [];
        foreach ($externalItems as $row) {
            $bc = trim((string)($row['barcode'] ?? ''));
            if ($bc !== '') { $extByBarcode[$bc][] = $row; $barcodeCountsExt[$bc] = ($barcodeCountsExt[$bc] ?? 0) + 1; }
            $cd = trim((string)($row['code'] ?? ($row['sku'] ?? '')));
            if ($cd !== '') { $extByCode[$cd][] = $row; }
        }
        // iç ürünler ve iç barkod sayıları
        $products = $this->fetchInternalProducts();
        $barcodeCountsInt = [];
        foreach ($products as $p) { $b = trim((string)($p['barkod'] ?? '')); if ($b !== '') { $barcodeCountsInt[$b] = ($barcodeCountsInt[$b] ?? 0) + 1; } }
        $matches = [];
        foreach ($products as $p) {
            $extCode = (string)($p['ext_urun_id'] ?? '');
            $barcode = trim((string)($p['barkod'] ?? ''));
            $title = (string)($p['urun_adi'] ?? '');
            // Barkod 1-1
            if ($barcode !== '' && ($barcodeCountsInt[$barcode] ?? 0) === 1 && ($barcodeCountsExt[$barcode] ?? 0) === 1) {
                $cand = $extByBarcode[$barcode][0] ?? null;
                if ($cand) {
                    $matches[] = [
                        'ext_urun_id' => $extCode,
                        'barkod' => $barcode,
                        'urun_adi' => $title,
                        'candidate_code' => $cand['code'] ?? ($cand['sku'] ?? null),
                        'candidate_barcode' => $cand['barcode'] ?? null,
                        'candidate_title' => trim(((string)($cand['brand'] ?? '')).' '.((string)($cand['name'] ?? ($cand['title'] ?? '')))),
                        'candidate_brand' => $cand['brand'] ?? null,
                        'score' => 1.00,
                        'reason' => 'barcode_1to1'
                    ];
                    continue;
                }
            }
            // Kod tekil
            if ($extCode !== '' && !empty($extByCode[$extCode]) && count($extByCode[$extCode]) === 1) {
                $cand = $extByCode[$extCode][0];
                $matches[] = [
                    'ext_urun_id' => $extCode,
                    'barkod' => $barcode,
                    'urun_adi' => $title,
                    'candidate_code' => $cand['code'] ?? ($cand['sku'] ?? null),
                    'candidate_barcode' => $cand['barcode'] ?? null,
                    'candidate_title' => trim(((string)($cand['brand'] ?? '')).' '.((string)($cand['name'] ?? ($cand['title'] ?? '')))),
                    'candidate_brand' => $cand['brand'] ?? null,
                    'score' => 0.95,
                    'reason' => 'code_unique'
                ];
                continue;
            }
        }
        return [ 'success' => true, 'items' => $matches, 'count' => count($matches) ];
    }

    private function fetchInternalProducts(): array
    {
        $sql = "SELECT id, ext_urun_id, barkod, urun_adi FROM tamsoft_urunler WHERE aktif=1";
        $stmt = $this->db->query($sql);
        return $stmt ? ($stmt->fetchAll(\PDO::FETCH_ASSOC) ?: []) : [];
    }

    private function normalizeTitle(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $tr = [ 'ı'=>'i','İ'=>'i','ö'=>'o','ü'=>'u','ş'=>'s','ç'=>'c','ğ'=>'g' ];
        $s = strtr($s, $tr);
        $s = preg_replace('~[^a-z0-9\s]~u', ' ', $s ?? '');
        $s = preg_replace('~\s+~', ' ', trim($s ?? ''));
        return $s;
    }

    private function guessBrand(string $title): ?string
    {
        $t = $this->normalizeTitle($title);
        $parts = preg_split('~\s+~', $t);
        return isset($parts[0]) ? $parts[0] : null;
    }

    private function extractQuantity(string $title): ?string
    {
        $t = $this->normalizeTitle($title);
        if (preg_match('~(\d+[\.,]?\d*)\s*(kg|g|gr|l|lt|ml|adet|pk|paket)~', $t, $m)) {
            $num = str_replace(',', '.', $m[1]);
            $unit = $m[2];
            // normalize unit to gram or ml
            if ($unit === 'kg') { $num = (float)$num * 1000; $unit = 'g'; }
            if ($unit === 'lt' || $unit === 'l') { $unit = 'ml'; if ((float)$num < 50) { $num = (float)$num * 1000; } }
            return ((string)(int)$num) . $unit;
        }
        return null;
    }

    /**
     * Tamsoft fiyatı kg veya lt bazında ise, ürün gramaj/hacmine göre birim fiyat hesapla
     * - Tamsoft 'fiyat'ı KDV dahil kabul edilir (sistemdeki mantığa göre ayarlanabilir)
     */
    public function computeUnitPrice(?float $tamsoftPrice, string $title): ?float
    {
        if ($tamsoftPrice === null) return null;
        $q = $this->extractQuantity($title); // örn 250g, 1000ml
        if (!$q) return $tamsoftPrice; // gramaj yoksa olduğu gibi
        if (preg_match('~^(\d+)(g|ml)~', $q, $m)) {
            $qty = (int)$m[1]; $unit = $m[2];
            if ($unit === 'g') {
                // kg fiyatından 250g fiyatı: (kg_fiyat / 1000) * 250
                return round(($tamsoftPrice / 1000.0) * max(1, $qty), 2);
            }
            if ($unit === 'ml') {
                // litre fiyatından 250ml: (lt_fiyat / 1000) * ml
                return round(($tamsoftPrice / 1000.0) * max(1, $qty), 2);
            }
        }
        return $tamsoftPrice;
    }

    private function similarityScore(string $a, string $b): float
    {
        if ($a === '' || $b === '') { return 0.0; }
        // token overlap Jaccard
        $ta = array_values(array_unique(array_filter(preg_split('~\s+~', $a))));
        $tb = array_values(array_unique(array_filter(preg_split('~\s+~', $b))));
        $inter = array_intersect($ta, $tb);
        $union = array_unique(array_merge($ta, $tb));
        $jacc = count($union) ? (count($inter) / count($union)) : 0.0;
        // levenshtein ratio
        $lev = $this->levRatio($a, $b);
        return max(0.0, min(1.0, 0.6 * $lev + 0.4 * $jacc));
    }

    private function levRatio(string $a, string $b): float
    {
        $la = mb_strlen($a, 'UTF-8');
        $lb = mb_strlen($b, 'UTF-8');
        if ($la === 0 && $lb === 0) { return 1.0; }
        // Use simple levenshtein on ascii fallback
        $aa = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$a);
        $bb = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$b);
        $dist = levenshtein($aa, $bb);
        $maxLen = max(strlen($aa), strlen($bb));
        if ($maxLen === 0) { return 1.0; }
        return 1.0 - ($dist / $maxLen);
    }
}


