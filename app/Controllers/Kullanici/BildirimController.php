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
        
        // Veritabanı bağlantısı
        $config = require 'config/database.php';
        $this->db = new \PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
            $config['username'],
            $config['password'],
            [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"]
        );
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
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
            'bildirimler' => $this->getKullaniciBildirimleri($kullaniciId, $page, $perPage, $durumFilter, $searchTerm),
            'bildirimSayilari' => $this->getBildirimSayilari($kullaniciId)
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

        $bildirim = $this->getBildirimDetay($id, $kullaniciId);
        if (!$bildirim) {
            $_SESSION['message'] = "Bildirim bulunamadı.";
            $_SESSION['message_type'] = 'error';
            header('Location: /kullanici/bildirimler');
            exit();
        }

        // Bildirimi okundu olarak işaretle
        $this->markAsRead($id, $kullaniciId);

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

        if ($this->markAsRead($id, $kullaniciId)) {
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
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM bildirimler WHERE hedef_kullanici_id = :kullanici_id AND okundu = 0");
            $stmt->execute([':kullanici_id' => $kullaniciId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'count' => (int)$result['count']]);
        } catch (\PDOException $e) {
            error_log("Okunmamış bildirim sayısı alınırken hata: " . $e->getMessage());
            echo json_encode(['success' => false, 'count' => 0]);
        }
    }

    private function getKullaniciBildirimleri($kullaniciId, $page = 1, $perPage = 10, $durumFilter = 'all', $searchTerm = '')
    {
        try {
            $offset = ($page - 1) * $perPage;
            $whereConditions = ["hedef_kullanici_id = :kullanici_id"];
            $params = [':kullanici_id' => $kullaniciId];

            // Durum filtresi
            if ($durumFilter !== 'all') {
                $whereConditions[] = "okundu = :okundu";
                $params[':okundu'] = ($durumFilter === 'okundu') ? 1 : 0;
            }

            // Arama terimi
            if (!empty($searchTerm)) {
                $whereConditions[] = "(baslik LIKE :search OR mesaj LIKE :search)";
                $params[':search'] = "%{$searchTerm}%";
            }

            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

            $sql = "SELECT b.*, k.ad as gonderici_adi, k.soyad as gonderici_soyadi 
                    FROM bildirimler b 
                    LEFT JOIN kullanicilar k ON b.kullanici_id = k.id 
                    {$whereClause} 
                    ORDER BY b.gonderim_tarihi DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Kullanıcı bildirimleri alınırken hata: " . $e->getMessage());
            return [];
        }
    }

    private function getBildirimSayilari($kullaniciId)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as toplam,
                        SUM(CASE WHEN okundu = 0 THEN 1 ELSE 0 END) as okunmamis,
                        SUM(CASE WHEN okundu = 1 THEN 1 ELSE 0 END) as okundu,
                        SUM(CASE WHEN DATE(gonderim_tarihi) = CURDATE() THEN 1 ELSE 0 END) as bugun
                    FROM bildirimler 
                    WHERE hedef_kullanici_id = :kullanici_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':kullanici_id' => $kullaniciId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Bildirim sayıları alınırken hata: " . $e->getMessage());
            return [
                'toplam' => 0,
                'okunmamis' => 0,
                'okundu' => 0,
                'bugun' => 0
            ];
        }
    }

    private function getBildirimDetay($id, $kullaniciId)
    {
        try {
            $stmt = $this->db->prepare("SELECT b.*, k.ad as gonderici_adi, k.soyad as gonderici_soyadi 
                                       FROM bildirimler b 
                                       LEFT JOIN kullanicilar k ON b.kullanici_id = k.id 
                                       WHERE b.id = :id AND b.hedef_kullanici_id = :kullanici_id");
            $stmt->execute(['id' => $id, 'kullanici_id' => $kullaniciId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Bildirim detayı alınırken hata: " . $e->getMessage());
            return false;
        }
    }

    private function markAsRead($id, $kullaniciId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE bildirimler SET okundu = 1, okunma_tarihi = NOW() WHERE id = :id AND hedef_kullanici_id = :kullanici_id");
            return $stmt->execute(['id' => $id, 'kullanici_id' => $kullaniciId]);
        } catch (\PDOException $e) {
            error_log("Bildirim okundu işaretlenirken hata: " . $e->getMessage());
            return false;
        }
    }
} 