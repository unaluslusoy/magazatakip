<?php
namespace app\Controllers\Api;

use app\Models\Kullanici\BildirimModel;
use core\Controller;

class BildirimApiController extends Controller {
    
    private $bildirimModel;
    
    public function __construct() {
        $this->bildirimModel = new BildirimModel();
        
        // API için CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Content-Type: application/json; charset=utf-8');
        
        // Cache prevention headers
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
        header('Pragma: no-cache');
        header('Expires: -1');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('ETag: "' . md5(time()) . '"');
        
        // OPTIONS request için
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
    
    /**
     * Tüm bildirimleri getir
     */
    public function getBildirimListesi() {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı girişi gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $bildirimListesi = $this->bildirimModel->getAll($_SESSION['user_id']);
            
            $response = [
                'success' => true,
                'data' => $bildirimListesi,
                'count' => count($bildirimListesi),
                'timestamp' => time(),
                'message' => 'Bildirim listesi başarıyla getirildi'
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Veri getirme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Tek bir bildirimi getir
     */
    public function getBildirim($id) {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı girişi gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $bildirim = $this->bildirimModel->get($id, $_SESSION['user_id']);
            
            if (!$bildirim) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bildirim bulunamadı',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $response = [
                'success' => true,
                'data' => $bildirim,
                'timestamp' => time(),
                'message' => 'Bildirim başarıyla getirildi'
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Veri getirme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Bildirimi okundu olarak işaretle
     */
    public function markAsRead($id) {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı girişi gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $result = $this->bildirimModel->markAsRead($id, $_SESSION['user_id']);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Bildirim okundu olarak işaretlendi',
                    'timestamp' => time()
                ];
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'Bildirim işaretlenirken hata oluştu',
                    'timestamp' => time()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'İşaretleme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Tüm bildirimleri okundu olarak işaretle
     */
    public function markAllAsRead() {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı girişi gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $result = $this->bildirimModel->markAllAsRead($_SESSION['user_id']);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Tüm bildirimler okundu olarak işaretlendi',
                    'timestamp' => time()
                ];
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'Bildirimler işaretlenirken hata oluştu',
                    'timestamp' => time()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'İşaretleme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Bildirimi sil
     */
    public function deleteBildirim($id) {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı girişi gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $result = $this->bildirimModel->delete($id, $_SESSION['user_id']);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Bildirim başarıyla silindi',
                    'timestamp' => time()
                ];
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'Bildirim silinirken hata oluştu',
                    'timestamp' => time()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Silme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Okunmamış bildirim sayısını getir
     */
    public function getUnreadCount() {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı girişi gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $count = $this->bildirimModel->getUnreadCount($_SESSION['user_id']);
            
            $response = [
                'success' => true,
                'data' => ['unread_count' => $count],
                'timestamp' => time(),
                'message' => 'Okunmamış bildirim sayısı başarıyla getirildi'
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Sayı getirme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Bildirim istatistiklerini getir
     */
    public function getBildirimStats() {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı girişi gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $stats = [
                'okunmamis' => $this->bildirimModel->getUnreadCount($_SESSION['user_id']),
                'okunmus' => $this->bildirimModel->getReadCount($_SESSION['user_id']),
                'toplam' => $this->bildirimModel->getTotalCount($_SESSION['user_id']),
                'bugun' => $this->bildirimModel->getTodayCount($_SESSION['user_id'])
            ];
            
            $response = [
                'success' => true,
                'data' => $stats,
                'timestamp' => time(),
                'message' => 'Bildirim istatistikleri başarıyla getirildi'
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'İstatistik getirme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
} 