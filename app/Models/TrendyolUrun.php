<?php

namespace app\Models;

use core\Model;

class TrendyolUrun extends Model
{
	protected $table = 'trendyol_urunler';

	public function __construct()
	{
		parent::__construct();
		$this->createTableIfNotExists();
		$this->ensureIndexes();
	}

	private function createTableIfNotExists(): void
	{
		try {
			$sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
				id INT AUTO_INCREMENT PRIMARY KEY,
				supplier_id BIGINT NULL,
				store_id BIGINT NULL,
				barcode VARCHAR(64) NOT NULL,
				title VARCHAR(255) NULL,
				description TEXT NULL,
				stock_code VARCHAR(64) NULL,
				sku VARCHAR(64) NULL,
				brand_id BIGINT NULL,
				brand_name VARCHAR(200) NULL,
				category_id BIGINT NULL,
				category_name VARCHAR(200) NULL,
				quantity INT NULL,
				original_price DECIMAL(12,2) NULL,
				selling_price DECIMAL(12,2) NULL,
				status VARCHAR(50) NULL,
				on_sale TINYINT(1) NULL,
				image_url TEXT NULL,
                created_ts BIGINT NULL,
                modified_ts BIGINT NULL,
                created_at_api DATETIME NULL,
                updated_at_api DATETIME NULL,
				created_at DATETIME NULL,
				updated_at DATETIME NULL,
				UNIQUE KEY uniq_store_barcode (store_id, barcode)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
			$this->db->exec($sql);
		} catch (\Throwable $e) {
			error_log('TrendyolUrun create table error: ' . $e->getMessage());
		}
	}

	private function ensureIndexes(): void
	{
		$indexes = [
			'idx_barcode' => 'CREATE INDEX idx_barcode ON ' . $this->table . ' (barcode)',
			'idx_brand' => 'CREATE INDEX idx_brand_name ON ' . $this->table . ' (brand_name)',
			'idx_category' => 'CREATE INDEX idx_category_id ON ' . $this->table . ' (category_id)',
            'idx_selling' => 'CREATE INDEX idx_selling_price ON ' . $this->table . ' (selling_price)',
            'idx_modified' => 'CREATE INDEX idx_modified_ts ON ' . $this->table . ' (modified_ts)'
		];
		foreach ($indexes as $name => $sql) {
			try { $this->db->exec($sql); } catch (\Throwable $e) { /* already exists */ }
		}
	}

	public function upsertByStoreBarcode(string $storeId, string $barcode, array $data): bool
	{
		try {
			$now = date('Y-m-d H:i:s');
			$fields = [
				'supplier_id' => $data['supplier_id'] ?? null,
				'store_id' => $storeId,
				'barcode' => $barcode,
				'title' => $data['title'] ?? ($data['name'] ?? null),
				'description' => $data['description'] ?? null,
				'stock_code' => $data['stockCode'] ?? ($data['code'] ?? null),
				'sku' => $data['sku'] ?? null,
				'brand_id' => $data['brand_id'] ?? null,
				'brand_name' => $data['brand'] ?? null,
				'category_id' => $data['category_id'] ?? ($data['categoryId'] ?? null),
				'category_name' => $data['category_name'] ?? ($data['categoryName'] ?? null),
				'quantity' => isset($data['stock']) ? (int)$data['stock'] : (isset($data['quantity']) ? (int)$data['quantity'] : null),
				'original_price' => isset($data['listPrice']) ? (float)$data['listPrice'] : (isset($data['originalPrice']) ? (float)$data['originalPrice'] : null),
				'selling_price' => isset($data['trendyolPrice']) ? (float)$data['trendyolPrice'] : (isset($data['sellingPrice']) ? (float)$data['sellingPrice'] : null),
				'status' => $data['status'] ?? null,
				'on_sale' => isset($data['onSale']) ? (int)(bool)$data['onSale'] : null,
				'image_url' => $data['imageUrl'] ?? null,
                'created_ts' => isset($data['createdDate']) ? (int)$data['createdDate'] : null,
                'modified_ts' => isset($data['lastModifiedDate']) ? (int)$data['lastModifiedDate'] : null,
                'created_at_api' => isset($data['createdDate']) ? date('Y-m-d H:i:s', (int)$data['createdDate']/1000) : null,
                'updated_at_api' => isset($data['lastModifiedDate']) ? date('Y-m-d H:i:s', (int)$data['lastModifiedDate']/1000) : null,
				'created_at' => $now,
				'updated_at' => $now
			];
			$cols = array_keys($fields);
			$place = ':' . implode(',:', $cols);
			// Conditional update if incoming modified_ts is newer
			$cond = "(VALUES(modified_ts) IS NOT NULL AND (modified_ts IS NULL OR VALUES(modified_ts) > modified_ts))";
			$updates = [
				"modified_ts = GREATEST(COALESCE(modified_ts,0), COALESCE(VALUES(modified_ts),0))",
				"updated_at_api = CASE WHEN $cond THEN VALUES(updated_at_api) ELSE updated_at_api END",
				"title = CASE WHEN $cond THEN VALUES(title) ELSE title END",
				"description = CASE WHEN $cond THEN VALUES(description) ELSE description END",
				"stock_code = CASE WHEN $cond THEN VALUES(stock_code) ELSE stock_code END",
				"sku = CASE WHEN $cond THEN VALUES(sku) ELSE sku END",
				"brand_id = CASE WHEN $cond THEN VALUES(brand_id) ELSE brand_id END",
				"brand_name = CASE WHEN $cond THEN VALUES(brand_name) ELSE brand_name END",
				"category_id = CASE WHEN $cond THEN VALUES(category_id) ELSE category_id END",
				"category_name = CASE WHEN $cond THEN VALUES(category_name) ELSE category_name END",
				"quantity = CASE WHEN $cond THEN VALUES(quantity) ELSE quantity END",
				"original_price = CASE WHEN $cond THEN VALUES(original_price) ELSE original_price END",
				"selling_price = CASE WHEN $cond THEN VALUES(selling_price) ELSE selling_price END",
				"status = CASE WHEN $cond THEN VALUES(status) ELSE status END",
				"on_sale = CASE WHEN $cond THEN VALUES(on_sale) ELSE on_sale END",
				"image_url = CASE WHEN $cond THEN VALUES(image_url) ELSE image_url END",
				"created_ts = COALESCE(created_ts, VALUES(created_ts))",
				"created_at_api = COALESCE(created_at_api, VALUES(created_at_api))",
				"updated_at = VALUES(updated_at)"
			];
			$sql = "INSERT INTO {$this->table} (" . implode(',', $cols) . ") VALUES ($place) ON DUPLICATE KEY UPDATE " . implode(',', $updates);
			$stmt = $this->db->prepare($sql);
			foreach ($fields as $k => $v) { $stmt->bindValue(':' . $k, $v); }
			return $stmt->execute();
		} catch (\Throwable $e) { error_log('TrendyolUrun upsert error: ' . $e->getMessage()); return false; }
	}

	public function listDedup(string $search = '', int $page = 1, int $perPage = 50, ?string $storeId = null, string $orderBy = 'title', string $orderDir = 'ASC'): array
	{
		$offset = max(0, ($page - 1) * $perPage);
		$where = [];
		$params = [];
		if (!empty($storeId)) { $where[] = 'store_id = :sid'; $params[':sid'] = $storeId; }
		if ($search !== '') {
			$where[] = '(barcode LIKE :q OR title LIKE :q OR brand_name LIKE :q)';
			$params[':q'] = '%' . $search . '%';
		}
		$whereSql = empty($where) ? '' : ('WHERE ' . implode(' AND ', $where));
		// toplam benzersiz barkod sayısı
		try {
			$sqlCount = "SELECT COUNT(DISTINCT barcode) AS c FROM {$this->table} {$whereSql}";
			$stmt = $this->db->prepare($sqlCount);
			foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
			$stmt->execute();
			$total = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);
		} catch (\Throwable $e) { $total = 0; }

		$items = [];
		try {
			$sql = "SELECT 
				barcode,
				MAX(title) AS title,
				MAX(brand_name) AS brand_name,
				MAX(category_name) AS category_name,
				MAX(category_id) AS category_id,
				MAX(sku) AS sku,
				SUM(COALESCE(quantity,0)) AS total_qty,
				MIN(selling_price) AS min_sell,
				MIN(original_price) AS min_list,
				MAX(image_url) AS image_url,
				MAX(status) AS any_status,
				GROUP_CONCAT(DISTINCT store_id ORDER BY store_id SEPARATOR ',') AS store_ids
			FROM {$this->table}
			{$whereSql}
			GROUP BY barcode
			ORDER BY " . ($orderBy === 'barcode' ? 'barcode' : 'title') . " " . (strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC') . "
			LIMIT :lim OFFSET :off";
			$stmt = $this->db->prepare($sql);
			foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
			$stmt->bindValue(':lim', $perPage, \PDO::PARAM_INT);
			$stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
			$stmt->execute();
			$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
			foreach ($rows as $r) {
				$qty = (int)($r['total_qty'] ?? 0);
				$items[] = [
					'name' => $r['title'] ?? null,
					'barcode' => $r['barcode'] ?? null,
					'brand' => $r['brand_name'] ?? null,
					'categoryId' => $r['category_id'] ?? null,
					'categoryName' => $r['category_name'] ?? null,
					'imageUrl' => $r['image_url'] ?? null,
					'storeIds' => $r['store_ids'] ?? '',
					'sku' => $r['sku'] ?? null,
					// Ana listede stok toplama yapma: kullanıcı talebi gereği stok alanını boş bırakıyoruz
					'stock' => null,
					'trendyolPrice' => isset($r['min_sell']) ? (float)$r['min_sell'] : null,
					'listPrice' => isset($r['min_list']) ? (float)$r['min_list'] : null,
					'status' => $qty > 0 ? 'ACTIVE' : 'PASSIVE'
				];
			}
		} catch (\Throwable $e) { $items = []; }

		return [ 'items' => $items, 'total' => $total, 'page' => $page, 'per_page' => $perPage ];
	}

	public function listByStore(string $storeId, string $search = '', int $page = 1, int $perPage = 50, string $orderBy = 'title', string $orderDir = 'ASC'): array
	{
		$offset = max(0, ($page - 1) * $perPage);
		$items = [];
		$total = 0;
		$filtered = 0;
		$orderBySql = ($orderBy === 'barcode') ? 'barcode' : 'title';
		$orderDirSql = (strtoupper($orderDir) === 'DESC') ? 'DESC' : 'ASC';
		try {
			// Toplam kayıt
			$sqlTotal = "SELECT COUNT(*) AS c FROM {$this->table} WHERE store_id = :sid";
			$stmtT = $this->db->prepare($sqlTotal);
			$stmtT->bindValue(':sid', $storeId);
			$stmtT->execute();
			$total = (int)($stmtT->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);

			$where = 'WHERE store_id = :sid';
			$params = [ ':sid' => $storeId ];
			if ($search !== '') {
				$where .= ' AND (barcode LIKE :q OR title LIKE :q OR brand_name LIKE :q)';
				$params[':q'] = '%' . $search . '%';
			}
			// Filtreli toplam
			$sqlFiltered = "SELECT COUNT(*) AS c FROM {$this->table} {$where}";
			$stmtF = $this->db->prepare($sqlFiltered);
			foreach ($params as $k => $v) { $stmtF->bindValue($k, $v); }
			$stmtF->execute();
			$filtered = (int)($stmtF->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);

			$sql = "SELECT 
				barcode,
				title,
				brand_name,
				category_id,
				category_name,
				sku,
				quantity,
				selling_price,
				original_price,
				image_url,
				status,
				description
			FROM {$this->table}
			{$where}
			ORDER BY {$orderBySql} {$orderDirSql}
			LIMIT :lim OFFSET :off";
			$stmt = $this->db->prepare($sql);
			foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
			$stmt->bindValue(':lim', $perPage, \PDO::PARAM_INT);
			$stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
			$stmt->execute();
			$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
			foreach ($rows as $r) {
				$items[] = [
					'name' => $r['title'] ?? null,
					'barcode' => $r['barcode'] ?? null,
					'brand' => $r['brand_name'] ?? null,
					'categoryId' => $r['category_id'] ?? null,
					'categoryName' => $r['category_name'] ?? null,
					'imageUrl' => $r['image_url'] ?? null,
					'sku' => $r['sku'] ?? null,
					'stock' => isset($r['quantity']) ? (int)$r['quantity'] : null,
					'trendyolPrice' => isset($r['selling_price']) ? (float)$r['selling_price'] : null,
					'listPrice' => isset($r['original_price']) ? (float)$r['original_price'] : null,
					'status' => $r['status'] ?? null,
					'description' => $r['description'] ?? null
				];
			}
		} catch (\Throwable $e) {
			$items = [];
		}
		return [ 'items' => $items, 'total' => $total, 'filtered' => $filtered, 'page' => $page, 'per_page' => $perPage ];
	}

	public function countByStoreTotals(string $storeId, string $search = ''): array
	{
		try {
			$sqlTotal = "SELECT COUNT(*) AS c FROM {$this->table} WHERE store_id = :sid";
			$stmtT = $this->db->prepare($sqlTotal);
			$stmtT->bindValue(':sid', $storeId);
			$stmtT->execute();
			$total = (int)($stmtT->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);

			$where = 'WHERE store_id = :sid';
			$params = [ ':sid' => $storeId ];
			if ($search !== '') {
				$where .= ' AND (barcode LIKE :q OR title LIKE :q OR brand_name LIKE :q)';
				$params[':q'] = '%' . $search . '%';
			}
			$sqlFiltered = "SELECT COUNT(*) AS c FROM {$this->table} {$where}";
			$stmtF = $this->db->prepare($sqlFiltered);
			foreach ($params as $k => $v) { $stmtF->bindValue($k, $v); }
			$stmtF->execute();
			$filtered = (int)($stmtF->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);
			return [ $total, $filtered ];
		} catch (\Throwable $e) {
			return [0, 0];
		}
	}

	public function countDedupTotals(?string $storeId = null, string $search = ''): array
	{
		$whereAll = [];
		$paramsAll = [];
		if (!empty($storeId)) { $whereAll[] = 'store_id = :sid'; $paramsAll[':sid'] = $storeId; }
		$whereAllSql = empty($whereAll) ? '' : ('WHERE ' . implode(' AND ', $whereAll));
		$whereFiltered = $whereAll;
		$paramsFiltered = $paramsAll;
		if ($search !== '') {
			$whereFiltered[] = '(barcode LIKE :q OR title LIKE :q OR brand_name LIKE :q)';
			$paramsFiltered[':q'] = '%' . $search . '%';
		}
		$whereFilteredSql = empty($whereFiltered) ? '' : ('WHERE ' . implode(' AND ', $whereFiltered));
		$tot = 0; $fil = 0;
		try {
			$stmt = $this->db->prepare("SELECT COUNT(DISTINCT barcode) AS c FROM {$this->table} {$whereAllSql}");
			foreach ($paramsAll as $k => $v) { $stmt->bindValue($k, $v); }
			$stmt->execute();
			$tot = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);
		} catch (\Throwable $e) { $tot = 0; }
		try {
			$stmt2 = $this->db->prepare("SELECT COUNT(DISTINCT barcode) AS c FROM {$this->table} {$whereFilteredSql}");
			foreach ($paramsFiltered as $k => $v) { $stmt2->bindValue($k, $v); }
			$stmt2->execute();
			$fil = (int)($stmt2->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);
		} catch (\Throwable $e) { $fil = 0; }
		return [ $tot, $fil ];
	}
}


