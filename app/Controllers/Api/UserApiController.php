<?php
namespace app\Controllers\Api;

use app\Models\Kullanici;
use app\Models\Kullanici\Ciro\CiroModel;
use app\Models\Gider;
use app\Models\Kullanici\IsEmri\IsEmriModel;
use app\Models\System\ActivityLog;
use app\Models\Bildirim;
use app\Services\BildirimService;
use core\Controller;

class UserApiController extends Controller {
    
    private $kullaniciModel;
    private $ciroModel;
    private $giderModel;
    private $isEmriModel;
    
    public function __construct() {
        $this->kullaniciModel = new Kullanici();
        $this->ciroModel = new CiroModel();
        $this->giderModel = new Gider();
        $this->isEmriModel = new IsEmriModel();
        
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
     * Kullanıcı profil bilgilerini getir
     */
    public function getUserProfile() {
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
            
            $user = $this->kullaniciModel->get($_SESSION['user_id']);
            
            if (!$user) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı bulunamadı',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Hassas bilgileri gizle
            unset($user['sifre']);
            
            $response = [
                'success' => true,
                'data' => $user,
                'timestamp' => time(),
                'message' => 'Kullanıcı bilgileri başarıyla getirildi'
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Profil getirme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Kullanıcı profilini güncelle
     */
    public function updateUserProfile() {
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
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Geçersiz JSON verisi',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $userId = $_SESSION['user_id'];
            $result = $this->kullaniciModel->updateProfile($userId, $input);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Profil başarıyla güncellendi',
                    'timestamp' => time()
                ];

                // Aktivite log + admin bildirimi (best-effort)
                try {
                    $userId = $_SESSION['user_id'] ?? null;
                    if ($userId) {
                        // Activity Log
                        (new ActivityLog())->log($userId, 'update', 'profile', $userId, [
                            'degisen_alanlar' => array_keys($input)
                        ]);
                        // DB bildirimi (admin id=1)
                        (new Bildirim())->createForAdmin(
                            'Profil Güncellendi',
                            'Kullanıcı #' . $userId . ' profilini güncelledi.',
                            '/admin/bildirimler',
                            $userId
                        );
                        // Push bildirimi (OneSignal)
                        $admin = (new Kullanici())->get(1);
                        if ($admin) {
                            (new BildirimService())->tekBildirimGonder(
                                $admin,
                                'Profil Güncellendi',
                                'Kullanıcı #' . $userId . ' profilini güncelledi.',
                                'web',
                                '/admin/bildirimler'
                            );
                        }
                    }
                } catch (\Throwable $t) { error_log('Profile update notify/log error: ' . $t->getMessage()); }
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'Profil güncellenirken hata oluştu',
                    'timestamp' => time()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Profil güncelleme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Şifre değiştir
     */
    public function changePassword() {
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
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['current_password']) || !isset($input['new_password'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Mevcut şifre ve yeni şifre gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $userId = $_SESSION['user_id'];
            $result = $this->kullaniciModel->changePassword($userId, $input['current_password'], $input['new_password']);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Şifre başarıyla değiştirildi',
                    'timestamp' => time()
                ];

                // Aktivite log + admin bildirimi (best-effort)
                try {
                    $userId = $_SESSION['user_id'] ?? null;
                    if ($userId) {
                        (new ActivityLog())->log($userId, 'update', 'password', $userId, [ 'degistirildi' => true ]);
                        (new Bildirim())->createForAdmin(
                            'Şifre Değiştirildi',
                            'Kullanıcı #' . $userId . ' şifresini değiştirdi.',
                            '/admin/bildirimler',
                            $userId
                        );
                        $admin = (new Kullanici())->get(1);
                        if ($admin) {
                            (new BildirimService())->tekBildirimGonder(
                                $admin,
                                'Şifre Değiştirildi',
                                'Kullanıcı #' . $userId . ' şifresini değiştirdi.',
                                'web',
                                '/admin/bildirimler'
                            );
                        }
                    }
                } catch (\Throwable $t) { error_log('Password change notify/log error: ' . $t->getMessage()); }
            } else {
                http_response_code(400);
                $response = [
                    'success' => false,
                    'message' => 'Mevcut şifre yanlış veya güncelleme hatası',
                    'timestamp' => time()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Şifre değiştirme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Dashboard istatistiklerini getir
     */
    public function getDashboardStats() {
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
            
            $userId = $_SESSION['user_id'];
            $user = $this->kullaniciModel->get($userId);
            
            if (!$user) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı bulunamadı',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $magaza_id = $user['magaza_id'];
            
            // İstatistikleri topla
            $stats = [
                'bekleyenIsler' => $this->isEmriModel->getPendingCount($magaza_id),
                'aktifIsler' => $this->isEmriModel->getInProgressCount($magaza_id),
                'aylikGelir' => $this->ciroModel->getMonthlyTotal($magaza_id),
                'aylikGider' => $this->giderModel->getMonthlyTotal($magaza_id),
                'netCiro' => $this->ciroModel->getMonthlyTotal($magaza_id) - $this->giderModel->getMonthlyTotal($magaza_id),
                'toplamCiro' => $this->ciroModel->getTotal($magaza_id),
                'bugunGelir' => $this->ciroModel->getTodayTotal($magaza_id),
                'bugunGider' => $this->giderModel->getTodayTotal($magaza_id)
            ];
            
            $response = [
                'success' => true,
                'data' => $stats,
                'timestamp' => time(),
                'message' => 'Dashboard istatistikleri başarıyla getirildi'
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
    
    /**
     * Sistem durumunu kontrol et
     */
    public function getSystemStatus() {
        try {
            $status = [
                'database' => $this->checkDatabaseConnection(),
                'session' => isset($_SESSION['user_id']),
                'timestamp' => time(),
                'server_time' => date('Y-m-d H:i:s'),
                'php_version' => PHP_VERSION,
                'memory_usage' => memory_get_usage(true),
                'memory_limit' => ini_get('memory_limit')
            ];
            
            $response = [
                'success' => true,
                'data' => $status,
                'message' => 'Sistem durumu başarıyla getirildi'
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Sistem durumu hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Veritabanı bağlantısını kontrol et
     */
    private function checkDatabaseConnection() {
        try {
            $this->kullaniciModel->get(1); // Basit bir sorgu
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
} 