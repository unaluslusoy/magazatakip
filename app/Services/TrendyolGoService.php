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
            'default_store_id' => (string)($db['default_store_id'] ?? ''),
            'enabled' => (bool)($db['enabled'] ?? 0),
            'schedule_minutes' => (int)($db['schedule_minutes'] ?? 0),
            'price_markup_percent' => isset($db['price_markup_percent']) ? (float)$db['price_markup_percent'] : null,
            'price_add_abs' => isset($db['price_add_abs']) ? (float)$db['price_add_abs'] : null
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
    public function getProducts(string $search = '', int $page = 1, int $perPage = 50, array $options = []): array
    {
        $cfg = $this->getConfig();
        // base_url boş olabilir; tgoRequest varsayılanı kullanır. Zorunlular: api_key, api_secret, satici_cari_id
        if (empty($cfg['api_key']) || empty($cfg['api_secret']) || empty($cfg['satici_cari_id'])) {
            return [ 'items' => [], 'page' => $page, 'per_page' => $perPage, 'total' => 0, 'error' => 'config_missing' ];
        }

        // filter-v2: kategori/marka/arama/sayfalama
        $qs = [ 'page' => max(0, $page - 1), 'size' => $perPage ];
        $payload = [ 'page' => max(0, $page - 1), 'size' => $perPage ];
        if ($search !== '') { $qs['query'] = $search; $payload['query'] = $search; }
        // Dokümana göre filtre yapısı destekle
        // options['filters'] doğrudan geçilir; yoksa bilinen anahtarları toplayıp filters altında birleştiririz
        if (!empty($options['filters']) && is_array($options['filters'])) {
            $payload['filters'] = $options['filters'];
        } else {
            $filters = [];
            foreach ([
                'category', 'categoryId', 'categoryIds', 'brand', 'brandId', 'brandIds',
                'barcode', 'barcodes', 'productCode', 'productCodes', 'status', 'storeId',
                'priceRange', 'inventoryRange', 'sort'
            ] as $k) {
                if (array_key_exists($k, $options)) { $filters[$k] = $options[$k]; }
            }
            if (!empty($filters)) { $payload['filters'] = $filters; }
        }
        // Varsayılanlar: storeId ve status ACTIVE
        if (empty(($payload['filters']['storeId'] ?? null)) && !empty($cfg['default_store_id'])) {
            $payload['filters']['storeId'] = $cfg['default_store_id'];
        }
        if (empty(($payload['filters']['status'] ?? null))) {
            $payload['filters']['status'] = 'ACTIVE';
        }

		// Parametrik canlı ortam yolu: /suppliers/{supplierId}/stores/{storeId}/products
		$supplierParam = trim((string)($cfg['supplier_id'] ?: $cfg['satici_cari_id']));
		$storeParam = trim((string)($payload['filters']['storeId'] ?? ''));

        // Bazı dokümanlarda path ve method farklı sürümlerle geçebilir; çoklu deneme yapalım
		$candidateRequests = [
			// Canlı ortam parametrik endpoint (öncelik)
			[ 'GET', '/integrator/product/grocery/suppliers/' . rawurlencode($supplierParam) . '/stores/' . rawurlencode($storeParam) . '/products', $qs, null ],
            // Dokümana göre birincil uç nokta
            [ 'POST', '/integrator/product/grocery/products/filter-v2', null, $payload ],
            // Alternatifler
            [ 'POST', '/integrator/product/grocery/filter-v2', null, $payload ],
            [ 'POST', '/integrator/product/grocery/products/filter', null, $payload ],
            [ 'POST', '/integrator/product/grocery/filter', null, $payload ],
            [ 'POST', '/integrator/product/grocery/product/filter-v2', null, $payload ],
            [ 'POST', '/integrator/product/grocery/product/filter', null, $payload ],
            [ 'POST', '/integrator/product/grocery/items/filter-v2', null, $payload ],
            [ 'POST', '/integrator/product/grocery/items/filter', null, $payload ],
            // GET fallback (bazı kurulumlarda geçici destek olabilir)
            [ 'GET', '/integrator/product/grocery/products', $qs, null ],
            [ 'GET', '/integrator/product/grocery/products/filter-v2', $qs, null ],
            [ 'GET', '/integrator/product/grocery/products/filter', $qs, null ],
            [ 'GET', '/integrator/product/grocery/product/filter-v2', $qs, null ],
            [ 'GET', '/integrator/product/grocery/product/filter', $qs, null ],
            [ 'GET', '/integrator/product/grocery/items/filter-v2', $qs, null ],
            [ 'GET', '/integrator/product/grocery/items/filter', $qs, null ],
        ];

        $resp = null; $lastDebug = null; $attempts = [];
        foreach ($candidateRequests as $req) {
            [ $m, $p, $q, $b ] = $req;
            $resp = $this->tgoRequest($m, $p, $q ?? [], $cfg, $b);
            $lastDebug = $resp['debug'] ?? null;
            if ($lastDebug) { $attempts[] = $lastDebug; }
            if ($resp && ($resp['status'] ?? 0) === 200) { break; }
        }
        // başka varyant denemeyelim; dokümana sadık kalalım
        if (!$resp || ($resp['status'] ?? 0) !== 200) {
            return [ 'items' => [], 'page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_request' => $lastDebug, 'attempts' => $attempts ];
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
			$brandName = '';
			if (isset($row['brand'])) {
				$brandName = is_array($row['brand']) ? ($row['brand']['name'] ?? '') : (string)$row['brand'];
			}
			$categoryId = $row['categoryId'] ?? (isset($row['category']) && is_array($row['category']) ? ($row['category']['id'] ?? null) : null);
			$categoryName = isset($row['category']) && is_array($row['category']) ? ($row['category']['name'] ?? '') : '';
			$title = $row['name'] ?? ($row['productName'] ?? ($row['title'] ?? ''));
			$barcode = $row['barcode'] ?? ($row['barCode'] ?? '');
			$stockCode = $row['stockCode'] ?? null;
			$sku = $row['sku'] ?? null;
			$listPrice = $row['originalPrice'] ?? null;
			$trendyolPrice = $row['sellingPrice'] ?? null;
			$stockQty = $row['inventoryQuantity'] ?? ($row['quantity'] ?? null);
			$status = $row['status'] ?? null;
			$modelCode = $row['modelCode'] ?? null;
			$description = $row['description'] ?? ($row['longName'] ?? null);
			$imageUrl = null;
			if (isset($row['images']) && is_array($row['images']) && !empty($row['images'])) {
				$firstImage = $row['images'][0];
				if (is_array($firstImage)) { $imageUrl = $firstImage['url'] ?? null; }
				elseif (is_string($firstImage)) { $imageUrl = $firstImage; }
			}

			$items[] = [
				'id' => $row['id'] ?? null,
				'supplierId' => $row['supplierId'] ?? null,
				'storeId' => $row['storeId'] ?? null,
				'name' => $title,
				'description' => $description,
				'modelCode' => $modelCode,
				'barcode' => $barcode,
				'code' => ($row['productCode'] ?? ($row['sku'] ?? ($stockCode ?? ''))),
				'stockCode' => $stockCode,
				'onSale' => $row['onSale'] ?? null,
				'sku' => $sku,
				'brand' => $brandName,
				'categoryId' => $categoryId,
				'categoryName' => $categoryName,
				'listPrice' => $listPrice,
				'trendyolPrice' => $trendyolPrice,
				'price' => $trendyolPrice,
				'stock' => $stockQty,
				'status' => $status,
				'imageUrl' => $imageUrl
			];
        }

        return [ 'items' => $items, 'page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_request' => $lastDebug, 'attempts' => $attempts ];
    }

	/**
	 * Tüm ürünleri sayfalayarak çeker (filtre/kategori uygulanmaz)
	 * Büyük veri setlerinde bellek kullanımına dikkat edin.
	 */
    public function getAllProducts(int $perPage = 200, ?int $maxPages = null): array
	{
		$page = 1;
		$all = [];
		$total = null;
		$lastDebug = null;
		$attemptsAll = [];
		while (true) {
            $res = $this->getProducts('', $page, $perPage, []);
			$lastDebug = $res['last_request'] ?? $lastDebug;
			if (!empty($res['attempts']) && is_array($res['attempts'])) {
				$attemptsAll = array_merge($attemptsAll, $res['attempts']);
			}
			$items = $res['items'] ?? [];
			if (empty($items)) { break; }
			$all = array_merge($all, $items);
			$total = $res['total'] ?? (count($all));
			if ($maxPages !== null && $page >= $maxPages) { break; }
			if (count($all) >= (int)$total) { break; }
			$page++;
		}
		return [ 'items' => $all, 'total' => count($all), 'pages_fetched' => $page, 'last_request' => $lastDebug, 'attempts' => $attemptsAll ];
	}

    /** Ortak istek oluşturucu (Basic + User-Agent) */
    private function tgoRequest(string $method, string $path, array $query, array $cfg, ?array $body = null, bool $tryAlternateAuth = true): ?array
    {
        // Rate limit: aynı endpoint (path) için 10 sn'de max 50 istek – APCu hızlı sayaç + DB fallback
        try {
            if (function_exists('apcu_inc')) {
                $win = (int)floor(time() / 10) * 10;
                $k = 'tgo_rate:' . $path . ':' . $win;
                $exists = false;
                $c = apcu_inc($k, 1, $exists, 11);
                if ($exists === false) { apcu_store($k, 1, 11); $c = 1; }
                if ((int)$c > 50) { usleep(250000); }
            } else {
                (new \app\Models\TrendyolGoRate())->acquireSlot($path, 50);
            }
        } catch (\Throwable $e) {}
        $logger = null;
        try { $logger = new \app\Models\TrendyolGoLog(); } catch (\Throwable $e) { $logger = null; }
        $base = rtrim($cfg['base_url'] ?: (getenv('TGO_BASE_URL') ?: ''), '/');
        if ($base === '') { $base = 'https://api.tgoapis.com'; }
        $supplierId = trim((string)$cfg['satici_cari_id']);
        $apiKey = trim((string)$cfg['api_key']);
        $apiSecret = trim((string)($cfg['api_secret'] ?? ''));
        $ua = getenv('TGO_USER_AGENT') ?: (trim($supplierId) !== '' ? ($supplierId . ' - SelfIntegration') : 'MagazaTakip - TrendyolGO Integration/1.0');

        $basicTriple = base64_encode($supplierId . ':' . $apiKey . ':' . $apiSecret);
        $headers = [
            'Authorization: Basic ' . $basicTriple,
            'Auth: Basic ' . $basicTriple,
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
        $attempt = 0; $authMode = 'triple';
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
                if (!is_null($body)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                }
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
            $lastResp = [ 'status' => (int)$status, 'body' => $body, 'debug' => [ 'url' => $url, 'method' => strtoupper($method), 'status' => (int)$status, 'body_preview' => substr($body, 0, 300) ] ];
            if (in_array((int)$status, [429, 500, 502, 503, 504], true) && $attempt < $maxAttempts) {
                // Üstel backoff + jitter (0.5s, 1s, 2s) +/- 0-250ms
                $baseMs = 500 * (1 << ($attempt - 1));
                $jitterMs = random_int(0, 250);
                $sleepMs = min(3000, $baseMs + $jitterMs);
                usleep($sleepMs * 1000);
                continue;
            }
            if ($logger) { $logger->add($method, $url, (int)$status, $lastResp['debug']['body_preview'], null); }
            // 401 durumunda alternatif Basic denemesi: apiKey:apiSecret
            if ((int)$status === 401 && $tryAlternateAuth && $authMode === 'triple') {
                $authMode = 'double';
                $basicDouble = base64_encode($apiKey . ':' . $apiSecret);
                $headers[0] = 'Authorization: Basic ' . $basicDouble;
                $headers[1] = 'Auth: Basic ' . $basicDouble;
                // tekrar dene (tek seferlik)
                $tryAlternateAuth = false;
                continue;
            }
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
        // APCu cache - tüm sayfaları ilk istekte cache'le (TTL 1 saat)
        try {
            if (function_exists('apcu_fetch')) {
                $cacheKey = 'tgo_cats_page_' . $page . '_' . $perPage;
                $cached = apcu_fetch($cacheKey, $ok);
                if ($ok && is_array($cached)) { return $cached; }
            }
        } catch (\Throwable $e) {}
        $cfg = $this->getConfig();
        $qs = [ 'page' => max(0, $page - 1), 'size' => $perPage ];
        $resp = $this->tgoRequest('GET', '/integrator/product/grocery/categories', $qs, $cfg);
        if (!$resp || ($resp['status'] ?? 0) !== 200) {
            return [ 'items' => [], 'total' => 0, 'last_request' => $resp['debug'] ?? null ];
        }
        $data = json_decode($resp['body'] ?? '[]', true);
        $items = isset($data['content']) && is_array($data['content']) ? $data['content'] : (is_array($data) ? $data : []);
        $total = (int)($data['totalElements'] ?? count($items));
        $res = [ 'items' => $items, 'total' => $total, 'page' => $page, 'per_page' => $perPage, 'last_request' => $resp['debug'] ?? null ];
        try { if (function_exists('apcu_store')) { apcu_store($cacheKey, $res, 3600); } } catch (\Throwable $e) {}
        return $res;
    }

    // Sipariş listeleme (yeni/aktif)
    public function getOrders(string $status = 'ACTIVE', int $page = 1, int $perPage = 50, ?string $dateStart = null, ?string $dateEnd = null, ?string $storeId = null): array
    {
        $cfg = $this->getConfig();
        if (empty($cfg['api_key']) || empty($cfg['api_secret']) || empty($cfg['satici_cari_id'])) {
            return [ 'items' => [], 'page' => $page, 'per_page' => $perPage, 'total' => 0, 'error' => 'config_missing' ];
        }
        $qs = [ 'page' => max(0, $page - 1), 'size' => $perPage ];
        $payload = [ 'page' => max(0, $page - 1), 'size' => $perPage ];
        if (!empty($status)) { $qs['status'] = $status; $payload['status'] = $status; }
        if (!empty($dateStart)) { $qs['startDate'] = $dateStart; $payload['startDate'] = $dateStart; }
        if (!empty($dateEnd)) { $qs['endDate'] = $dateEnd; $payload['endDate'] = $dateEnd; }
        if (!empty($storeId)) { $qs['storeId'] = $storeId; $payload['storeId'] = $storeId; }
        if (empty($storeId) && !empty($cfg['default_store_id'])) { $qs['storeId'] = $cfg['default_store_id']; $payload['storeId'] = $cfg['default_store_id']; }
        $candidateRequests = [
            // Önce POST + JSON varyantları
            [ 'POST', '/integrator/order/grocery/orders/filter-v2', null, $payload ],
            [ 'POST', '/integrator/order/grocery/orders/filter', null, $payload ],
            // Ardından GET fallback'leri
            [ 'GET', '/integrator/order/grocery/orders/filter-v2', $qs, null ],
            [ 'GET', '/integrator/order/grocery/orders/filter', $qs, null ],
            [ 'GET', '/integrator/order/grocery/orders', $qs, null ]
        ];
        $resp = null; $lastDebug = null; $attempts = [];
        foreach ($candidateRequests as $req) {
            [ $m, $p, $q, $b ] = $req;
            $resp = $this->tgoRequest($m, $p, $q ?? [], $cfg, $b);
            $lastDebug = $resp['debug'] ?? null;
            if ($lastDebug) { $attempts[] = $lastDebug; }
            if ($resp && ($resp['status'] ?? 0) === 200) { break; }
        }
        if (!$resp || ($resp['status'] ?? 0) !== 200) {
            return [ 'items' => [], 'page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_request' => $lastDebug, 'attempts' => $attempts ];
        }
        $data = json_decode($resp['body'] ?? '[]', true);
        $rows = isset($data['content']) && is_array($data['content']) ? $data['content'] : (is_array($data) ? $data : []);
        $total = (int)($data['totalElements'] ?? count($rows));
        return [ 'items' => $rows, 'page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_request' => $lastDebug, 'attempts' => $attempts ];
    }

    // İptal edilen siparişler
    public function getCancelledOrders(int $page = 1, int $perPage = 50, ?string $dateStart = null, ?string $dateEnd = null, ?string $storeId = null): array
    {
        return $this->getOrders('CANCELLED', $page, $perPage, $dateStart, $dateEnd, $storeId);
    }

    // Sipariş durum güncelleme
    public function updateOrderStatus(string $orderId, string $newStatus): array
    {
        $cfg = $this->getConfig();
        if (empty($orderId) || empty($newStatus)) {
            return [ 'success' => false, 'error' => 'invalid_params' ];
        }
        $qs = [ 'status' => $newStatus ];
        $resp = $this->tgoRequest('PUT', '/integrator/order/grocery/orders/' . rawurlencode($orderId) . '/status', $qs, $cfg);
        if (!$resp) { return [ 'success' => false, 'error' => 'no_response' ]; }
        $ok = (int)($resp['status'] ?? 0) === 200;
        return [ 'success' => $ok, 'status' => $resp['status'] ?? 0, 'last_request' => $resp['debug'] ?? null, 'body' => $resp['body'] ?? null ];
    }

    /**
     * Ürün fiyat/stock güncelleme (tek kalem)
     * Not: TGO dokümana göre endpoint/şema değişkenlik gösterebilir; candidate paths denenir
     */
    public function updateProductPriceStock(string $productCode, ?float $price, ?int $stock, ?string $storeId = null): array
    {
        $cfg = $this->getConfig();
        if (empty($cfg['api_key']) || empty($cfg['api_secret']) || empty($cfg['satici_cari_id'])) {
            return [ 'success' => false, 'error' => 'config_missing' ];
        }
        $payload = [ 'productCode' => $productCode ];
        if ($price !== null) { $payload['price'] = $price; }
        if ($stock !== null) { $payload['stock'] = $stock; }
        if ($storeId) { $payload['storeId'] = $storeId; }
        $candidates = [
            [ 'POST', '/integrator/product/grocery/product/update', null, $payload ],
            [ 'POST', '/integrator/product/grocery/products/update', null, [ 'items' => [ $payload ] ] ],
        ];
        $resp = null; $lastDebug = null;
        foreach ($candidates as $req) {
            [ $m, $p, $q, $b ] = $req;
            $resp = $this->tgoRequest($m, $p, $q ?? [], $cfg, $b);
            $lastDebug = $resp['debug'] ?? null;
            if ($resp && in_array((int)($resp['status'] ?? 0), [200,201], true)) break;
        }
        $ok = $resp && in_array((int)($resp['status'] ?? 0), [200,201], true);
        return [ 'success' => $ok, 'status' => (int)($resp['status'] ?? 0), 'last_request' => $lastDebug, 'body' => $resp['body'] ?? null ];
    }
}


