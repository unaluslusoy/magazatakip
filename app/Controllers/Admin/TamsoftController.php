<?php
namespace app\Controllers\Admin;

use core\Controller;
use app\Middleware\AdminMiddleware;
use app\Services\TamsoftService;
use app\Models\TamsoftAyarlar;

class TamsoftController extends Controller
{
    private TamsoftService $tamsoft;
    private TamsoftAyarlar $ayarlar;

    public function __construct()
    {
        AdminMiddleware::handle();
        $this->tamsoft = new TamsoftService();
        $this->ayarlar = new TamsoftAyarlar();
    }

    public function stokListesi()
    {
        $data = [ 'title' => 'Tamsoft Stok Listesi', 'link' => 'Tamsoft' ];

        $tarih = $_GET['tarih'] ?? '1900-01-01';
        $onlyPositive = isset($_GET['pozitif']) ? (bool)$_GET['pozitif'] : true;
        $useStok = isset($_GET['use_stok']) ? (bool)$_GET['use_stok'] : false;
        $lastBarcodeOnly = isset($_GET['last_barcode']) ? (bool)$_GET['last_barcode'] : false;
        $onlyEcommerce = isset($_GET['only_ecom']) ? (bool)$_GET['only_ecom'] : false;

        $selectedDepotId = isset($_GET['depoid']) && $_GET['depoid'] !== '' ? (int)$_GET['depoid'] : null;
        $stokData = $this->tamsoft->getEStokGrid($tarih, $onlyPositive, $useStok, $lastBarcodeOnly, $onlyEcommerce, $selectedDepotId);
        $data['depots'] = $stokData['depots'] ?? [];
        $data['items'] = $stokData['items'] ?? [];

        $this->view('admin/tamsoft/stok_listesi', $data);
    }

    // AJAX: Token testi
    public function tokenTest()
    {
        header('Content-Type: application/json; charset=utf-8');
        $result = $this->tamsoft->testToken();
        // Debug amaçlı son token isteği bilgisi
        if (!$result['success']) {
            $last = $_SESSION['palmiye_token_last'] ?? null;
            if ($last) {
                $result['last_request'] = $last;
            }
            $cfg = $this->tamsoft->getConfig();
            $result['config_used'] = [
                'base_url' => $cfg['base_url'],
                'alt_base_url' => $cfg['alt_base_url'],
                'username_set' => !empty($cfg['username']),
                'password_set' => !empty($cfg['password'])
            ];
        }
        echo json_encode($result);
    }

    // AJAX: Depo listesi
    public function depolar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $data = $this->tamsoft->getDepolar();
        $resp = [ 'success' => true, 'data' => $data ];
        if (empty($data)) { $resp['last_request'] = ($_SESSION['palmiye_depo_last'] ?? null); }
        echo json_encode($resp);
    }

    // AJAX: E-stok test (önizleme)
    public function estokPreview()
    {
        header('Content-Type: application/json; charset=utf-8');
        $depoId = isset($_GET['depoid']) ? (int)$_GET['depoid'] : 0;
        $tarih = $_GET['tarih'] ?? '1900-01-01';
        $onlyPositive = isset($_GET['pozitif']) ? (bool)$_GET['pozitif'] : true;
        if ($depoId <= 0) { echo json_encode(['success' => false, 'error' => 'invalid_depo']); return; }
        $res = $this->tamsoft->testEStok($depoId, $tarih, $onlyPositive);
        if (empty($res['preview'])) { $res['last_request'] = ($_SESSION['palmiye_estok_last'] ?? $_SESSION['palmiye_stok_last'] ?? null); }
        echo json_encode($res);
    }

    // Ayarlar sayfası GET/POST
    public function ayarlar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = $this->ayarlar->updateAyarlar([
                'base_url' => trim($_POST['base_url'] ?? ''),
                'alt_base_url' => trim($_POST['alt_base_url'] ?? ''),
                'username' => trim($_POST['username'] ?? ''),
                'password' => trim($_POST['password'] ?? ''),
                'enabled' => isset($_POST['enabled']) ? 1 : 0,
                'schedule_minutes' => (int)($_POST['schedule_minutes'] ?? 0)
            ]);
            $_SESSION['alert_message'] = [
                'text' => $ok ? 'Ayarlar kaydedildi' : 'Ayarlar kaydedilirken hata oluştu',
                'icon' => $ok ? 'success' : 'error',
                'confirmButtonText' => 'Tamam'
            ];
            header('Location: /admin/tamsoft/ayarlar');
            exit;
        }
        $data = [ 'title' => 'Tamsoft Entegrasyon Ayarları', 'link' => 'Tamsoft' ];
        $data['ayarlar'] = $this->ayarlar->getAyarlar();
        $this->view('admin/tamsoft/ayarlar', $data);
    }
}


