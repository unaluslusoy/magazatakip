<?php
namespace app\Controllers\Kullanici;

use core\Controller;
use app\Middleware\AuthMiddleware;
use app\Models\Bildirim;

class BildirimController extends Controller
{
    private $bildirimModel;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->bildirimModel = new Bildirim();
    }

    public function index()
    {
        $kullaniciId = $_SESSION['user_id'] ?? null;
        if (!$kullaniciId) {
            header('Location: /giris');
            exit();
        }

        $page = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 10;
        $durumFilter = $_GET['durum_filter'] ?? 'all';
        $searchTerm = $_GET['search'] ?? '';

        $data = [
            'title' => 'Bildirimlerim',
            'link' => 'Bildirimlerim',
            'page' => $page,
            'perPage' => $perPage,
            'durumFilter' => $durumFilter,
            'searchTerm' => $searchTerm,
            'bildirimler' => $this->bildirimModel->getKullaniciBildirimleriPaginated($kullaniciId, $page, $perPage, $durumFilter, $searchTerm),
            'bildirimSayilari' => $this->bildirimModel->getKullaniciBildirimSayilari($kullaniciId)
        ];

        $this->view('kullanici/bildirimler/index', $data);
    }

    public function detay($id)
    {
        $kullaniciId = $_SESSION['user_id'] ?? null;
        if (!$kullaniciId) {
            header('Location: /giris');
            exit();
        }

        $bildirim = $this->bildirimModel->getKullaniciBildirimDetay($id, $kullaniciId);
        if (!$bildirim) {
            $_SESSION['message'] = "Bildirim bulunamadı.";
            $_SESSION['message_type'] = 'error';
            header('Location: /kullanici/bildirimler');
            exit();
        }

        // Bildirimi okundu olarak işaretle
        $this->bildirimModel->markKullaniciBildirimAsRead($id, $kullaniciId);

        $data = [
            'title' => 'Bildirim Detayı',
            'link' => 'Bildirim Detayı',
            'bildirim' => $bildirim
        ];

        $this->view('kullanici/bildirimler/detay', $data);
    }

    public function markAsReadAjax($id)
    {
        $kullaniciId = $_SESSION['user_id'] ?? null;
        if (!$kullaniciId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Oturum geçersiz']);
            return;
        }

        if ($this->bildirimModel->markKullaniciBildirimAsRead($id, $kullaniciId)) {
            echo json_encode(['success' => true, 'message' => 'Bildirim okundu olarak işaretlendi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'İşlem başarısız']);
        }
    }

    public function getUnreadCount()
    {
        $kullaniciId = $_SESSION['user_id'] ?? null;
        if (!$kullaniciId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Oturum geçersiz']);
            return;
        }

        try {
            $count = $this->bildirimModel->getUnreadCount($kullaniciId);
            echo json_encode(['success' => true, 'count' => $count]);
        } catch (\Exception $e) {
            error_log("Okunmamış bildirim sayısı alınırken hata: " . $e->getMessage());
            echo json_encode(['success' => false, 'count' => 0]);
        }
    }
} 