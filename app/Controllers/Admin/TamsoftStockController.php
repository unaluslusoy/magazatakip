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
			if (isset($_GET['order'][0]['column'])) {
				$colIdx = (int)$_GET['order'][0]['column'];
				$dir = strtoupper($_GET['order'][0]['dir'] ?? 'ASC');
				$map = [0=>'u.ext_urun_id',1=>'u.barkod',2=>'u.urun_adi',5=>'u.fiyat',6=>'u.miktari'];
				if (isset($map[$colIdx])) { $orderBy = $map[$colIdx]; }
				$orderDir = $dir === 'DESC' ? 'DESC' : 'ASC';
			}
			$hasIntegration = isset($_GET['has_integration']) && $_GET['has_integration'] !== '' ? (int)$_GET['has_integration'] : null;
			$onlyPositive = isset($_GET['only_positive']) && $_GET['only_positive'] !== '' ? (int)$_GET['only_positive'] : null;
			$depoId = isset($_GET['depo_id']) && $_GET['depo_id'] !== '' ? (int)$_GET['depo_id'] : null;
			$result = $this->svc->listProductsServerSide(
				$start, $length, is_string($search)?$search:null, $orderBy, $orderDir,
				in_array($filter,['IPT','BK'])?$filter:null, $hasIntegration, $onlyPositive, $depoId
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
}



