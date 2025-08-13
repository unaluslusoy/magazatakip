<?php

namespace app\Controllers\Admin;

use core\Controller;
use app\Middleware\AdminMiddleware;
use app\Models\GetirAyarlar;
use app\Models\GetirLog;

class GetirController extends Controller
{
    private GetirAyarlar $ayar;
    private GetirLog $log;

    public function __construct()
    {
        AdminMiddleware::handle();
        $this->ayar = new GetirAyarlar();
        $this->log = new GetirLog();
    }

    public function index()
    {
        $data = [ 'title' => 'GetirÇarşı', 'link' => 'Entegrasyonlar' ];
        $this->view('admin/getir/index', $data);
    }

    public function ayarlar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = $this->ayar->updateAyarlar([
                'base_url' => trim($_POST['base_url'] ?? ''),
                'merchant_id' => trim($_POST['merchant_id'] ?? ''),
                'api_key' => trim($_POST['api_key'] ?? ''),
                'api_secret' => trim($_POST['api_secret'] ?? ''),
                'client_id' => trim($_POST['client_id'] ?? ''),
                'client_secret' => trim($_POST['client_secret'] ?? ''),
                'webhook_secret' => trim($_POST['webhook_secret'] ?? ''),
                'token' => trim($_POST['token'] ?? ''),
                'webhook_api_key' => trim($_POST['webhook_api_key'] ?? ''),
                'enabled' => !empty($_POST['enabled']) ? 1 : 0,
                'schedule_minutes' => (int)($_POST['schedule_minutes'] ?? 0)
            ]);
            $_SESSION['alert_message'] = [ 'text' => $ok ? 'Ayarlar kaydedildi' : 'Kayıt hatası', 'icon' => $ok ? 'success' : 'error', 'confirmButtonText' => 'Tamam' ];
            header('Location: /admin/getir/ayarlar');
            exit;
        }
        $data = [ 'title' => 'GetirÇarşı Ayarları', 'link' => 'GetirÇarşı' ];
        $data['ayarlar'] = $this->ayar->getAyarlar();
        $this->view('admin/getir/ayarlar', $data);
    }

    public function generateWebhookKey()
    {
        $key = bin2hex(random_bytes(24));
        $ok = $this->ayar->updateAyarlar([ 'webhook_api_key' => $key ]);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([ 'success' => (bool)$ok, 'webhook_api_key' => $key ]);
    }

    public function loglar()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([ 'success' => true, 'logs' => $this->log->latest(100) ]);
    }

    public function logTemizle()
    {
        $ok = $this->log->clear();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([ 'success' => (bool)$ok ]);
    }
}


