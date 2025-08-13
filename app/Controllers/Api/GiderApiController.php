<?php
namespace app\Controllers\Api;

use app\Models\Gider;
use app\Models\Kullanici;
use app\Services\ActivityNotifier;
use core\Controller;

class GiderApiController extends Controller {
    
    private $giderModel;
    
    public function __construct() {
        $this->giderModel = new Gider();
        
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
     * Tüm gider kayıtlarını getir
     */
    public function getGiderListesi() {
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
            
            // Kullanıcının mağaza ID'sini al
            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($_SESSION['user_id']);
            $magaza_id = $kullanici ? $kullanici['magaza_id'] : null;
            
            $giderListesi = $this->giderModel->getAll($magaza_id);
            
            $response = [
                'success' => true,
                'data' => $giderListesi,
                'count' => count($giderListesi),
                'timestamp' => time(),
                'message' => 'Gider listesi başarıyla getirildi'
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
     * Tek bir gider kaydını getir
     */
    public function getGider($id) {
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
            
            // Kullanıcının mağaza ID'sini al
            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($_SESSION['user_id']);
            $magaza_id = $kullanici ? $kullanici['magaza_id'] : null;
            
            $gider = $this->giderModel->get($id);
            
            // Gider kullanıcının mağazasına ait mi kontrol et
            if ($gider && $magaza_id && $gider['magaza_id'] != $magaza_id) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bu gider kaydına erişim izniniz yok',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (!$gider) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Gider kaydı bulunamadı',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $response = [
                'success' => true,
                'data' => $gider,
                'timestamp' => time(),
                'message' => 'Gider kaydı başarıyla getirildi'
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
     * Gider kaydı ekle
     */
    public function addGider() {
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
            
            // Veri doğrulama
            $requiredFields = ['baslik', 'tarih', 'miktar'];
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
            
            // Kullanıcının mağaza ID'sini al
            if (!isset($input['magaza_id'])) {
                // Session'dan kullanıcı bilgilerini al
                $kullaniciModel = new Kullanici();
                $kullanici = $kullaniciModel->get($_SESSION['user_id']);
                if ($kullanici) {
                    $input['magaza_id'] = $kullanici['magaza_id'];
                } else {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Kullanıcı mağaza bilgisi bulunamadı',
                        'timestamp' => time()
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }
            
            // Para birimi formatlaması
            $input['miktar'] = $this->formatMoney($input['miktar']);
            
            // Eksik alanları varsayılan değerlerle doldur
            $input['aciklama'] = $input['aciklama'] ?? '';
            $input['kategori'] = $input['kategori'] ?? 'Genel';
            $input['gorsel'] = $input['gorsel'] ?? null;
            
            $result = $this->giderModel->create($input);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Gider kaydı başarıyla eklendi',
                    'timestamp' => time()
                ];

                try {
                    $userId = $_SESSION['user_id'] ?? null;
                    if ($userId) {
                        (new ActivityNotifier())->recordAndNotify((int)$userId, 'create', 'gider', null, [
                            'magaza_id' => $input['magaza_id'] ?? null,
                            'tarih' => $input['tarih'] ?? null,
                            'miktar' => $input['miktar'] ?? null
                        ]);
                    }
                } catch (\Throwable $t) { error_log('Gider add notify/log error: ' . $t->getMessage()); }
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'Gider kaydı eklenirken hata oluştu',
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
     * Gider kaydını güncelle
     */
    public function updateGider($id) {
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
            
            // Kullanıcının mağaza ID'sini al
            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($_SESSION['user_id']);
            $magaza_id = $kullanici ? $kullanici['magaza_id'] : null;
            
            // Gider kaydını kontrol et
            $gider = $this->giderModel->get($id);
            if ($gider && $magaza_id && $gider['magaza_id'] != $magaza_id) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bu gider kaydını güncelleme izniniz yok',
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
            
            // Para birimi formatlaması
            if (isset($input['miktar'])) {
                $input['miktar'] = $this->formatMoney($input['miktar']);
            }
            
            // Eksik alanları varsayılan değerlerle doldur
            if (!isset($input['aciklama'])) $input['aciklama'] = '';
            if (!isset($input['kategori'])) $input['kategori'] = 'Genel';
            
            $result = $this->giderModel->update($id, $input);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Gider kaydı başarıyla güncellendi',
                    'timestamp' => time()
                ];

                try {
                    $userId = $_SESSION['user_id'] ?? null;
                    if ($userId) {
                        (new ActivityNotifier())->recordAndNotify((int)$userId, 'update', 'gider', (int)$id, [
                            'degisen_alanlar' => array_keys($input)
                        ]);
                    }
                } catch (\Throwable $t) { error_log('Gider update notify/log error: ' . $t->getMessage()); }
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'Gider kaydı güncellenirken hata oluştu',
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
     * Gider kaydını sil
     */
    public function deleteGider($id) {
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
            
            // Kullanıcının mağaza ID'sini al
            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($_SESSION['user_id']);
            $magaza_id = $kullanici ? $kullanici['magaza_id'] : null;
            
            // Gider kaydını kontrol et
            $gider = $this->giderModel->get($id);
            if ($gider && $magaza_id && $gider['magaza_id'] != $magaza_id) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bu gider kaydını silme izniniz yok',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $result = $this->giderModel->delete($id);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Gider kaydı başarıyla silindi',
                    'timestamp' => time()
                ];

                try {
                    $userId = $_SESSION['user_id'] ?? null;
                    if ($userId) {
                        (new ActivityNotifier())->recordAndNotify((int)$userId, 'delete', 'gider', (int)$id);
                    }
                } catch (\Throwable $t) { error_log('Gider delete notify/log error: ' . $t->getMessage()); }
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'Gider kaydı silinirken hata oluştu',
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
     * Gider istatistiklerini getir
     */
    public function getGiderStats() {
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
            
            // Kullanıcının mağaza ID'sini al
            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($_SESSION['user_id']);
            $magaza_id = $kullanici ? $kullanici['magaza_id'] : null;
            
            $stats = [
                'bugun' => $this->giderModel->getTodayTotal($magaza_id),
                'bu_ay' => $this->giderModel->getMonthlyTotal($magaza_id),
                'bu_yil' => $this->giderModel->getYearlyTotal($magaza_id),
                'toplam' => $this->giderModel->getTotal($magaza_id)
            ];
            
            $response = [
                'success' => true,
                'data' => $stats,
                'timestamp' => time(),
                'message' => 'Gider istatistikleri başarıyla getirildi'
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
     * Para birimi formatlama yardımcı fonksiyonu
     */
    private function formatMoney($value) {
        if (empty($value) || $value == 0) return 0;
        
        // Türkçe para formatını temizle (1.234,56 -> 1234.56)
        $cleanValue = $value;
        if (is_string($value)) {
            $cleanValue = str_replace(['₺', ' ', '.'], '', $value); // Sembolleri ve boşlukları kaldır
            $cleanValue = str_replace(',', '.', $cleanValue); // Virgülü noktaya çevir
        }
        
        $floatValue = (float)$cleanValue;
        
        // NaN kontrolü
        if (is_nan($floatValue)) {
            return 0;
        }
        
        return $floatValue;
    }
} 