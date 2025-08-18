<?php
namespace app\Controllers\Api;

use app\Models\Kullanici;
use app\Models\OneSignalAyarlar;
use core\Controller;

class TestBildirimController extends Controller {
    
    private $kullaniciModel;
    private $oneSignalAyarlarModel;
    
    public function __construct() {
        $this->kullaniciModel = new Kullanici();
        $this->oneSignalAyarlarModel = new OneSignalAyarlar();
        
        // Response tipi
        header('Content-Type: application/json; charset=utf-8');
        
        // Cache prevention headers
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
        header('Pragma: no-cache');
        header('Expires: -1');
        
        // OPTIONS ApiAuthMiddleware tarafından handle edilir
    }
    
    /**
     * Test bildirimi gönder
     */
    public function sendTestNotification() {
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
            $title = $input['title'] ?? 'Test Bildirimi';
            $message = $input['message'] ?? 'Bu bir test bildirimidir.';
            $url = $input['url'] ?? null;
            
            // Kullanıcı bilgilerini al
            $kullanici = $this->kullaniciModel->get($userId);
            
            if (!$kullanici) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı bulunamadı',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // v16: alias (external_id) destekleniyor; cihaz_token zorunlu olmayabilir
            
            // OneSignal ayarlarını al
            $ayarlar = $this->oneSignalAyarlarModel->getAyarlar();
            
            if (empty($ayarlar['onesignal_app_id']) || empty($ayarlar['onesignal_api_key'])) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'OneSignal ayarları eksik',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Test bildirimi gönder
            $result = $this->sendOneSignalNotification($kullanici, $title, $message, $url, $ayarlar);
            
            if ($result['success']) {
                $response = [
                    'success' => true,
                    'message' => 'Test bildirimi başarıyla gönderildi',
                    'data' => [
                        'notification_id' => $result['id'],
                        'user_id' => $userId,
                        'device_token' => $kullanici['cihaz_token'] ?? null,
                        'platform' => $kullanici['isletim_sistemi']
                    ],
                    'timestamp' => time()
                ];
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'Test bildirimi gönderilemedi: ' . $result['error'],
                    'timestamp' => time()
                ];
            }
            
            // Admin'e bildirim kaydı oluştur (id=1)
            try {
                $bildirimModel = new \app\Models\Bildirim();
                $bildirimModel->createForAdmin($title, $message, $url ?? '/', $_SESSION['user_id'] ?? null);
            } catch (\Exception $e) { /* sessiz geç */ }

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Test bildirimi hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Tüm kullanıcılara test bildirimi gönder
     */
    public function sendTestNotificationToAll() {
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
            
            $title = $input['title'] ?? 'Toplu Test Bildirimi';
            $message = $input['message'] ?? 'Bu bir toplu test bildirimidir.';
            $url = $input['url'] ?? null;
            
            // Bildirim izni olan kullanıcıları al
            $kullanicilar = $this->kullaniciModel->getBildirimIzinliKullanicilar();
            
            if (empty($kullanicilar)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bildirim izni olan kullanıcı bulunamadı',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // OneSignal ayarlarını al
            $ayarlar = $this->oneSignalAyarlarModel->getAyarlar();
            
            if (empty($ayarlar['onesignal_app_id']) || empty($ayarlar['onesignal_api_key'])) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'OneSignal ayarları eksik',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            // Her kullanıcıya bildirim gönder
            foreach ($kullanicilar as $kullanici) {
                if (!empty($kullanici['cihaz_token'])) {
                    $result = $this->sendOneSignalNotification($kullanici, $title, $message, $url, $ayarlar);
                    
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "Kullanıcı {$kullanici['email']}: " . $result['error'];
                    }
                }
            }
            
            $response = [
                'success' => true,
                'message' => "Toplu test bildirimi tamamlandı",
                'data' => [
                    'total_users' => count($kullanicilar),
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ],
                'timestamp' => time()
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Toplu test bildirimi hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * OneSignal API'ye bildirim gönder
     */
        private function sendOneSignalNotification($kullanici, $title, $message, $url, $ayarlar) {
        try {
            // v16: alias (external_id) öncelikli, yoksa player_ids
            $data = [
                'app_id' => $ayarlar['onesignal_app_id'],
                'headings' => ['tr' => $title],
                'contents' => ['tr' => $message],
                'data' => [
                    'kullanici_id' => $kullanici['id'],
                    'test' => true,
                    'url' => $url
                ]
            ];
            if (!empty($kullanici['id'])) {
                $data['include_aliases'] = [ 'external_id' => [ (string)$kullanici['id'] ] ];
                $data['channel_for_external_user_ids'] = 'push';
            } elseif (!empty($kullanici['cihaz_token'])) {
                $data['include_player_ids'] = [ $kullanici['cihaz_token'] ];
            }
            
            $url = 'https://onesignal.com/api/v1/notifications';
            
            $headers = [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Basic ' . $ayarlar['onesignal_api_key']
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                return ['success' => false, 'error' => 'cURL Hatası: ' . $error];
            }
            
            if ($httpCode < 200 || $httpCode >= 300) {
                return ['success' => false, 'error' => 'HTTP Kodu: ' . $httpCode . ' - Yanıt: ' . $response];
            }
            
            $result = json_decode($response, true);
            
            if (isset($result['success']) && $result['success']) {
                return ['success' => true, 'id' => $result['id'] ?? 'Bilinmiyor'];
            } else {
                return ['success' => false, 'error' => json_encode($result)];
            }
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
