<?php

namespace app\Controllers\Admin;

use core\Controller;
use app\Middleware\AdminMiddleware;
use app\Services\ProductMatchService;

class ProductMatchController extends Controller
{
    public function __construct()
    {
        AdminMiddleware::handle();
    }

    public function page()
    {
        $data = [ 'title' => 'Ürün Eşleştirme (Hızlı Marketler)' ];
        // Mağaza listesini TrendyolGoMagaza'dan getir ve view'a ilet
        try {
            $mag = new \app\Models\TrendyolGoMagaza();
            $data['stores'] = $mag->getAll();
        } catch (\Throwable $e) {
            $data['stores'] = [];
        }
        $this->view('admin/match/index', $data);
    }

    public function storesPage()
    {
        $data = [ 'title' => 'Mağaza - Depo Eşleştirme' ];
        $this->view('admin/match/stores', $data);
    }

    public function storesList()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $platform = isset($_GET['platform']) ? (string)$_GET['platform'] : 'trendyolgo';
            $map = new \app\Models\StoreDepotMap();
            $rows = $map->listMappings($platform);
            // Depo listesi
            $repo = new \app\Models\TamsoftStockRepo();
            $depots = $repo->getActiveDepots();
            // Mağaza listesi (Trendyol GO)
            $mag = new \app\Models\TrendyolGoMagaza();
            $stores = $mag->getAll();
            echo json_encode(['success'=>true,'rows'=>$rows,'depots'=>$depots,'stores'=>$stores]);
        } catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
    }

    public function storesSave()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $platform = (string)($_POST['platform'] ?? 'trendyolgo');
            $storeId = (string)($_POST['store_id'] ?? '');
            $depoId = isset($_POST['depo_id']) ? (int)$_POST['depo_id'] : 0;
            $enabled = isset($_POST['enabled']) ? (int)$_POST['enabled'] === 1 : true;
            if ($storeId === '' || $depoId <= 0) { throw new \InvalidArgumentException('Mağaza veya depo eksik'); }
            $map = new \app\Models\StoreDepotMap();
            $ok = $map->setMapping($platform, $storeId, $depoId, $enabled);
            echo json_encode(['success'=>$ok]);
        } catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
    }

    public function runTrendyolGo()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $maxPages = isset($_POST['max_pages']) ? (int)$_POST['max_pages'] : 10;
            $perPage = isset($_POST['per_page']) ? (int)$_POST['per_page'] : 200;
            $threshold = isset($_POST['threshold']) ? (float)$_POST['threshold'] : 0.82;
            $svc = new ProductMatchService();
            $res = $svc->autoMatchTrendyolGo($maxPages, $perPage, $threshold);
            echo json_encode(['success'=>true] + $res);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function suggestionsTrendyolGo()
    {
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'Bad Request']);
            return;
        }
        header('Content-Type: application/json; charset=utf-8');
        try {
            $minScore = isset($_GET['min']) ? (float)$_GET['min'] : 0.70;
            $threshold = isset($_GET['thr']) ? (float)$_GET['thr'] : 0.82;
            $storeId = isset($_GET['store_id']) ? (string)$_GET['store_id'] : null;
            $svc = new ProductMatchService();
            $res = $svc->generateSuggestionsTrendyolGo($minScore, $threshold, 10, 200, $storeId);
            echo json_encode(['success'=>true] + $res);
        } catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
    }

    public function approve()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $ext = (string)($_POST['ext_urun_id'] ?? '');
            $code = (string)($_POST['candidate_code'] ?? '');
            $barcode = (string)($_POST['candidate_barcode'] ?? '');
            $score = isset($_POST['score']) ? (float)$_POST['score'] : null;
            $platform = (string)($_POST['platform'] ?? 'trendyolgo');
            if ($ext === '' || ($code === '' && $barcode === '')) { throw new \InvalidArgumentException('Eksik parametre'); }
            $map = new \app\Models\UrunEntegrasyonMap();
            $ok = $map->upsert([
                'urun_kodu' => $ext,
                'barkod' => $barcode ?: null,
                'trendyolgo_sku' => $code ?: null,
                'platform' => $platform,
                'match_confidence' => $score,
                'match_source' => 'rule',
                'manual_override' => 1,
                'last_matched_at' => date('Y-m-d H:i:s')
            ]);
            echo json_encode(['success'=>$ok]);
        } catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
    }

    // Tamsoft master ürün listesi (arama + sayfalama)
    public function tamsoftList()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            // DataTables parametreleri
            $draw = isset($_GET['draw']) ? (int)$_GET['draw'] : null;
            $start = isset($_GET['start']) ? max(0, (int)$_GET['start']) : 0;
            $length = isset($_GET['length']) ? max(10, (int)$_GET['length']) : 50;
            $searchVal = isset($_GET['search']['value']) ? trim((string)$_GET['search']['value']) : '';
            $q = isset($_GET['q']) ? trim((string)$_GET['q']) : $searchVal;
            $platform = isset($_GET['platform']) ? (string)$_GET['platform'] : 'trendyolgo';
            $unmatched = isset($_GET['unmatched']) && (int)$_GET['unmatched'] === 1;
            $page = ($draw !== null) ? ((int)floor($start / $length) + 1) : (isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1);
            $per = ($draw !== null) ? $length : (isset($_GET['per']) ? max(10, (int)$_GET['per']) : 50);
            $offset = ($page - 1) * $per;
            // PDO erişimi
            $repo = new \app\Models\TamsoftStockRepo();
            $ref = new \ReflectionClass($repo);
            $prop = $ref->getParentClass()->getProperty('db');
            $prop->setAccessible(true);
            /** @var \PDO $db */
            $db = $prop->getValue($repo);
            $where = 'WHERE aktif = 1';
            $params = [];
            if ($q !== '') {
                $where .= ' AND (ext_urun_id = :qe OR barkod = :qb OR urun_adi LIKE :q)';
                $params[':qe'] = $q; // kod birebir
                $params[':qb'] = $q; // barkod birebir
                $params[':q'] = '%' . $q . '%'; // isimde içerir
            }
            if ($unmatched) {
                $where .= ' AND NOT EXISTS (SELECT 1 FROM urun_entegrasyon_map m WHERE (m.platform IS NULL OR m.platform = :pf) AND (m.urun_kodu = tamsoft_urunler.ext_urun_id OR (m.barkod IS NOT NULL AND m.barkod = tamsoft_urunler.barkod)))';
                $params[':pf'] = $platform;
            }
            $stmt = $db->prepare("SELECT SQL_CALC_FOUND_ROWS id, ext_urun_id, barkod, urun_adi FROM tamsoft_urunler {$where} ORDER BY urun_adi ASC LIMIT :off, :per");
            foreach ($params as $k=>$v) { $stmt->bindValue($k, $v); }
            $stmt->bindValue(':off', (int)$offset, \PDO::PARAM_INT);
            $stmt->bindValue(':per', (int)$per, \PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $filtered = (int)$db->query('SELECT FOUND_ROWS()')->fetchColumn();
            $totalAll = (int)$db->query("SELECT COUNT(*) FROM tamsoft_urunler WHERE aktif=1")->fetchColumn();
            if ($draw !== null) {
                echo json_encode([
                    'draw' => $draw,
                    'recordsTotal' => $totalAll,
                    'recordsFiltered' => ($q !== '' || $unmatched ? $filtered : $totalAll),
                    'data' => $items
                ]);
            } else {
                echo json_encode(['success'=>true,'items'=>$items,'page'=>$page,'per'=>$per,'total'=>($q!==''||$unmatched?$filtered:$totalAll)]);
            }
        } catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
    }

    // Trendyol GO ürün listesi (store bazlı arama + sayfalama)
    public function trendyolList()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $storeId = isset($_GET['store_id']) ? (string)$_GET['store_id'] : '';
            $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
            $platform = isset($_GET['platform']) ? (string)$_GET['platform'] : 'trendyolgo';
            $unmatched = isset($_GET['unmatched']) && (int)$_GET['unmatched'] === 1;
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $per = isset($_GET['per']) ? max(10, (int)$_GET['per']) : 50;
            if ($storeId === '') {
                $draw = isset($_GET['draw']) ? (int)$_GET['draw'] : null;
                if ($draw !== null) { echo json_encode(['draw'=>$draw,'recordsTotal'=>0,'recordsFiltered'=>0,'data'=>[]]); }
                else { echo json_encode(['success'=>true,'items'=>[],'page'=>$page,'per'=>$per,'total'=>0]); }
                return;
            }
            $model = new \app\Models\TrendyolUrun();
            list($recordsTotal, $recordsFiltered) = $model->countByStoreTotals($storeId, $q);
            $orderBy = 'title'; $orderDir = 'ASC';
            $list = $model->listByStore($storeId, $q, $page, $per, $orderBy, $orderDir);
            $items = $list['items'] ?? [];
            if ($unmatched) {
                $pdoRepo = new \app\Models\TamsoftStockRepo();
                $r = new \ReflectionClass($pdoRepo);
                $p = $r->getParentClass()->getProperty('db');
                $p->setAccessible(true);
                /** @var \PDO $db2 */
                $db2 = $p->getValue($pdoRepo);
                $stmt = $db2->prepare("SELECT 1 FROM urun_entegrasyon_map m WHERE (m.platform IS NULL OR m.platform = :pf) AND ((m.trendyolgo_sku IS NOT NULL AND m.trendyolgo_sku = :sku) OR (m.barkod IS NOT NULL AND m.barkod = :bc) OR (m.urun_kodu IS NOT NULL AND m.urun_kodu = :code)) LIMIT 1");
                $tmp = [];
                foreach ($items as $row) {
                    $sku = (string)($row['sku'] ?? '');
                    $bc = (string)($row['barcode'] ?? '');
                    $code = (string)($row['stock_code'] ?? ($row['sku'] ?? ''));
                    $stmt->execute([':pf'=>$platform, ':sku'=>$sku, ':bc'=>$bc, ':code'=>$code]);
                    if (!$stmt->fetchColumn()) { $tmp[] = $row; }
                }
                $items = $tmp;
            }
            $draw = isset($_GET['draw']) ? (int)$_GET['draw'] : null;
            if ($draw !== null) {
                echo json_encode([
                    'draw' => $draw,
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $unmatched ? count($items) : $recordsFiltered,
                    'data' => $items
                ]);
            } else {
                echo json_encode(['success'=>true,'items'=>$items,'page'=>$page,'per'=>$per,'total'=>$unmatched?count($items):$recordsFiltered]);
            }
        } catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
    }

    // Otomatik analiz: öneri listesi üret (mağaza bazlı)
    public function analyzeTrendyolGo()
    {
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'Bad Request']);
            return;
        }
        header('Content-Type: application/json; charset=utf-8');
        try {
            $storeId = isset($_GET['store_id']) ? (string)$_GET['store_id'] : '';
            $mode = isset($_GET['mode']) ? (string)$_GET['mode'] : 'mixed';
            $svc = new ProductMatchService();
            if ($mode === 'exact') {
                $res = $svc->generateExactMatchesTrendyolGo($storeId ?: null, 10, 200);
            } else {
                $minScore = isset($_GET['min']) ? (float)$_GET['min'] : 0.70;
                $threshold = isset($_GET['thr']) ? (float)$_GET['thr'] : 0.82;
                $res = $svc->generateSuggestionsTrendyolGo($minScore, $threshold, 10, 200, $storeId ?: null);
            }
            echo json_encode(['success'=>true] + $res);
        } catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
    }

    // Tek Tamsoft ürünü için Trendyol GO tarafında barkod ve kod ile aday önizleme
    public function previewSingle()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $storeId = isset($_GET['store_id']) ? (string)$_GET['store_id'] : '';
            $ext = isset($_GET['ext_urun_id']) ? (string)$_GET['ext_urun_id'] : '';
            if ($storeId === '' || $ext === '') { echo json_encode(['success'=>false,'error'=>'missing_params']); return; }
            // Tamsoft ürün bilgisi
            $repo = new \app\Models\TamsoftStockRepo();
            $r = new \ReflectionClass($repo);
            $p = $r->getParentClass()->getProperty('db');
            $p->setAccessible(true);
            /** @var \PDO $db */
            $db = $p->getValue($repo);
            $st = $db->prepare('SELECT ext_urun_id, barkod, urun_adi FROM tamsoft_urunler WHERE ext_urun_id = :e LIMIT 1');
            $st->execute([':e'=>$ext]);
            $u = $st->fetch(\PDO::FETCH_ASSOC) ?: [];
            $barcode = (string)($u['barkod'] ?? '');
            $code = $ext;
            // Trendyol eşleşme: barcode veya sku/stock_code (tam eşitlik)
            $sql = "SELECT barcode, title, brand_name, sku, stock_code, image_url FROM trendyol_urunler WHERE store_id = :sid AND ((:bc <> '' AND barcode = :bc) OR (:cd <> '' AND (sku = :cd OR stock_code = :cd))) LIMIT 50";
            $stm = $db->prepare($sql);
            $stm->execute([':sid'=>$storeId, ':bc'=>$barcode, ':cd'=>$code]);
            $items = $stm->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            // Skor/öncelik: barcode tam eşleşme öne
            $scored = [];
            foreach ($items as $row) {
                $score = 0.0;
                if ($barcode !== '' && (string)($row['barcode'] ?? '') === $barcode) { $score = 1.0; }
                elseif ($code !== '' && ((string)($row['sku'] ?? '') === $code || (string)($row['stock_code'] ?? '') === $code)) { $score = 0.95; }
                $scored[] = [
                    'candidate_barcode' => $row['barcode'] ?? null,
                    'candidate_code' => $row['sku'] ?? ($row['stock_code'] ?? null),
                    'candidate_title' => $row['title'] ?? null,
                    'brand' => $row['brand_name'] ?? null,
                    'image_url' => $row['image_url'] ?? null,
                    'score' => $score
                ];
            }
            usort($scored, function($a,$b){ return ($b['score'] <=> $a['score']); });
            echo json_encode(['success'=>true, 'tamsoft'=>[ 'ext_urun_id'=>$ext, 'barkod'=>$barcode, 'urun_adi'=>$u['urun_adi'] ?? null ], 'items'=>$scored]);
        } catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
    }

    // Toplu onay
    public function approveBatch()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            // JSON body desteği
            $raw = file_get_contents('php://input');
            $json = null;
            if ($raw) {
                $dec = json_decode($raw, true);
                if (is_array($dec)) { $json = $dec; }
            }
            $items = [];
            $platform = 'trendyolgo';
            if ($json) {
                $items = isset($json['items']) && is_array($json['items']) ? $json['items'] : [];
                $platform = isset($json['platform']) ? (string)$json['platform'] : 'trendyolgo';
            } else {
                $items = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : [];
                $platform = (string)($_POST['platform'] ?? 'trendyolgo');
            }
            $storeId = isset($json['store_id']) ? (string)$json['store_id'] : ((string)($_POST['store_id'] ?? ''));
            $map = new \app\Models\UrunEntegrasyonMap();
            $ok = 0; $fail = 0;
            foreach ($items as $it) {
                $ext = (string)($it['ext_urun_id'] ?? '');
                $code = (string)($it['candidate_code'] ?? '');
                $barcode = (string)($it['candidate_barcode'] ?? '');
                $score = isset($it['score']) ? (float)$it['score'] : null;
                if ($ext === '' || ($code === '' && $barcode === '')) { $fail++; continue; }
                $r = $map->upsert([
                    'urun_kodu' => $ext,
                    'barkod' => $barcode ?: null,
                    'trendyolgo_sku' => $code ?: null,
                    'platform' => $platform,
                    'store_id' => $storeId ?: null,
                    'match_confidence' => $score,
                    'match_source' => 'rule',
                    'manual_override' => 0,
                    'last_matched_at' => date('Y-m-d H:i:s')
                ]);
                if ($r) $ok++; else $fail++;
            }
            echo json_encode(['success'=>true,'ok'=>$ok,'fail'=>$fail]);
        } catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
    }

    public function pushSingle()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $ext = (string)($_POST['ext_urun_id'] ?? '');
            $storeId = (string)($_POST['store_id'] ?? '');
            if ($ext === '' || $storeId === '') { throw new \InvalidArgumentException('Eksik parametre'); }
            $svc = new \app\Services\FastMarketSyncService();
            $res = $svc->pushTrendyolGoSingle($ext, $storeId);
            echo json_encode(['success'=>!empty($res['success'])] + $res);
        } catch (\Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
    }
}


