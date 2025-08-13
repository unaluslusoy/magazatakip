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
            $ok = $this->ayar->updateAyarlar([
                'api_key' => trim($_POST['api_key'] ?? ''),
                'satici_cari_id' => trim($_POST['satici_cari_id'] ?? ''),
                'entegrasyon_ref_kodu' => trim($_POST['entegrasyon_ref_kodu'] ?? ''),
                'api_secret' => trim($_POST['api_secret'] ?? ''),
                'token' => trim($_POST['token'] ?? ''),
                'enabled' => !empty($_POST['enabled']) ? 1 : 0,
            ]);
            $_SESSION['alert_message'] = [ 'text' => $ok ? 'Ayarlar kaydedildi' : 'Kayıt hatası', 'icon' => $ok ? 'success' : 'error', 'confirmButtonText' => 'Tamam' ];
            header('Location: /admin/trendyolgo/ayarlar');
            exit;
        }
        $data = [ 'title' => 'Trendyol Go Ayarları', 'link' => 'Trendyol Go' ];
        $data['ayarlar'] = $this->ayar->getAyarlar();
        $this->view('admin/trendyolgo/ayarlar', $data);
    }

    public function urunler()
    {
        $q = $_GET['q'] ?? '';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per = isset($_GET['per']) ? max(10, (int)$_GET['per']) : 50;
        $list = $this->svc->getProducts($q, $page, $per);
        $data = [ 'title' => 'Trendyol Go - Ürünler', 'link' => 'Trendyol Go' ];
        $data['search'] = $q;
        $data['page'] = $list['page'] ?? $page;
        $data['per'] = $list['per_page'] ?? $per;
        $data['total'] = $list['total'] ?? 0;
        $data['items'] = $list['items'] ?? [];
        $data['last_request'] = $list['last_request'] ?? null;
        $data['error'] = $list['error'] ?? null;
        $this->view('admin/trendyolgo/urunler', $data);
    }

    // Ürün içe aktarma tetikleme (önizleme ile birlikte, background simülasyonu)
    public function urunImportTrigger()
    {
        $jobId = (new TrendyolGoImportJob())->createJob();
        $preview = $this->svc->getProducts('', 1, 10);
        (new TrendyolGoImportJob())->updateJob($jobId, [
            'status' => 'running',
            'total' => $preview['total'] ?? 0,
            'processed' => 10,
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
        $data = [ 'title' => 'Trendyol Go - Siparişler', 'link' => 'Trendyol Go' ];
        $this->view('admin/trendyolgo/siparisler', $data);
    }

    public function iptaller()
    {
        $data = [ 'title' => 'Trendyol Go - İptaller', 'link' => 'Trendyol Go' ];
        $this->view('admin/trendyolgo/iptaller', $data);
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


