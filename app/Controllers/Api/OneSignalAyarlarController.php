<?php
namespace app\Controllers\Api;

use app\Models\OneSignalAyarlar;
use core\Controller;

class OneSignalAyarlarController extends Controller {
    
    private $oneSignalAyarlarModel;
    
    public function __construct() {
        $this->oneSignalAyarlarModel = new OneSignalAyarlar();
        
        // API için CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
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
     * OneSignal ayarlarını getir (sadece App ID)
     */
    public function getOneSignalConfig() {
        try {
            $appId = $this->oneSignalAyarlarModel->getAppId();
            
            if (!$appId) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'OneSignal App ID bulunamadı',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $response = [
                'success' => true,
                'data' => [
                    'app_id' => $appId,
                    'configured' => true
                ],
                'timestamp' => time(),
                'message' => 'OneSignal yapılandırması başarıyla getirildi'
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'OneSignal yapılandırması getirme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * OneSignal ayarlarının durumunu kontrol et
     */
    public function checkOneSignalStatus() {
        try {
            $ayarlarMevcut = $this->oneSignalAyarlarModel->ayarlarMevcut();
            $ayarlarGecerli = $this->oneSignalAyarlarModel->ayarlarGecerli();
            
            $response = [
                'success' => true,
                'data' => [
                    'configured' => $ayarlarMevcut,
                    'valid' => $ayarlarGecerli,
                    'app_id_available' => !empty($this->oneSignalAyarlarModel->getAppId()),
                    'api_key_available' => !empty($this->oneSignalAyarlarModel->getApiKey())
                ],
                'timestamp' => time(),
                'message' => 'OneSignal durumu kontrol edildi'
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'OneSignal durumu kontrol hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
