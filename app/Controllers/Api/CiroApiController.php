<?php
namespace app\Controllers\Api;

use app\Models\Kullanici\Ciro\CiroModel;
use core\Controller;

class CiroApiController extends Controller {
    
    private $ciroModel;
    
    public function __construct() {
        $this->ciroModel = new CiroModel();
        
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
     * Tüm ciro kayıtlarını getir
     */
    public function getCiroListesi() {
        try {
            $ciroListesi = $this->ciroModel->ciroListele();
            
            $response = [
                'success' => true,
                'data' => $ciroListesi,
                'count' => count($ciroListesi),
                'timestamp' => time(),
                'message' => 'Ciro listesi başarıyla getirildi'
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
     * Tek bir ciro kaydını getir
     */
    public function getCiro($id) {
        try {
            $ciro = $this->ciroModel->ciroGetir($id);
            
            if (!$ciro) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Ciro kaydı bulunamadı',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $response = [
                'success' => true,
                'data' => $ciro,
                'timestamp' => time(),
                'message' => 'Ciro kaydı başarıyla getirildi'
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
     * Ciro kaydı ekle
     */
    public function addCiro() {
        try {
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
            
            // Veri doğrulama
            $requiredFields = ['magaza_id', 'gun', 'nakit', 'kredi_karti'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => "Gerekli alan eksik: $field",
                        'timestamp' => time()
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }
            
            // Para birimi formatlaması
            $moneyFields = ['nakit', 'kredi_karti', 'carliston', 'getir_carsi', 'trendyolgo', 
                           'multinet', 'sodexo', 'edenred', 'setcard', 'tokenflex', 
                           'iwallet', 'metropol', 'ticket', 'didi', 'toplam'];
            
            foreach ($moneyFields as $field) {
                if (isset($input[$field])) {
                    $input[$field] = $this->formatMoney($input[$field]);
                }
            }
            
            $input['ekleme_tarihi'] = date('Y-m-d');
            
            $result = $this->ciroModel->ciroEkle($input);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Ciro kaydı başarıyla eklendi',
                    'timestamp' => time()
                ];
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'Ciro kaydı eklenirken hata oluştu',
                    'timestamp' => time()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Kayıt hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Ciro kaydını güncelle
     */
    public function updateCiro($id) {
        try {
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
            
            // Para birimi formatlaması
            $moneyFields = ['nakit', 'kredi_karti', 'carliston', 'getir_carsi', 'trendyolgo', 
                           'multinet', 'sodexo', 'edenred', 'setcard', 'tokenflex', 
                           'iwallet', 'metropol', 'ticket', 'didi', 'toplam'];
            
            foreach ($moneyFields as $field) {
                if (isset($input[$field])) {
                    $input[$field] = $this->formatMoney($input[$field]);
                }
            }
            
            $result = $this->ciroModel->ciroGuncelle($id, $input);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Ciro kaydı başarıyla güncellendi',
                    'timestamp' => time()
                ];
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'Ciro kaydı güncellenirken hata oluştu',
                    'timestamp' => time()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Güncelleme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Ciro kaydını sil
     */
    public function deleteCiro($id) {
        try {
            $result = $this->ciroModel->ciroSil($id);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Ciro kaydı başarıyla silindi',
                    'timestamp' => time()
                ];
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'Ciro kaydı silinirken hata oluştu',
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
     * Mağaza listesini getir
     */
    public function getMagazalar() {
        try {
            $magazalar = $this->ciroModel->getMagazalar();
            
            $response = [
                'success' => true,
                'data' => $magazalar,
                'count' => count($magazalar),
                'timestamp' => time(),
                'message' => 'Mağaza listesi başarıyla getirildi'
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Mağaza listesi getirme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Para birimi formatlama yardımcı fonksiyonu
     */
    private function formatMoney($value) {
        if (empty($value) || $value == 0) return 0;
        
        // Türkçe para birimi formatını temizle
        $value = str_replace(['₺', 'TL', ' '], '', $value);
        $value = str_replace('.', '', $value); // Binlik ayırıcıyı kaldır
        $value = str_replace(',', '.', $value); // Virgülü noktaya çevir
        
        $result = floatval($value);
        
        // NaN kontrolü
        if (is_nan($result)) {
            return 0;
        }
        
        return $result;
    }
} 