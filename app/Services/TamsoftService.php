<?php

namespace app\Services;

/**
 * Tamsoft entegrasyon servisi – E-ticaret stok listesi
 * Kaynak: PALMIYE_API_Dokumantasyon_Detayli.md
 */
class TamsoftService
{
    private const DEFAULT_BASE_URL = 'http://tamsoftintegration.camlica.com.tr';
    private const ALT_BASE_URL = 'http://185.124.86.45:8899';

    /**
     * .env üzerinden ayarları oku
     */
    public function getConfig(): array
    {
        // 1) DB ayarları öncelikli
        try {
            $ayarlarModel = new \app\Models\TamsoftAyarlar();
            $dbCfg = $ayarlarModel->getAyarlar();
        } catch (\Throwable $e) { $dbCfg = null; }

        $baseUrl = $dbCfg['base_url'] ?? (getenv('PALMIYE_BASE_URL') ?: self::DEFAULT_BASE_URL);
        $username = $dbCfg['username'] ?? (getenv('PALMIYE_API_USER') ?: '');
        $password = $dbCfg['password'] ?? (getenv('PALMIYE_API_PASS') ?: '');
        $altBase = $dbCfg['alt_base_url'] ?? (getenv('PALMIYE_ALT_BASE_URL') ?: self::ALT_BASE_URL);
        $enabledFlag = isset($dbCfg['enabled']) ? (bool)$dbCfg['enabled'] : null;

        // Normalize base URLs (strip any /token or /api path parts)
        $baseUrl = $this->normalizeBaseUrl((string)$baseUrl);
        $altBase = $this->normalizeBaseUrl((string)$altBase);

        return [
            'base_url' => rtrim($baseUrl, '/'),
            'alt_base_url' => rtrim($altBase, '/'),
            'username' => (string)$username,
            'password' => (string)$password,
            'enabled' => $enabledFlag !== null ? $enabledFlag : (!empty($baseUrl) && !empty($username) && !empty($password))
        ];
    }

    /**
     * Sadece origin döndür (scheme://host[:port]); '/token' veya '/api/..' ekleri varsa temizler
     */
    private function normalizeBaseUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') { return ''; }
        // Eğer tamamen domain değilse, parse edip origin'i oluştur
        $parts = parse_url($url);
        if (!empty($parts['scheme']) && !empty($parts['host'])) {
            $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? (':' . $parts['port']) : '');
            return $origin;
        }
        // Fallback: trailing '/token' veya '/api' kırp
        $url = preg_replace('#/token/?$#i', '', $url);
        $url = preg_replace('#/api/.*$#i', '', $url);
        return $url;
    }

    /**
     * Admin ekranı için birleştirilmiş E-Stok grid verisi döndürür
     * - Depo listesini çeker
     * - Her depo için EticaretStokListesi çağırır ve ürünleri depolara göre birleştirir
     * - Varsayılanlar: tarih=1900-01-01, miktarsifirdanbuyuk=true
     */
    public function getEStokGrid(
        string $tarih = '1900-01-01',
        bool $onlyPositive = true,
        bool $useStokEndpoint = false,
        bool $lastBarcodeOnly = false,
        bool $onlyEcommerce = false,
        ?int $selectedDepotId = null
    ): array
    {
        $cfg = $this->getConfig();
        if (!$cfg['enabled']) {
            return ['depots' => [], 'items' => []];
        }

        $token = $this->getToken($cfg);
        if (!$token) {
            return ['depots' => [], 'items' => []];
        }

        $depots = $this->getDepoListesi($cfg, $token);
        if (empty($depots)) {
            return ['depots' => [], 'items' => []];
        }

        // Depo isimleri listesi
        $depotNames = array_map(function ($d) { return $d['Adi'] ?? ($d['Kod'] ?? (string)($d['Depoid'] ?? 'Depo')); }, $depots);

        // Ürünleri UrunKodu bazında birleştir
        $itemsByCode = [];
        foreach ($depots as $depo) {
            $depoId = (int)($depo['Depoid'] ?? 0);
            $depoName = $depo['Adi'] ?? ($depo['Kod'] ?? (string)$depoId);
            if ($depoId <= 0) { continue; }
            if ($selectedDepotId !== null && $depoId !== $selectedDepotId) { continue; }

            if ($useStokEndpoint) {
                $stoklar = $this->getStokListesi($cfg, $token, $depoId, $tarih, $onlyPositive, $lastBarcodeOnly, $onlyEcommerce);
            } else {
                $stoklar = $this->getEticaretStokListesi($cfg, $token, $depoId, $tarih, $onlyPositive);
            }
            if (!is_array($stoklar)) { continue; }

            foreach ($stoklar as $row) {
                $urunKodu = trim((string)($row['UrunKodu'] ?? ''));
                $urunAdi = trim((string)($row['UrunAdi'] ?? ''));
                if ($urunKodu === '' && $urunAdi === '') { continue; }

                $barkod = null;
                if (!empty($row['Barkodlar']) && is_array($row['Barkodlar'])) {
                    $first = $row['Barkodlar'][0];
                    $barkod = $first['Barkodu'] ?? null;
                }

                $key = $urunKodu !== '' ? $urunKodu : ($urunAdi . '|' . ($barkod ?? ''));
                if (!isset($itemsByCode[$key])) {
                    $itemsByCode[$key] = [
                        'urun_adi' => $urunAdi,
                        'urun_kodu' => $urunKodu,
                        'barkod' => $barkod,
                        'kdv' => $row['KDVOrani'] ?? null,
                        'alis_fiyati' => null, // e-stok listesinde bulunmuyor
                        'satis_fiyati' => $row['IndirimliTutar'] ?? ($row['Tutar'] ?? null),
                        'stoklar' => []
                    ];
                }
                // Depo bazlı miktar: doc'ta Envanter toplam olabilir; e-stok için o depoya göre döner
                $miktar = (int)($row['Envanter'] ?? 0);
                $itemsByCode[$key]['stoklar'][$depoName] = $miktar;
            }
        }

        // Tüm depolar için eksik stok anahtarlarını 0 ile doldur
        $normalized = [];
        foreach ($itemsByCode as $item) {
            foreach ($depotNames as $name) {
                if (!isset($item['stoklar'][$name])) {
                    $item['stoklar'][$name] = 0;
                }
            }
            // Depo kolon sırasını sabitle
            $item['stoklar'] = $this->sortStocksByDepotOrder($item['stoklar'], $depotNames);
            $normalized[] = $item;
        }

        // Eğer belirli depo seçildiyse, depots yalnızca o deponun adıyla sınırlansın
        $visibleDepotNames = $depotNames;
        if ($selectedDepotId !== null) {
            $match = null;
            foreach ($depots as $d) {
                if ((int)($d['Depoid'] ?? 0) === $selectedDepotId) {
                    $match = $d['Adi'] ?? ($d['Kod'] ?? (string)$selectedDepotId);
                    break;
                }
            }
            $visibleDepotNames = $match !== null ? [$match] : $depotNames;
        }

        return [
            'depots' => $visibleDepotNames,
            'items' => $normalized
        ];
    }

    /** Token alma */
    private function getToken(array $cfg): ?string
    {
        // Basit session cache
        if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
        $now = time();
        if (!empty($_SESSION['palmiye_token']) && !empty($_SESSION['palmiye_token_exp']) && $_SESSION['palmiye_token_exp'] > $now + 60) {
            return $_SESSION['palmiye_token'];
        }

        $url = $cfg['base_url'] . '/token';
        $body = http_build_query([
            'grant_type' => 'password',
            'username' => $cfg['username'],
            'password' => $cfg['password']
        ], '', '&', PHP_QUERY_RFC3986);
        $res = $this->curlRequest('POST', $url, ['Content-Type: application/x-www-form-urlencoded; charset=UTF-8'], $body);
        $_SESSION['palmiye_token_last'] = [ 'url' => $url, 'status' => $res['status'] ?? null, 'body_preview' => isset($res['body']) ? substr($res['body'], 0, 300) : null ];
        if (!$res || $res['status'] < 200 || $res['status'] >= 300) {
            // Alternatif base ile dener
            if (!empty($cfg['alt_base_url'])) {
                $url = $cfg['alt_base_url'] . '/token';
                $res = $this->curlRequest('POST', $url, ['Content-Type: application/x-www-form-urlencoded'], $body);
            } else {
                $res = null;
            }
            $_SESSION['palmiye_token_last'] = [ 'url' => $url, 'status' => $res['status'] ?? null, 'body_preview' => isset($res['body']) ? substr($res['body'], 0, 300) : null ];
        }
        if ($res && $res['status'] >= 200 && $res['status'] < 300) {
            $json = json_decode($res['body'] ?? 'null', true);
            $token = $json['access_token'] ?? null;
            $expires = (int)($json['expires_in'] ?? 3600);
            if ($token) {
                $_SESSION['palmiye_token'] = $token;
                $_SESSION['palmiye_token_exp'] = $now + max(300, $expires - 60);
                return $token;
            }
        }
        return null;
    }

    /** Test amaçlı token bilgisi (masked) */
    public function testToken(): array
    {
        $cfg = $this->getConfig();
        if (!$cfg['enabled']) { return ['success' => false, 'error' => 'config_missing']; }
        $token = $this->getToken($cfg);
        if (!$token) { return ['success' => false, 'error' => 'token_failed']; }
        $preview = strlen($token) > 12 ? substr($token, 0, 6) . '...' . substr($token, -4) : $token;
        $exp = $_SESSION['palmiye_token_exp'] ?? null;
        $last = $_SESSION['palmiye_token_last'] ?? null;
        return [
            'success' => true,
            'token_preview' => $preview,
            'expires_at' => $exp ? date('Y-m-d H:i:s', (int)$exp) : null
        ];
    }

    /** DepoListesi */
    private function getDepoListesi(array $cfg, string $token): array
    {
        $headers = ['Authorization: Bearer ' . $token];
        $url = $cfg['base_url'] . '/api/Integration/DepoListesi';
        $res = $this->curlRequest('GET', $url, $headers);
        $_SESSION['palmiye_depo_last'] = [ 'url' => $url, 'status' => $res['status'] ?? null, 'body_preview' => isset($res['body']) ? substr($res['body'], 0, 300) : null ];
        if (!$res || $res['status'] !== 200) {
            $url = $cfg['alt_base_url'] . '/api/Integration/DepoListesi';
            $res = $this->curlRequest('GET', $url, $headers);
            $_SESSION['palmiye_depo_last'] = [ 'url' => $url, 'status' => $res['status'] ?? null, 'body_preview' => isset($res['body']) ? substr($res['body'], 0, 300) : null ];
        }
        if ($res && $res['status'] === 200) {
            $json = json_decode($res['body'] ?? '[]', true);
            return is_array($json) ? $json : [];
        }
        return [];
    }

    /** Test için depolar */
    public function getDepolar(): array
    {
        $cfg = $this->getConfig();
        if (!$cfg['enabled']) { return []; }
        $token = $this->getToken($cfg);
        if (!$token) { return []; }
        return $this->getDepoListesi($cfg, $token);
    }

    /** EticaretStokListesi */
    private function getEticaretStokListesi(array $cfg, string $token, int $depoId, string $tarih, bool $onlyPositive): array
    {
        $headers = ['Authorization: Bearer ' . $token];
        // İlk deneme: küçük harf true/false
        $qsLower = http_build_query([
            'tarih' => $tarih,
            'depoid' => $depoId,
            'miktarsifirdanbuyukstoklarlistelensin' => $onlyPositive ? 'true' : 'false'
        ], '', '&', PHP_QUERY_RFC3986);
        $url = $cfg['base_url'] . '/api/Integration/EticaretStokListesi?' . $qsLower;
        $res = $this->curlRequest('GET', $url, $headers);
        $_SESSION['palmiye_estok_last'] = [ 'url' => $url, 'status' => $res['status'] ?? null, 'body_preview' => isset($res['body']) ? substr($res['body'], 0, 300) : null ];
        // İkinci deneme: Title-case True/False
        if (!$res || $res['status'] !== 200) {
            $qsTitle = http_build_query([
                'tarih' => $tarih,
                'depoid' => $depoId,
                'miktarsifirdanbuyukstoklarlistelensin' => $onlyPositive ? 'True' : 'False'
            ], '', '&', PHP_QUERY_RFC3986);
            $url = $cfg['base_url'] . '/api/Integration/EticaretStokListesi?' . $qsTitle;
            $res = $this->curlRequest('GET', $url, $headers);
            $_SESSION['palmiye_estok_last'] = [ 'url' => $url, 'status' => $res['status'] ?? null, 'body_preview' => isset($res['body']) ? substr($res['body'], 0, 300) : null ];
        }
        if (!$res || $res['status'] !== 200) {
            if (!empty($cfg['alt_base_url'])) {
                $url = $cfg['alt_base_url'] . '/api/Integration/EticaretStokListesi?' . ($qsTitle ?? $qsLower);
                $res = $this->curlRequest('GET', $url, $headers);
                $_SESSION['palmiye_estok_last'] = [ 'url' => $url, 'status' => $res['status'] ?? null, 'body_preview' => isset($res['body']) ? substr($res['body'], 0, 300) : null ];
            }
        }
        if ($res && $res['status'] === 200) {
            $json = json_decode($res['body'] ?? '[]', true);
            $arr = is_array($json) ? $json : [];
            // Eğer boş geldiyse StokListesi ile e-ticaret filtresine düş
            if (!empty($arr)) { return $arr; }
        }
        // Fallback: StokListesi ile sadece e-ticaret ürünlerini çekmeyi dene
        return $this->getStokListesi($cfg, $token, $depoId, $tarih, $onlyPositive, false, true);
    }

    /** StokListesi (genel) */
    private function getStokListesi(
        array $cfg,
        string $token,
        int $depoId,
        string $tarih,
        bool $onlyPositive,
        bool $lastBarcodeOnly,
        bool $onlyEcommerce
    ): array {
        $headers = ['Authorization: Bearer ' . $token];
        $lowerParams = [
            'tarih' => $tarih,
            'depoid' => $depoId,
            'miktarsifirdanbuyukstoklarlistelensin' => $onlyPositive ? 'true' : 'false',
            'urununsonbarkodulistelensin' => $lastBarcodeOnly ? 'true' : 'false',
            'sadeceeticaretstoklarigetir' => $onlyEcommerce ? 'true' : 'false'
        ];
        $qs = http_build_query($lowerParams, '', '&', PHP_QUERY_RFC3986);
        $url = $cfg['base_url'] . '/api/Integration/StokListesi?' . $qs;
        $res = $this->curlRequest('GET', $url, $headers);
        $_SESSION['palmiye_stok_last'] = [ 'url' => $url, 'status' => $res['status'] ?? null, 'body_preview' => isset($res['body']) ? substr($res['body'], 0, 300) : null ];
        if (!$res || $res['status'] !== 200) {
            // Title-case denemesi
            $titleParams = $lowerParams;
            foreach ($titleParams as $k => $v) {
                if ($v === 'true') { $titleParams[$k] = 'True'; }
                if ($v === 'false') { $titleParams[$k] = 'False'; }
            }
            $qsTitle = http_build_query($titleParams, '', '&', PHP_QUERY_RFC3986);
            $url = $cfg['base_url'] . '/api/Integration/StokListesi?' . $qsTitle;
            $res = $this->curlRequest('GET', $url, $headers);
            $_SESSION['palmiye_stok_last'] = [ 'url' => $url, 'status' => $res['status'] ?? null, 'body_preview' => isset($res['body']) ? substr($res['body'], 0, 300) : null ];
        }
        if (!$res || $res['status'] !== 200) {
            if (!empty($cfg['alt_base_url'])) {
                $url = $cfg['alt_base_url'] . '/api/Integration/StokListesi?' . ($qsTitle ?? $qs);
                $res = $this->curlRequest('GET', $url, $headers);
                $_SESSION['palmiye_stok_last'] = [ 'url' => $url, 'status' => $res['status'] ?? null, 'body_preview' => isset($res['body']) ? substr($res['body'], 0, 300) : null ];
            }
        }
        if ($res && $res['status'] === 200) {
            $json = json_decode($res['body'] ?? '[]', true);
            return is_array($json) ? $json : [];
        }
        return [];
    }

    /** Test için e-stok kısıtlı veri */
    public function testEStok(int $depoId, string $tarih = '1900-01-01', bool $onlyPositive = true): array
    {
        $cfg = $this->getConfig();
        if (!$cfg['enabled']) { return ['success' => false, 'error' => 'config_missing']; }
        $token = $this->getToken($cfg);
        if (!$token) { return ['success' => false, 'error' => 'token_failed']; }
        $rows = $this->getEticaretStokListesi($cfg, $token, $depoId, $tarih, $onlyPositive);
        $count = is_array($rows) ? count($rows) : 0;
        // Önizleme için ilk 5 kaydı döndür
        $preview = array_slice($rows ?? [], 0, 5);
        return [
            'success' => true,
            'depo_id' => $depoId,
            'total' => $count,
            'preview' => $preview
        ];
    }

    /** Basit cURL wrapper */
    private function curlRequest(string $method, string $url, array $headers = [], ?string $body = null): ?array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        // Ensure Accept header
        $hasAccept = false;
        foreach ($headers as $h) { if (stripos($h, 'accept:') === 0) { $hasAccept = true; break; } }
        if (!$hasAccept) { $headers[] = 'Accept: application/json; charset=UTF-8, */*;q=0.8'; }
        $headers[] = 'Accept-Charset: UTF-8';
        $headers[] = 'Accept-Language: tr-TR,tr;q=0.9,en;q=0.8';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body ?? '');
        }
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            error_log('TamsoftService curl error: ' . $err);
            return null;
        }
        curl_close($ch);
        return [ 'status' => (int)$status, 'body' => $response ];
    }

    /** Depo sırasına göre stok anahtarlarını sırala */
    private function sortStocksByDepotOrder(array $stocks, array $depotNames): array
    {
        $sorted = [];
        foreach ($depotNames as $name) {
            $sorted[$name] = $stocks[$name] ?? 0;
        }
        return $sorted;
    }
}


