<?php

namespace app\Services;

use app\Models\TrendyolGoAyarlar;

class TrendyolGoService
{
    public function getConfig(): array
    {
        try { $m = new TrendyolGoAyarlar(); $db = $m->getAyarlar(); } catch (\Throwable $e) { $db = null; }
        return [
            'base_url' => rtrim((string)($db['base_url'] ?? getenv('TRENDYOL_GO_BASE_URL') ?? ''), '/'),
            'api_key' => (string)($db['api_key'] ?? getenv('TRENDYOL_GO_API_KEY') ?? ''),
            'supplier_id' => (string)($db['supplier_id'] ?? getenv('TRENDYOL_GO_SUPPLIER_ID') ?? ''),
            'webhook_secret' => (string)($db['webhook_secret'] ?? getenv('TRENDYOL_GO_WEBHOOK_SECRET') ?? ''),
            'satici_cari_id' => (string)($db['satici_cari_id'] ?? ''),
            'entegrasyon_ref_kodu' => (string)($db['entegrasyon_ref_kodu'] ?? ''),
            'api_secret' => (string)($db['api_secret'] ?? ''),
            'token' => (string)($db['token'] ?? ''),
            'enabled' => (bool)($db['enabled'] ?? 0),
            'schedule_minutes' => (int)($db['schedule_minutes'] ?? 0)
        ];
    }

    public function health(): array
    {
        $cfg = $this->getConfig();
        $ok = false; $debug = null;
        if ($cfg['enabled']) {
            // Hızlı ping: kategori endpointinden 1 kayıt çek
            $resp = $this->tgoRequest('GET', '/integrator/product/grocery/categories', [ 'page' => 0, 'size' => 1 ], $cfg);
            $ok = $resp && ($resp['status'] ?? 0) === 200;
            $debug = $resp['debug'] ?? null;
        }
        $baseShow = $cfg['base_url'] ?: (getenv('TGO_BASE_URL') ?: 'https://api.tgoapis.com');
        return [ 'enabled' => $cfg['enabled'], 'base_url' => $baseShow, 'has_api_key' => !empty($cfg['api_key']), 'service_ok' => $ok, 'last_request' => $debug ];
    }

    /**
     * Ürün listeleme – filter-v2 endpoint (Grocery)
     * Kaynak: docs 'Ürün Filtreleme v2'
     */
    public function getProducts(string $search = '', int $page = 1, int $perPage = 50): array
    {
        $cfg = $this->getConfig();
        // base_url boş olabilir; tgoRequest varsayılanı kullanır. Zorunlular: api_key, api_secret, satici_cari_id
        if (empty($cfg['api_key']) || empty($cfg['api_secret']) || empty($cfg['satici_cari_id'])) {
            return [ 'items' => [], 'page' => $page, 'per_page' => $perPage, 'total' => 0, 'error' => 'config_missing' ];
        }

        // filter-v2: kategori/marka/arama/sayfalama; burada sadece arama ve sayfa/size kullanıyoruz
        $qs = [ 'page' => max(0, $page - 1), 'size' => $perPage ];
        if ($search !== '') { $qs['query'] = $search; }

        // Bazı dokümanlarda path farklı sürümlerle geçebilir; çoklu deneme yapalım
        $supplierId = urlencode((string)$cfg['satici_cari_id']);
        $candidatePaths = [
            '/integrator/product/grocery/products/filter-v2',
            '/integrator/product/grocery/products/filter'
        ];

        $resp = null; $lastDebug = null;
        foreach ($candidatePaths as $path) {
            $resp = $this->tgoRequest('GET', $path, $qs, $cfg);
            $lastDebug = $resp['debug'] ?? null;
            if ($resp && ($resp['status'] ?? 0) === 200) { break; }
        }
        // başka varyant denemeyelim; dokümana sadık kalalım
        if (!$resp || ($resp['status'] ?? 0) !== 200) {
            return [ 'items' => [], 'page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_request' => $lastDebug ];
        }

        $data = json_decode($resp['body'] ?? '[]', true);
        $items = [];
        $total = 0;

        // Beklenen şema: { content: [ { id, barcode, productCode, brand, name, listPrice, salePrice, inventoryQuantity, categoryId, ... } ], totalElements }
        $rows = [];
        if (isset($data['content']) && is_array($data['content'])) {
            $rows = $data['content'];
            $total = (int)($data['totalElements'] ?? 0);
        } elseif (is_array($data)) {
            $rows = $data;
            $total = count($rows);
        }

        foreach ($rows as $row) {
            if (!is_array($row)) { continue; }
            $items[] = [
                'id' => $row['id'] ?? null,
                'name' => $row['name'] ?? ($row['productName'] ?? ''),
                'barcode' => $row['barcode'] ?? ($row['barCode'] ?? ''),
                'code' => $row['productCode'] ?? ($row['sku'] ?? ''),
                'brand' => (is_array($row['brand'] ?? null) ? ($row['brand']['name'] ?? '') : ($row['brand'] ?? '')),
                'categoryId' => $row['categoryId'] ?? null,
                'price' => $row['salePrice'] ?? ($row['price'] ?? null),
                'stock' => $row['inventoryQuantity'] ?? ($row['quantity'] ?? null),
                'status' => $row['status'] ?? null
            ];
        }

        return [ 'items' => $items, 'page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_request' => $lastDebug ];
    }

    /** Ortak istek oluşturucu (Basic + User-Agent) */
    private function tgoRequest(string $method, string $path, array $query, array $cfg): ?array
    {
        $logger = null;
        try { $logger = new \app\Models\TrendyolGoLog(); } catch (\Throwable $e) { $logger = null; }
        $base = rtrim($cfg['base_url'] ?: (getenv('TGO_BASE_URL') ?: ''), '/');
        if ($base === '') { $base = 'https://api.tgoapis.com'; }
        $supplierId = trim((string)$cfg['satici_cari_id']);
        $apiKey = trim((string)$cfg['api_key']);
        $apiSecret = trim((string)($cfg['api_secret'] ?? ''));
        $ua = getenv('TGO_USER_AGENT') ?: (trim($supplierId) !== '' ? ($supplierId . ' - SelfIntegration') : 'MagazaTakip - TrendyolGO Integration/1.0');

        $basic = base64_encode($supplierId . ':' . $apiKey . ':' . $apiSecret);
        $headers = [
            'Auth: Basic ' . $basic,
            'User-Agent: ' . $ua,
            'Content-Type: application/json',
            'Accept: application/json',
            'Accept-Charset: utf-8',
            'Accept-Language: tr-TR'
        ];
        if (!empty($cfg['entegrasyon_ref_kodu'])) {
            $headers[] = 'X-Integrator-Ref-Code: ' . $cfg['entegrasyon_ref_kodu'];
        }
        $url = $base . $path;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }
        $attempt = 0;
        $maxAttempts = 3;
        $lastErr = null; $lastResp = null;
        while ($attempt < $maxAttempts) {
            $attempt++;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            if (strtoupper($method) !== 'GET') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            }
            $body = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($body === false) {
                $lastErr = curl_error($ch);
                curl_close($ch);
                if ($logger) { $logger->add($method, $url, 0, null, $lastErr); }
                break;
            }
            curl_close($ch);
            $lastResp = [ 'status' => (int)$status, 'body' => $body, 'debug' => [ 'url' => $url, 'status' => (int)$status, 'body_preview' => substr($body, 0, 300) ] ];
            if (in_array((int)$status, [429, 500, 503], true) && $attempt < $maxAttempts) {
                // kısa bekleme: 200ms, sonra 600ms
                usleep($attempt === 1 ? 200000 : 600000);
                continue;
            }
            if ($logger) { $logger->add($method, $url, (int)$status, $lastResp['debug']['body_preview'], null); }
            return $lastResp;
        }
        if ($lastResp) { return $lastResp; }
        if ($lastErr) {
            error_log('TrendyolGO curl error: ' . $lastErr);
            return [ 'status' => 0, 'error' => $lastErr, 'debug' => [ 'url' => $url, 'status' => 0, 'body_preview' => null ] ];
        }
        return [ 'status' => 0, 'error' => 'unknown_error', 'debug' => [ 'url' => $url, 'status' => 0, 'body_preview' => null ] ];
    }

    public function getCategories(int $page = 1, int $perPage = 50): array
    {
        $cfg = $this->getConfig();
        $qs = [ 'page' => max(0, $page - 1), 'size' => $perPage ];
        $resp = $this->tgoRequest('GET', '/integrator/product/grocery/categories', $qs, $cfg);
        if (!$resp || ($resp['status'] ?? 0) !== 200) {
            return [ 'items' => [], 'total' => 0, 'last_request' => $resp['debug'] ?? null ];
        }
        $data = json_decode($resp['body'] ?? '[]', true);
        $items = isset($data['content']) && is_array($data['content']) ? $data['content'] : (is_array($data) ? $data : []);
        $total = (int)($data['totalElements'] ?? count($items));
        return [ 'items' => $items, 'total' => $total, 'page' => $page, 'per_page' => $perPage, 'last_request' => $resp['debug'] ?? null ];
    }
}


