<?php
namespace app\Controllers\Api;

use app\Models\Kullanici;
use core\Controller;

class CihazTokenController extends Controller {
    
    private $kullaniciModel;
    
    public function __construct() {
        $this->kullaniciModel = new Kullanici();
        
        // API için CORS headers
        // PWA standalone isteğinde çerezler için Origin kısıtla
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin && preg_match('#^https?://(www\.)?magazatakip\.com\.tr$#', $origin)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
        } else {
            header('Access-Control-Allow-Origin: https://magazatakip.com.tr');
        }
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json; charset=utf-8');
        
        // Cache prevention headers
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
        header('Pragma: no-cache');
        header('Expires: -1');
        
        // OPTIONS request için
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
    
    /**
     * Cihaz token'ını kaydet
     */
    public function saveDeviceToken() {
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
            
            // Gerekli alanları kontrol et
            $requiredFields = ['device_token', 'platform'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => "Gerekli alan eksik: {$field}",
                        'timestamp' => time()
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }
            
            $userId = $_SESSION['user_id'];
            $deviceToken = $input['device_token'];
            $platform = $input['platform'];
            $notificationPermission = isset($input['notification_permission']) ? (bool)$input['notification_permission'] : true;
            
            // Platform bilgisini standartlaştır
            $platform = $this->normalizePlatform($platform);
            
            // Kullanıcı bilgilerini güncelle
            $updateData = [
                'cihaz_token' => $deviceToken,
                'isletim_sistemi' => $platform,
                'bildirim_izni' => $notificationPermission ? 1 : 0
            ];
            
            $result = $this->kullaniciModel->update($userId, $updateData);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Cihaz token başarıyla kaydedildi',
                    'data' => [
                        'device_token' => $deviceToken,
                        'platform' => $platform,
                        'notification_permission' => $notificationPermission
                    ],
                    'timestamp' => time()
                ];
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'Token kaydedilirken hata oluştu',
                    'timestamp' => time()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Token kaydetme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Cihaz token'ını sil
     */
    public function removeDeviceToken() {
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
            
            // Token'ı temizle
            $updateData = [
                'cihaz_token' => null,
                'bildirim_izni' => 0
            ];
            
            $result = $this->kullaniciModel->update($userId, $updateData);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Cihaz token başarıyla silindi',
                    'timestamp' => time()
                ];
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'Token silinirken hata oluştu',
                    'timestamp' => time()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Token silme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Cihaz bilgilerini getir
     */
    public function getDeviceInfo() {
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
            
            $response = [
                'success' => true,
                'data' => [
                    'device_token' => $user['cihaz_token'],
                    'platform' => $user['isletim_sistemi'],
                    'notification_permission' => (bool)$user['bildirim_izni'],
                    'has_token' => !empty($user['cihaz_token'])
                ],
                'timestamp' => time()
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Cihaz bilgisi getirme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Bildirim iznini güncelle
     */
    public function updateNotificationPermission() {
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
            
            if (!isset($input['permission'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bildirim izni belirtilmedi',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $userId = $_SESSION['user_id'];
            $permission = (bool)$input['permission'];
            
            $updateData = [
                'bildirim_izni' => $permission ? 1 : 0
            ];
            
            $result = $this->kullaniciModel->update($userId, $updateData);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Bildirim izni güncellendi',
                    'data' => [
                        'notification_permission' => $permission
                    ],
                    'timestamp' => time()
                ];
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'İzin güncellenirken hata oluştu',
                    'timestamp' => time()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'İzin güncelleme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Platform bilgisini standartlaştır
     */
    private function normalizePlatform($platform) {
        $platform = strtolower(trim($platform));
        
        $platformMap = [
            'android' => 'Android',
            'ios' => 'iOS',
            'web' => 'Web',
            'desktop' => 'Desktop',
            'mobile' => 'Mobile',
            'tablet' => 'Tablet'
        ];
        
        return $platformMap[$platform] ?? ucfirst($platform);
    }

    /**
     * Debug: aktif kullanıcının token bilgisini JSON döndür
     */
    public function debug() {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı girişi gerekli',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $userId = (int)$_SESSION['user_id'];
            $user = $this->kullaniciModel->get($userId);
            if (!$user) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı bulunamadı'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'user_id' => $userId,
                    'email' => $user['email'] ?? null,
                    'device_token' => $user['cihaz_token'] ?? null,
                    'platform' => $user['isletim_sistemi'] ?? null,
                    'bildirim_izni' => isset($user['bildirim_izni']) ? (bool)$user['bildirim_izni'] : null,
                    'session_exists' => true
                ]
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Debug hata: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
