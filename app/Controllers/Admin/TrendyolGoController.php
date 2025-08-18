<?php
namespace app\Controllers\Admin;

use core\Controller;
use app\Middleware\AdminMiddleware;
use app\Services\TrendyolGoService;
use app\Models\TrendyolGoAyarlar;
use app\Models\TrendyolGoMagaza;
use app\Models\TrendyolGoImportJob;

class TrendyolGoController extends Controller
{
    private TrendyolGoService $svc;
    private TrendyolGoAyarlar $ayar;
    private TrendyolGoMagaza $magaza;
    private TrendyolGoImportJob $importJob;

    public function __construct()
    {
        AdminMiddleware::handle();
        $this->svc = new TrendyolGoService();
        $this->ayar = new TrendyolGoAyarlar();
        $this->magaza = new TrendyolGoMagaza();
        $this->importJob = new TrendyolGoImportJob();
    }

    public function index()
    {
        $data = [ 'title' => 'Trendyol Go', 'link' => 'Entegrasyonlar' ];
        $data['health'] = $this->svc->health();
        $this->view('admin/trendyolgo/index', $data);
    }

    public function ayarlar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Yerelleştirilmiş sayı girişlerini normalize et (virgül -> nokta)
            $markupRaw = isset($_POST['price_markup_percent']) ? (string)$_POST['price_markup_percent'] : '';
            $markupNorm = $markupRaw !== '' ? (float)str_replace(',', '.', $markupRaw) : null;
            $addRaw = isset($_POST['price_add_abs']) ? (string)$_POST['price_add_abs'] : '';
            $addNorm = $addRaw !== '' ? (float)str_replace(',', '.', $addRaw) : null;

            $ok = $this->ayar->updateAyarlar([
                'api_key' => trim($_POST['api_key'] ?? ''),
                'satici_cari_id' => trim($_POST['satici_cari_id'] ?? ''),
                'entegrasyon_ref_kodu' => trim($_POST['entegrasyon_ref_kodu'] ?? ''),
                'api_secret' => trim($_POST['api_secret'] ?? ''),
                'token' => trim($_POST['token'] ?? ''),
                'default_store_id' => trim($_POST['default_store_id'] ?? ''),
                'schedule_minutes' => (int)($_POST['schedule_minutes'] ?? 0),
                'price_markup_percent' => $markupNorm,
                'price_add_abs' => $addNorm,
                'enabled' => !empty($_POST['enabled']) ? 1 : 0,
            ]);
            $_SESSION['alert_message'] = [ 'text' => $ok ? 'Ayarlar kaydedildi' : 'Kayıt hatası', 'icon' => $ok ? 'success' : 'error', 'confirmButtonText' => 'Tamam' ];
            header('Location: /admin/trendyolgo/ayarlar');
            exit;
        }
        $data = [ 'title' => 'Trendyol Go Ayarları', 'link' => 'Trendyol Go' ];
        $data['ayarlar'] = $this->ayar->getAyarlar();
        try { $data['magazalar'] = $this->magaza->getAllCached(); } catch (\Throwable $e) { $data['magazalar'] = []; }
        $this->view('admin/trendyolgo/ayarlar', $data);
    }

    public function urunler()
    {
		$q = $_GET['q'] ?? '';
		$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
		$per = isset($_GET['per']) ? max(10, (int)$_GET['per']) : 50;
		$storeId = $_GET['store_id'] ?? '';
		// Listeyi DB'den, tekrarsız (dedup by barcode) göster
		try {
			$dedup = (new \app\Models\TrendyolUrun())->listDedup($q, $page, $per, ($storeId !== '' ? $storeId : null));
			$list = [ 'items' => $dedup['items'] ?? [], 'total' => $dedup['total'] ?? 0, 'page' => $page, 'per_page' => $per, 'last_request' => null ];
		} catch (\Throwable $e) {
			$list = [ 'items' => [], 'total' => 0, 'page' => $page, 'per_page' => $per, 'last_request' => null ];
		}
		$storeName = '';
		if ($storeId !== '') {
			try {
				$stores = $this->magaza->getAll();
				foreach ($stores as $s) {
					if ((string)($s['store_id'] ?? '') === (string)$storeId) { $storeName = (string)($s['magaza_adi'] ?? ''); break; }
				}
			} catch (\Throwable $e) {}
		}
        $data = [ 'title' => 'Trendyol Go - Ürünler', 'link' => 'Trendyol Go' ];
        $data['search'] = $q;
        $data['page'] = $list['page'] ?? $page;
        $data['per'] = $list['per_page'] ?? $per;
        $data['total'] = $list['total'] ?? 0;
        $data['items'] = $list['items'] ?? [];
        $data['last_request'] = $list['last_request'] ?? null;
        $data['error'] = $list['error'] ?? null;
        $data['store_id'] = $storeId;
		$data['store_name'] = $storeName;
        $this->view('admin/trendyolgo/urunler', $data);
    }

    public function eslesmeler()
    {
        $data = [ 'title' => 'Trendyol Go - Eşleşmeler', 'link' => 'Trendyol Go' ];
        $storeId = isset($_GET['store_id']) ? (string)$_GET['store_id'] : '';
        // Basit liste: urun_entegrasyon_map'ten TrendyolGO kayıtları
        try {
            $pdo = (new \app\Models\TamsoftStockRepo());
            $ref = new \ReflectionClass($pdo);
            $prop = $ref->getParentClass()->getProperty('db');
            $prop->setAccessible(true);
            /** @var \PDO $db */
            $db = $prop->getValue($pdo);
            $where = "WHERE (m.platform IS NULL OR m.platform = 'trendyolgo')";
            $params = [];
            if ($storeId !== '') { $where .= " AND (m.store_id = :sid OR m.store_id IS NULL)"; $params[':sid'] = $storeId; }
            $stmt = $db->prepare("SELECT m.id, m.urun_kodu, m.barkod, m.store_id, m.trendyolgo_sku, m.match_confidence, m.match_source, m.manual_override, m.last_matched_at, u.urun_adi FROM urun_entegrasyon_map m LEFT JOIN tamsoft_urunler u ON u.ext_urun_id = m.urun_kodu {$where} ORDER BY m.updated_at DESC LIMIT 1000");
            foreach ($params as $k=>$v) { $stmt->bindValue($k,$v); }
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            // Sayaçlar
            $matchedCount = (int)$db->query("SELECT COUNT(*) FROM urun_entegrasyon_map WHERE (platform IS NULL OR platform='trendyolgo')")->fetchColumn();
            $matchedDistinctTamsoft = (int)$db->query("SELECT COUNT(DISTINCT urun_kodu) FROM urun_entegrasyon_map WHERE (platform IS NULL OR platform='trendyolgo')")->fetchColumn();
            $totalTamsoft = (int)$db->query("SELECT COUNT(*) FROM tamsoft_urunler WHERE aktif=1")->fetchColumn();
            $unmatchedTamsoft = max(0, $totalTamsoft - $matchedDistinctTamsoft);
            $unmatchedTrendyol = null;
            if ($storeId !== '') {
                // Mağaza bazlı Trendyol ürünleri için eşleşmeyen yaklaşık sayı (SKU'ya göre)
                $stmtT = $db->prepare("SELECT COUNT(*) FROM trendyol_urunler WHERE store_id = :sid");
                $stmtT->execute([':sid'=>$storeId]);
                $totalTr = (int)$stmtT->fetchColumn();
                $stmtM = $db->prepare("SELECT COUNT(DISTINCT trendyolgo_sku) FROM urun_entegrasyon_map WHERE (platform IS NULL OR platform='trendyolgo') AND (store_id = :sid OR store_id IS NULL)");
                $stmtM->execute([':sid'=>$storeId]);
                $matchedSku = (int)$stmtM->fetchColumn();
                $unmatchedTrendyol = max(0, $totalTr - $matchedSku);
            }
            $data['matched_count'] = $matchedCount;
            $data['unmatched_tamsoft_count'] = $unmatchedTamsoft;
            $data['unmatched_trendyol_count'] = $unmatchedTrendyol;
        } catch (\Throwable $e) { $rows = []; }
        $data['rows'] = $rows;
        try { $data['stores'] = $this->magaza->getAllCached(); } catch (\Throwable $e) { $data['stores'] = []; }
        $data['store_id'] = $storeId;
        $this->view('admin/trendyolgo/eslesmeler', $data);
    }

    public function eslesmelerData()
    {
        $draw = (int)($_GET['draw'] ?? 1);
        $start = isset($_GET['start']) ? max(0, (int)$_GET['start']) : 0;
        $length = isset($_GET['length']) ? max(10, (int)$_GET['length']) : 50;
        $searchVal = isset($_GET['search']['value']) ? trim((string)$_GET['search']['value']) : '';
        $storeId = isset($_GET['store_id']) ? (string)$_GET['store_id'] : '';
        $orderColIdx = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : 0;
        $orderDir = isset($_GET['order'][0]['dir']) ? (string)$_GET['order'][0]['dir'] : 'desc';
        $orderable = ['m.id','m.urun_kodu','u.urun_adi','m.barkod','m.store_id','m.trendyolgo_sku','m.match_confidence','m.match_source','m.manual_override','m.last_matched_at'];
        $orderBy = $orderable[$orderColIdx] ?? 'm.updated_at';
        $orderDirSql = (strtoupper($orderDir) === 'DESC') ? 'DESC' : 'ASC';
        try {
            $pdo = (new \app\Models\TamsoftStockRepo());
            $ref = new \ReflectionClass($pdo);
            $prop = $ref->getParentClass()->getProperty('db');
            $prop->setAccessible(true);
            /** @var \PDO $db */
            $db = $prop->getValue($pdo);
            $where = "WHERE (m.platform IS NULL OR m.platform = 'trendyolgo')";
            $params = [];
            if ($storeId !== '') { $where .= " AND (m.store_id = :sid OR m.store_id IS NULL)"; $params[':sid'] = $storeId; }
            if ($searchVal !== '') {
                $where .= " AND (m.urun_kodu LIKE :q OR m.barkod LIKE :q OR m.trendyolgo_sku LIKE :q OR u.urun_adi LIKE :q)";
                $params[':q'] = '%' . $searchVal . '%';
            }
            $sqlBase = "FROM urun_entegrasyon_map m LEFT JOIN tamsoft_urunler u ON u.ext_urun_id = m.urun_kodu {$where}";
            $stmtCount = $db->prepare("SELECT COUNT(*) {$sqlBase}");
            foreach ($params as $k=>$v) { $stmtCount->bindValue($k,$v); }
            $stmtCount->execute();
            $recordsFiltered = (int)$stmtCount->fetchColumn();
            $recordsTotal = (int)$db->query("SELECT COUNT(*) FROM urun_entegrasyon_map WHERE (platform IS NULL OR platform='trendyolgo')")->fetchColumn();
            $stmt = $db->prepare("SELECT m.id, m.urun_kodu, m.barkod, m.store_id, m.trendyolgo_sku, m.match_confidence, m.match_source, m.manual_override, m.last_matched_at, u.urun_adi {$sqlBase} ORDER BY {$orderBy} {$orderDirSql} LIMIT :lim OFFSET :off");
            foreach ($params as $k=>$v) { $stmt->bindValue($k,$v); }
            $stmt->bindValue(':lim', $length, \PDO::PARAM_INT);
            $stmt->bindValue(':off', $start, \PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $rows
            ]);
        } catch (\Throwable $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['draw'=>$draw,'recordsTotal'=>0,'recordsFiltered'=>0,'data'=>[]]);
        }
    }

    // Eşleşme güncelle (modal kaydet)
    public function eslesmelerUpdate()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $data = $_POST;
            if ($raw && (($_SERVER['CONTENT_TYPE'] ?? '') === 'application/json' || str_contains(($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json'))) {
                $dec = json_decode($raw, true);
                if (is_array($dec)) { $data = $dec; }
            }
            $id = (int)($data['id'] ?? 0);
            if ($id <= 0) { throw new \InvalidArgumentException('Geçersiz ID'); }
            $payload = [];
            if (array_key_exists('trendyolgo_sku', $data)) { $payload['trendyolgo_sku'] = (string)$data['trendyolgo_sku']; }
            if (array_key_exists('barkod', $data)) { $payload['barkod'] = ($data['barkod'] !== '' ? (string)$data['barkod'] : null); }
            if (array_key_exists('manual_override', $data)) { $payload['manual_override'] = !empty($data['manual_override']) ? 1 : 0; }
            if (array_key_exists('store_id', $data)) { $payload['store_id'] = ($data['store_id'] !== '' ? (string)$data['store_id'] : null); }
            $payload['updated_at'] = date('Y-m-d H:i:s');
            $map = new \app\Models\UrunEntegrasyonMap();
            $ok = $map->update($id, $payload);
            echo json_encode(['success'=>(bool)$ok]);
        } catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
    }

    public function eslesmelerDelete()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { throw new \InvalidArgumentException('Geçersiz ID'); }
            $map = new \app\Models\UrunEntegrasyonMap();
            $ok = $map->delete($id);
            echo json_encode(['success'=>(bool)$ok]);
        } catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
    }

	public function urunlerData()
	{
		$draw = (int)($_GET['draw'] ?? 1);
		$start = isset($_GET['start']) ? max(0, (int)$_GET['start']) : 0;
		$length = isset($_GET['length']) ? max(10, (int)$_GET['length']) : 50;
		$searchVal = isset($_GET['search']['value']) ? (string)$_GET['search']['value'] : (string)($_GET['q'] ?? '');
		$storeId = $_GET['store_id'] ?? '';
		$onlyUnmatched = isset($_GET['unmatched']) && (int)$_GET['unmatched'] === 1;
		$orderColIdx = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : 1;
		$orderDir = isset($_GET['order'][0]['dir']) ? (string)$_GET['order'][0]['dir'] : 'asc';
		// columns mapping: 0 img,1 title(name),2 barcode,3 category,4 brand,5 sku,6 stock,7 trendyolPrice,8 listPrice,9 status,10 description
		$orderBy = ($orderColIdx === 2) ? 'barcode' : 'title';
		$page = (int)floor($start / $length) + 1;
		$per = $length;
		$model = new \app\Models\TrendyolUrun();
		try {
			$pdo = (new \app\Models\TamsoftStockRepo());
			$ref = new \ReflectionClass($pdo);
			$prop = $ref->getParentClass()->getProperty('db');
			$prop->setAccessible(true);
			/** @var \PDO $db */
			$db = $prop->getValue($pdo);
			$where = [];
			$params = [];
			if ($storeId !== '') { $where[] = 'store_id = :sid'; $params[':sid'] = $storeId; }
			if ($searchVal !== '') {
				$where[] = '(barcode LIKE :q OR title LIKE :q OR brand_name LIKE :q OR sku LIKE :q OR stock_code LIKE :q)';
				$params[':q'] = '%'.$searchVal.'%';
			}
			$whereSql = empty($where) ? '' : ('WHERE ' . implode(' AND ', $where));
			// counts
			$stmtTot = $db->prepare('SELECT COUNT(*) FROM trendyol_urunler' . ( $storeId!=='' ? ' WHERE store_id = :sid' : '' ));
			if ($storeId !== '') { $stmtTot->bindValue(':sid', $storeId); }
			$stmtTot->execute();
			$recordsTotal = (int)$stmtTot->fetchColumn();
			$stmtFil = $db->prepare('SELECT COUNT(*) FROM trendyol_urunler '.$whereSql);
			foreach ($params as $k=>$v) { $stmtFil->bindValue($k,$v); }
			$stmtFil->execute();
			$recordsFiltered = (int)$stmtFil->fetchColumn();
			// data
			$sql = "SELECT barcode, title AS name, brand_name AS brand, category_id AS categoryId, category_name AS categoryName, image_url AS imageUrl, sku, quantity AS stock, selling_price AS trendyolPrice, original_price AS listPrice, status, description FROM trendyol_urunler {$whereSql} ORDER BY ".($orderBy==='barcode'?'barcode':'title')." ".(strtoupper($orderDir)==='DESC'?'DESC':'ASC')." LIMIT :lim OFFSET :off";
			$stmt = $db->prepare($sql);
			foreach ($params as $k=>$v) { $stmt->bindValue($k,$v); }
			$stmt->bindValue(':lim', $per, \PDO::PARAM_INT);
			$stmt->bindValue(':off', $start, \PDO::PARAM_INT);
			$stmt->execute();
			$items = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
			// matched flag
			$st = $db->prepare("SELECT 1 FROM urun_entegrasyon_map m WHERE (m.platform IS NULL OR m.platform='trendyolgo') AND ((:sku<>'' AND m.trendyolgo_sku=:sku) OR (:bc<>'' AND m.barkod=:bc)) AND (:sid='' OR m.store_id=:sid OR m.store_id IS NULL) LIMIT 1");
			foreach ($items as &$row) {
				$sku = (string)($row['sku'] ?? '');
				$bc = (string)($row['barcode'] ?? '');
				$st->execute([':sku'=>$sku, ':bc'=>$bc, ':sid'=>($storeId ?? '')]);
				$row['matched'] = $st->fetchColumn() ? 1 : 0;
			}
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode([
				'draw' => $draw,
				'recordsTotal' => $recordsTotal,
				'recordsFiltered' => $recordsFiltered,
				'data' => $items
			]);
		} catch (\Throwable $e) {
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode([
				'draw' => $draw,
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => []
			]);
		}
	}

    // Ürün içe aktarma tetikleme (önizleme ile birlikte, background simülasyonu)
    public function urunImportTrigger()
    {
		$jobId = (new TrendyolGoImportJob())->createJob();
		// mağaza bağlamı: URL parametresinden ya da ayarlardan
		$storeId = $_GET['store_id'] ?? '';
		if ($storeId === '') {
			$cfg = $this->svc->getConfig();
			if (!empty($cfg['default_store_id'])) { $storeId = $cfg['default_store_id']; }
		}
		$filters = [ 'status' => 'ACTIVE' ]; if ($storeId !== '') { $filters['storeId'] = $storeId; }
		$preview = $this->svc->getProducts('', 1, 10, [ 'filters' => $filters ]);
		(new TrendyolGoImportJob())->updateJob($jobId, [
			'status' => 'running',
			'total' => $preview['total'] ?? 0,
			'processed' => count($preview['items'] ?? []),
			'preview' => json_encode($preview['items'] ?? [], JSON_UNESCAPED_UNICODE)
		]);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode([
			'success' => true,
			'job_id' => $jobId,
			'preview' => $preview['items'] ?? [],
			'last_request' => $preview['last_request'] ?? null,
			'error' => $preview['error'] ?? null
		]);
    }

	public function stoklar()
	{
		$storeId = $_GET['store_id'] ?? '';
		$stores = [];
		try { $stores = $this->magaza->getAll(); } catch (\Throwable $e) { $stores = []; }
		$data = [ 'title' => 'Trendyol Go - Şube Stokları', 'link' => 'Trendyol Go' ];
		$data['stores'] = $stores;
		$data['store_id'] = $storeId;
		$this->view('admin/trendyolgo/stoklar', $data);
	}

	public function stoklarData()
	{
		$draw = (int)($_GET['draw'] ?? 1);
		$start = isset($_GET['start']) ? max(0, (int)$_GET['start']) : 0;
		$length = isset($_GET['length']) ? max(10, (int)$_GET['length']) : 50;
		$searchVal = isset($_GET['search']['value']) ? (string)$_GET['search']['value'] : '';
		$storeId = $_GET['store_id'] ?? '';
		$orderColIdx = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : 1;
		$orderDir = isset($_GET['order'][0]['dir']) ? (string)$_GET['order'][0]['dir'] : 'asc';
		$orderBy = ($orderColIdx === 2) ? 'barcode' : 'title';
		if ($storeId === '') {
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode([ 'draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [] ]);
			return;
		}
		$model = new \app\Models\TrendyolUrun();
		try {
			list($recordsTotal, $recordsFiltered) = $model->countByStoreTotals($storeId, $searchVal);
			$page = (int)floor($start / $length) + 1;
			$per = $length;
			$list = $model->listByStore($storeId, $searchVal, $page, $per, $orderBy, strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode([
				'draw' => $draw,
				'recordsTotal' => $recordsTotal,
				'recordsFiltered' => $recordsFiltered,
				'data' => $list['items'] ?? []
			]);
		} catch (\Throwable $e) {
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode([
				'draw' => $draw,
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => []
			]);
		}
	}

    public function urunImportStatus($id)
    {
        $job = (new TrendyolGoImportJob())->getJob((int)$id);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([ 'success' => (bool)$job, 'job' => $job ]);
    }

    public function healthCheck()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->svc->health());
    }

    public function siparisler()
    {
        $status = $_GET['status'] ?? 'ACTIVE';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per = isset($_GET['per']) ? max(10, (int)$_GET['per']) : 50;
        $dateStart = $_GET['start'] ?? null;
        $dateEnd = $_GET['end'] ?? null;
        $storeId = $_GET['store_id'] ?? null;
        $list = $this->svc->getOrders($status, $page, $per, $dateStart, $dateEnd, $storeId);
        $data = [ 'title' => 'Trendyol Go - Siparişler', 'link' => 'Trendyol Go' ];
        $data['items'] = $list['items'] ?? [];
        $data['status'] = $status;
        $data['page'] = $list['page'] ?? $page;
        $data['per'] = $list['per_page'] ?? $per;
        $data['total'] = $list['total'] ?? 0;
        $data['last_request'] = $list['last_request'] ?? null;
        $this->view('admin/trendyolgo/siparisler', $data);
    }

    public function iptaller()
    {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per = isset($_GET['per']) ? max(10, (int)$_GET['per']) : 50;
        $dateStart = $_GET['start'] ?? null;
        $dateEnd = $_GET['end'] ?? null;
        $storeId = $_GET['store_id'] ?? null;
        $list = $this->svc->getCancelledOrders($page, $per, $dateStart, $dateEnd, $storeId);
        $data = [ 'title' => 'Trendyol Go - İptaller', 'link' => 'Trendyol Go' ];
        $data['items'] = $list['items'] ?? [];
        $data['page'] = $list['page'] ?? $page;
        $data['per'] = $list['per_page'] ?? $per;
        $data['total'] = $list['total'] ?? 0;
        $data['last_request'] = $list['last_request'] ?? null;
        $this->view('admin/trendyolgo/iptaller', $data);
    }

    public function siparisDurumGuncelle()
    {
        $orderId = trim($_POST['order_id'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $res = $this->svc->updateOrderStatus($orderId, $status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($res);
    }

    public function loglar()
    {
        $logModel = new \app\Models\TrendyolGoLog();
        $logs = $logModel->latest(100);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([ 'success' => true, 'logs' => $logs ]);
    }

    public function logTemizle()
    {
        $logModel = new \app\Models\TrendyolGoLog();
        $ok = $logModel->clear();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([ 'success' => (bool)$ok ]);
    }

	public function urunleriIceriAl()
	{
		$lock = new \app\Models\TrendyolGoLock();
		if (!$lock->acquire('import_store', 600, 'admin')) {
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(['success' => false, 'error' => 'import_in_progress']);
			return;
		}
		$storeId = $_POST['store_id'] ?? ($_GET['store_id'] ?? '');
		if ($storeId === '') {
			$cfg = $this->svc->getConfig();
			if (!empty($cfg['default_store_id'])) { $storeId = $cfg['default_store_id']; }
		}
		$per = isset($_POST['per']) ? max(50, (int)$_POST['per']) : 200;
		$maxPages = isset($_POST['max_pages']) ? max(1, (int)$_POST['max_pages']) : 100;
		$inserted = 0; $updated = 0; $errors = 0; $pagesFetched = 0; $fetchedTotal = 0; $expectedTotal = null;
		$model = new \app\Models\TrendyolUrun();
		$magModel = new \app\Models\TrendyolGoMagaza();
		for ($page = 1; $page <= $maxPages; $page++) {
			$res = $this->svc->getProducts('', $page, $per, [ 'filters' => [ 'storeId' => $storeId ] ]);
			$pagesFetched++;
			$items = $res['items'] ?? [];
			if ($expectedTotal === null) { $expectedTotal = (int)($res['total'] ?? 0); }
			if (empty($items)) { break; }
			foreach ($items as $p) {
				$ok = $model->upsertByStoreBarcode((string)$storeId, (string)($p['barcode'] ?? ''), [
					'supplier_id' => $p['supplierId'] ?? null,
					'title' => $p['name'] ?? null,
					'description' => $p['description'] ?? null,
					'stockCode' => $p['stockCode'] ?? ($p['code'] ?? null),
					'sku' => $p['sku'] ?? ($p['code'] ?? null),
					'brand_id' => $p['brand_id'] ?? null,
					'brand' => $p['brand'] ?? null,
					'category_id' => $p['categoryId'] ?? null,
					'category_name' => $p['categoryName'] ?? null,
					'stock' => $p['stock'] ?? null,
					'listPrice' => $p['listPrice'] ?? null,
					'sellingPrice' => $p['trendyolPrice'] ?? null,
					'price' => $p['price'] ?? null,
					'questionable' => null,
					'status' => (($p['stock'] ?? 0) > 0 ? 'ACTIVE' : 'PASSIVE'),
					'onSale' => (($p['stock'] ?? 0) > 0 ? 1 : 0),
					'imageUrl' => $p['imageUrl'] ?? null
				]);
				if ($ok) { $inserted++; } else { $errors++; }
			}
			$fetchedTotal += count($items);
			if ($expectedTotal !== null && $fetchedTotal >= $expectedTotal) { break; }
		}
		$magModel->touchSync((string)$storeId, true);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode([
			'success' => true,
			'store_id' => $storeId,
			'pages_fetched' => $pagesFetched,
			'inserted_or_updated' => $inserted,
			'errors' => $errors
		]);
		$lock->release('import_store');
	}

	public function cronImportAll()
	{
		$lock = new \app\Models\TrendyolGoLock();
		if (!$lock->acquire('import_all', 1800, 'cron')) {
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(['success' => false, 'error' => 'import_in_progress']);
			return;
		}
		$per = isset($_POST['per']) ? max(50, (int)$_POST['per']) : 200;
		$maxPages = isset($_POST['max_pages']) ? max(1, (int)$_POST['max_pages']) : 100;
		$summary = [];
		$model = new \app\Models\TrendyolUrun();
		$stores = $this->magaza->getAll();
		foreach ($stores as $s) {
			$sid = (string)($s['store_id'] ?? '');
			if ($sid === '') { continue; }
			$inserted = 0; $errors = 0; $pages = 0; $fetched = 0; $expected = null;
			for ($page = 1; $page <= $maxPages; $page++) {
				$res = $this->svc->getProducts('', $page, $per, [ 'filters' => [ 'storeId' => $sid ] ]);
				$pages++;
				$items = $res['items'] ?? [];
				if ($expected === null) { $expected = (int)($res['total'] ?? 0); }
				if (empty($items)) { break; }
				foreach ($items as $p) {
					$ok = $model->upsertByStoreBarcode($sid, (string)($p['barcode'] ?? ''), [
						'supplier_id' => $p['supplierId'] ?? null,
						'title' => $p['name'] ?? null,
						'description' => $p['description'] ?? null,
						'stockCode' => $p['stockCode'] ?? ($p['code'] ?? null),
						'sku' => $p['sku'] ?? ($p['code'] ?? null),
						'brand_id' => $p['brand_id'] ?? ($p['brand']['id'] ?? null),
						'brand' => $p['brand'] ?? (is_array($p['brand'] ?? null) ? ($p['brand']['name'] ?? null) : null),
						'category_id' => $p['categoryId'] ?? ($p['category']['id'] ?? null),
						'category_name' => $p['categoryName'] ?? ($p['category']['name'] ?? null),
						'stock' => $p['stock'] ?? ($p['quantity'] ?? null),
						'listPrice' => $p['listPrice'] ?? ($p['originalPrice'] ?? null),
						'sellingPrice' => $p['trendyolPrice'] ?? ($p['sellingPrice'] ?? null),
						'price' => $p['price'] ?? null,
						'status' => (($p['stock'] ?? 0) > 0 ? 'ACTIVE' : 'PASSIVE'),
						'onSale' => (($p['stock'] ?? 0) > 0 ? 1 : 0),
						'imageUrl' => $p['imageUrl'] ?? null
					]);
					if ($ok) { $inserted++; } else { $errors++; }
				}
				$fetched += count($items);
				if ($expected !== null && $fetched >= $expected) { break; }
			}
			$this->magaza->touchSync($sid, true);
			$summary[] = [ 'store_id' => $sid, 'name' => $s['magaza_adi'] ?? '', 'pages' => $pages, 'inserted' => $inserted, 'errors' => $errors, 'expected_total' => $expected, 'fetched' => $fetched ];
		}
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode([ 'success' => true, 'summary' => $summary ]);
		$lock->release('import_all');
	}

	public function diagnostic()
	{
		$q = $_GET['q'] ?? '';
		$storeId = $_GET['store_id'] ?? null;
		$per = isset($_GET['per']) ? max(1, (int)$_GET['per']) : 5;
		$health = $this->svc->health();
		$cats = $this->svc->getCategories(1, 1);
		$prods = $this->svc->getProducts($q, 1, $per);
		$orders = $this->svc->getOrders('ACTIVE', 1, $per, null, null, $storeId);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode([
			'success' => true,
			'health' => $health,
			'categories' => [
				'total' => $cats['total'] ?? 0,
				'items_preview' => array_slice($cats['items'] ?? [], 0, 1),
				'last_request' => $cats['last_request'] ?? null
			],
			'products' => [
				'total' => $prods['total'] ?? 0,
				'items_preview' => array_slice($prods['items'] ?? [], 0, min(3, $per)),
				'last_request' => $prods['last_request'] ?? null,
				'error' => $prods['error'] ?? null
			],
			'orders' => [
				'total' => $orders['total'] ?? 0,
				'items_preview' => array_slice($orders['items'] ?? [], 0, min(3, $per)),
				'last_request' => $orders['last_request'] ?? null
			]
		], JSON_UNESCAPED_UNICODE);
	}

	public function diagnosticStores()
	{
		$per = isset($_GET['per']) ? max(1, (int)$_GET['per']) : 1;
		$magazaModel = new \app\Models\TrendyolGoMagaza();
		$stores = $magazaModel->getAll();
		$results = [];
		foreach ($stores as $s) {
			$storeId = $s['store_id'] ?? '';
			if ($storeId === '') { continue; }
			$res = $this->svc->getProducts('', 1, $per, [ 'filters' => [ 'storeId' => $storeId, 'status' => 'ACTIVE' ] ]);
			$results[] = [
				'store_id' => $storeId,
				'magaza_adi' => $s['magaza_adi'] ?? '',
				'count' => count($res['items'] ?? []),
				'last_request' => $res['last_request'] ?? null,
				'attempts' => $res['attempts'] ?? [],
				'first_item' => ($res['items'][0] ?? null)
			];
		}
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode([ 'success' => true, 'per' => $per, 'stores_tested' => count($results), 'results' => $results ], JSON_UNESCAPED_UNICODE);
	}

     public function magazalar()
     {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Güncelleme mi ekleme mi?
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id > 0) {
                $ok = $this->magaza->updateById($id, [
                    'magaza_adi' => $_POST['magaza_adi'] ?? '',
                    'store_id' => $_POST['store_id'] ?? '',
                    'adres' => $_POST['adres'] ?? '',
                    'telefon' => $_POST['telefon'] ?? ''
                ]);
                $_SESSION['alert_message'] = [ 'text' => $ok ? 'Mağaza güncellendi' : 'Güncelleme hatası', 'icon' => $ok ? 'success' : 'error', 'confirmButtonText' => 'Tamam' ];
            } else {
                $ok = $this->magaza->add([
                    'magaza_adi' => $_POST['magaza_adi'] ?? '',
                    'store_id' => $_POST['store_id'] ?? '',
                    'adres' => $_POST['adres'] ?? '',
                    'telefon' => $_POST['telefon'] ?? ''
                ]);
                $_SESSION['alert_message'] = [ 'text' => $ok ? 'Mağaza eklendi' : 'Kayıt hatası (mağaza adı ve store id zorunlu)', 'icon' => $ok ? 'success' : 'error', 'confirmButtonText' => 'Tamam' ];
            }
            $_SESSION['alert_message'] = [ 'text' => $ok ? 'Mağaza eklendi' : 'Kayıt hatası (mağaza adı ve store id zorunlu)', 'icon' => $ok ? 'success' : 'error', 'confirmButtonText' => 'Tamam' ];
            header('Location: /admin/trendyolgo/magazalar');
            exit;
        }
        $data = [ 'title' => 'Trendyol Go - Mağazalar', 'link' => 'Trendyol Go' ];
        $data['magazalar'] = $this->magaza->getAll();
        $this->view('admin/trendyolgo/magazalar', $data);
     }

     public function magazaSil($id)
     {
        $id = (int)$id;
        $ok = $this->magaza->deleteById($id);
        $_SESSION['alert_message'] = [ 'text' => $ok ? 'Mağaza silindi' : 'Silme hatası', 'icon' => $ok ? 'success' : 'error', 'confirmButtonText' => 'Tamam' ];
        header('Location: /admin/trendyolgo/magazalar');
        exit;
     }
}


