<?php

namespace app\Controllers\Admin;

use core\Controller;
use app\Middleware\AdminMiddleware;
use app\Services\TamsoftStockService;

class TamsoftStockController extends Controller
{
	private TamsoftStockService $svc;

	public function __construct()
	{
		AdminMiddleware::handle();
		$this->svc = new TamsoftStockService();
	}

	public function ayarlar()
	{
		$data = [ 'title' => 'Tamsoft Stok Ayarları', 'config' => $this->svc->getConfig() ];
		$this->view('admin/tamsoft_stok/ayarlar', $data);
	}

	public function ayarlarPost()
	{
		header('Content-Type: application/json; charset=utf-8');
		try { echo json_encode(['success' => $this->svc->saveConfig($_POST)]); }
		catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
	}

	public function refresh()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			// Uzun sürebilir: output buffering kapat, script limitlerini yükselt
			ignore_user_abort(true);
			set_time_limit(0);
			if (function_exists('apache_setenv')) { @apache_setenv('no-gzip', '1'); }
			$cfg = $this->svc->getConfig();
			$date = $_POST['tarih'] ?? ($cfg['default_date'] ?? '1900-01-01');
			$depo = isset($_POST['depoid']) && $_POST['depoid'] !== '' ? (int)$_POST['depoid'] : (isset($cfg['default_depo_id']) ? (int)$cfg['default_depo_id'] : null);
			// Depo bazlı senkron aktifse, tüm depoları dolaşmak için depo'yu null'a zorla
			if (!empty($cfg['sync_by_depot'])) { $depo = null; }
			$onlyPos = isset($_POST['only_positive']) ? (bool)$_POST['only_positive'] : (bool)($cfg['default_only_positive'] ?? 1);
			$lastBarcode = isset($_POST['last_barcode_only']) ? (bool)$_POST['last_barcode_only'] : (bool)($cfg['default_last_barcode_only'] ?? 0);
			$onlyEcom = isset($_POST['only_ecommerce']) ? (bool)$_POST['only_ecommerce'] : (bool)($cfg['default_only_ecommerce'] ?? 0);
			$res = $this->svc->refreshStocks($date, $depo, $onlyPos, $lastBarcode, $onlyEcom);
			echo json_encode($res);
		} catch (\Throwable $e) {
			echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
		}
	}

	public function index()
	{
		$data = [ 'title' => 'Tamsoft ERP - Anasayfa', 'config' => $this->svc->getConfig() ];
		$this->view('admin/tamsoft_stok/index', $data);
	}

	public function dashboardSummary()
	{
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($this->svc->dashboardSummary());
	}

	public function inventory()
	{
		$data = [ 'title' => 'Tamsoft ERP - Ürün Envanter' ];
		$this->view('admin/tamsoft_stok/envanter', $data);
	}

	public function inventoryData()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			$filter = isset($_GET['filter']) ? strtoupper(trim((string)$_GET['filter'])) : '';
			$start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
			$length = isset($_GET['length']) ? (int)$_GET['length'] : 50;
			$search = $_GET['search']['value'] ?? ($_GET['search'] ?? null);
			$orderBy = 'u.urun_adi';
			$orderDir = 'ASC';
			$orderDepotId = null;
			if (isset($_GET['order'][0]['column'])) {
				$colIdx = (int)$_GET['order'][0]['column'];
				$dir = strtoupper($_GET['order'][0]['dir'] ?? 'ASC');
				$map = [0=>'u.ext_urun_id',1=>'u.barkod',2=>'u.urun_adi',5=>'u.fiyat',6=>'u.miktari'];
				if (isset($map[$colIdx])) { $orderBy = $map[$colIdx]; }
				$orderDir = $dir === 'DESC' ? 'DESC' : 'ASC';
				// Dinamik depo kolonları: columns[x][data] == dp_<id>
				if (isset($_GET['columns'][$colIdx]['data'])) {
					$cd = (string)$_GET['columns'][$colIdx]['data'];
					if (str_starts_with($cd, 'dp_')) { $orderDepotId = (int)substr($cd, 3); }
				}
			}
			$hasIntegration = isset($_GET['has_integration']) && $_GET['has_integration'] !== '' ? (int)$_GET['has_integration'] : null;
			$onlyPositive = isset($_GET['only_positive']) && $_GET['only_positive'] !== '' ? (int)$_GET['only_positive'] : null;
			$depoId = isset($_GET['depo_id']) && $_GET['depo_id'] !== '' ? (int)$_GET['depo_id'] : null;
			$result = $this->svc->listProductsServerSide(
				$start, $length, is_string($search)?$search:null, $orderBy, $orderDir,
				in_array($filter,['IPT','BK'])?$filter:null, $hasIntegration, $onlyPositive, $depoId, $orderDepotId
			);
			echo json_encode([
				'draw' => isset($_GET['draw']) ? (int)$_GET['draw'] : 1,
				'recordsTotal' => $result['total'],
				'recordsFiltered' => $result['filtered'],
				'data' => $result['rows'],
				'depots' => $result['depots'] ?? [],
				'success' => true
			]);
		} catch (\Throwable $e) {
			echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
		}
	}

	// Envanter export (tüm filtreleri uygulayıp geniş limit)
	public function inventoryExport()
	{
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="envanter.csv"');
		$start = 0; $length = 200000; // geniş limit
		$search = $_GET['search'] ?? null;
		$orderBy = 'u.urun_adi'; $orderDir = 'ASC';
		$filter = isset($_GET['filter']) ? strtoupper(trim((string)$_GET['filter'])) : '';
		$hasIntegration = isset($_GET['has_integration']) && $_GET['has_integration'] !== '' ? (int)$_GET['has_integration'] : null;
		$onlyPositive = isset($_GET['only_positive']) && $_GET['only_positive'] !== '' ? (int)$_GET['only_positive'] : null;
		$depoId = isset($_GET['depo_id']) && $_GET['depo_id'] !== '' ? (int)$_GET['depo_id'] : null;
		$res = $this->svc->listProductsServerSide($start, $length, is_string($search)?$search:null, $orderBy, $orderDir, in_array($filter,['IPT','BK'])?$filter:null, $hasIntegration, $onlyPositive, $depoId);
		$out = fopen('php://output', 'w');
		fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
		fputcsv($out, ['Kod','Barkod','Ürün Adı','Birim','KDV','Fiyat','Miktar'], ';');
		foreach (($res['rows'] ?? []) as $row) {
			fputcsv($out, [
				$row['ext_urun_id'] ?? '',
				$row['barkod'] ?? '',
				$row['urun_adi'] ?? '',
				$row['birim'] ?? '',
				$row['kdv'] ?? '',
				$row['fiyat'] ?? '',
				$row['miktari'] ?? '',
			], ';');
		}
		fclose($out);
		exit;
	}

	public function tokenTest()
	{
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($this->svc->testToken());
	}

	public function depolarSync()
	{
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($this->svc->syncDepots());
	}

	public function depolarPreview()
	{
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($this->svc->previewDepots());
	}

	public function stokPreview()
	{
		header('Content-Type: application/json; charset=utf-8');
		$cfg = $this->svc->getConfig();
		$date = $_POST['tarih'] ?? ($cfg['default_date'] ?? '1900-01-01');
		$depo = isset($_POST['depoid']) && $_POST['depoid'] !== '' ? (int)$_POST['depoid'] : null;
		$onlyPos = isset($_POST['only_positive']) ? (bool)$_POST['only_positive'] : null;
		$lastBarcode = isset($_POST['last_barcode_only']) ? (bool)$_POST['last_barcode_only'] : null;
		$onlyEcom = isset($_POST['only_ecommerce']) ? (bool)$_POST['only_ecommerce'] : null;
		echo json_encode($this->svc->previewStocks($date, $depo, $onlyPos, $lastBarcode, $onlyEcom));
	}

	// Manuel fiyat güncelleme tetikleme (yalnızca fiyat çekip günceller)
	public function priceRefresh()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			$cfg = $this->svc->getConfig();
			$date = $_POST['tarih'] ?? ($cfg['default_date'] ?? '1900-01-01');
			$depo = isset($_POST['depoid']) && $_POST['depoid'] !== '' ? (int)$_POST['depoid'] : null;
			$res = $this->svc->refreshPricesOnly($date, $depo);
			echo json_encode(['success'=>true] + $res);
		} catch (\Throwable $e) {
			echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
		}
	}

	public function mapSave()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			$id = (int)($_POST['id'] ?? 0);
			$field = (string)($_POST['field'] ?? '');
			$value = (string)($_POST['value'] ?? '');
			if ($id <= 0 || !in_array($field, ['trendyolgo_sku','getir_code','yemeksepeti_code'])) {
				throw new \InvalidArgumentException('Geçersiz veri');
			}
			// Ürün bilgisi çek
			$db = $this->svc->repoDb();
			$stmt = $db->prepare('SELECT ext_urun_id, barkod FROM tamsoft_urunler WHERE id=:id');
			$stmt->execute([':id'=>$id]);
			$u = $stmt->fetch(\PDO::FETCH_ASSOC);
			if (!$u) { throw new \RuntimeException('Ürün bulunamadı'); }
			// Upsert map
			$map = new \app\Models\UrunEntegrasyonMap();
			$payload = [ 'urun_kodu' => $u['ext_urun_id'] ?? null, 'barkod' => $u['barkod'] ?? null, $field => $value ];
			$ok = $map->upsert($payload);
			echo json_encode(['success'=>$ok]);
		} catch (\Throwable $e) {
			echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
		}
	}

	// Cron uçları — panelden tetiklenebilir; sistem cronuna da eklenebilir
	public function cronStockSync()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			ignore_user_abort(true);
			set_time_limit(0);
			$res = $this->svc->intervalStockSync();
			echo json_encode(['success'=>true] + $res);
		} catch (\Throwable $e) {
			echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
		}
	}

	// Yeni: Eticaret stok listesinden depo bazlı sadece miktar güncelleme
	public function cronEcommerceStock()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			ignore_user_abort(true);
			set_time_limit(0);
			$date = $_POST['tarih'] ?? null;
			$depo = isset($_POST['depoid']) && $_POST['depoid'] !== '' ? (int)$_POST['depoid'] : null;
			$res = $this->svc->refreshDepotQtyFromEcommerce($date, $depo);
			echo json_encode(['success'=>true] + $res);
		} catch (\Throwable $e) {
			echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
		}
	}

	public function cronMonthlyMaster()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			ignore_user_abort(true);
			set_time_limit(0);
			$res = $this->svc->monthlyProductMasterSync();
			echo json_encode(['success'=>true] + $res);
		} catch (\Throwable $e) {
			echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
		}
	}

	public function depolarPage()
	{
		$data = [ 'title' => 'Tamsoft ERP - Depolar' ];
		$this->view('admin/tamsoft_stok/depolar', $data);
	}

	public function depolarData()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			$db = $this->svc->repoDb();
			$stmt = $db->query('SELECT depo_id AS id, depo_adi, aktif FROM tamsoft_depolar ORDER BY depo_id ASC');
			$rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
			echo json_encode(['success'=>true,'rows'=>$rows]);
		} catch (\Throwable $e) {
			echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
		}
	}

	public function depolarSetActive()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			$depoId = (int)($_POST['id'] ?? 0);
			$active = (int)($_POST['aktif'] ?? 1) === 1;
			$repo = new \app\Models\TamsoftStockRepo();
			$ok = $repo->setDepotActive($depoId, $active);
			echo json_encode(['success'=>$ok]);
		} catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
	}

	public function import()
	{
		$data = [ 'title' => 'Tamsoft ERP - Import (Manuel)' ];
		$this->view('admin/tamsoft_stok/import', $data);
	}

	public function importRun()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			if (!isset($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
				throw new \RuntimeException('Dosya yüklenmedi');
			}
			$path = $_FILES['file']['tmp_name'];
			$name = $_FILES['file']['name'] ?? 'upload';
			$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
			$rows = [];
			if (in_array($ext, ['csv'])) {
				$fh = fopen($path, 'r');
				$headers = null;
				while(($r = fgetcsv($fh, 0, ",")) !== false){
					if ($headers === null) { $headers = $r; continue; }
					$rows[] = array_combine($headers, $r);
				}
				fclose($fh);
			} elseif (in_array($ext, ['json'])) {
				$rows = json_decode(file_get_contents($path), true) ?: [];
			} elseif (in_array($ext, ['xml'])) {
				$xml = simplexml_load_file($path, 'SimpleXMLElement', LIBXML_NOCDATA);
				$json = json_decode(json_encode($xml), true);
				$rows = isset($json['row']) ? ((isset($json['row'][0]) && is_array($json['row'][0])) ? $json['row'] : [$json['row']]) : (is_array($json) ? $json : []);
			} elseif (in_array($ext, ['txt'])) {
				$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				$headers = null;
				foreach ($lines as $line) {
					$parts = preg_split('/[\t;|]/', $line);
					if ($headers === null) { $headers = $parts; continue; }
					$rows[] = array_combine($headers, $parts);
				}
			} elseif (in_array($ext, ['xlsx','xls'])) {
				if (!class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')) { throw new \RuntimeException('Excel desteği yok: phpoffice/phpspreadsheet kurulu değil'); }
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
				$spreadsheet = $reader->load($path);
				$sheet = $spreadsheet->getActiveSheet();
				$headers = null;
				foreach ($sheet->toArray(null, true, true, true) as $row) {
					$vals = array_values($row);
					if ($headers === null) { $headers = $vals; continue; }
					$rows[] = array_combine($headers, $vals);
				}
			} else {
				throw new \RuntimeException('Desteklenmeyen dosya türü');
			}
			$summary = $this->svc->importRows($rows);
			echo json_encode(['success'=>true] + $summary);
		} catch (\Throwable $e) {
			echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
		}
	}

	// Job Manager — panelden cron kontrolü
	public function jobsPage()
	{
		$data = [ 'title' => 'Tamsoft ERP - Job Manager' ];
		$this->view('admin/tamsoft_stok/jobs', $data);
	}

	public function jobsList()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			$db = $this->svc->repoDb();
			// Varsayılan işler yoksa ekle
			$db->exec("INSERT IGNORE INTO job_schedule(job_key, description, cron_expr, enabled, created_at, updated_at) VALUES
				('tamsoft_depo_listesi','Depo Listesi Senkronu','0 */6 * * *',1,NOW(),NOW()),
				('tamsoft_stock_sync','StokListesi (fiyat+miktar) senkronu','*/30 * * * *',1,NOW(),NOW()),
				('tamsoft_ecommerce_stock','EticaretStokListesi (depo bazlı miktar)','*/20 * * * *',1,NOW(),NOW()),
				('tamsoft_monthly_master','Aylık Master Ürün Senkronu','0 3 1 * *',1,NOW(),NOW()),
				('tamsoft_price_refresh','Fiyat Güncelleme (ayda 3)','0 3 1,11,21 * *',1,NOW(),NOW())");
			$rows = $db->query("SELECT job_key, description, cron_expr, enabled, last_run_at, next_run_at FROM job_schedule ORDER BY job_key ASC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
			echo json_encode(['success'=>true, 'rows'=>$rows]);
		} catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
	}

	// Yeni job ekle/sil
	public function jobsCreate()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			$key = trim((string)($_POST['job_key'] ?? ''));
			$desc = trim((string)($_POST['description'] ?? ''));
			$cron = trim((string)($_POST['cron_expr'] ?? ''));
			if ($key === '' || $cron === '') { throw new \InvalidArgumentException('job_key ve cron_expr zorunlu'); }
			// cron doğrulaması ve next_run_at
			$next = \app\Utils\CronHelper::nextRunAt($cron, new \DateTime('now'));
			if ($next === null) { throw new \InvalidArgumentException('Geçersiz cron ifadesi'); }
			$db = $this->svc->repoDb();
			$stmt = $db->prepare("INSERT INTO job_schedule(job_key, description, cron_expr, enabled, next_run_at, created_at, updated_at) VALUES(:k,:d,:c,1,:n,NOW(),NOW())");
			$ok = $stmt->execute([':k'=>$key, ':d'=>$desc ?: null, ':c'=>$cron, ':n'=>$next]);
			echo json_encode(['success'=>$ok]);
		} catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
	}

	public function jobsDelete()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			$key = trim((string)($_POST['job_key'] ?? ''));
			if ($key === '') { throw new \InvalidArgumentException('job_key zorunlu'); }
			$db = $this->svc->repoDb();
			$ok = $db->prepare("DELETE FROM job_schedule WHERE job_key=:k")->execute([':k'=>$key]);
			echo json_encode(['success'=>$ok]);
		} catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
	}

	public function jobsToggle()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			$key = (string)($_POST['job_key'] ?? '');
			$enabled = (int)($_POST['enabled'] ?? 1) === 1 ? 1 : 0;
			if ($key==='') { throw new \InvalidArgumentException('job_key gerekli'); }
			$db = $this->svc->repoDb();
			$stmt = $db->prepare("UPDATE job_schedule SET enabled=:e, updated_at=NOW() WHERE job_key=:k");
			$ok = $stmt->execute([':e'=>$enabled, ':k'=>$key]);
			echo json_encode(['success'=>$ok]);
		} catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
	}

	public function jobsRun()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			$key = (string)($_POST['job_key'] ?? '');
			if ($key==='') { throw new \InvalidArgumentException('job_key gerekli'); }
			$db = $this->svc->repoDb();
			// Basit lock: süresi geçmişse düşer
			$expires = date('Y-m-d H:i:s', time() + 3600);
			$host = gethostname() ?: 'web';
			$db->beginTransaction();
			$locked = false;
			try {
				$stmt = $db->prepare("SELECT expires_at FROM job_lock WHERE job_key=:k FOR UPDATE");
				$stmt->execute([':k'=>$key]);
				$row = $stmt->fetch(\PDO::FETCH_ASSOC);
				if ($row && strtotime((string)$row['expires_at']) > time()) {
					$locked = true;
				} else {
					// al veya yenile
					$db->prepare("REPLACE INTO job_lock(job_key, locked_by, locked_at, expires_at) VALUES(:k,:by,NOW(),:ex)")
						->execute([':k'=>$key, ':by'=>$host, ':ex'=>$expires]);
				}
				$db->commit();
			} catch (\Throwable $e) { $db->rollBack(); throw $e; }
			if ($locked) { echo json_encode(['success'=>false,'error'=>'locked']); return; }
			$start = date('Y-m-d H:i:s');
			$status = 'success'; $message = null; $payload = [];
			try {
				// Queue üzerinden asenkron çalıştır (enqueue)
				$qr = new \app\Models\QueueRepo();
				$qr->enqueue($key, []);
				$payload = ['enqueued' => true];
			} catch (\Throwable $e) { $status='error'; $message=$e->getMessage(); }
			$finish = date('Y-m-d H:i:s');
			// Kayıtlar
			try {
				$db->prepare("INSERT INTO job_runs(job_key, started_at, finished_at, status, message) VALUES(:k,:s,:f,:st,:m)")
					->execute([':k'=>$key, ':s'=>$start, ':f'=>$finish, ':st'=>$status, ':m'=>($message ?? json_encode($payload, JSON_UNESCAPED_UNICODE))]);
				$next = null;
				try { $next = \app\Utils\CronHelper::nextRunAt((string)($db->query("SELECT cron_expr FROM job_schedule WHERE job_key='".$key."'")->fetchColumn() ?: ''), new \DateTime($finish)); } catch (\Throwable $e) { $next = null; }
				$db->prepare("UPDATE job_schedule SET last_run_at=:t, next_run_at=:n, updated_at=NOW() WHERE job_key=:k")
					->execute([':t'=>$finish, ':n'=>$next, ':k'=>$key]);
				$db->prepare("DELETE FROM job_lock WHERE job_key=:k")->execute([':k'=>$key]);
			} catch (\Throwable $e) { /* yut */ }
			echo json_encode(['success'=>($status==='success')] + $payload);
		} catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
	}

	public function jobsRuns()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			$key = (string)($_GET['job_key'] ?? '');
			$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 20;
			$db = $this->svc->repoDb();
			$params = []; $sql = "SELECT job_key, started_at, finished_at, status, message FROM job_runs";
			if ($key !== '') { $sql .= " WHERE job_key = :k"; $params[':k'] = $key; }
			$sql .= " ORDER BY started_at DESC LIMIT :lim";
			$stmt = $db->prepare($sql);
			foreach ($params as $k=>$v) { $stmt->bindValue($k, $v); }
			$stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
			$stmt->execute();
			$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
			echo json_encode(['success'=>true,'rows'=>$rows]);
		} catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
	}

	public function jobsLockRelease()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			$key = (string)($_POST['job_key'] ?? '');
			if ($key==='') { throw new \InvalidArgumentException('job_key gerekli'); }
			$db = $this->svc->repoDb();
			$ok = $db->prepare("DELETE FROM job_lock WHERE job_key=:k")->execute([':k'=>$key]);
			echo json_encode(['success'=>$ok]);
		} catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
	}

	// Queue/Due özetleri
	public function queueSummary()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			$db = $this->svc->repoDb();
			$counts = [
				'pending' => (int)($db->query("SELECT COUNT(*) FROM job_queue WHERE status='pending'")->fetchColumn() ?: 0),
				'reserved'=> (int)($db->query("SELECT COUNT(*) FROM job_queue WHERE status='reserved'")->fetchColumn() ?: 0),
				'failed'  => (int)($db->query("SELECT COUNT(*) FROM job_queue WHERE status='failed' AND updated_at >= (NOW() - INTERVAL 1 DAY)")->fetchColumn() ?: 0),
			];
			$due = (int)($db->query("SELECT COUNT(*) FROM job_schedule WHERE enabled=1 AND (next_run_at IS NULL OR next_run_at <= NOW())")->fetchColumn() ?: 0);
			$lastRuns = $db->query("SELECT job_key, started_at, finished_at, status FROM job_runs ORDER BY started_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
			echo json_encode(['success'=>true,'queue'=>$counts,'due_jobs'=>$due,'last_runs'=>$lastRuns]);
		} catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
	}

	public function jobsScheduleNow()
	{
		header('Content-Type: application/json; charset=utf-8');
		try {
			$db = $this->svc->repoDb();
			$qr = new \app\Models\QueueRepo();
			$now = date('Y-m-d H:i:00');
			$stmt = $db->prepare("SELECT job_key, cron_expr FROM job_schedule WHERE enabled=1 AND (next_run_at IS NULL OR next_run_at <= :now)");
			$stmt->execute([':now'=>$now]);
			$due = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
			$enq = 0; $updated = 0;
			foreach ($due as $row) {
				$qr->enqueue((string)$row['job_key'], []);
				$enq++;
				try {
					$next = \app\Utils\CronHelper::nextRunAt((string)$row['cron_expr'], new \DateTime($now));
					$db->prepare("UPDATE job_schedule SET next_run_at=:n, updated_at=NOW() WHERE job_key=:k")
						->execute([':n'=>$next, ':k'=>(string)$row['job_key']]);
					$updated++;
				} catch (\Throwable $e) {}
			}
			echo json_encode(['success'=>true,'enqueued'=>$enq,'updated'=>$updated]);
		} catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
	}
}



